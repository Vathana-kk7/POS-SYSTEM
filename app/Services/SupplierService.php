<?php
namespace App\Services;

use App\DTO\Supplier\CreateSupplierDTO;
use App\DTO\Supplier\UpdateSupplierDTO;
use App\Exports\SupplierExport;
use App\Imports\SupplierImport;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SupplierService{
    private const STATS_CACHE_KEY = 'supplier:stats:v1';

    public function __construct(private SupplierRepositoryInterface $repo){}

    public function create(CreateSupplierDTO $dto){
        $data = [
            "name"=>$dto->name,
            "contact_person"=>$dto->contact_person,
            "email"=>$dto->email,
            "address"=>$dto->address,
            "phone"=>$dto->phone,
            "status"=>$dto->status,
            "bank_name"=>$dto->bank_name,
            "account_name"=>$dto->account_name,
            "account_number"=>$dto->account_number,
            "bakong_account_id"=>$dto->bakong_account_id,
        ];
        $imagePath = null;
        try {
            if ($dto->khqr_image) {
                $imagePath = $dto->khqr_image->store('suppliers/khqr', 'public');
                if (!$imagePath) {
                    throw new \RuntimeException('Unable to store supplier KHQR image.');
                }
                $data['khqr_image'] = $imagePath;
            }
            $result=$this->repo->create($data);
        } catch (\Throwable $exception) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $exception;
        }
        $this->clearStatsCache();
        return $result;
    }

    public function getAll(int $perPage = 10, array $filters = [])
    {
        return $this->repo->all($perPage, $filters);
    }
    public function getById($id){
        return $this->repo->findById($id);
    }
    public function update($id,UpdateSupplierDTO $dto){
        $supplier = $this->repo->findById($id);
        $data = [
            "name"=>$dto->name,
            "contact_person"=>$dto->contact_person,
            "email"=>$dto->email,
            "address"=>$dto->address,
            "phone"=>$dto->phone,
            "status"=>$dto->status,
            "bank_name"=>$dto->bank_name,
            "account_name"=>$dto->account_name,
            "account_number"=>$dto->account_number,
            "bakong_account_id"=>$dto->bakong_account_id,
        ];
        $newImagePath = null;
        try {
            $newImagePath = $dto->khqr_image?->store('suppliers/khqr', 'public');
            if ($dto->khqr_image && !$newImagePath) {
                throw new \RuntimeException('Unable to store supplier KHQR image.');
            }
            if ($newImagePath) {
                $data['khqr_image'] = $newImagePath;
            }
            $result = $this->repo->update($id, $data);
        } catch (\Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }
            throw $exception;
        }
        if ($newImagePath && $supplier->khqr_image) {
            Storage::disk('public')->delete($supplier->khqr_image);
        }
        $this->clearStatsCache();
        return $result;
    }
    public function delete(string $id){
        $result = $this->repo->delete($id);
        $this->clearStatsCache();
        return $result;
    }
    public function stats(): array
    {
        return Cache::store('redis')->remember(
            self::STATS_CACHE_KEY,
            300,
            fn () => $this->repo->stats()
        );
    }
    public function clearStatsCache(): void
    {
        Cache::store('redis')->forget(self::STATS_CACHE_KEY);
    }
    public function importSuppliers(UploadedFile $file): array
    {
        $rows = Excel::toCollection(new SupplierImport(), $file)->first();
        $suppliers = $rows?->map(fn ($row, $index) => [
            'row_number' => $index + 2,
            'data' => $row->toArray(),
        ])->filter(function ($supplier) {
            $row = $supplier['data'];
            return trim((string) ($row['name'] ?? $row['supplier_name'] ?? '')) !== ''
                || trim((string) ($row['phone'] ?? '')) !== '';
        }) ?? collect();

        $records = [];
        $errors = [];
        $seenEmails = [];

        foreach ($suppliers as $supplier) {
            $row = $supplier['data'];
            $row['name'] = trim((string) ($row['name'] ?? $row['supplier_name'] ?? ''));
            $row['contact_person'] = trim((string) ($row['contact_person'] ?? '')) ?: null;
            $row['email'] = trim((string) ($row['email'] ?? '')) ?: null;
            $row['address'] = trim((string) ($row['address'] ?? ''));
            $row['phone'] = trim((string) ($row['phone'] ?? ''));
            $status = strtolower(trim((string) ($row['status'] ?? '')));
            $row['status'] = match ($status) {
                '', 'active' => 'Active',
                'inactive' => 'Inactive',
                default => ucfirst($status),
            };

            $validator = Validator::make($row, [
                'name' => ['required', 'string', 'max:255'],
                'contact_person' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'address' => ['nullable', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:30'],
                'status' => ['required', 'in:Active,Inactive'],
            ]);

            $rowNumber = $supplier['row_number'];
            if ($row['email'] !== null) {
                $email = strtolower($row['email']);
                if (isset($seenEmails[$email])) {
                    $validator->errors()->add('email', 'Email is duplicated in this file.');
                }
            }

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $errors["row_{$rowNumber}.{$field}"] = $messages;
                }
                continue;
            }

            if ($row['email'] !== null) {
                $seenEmails[strtolower($row['email'])] = true;
            }

            $records[] = [
                '_row_number' => $rowNumber,
                'name' => $row['name'],
                'contact_person' => $row['contact_person'],
                'email' => $row['email'],
                'address' => $row['address'],
                'phone' => $row['phone'],
                'status' => $row['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $emails = collect($records)->pluck('email')->filter()->map(fn ($email) => strtolower($email))->unique()->values()->all();
        if ($emails !== []) {
            $existing = array_fill_keys($this->repo->existingEmails($emails), true);
            $records = collect($records)->reject(function ($record) use (&$errors, $existing) {
                if ($record['email'] === null || !isset($existing[strtolower($record['email'])])) {
                    return false;
                }

                $errors["row_{$record['_row_number']}.email"] = ['This email already belongs to a supplier.'];
                return true;
            })->values()->all();
        }

        DB::transaction(function () use ($records) {
            foreach (array_chunk($records, 500) as $chunk) {
                $chunk = array_map(function ($record) {
                    unset($record['_row_number']);
                    return $record;
                }, $chunk);
                $this->repo->insertBulk($chunk);
            }
        });

        if ($records !== []) {
            $this->clearStatsCache();
        }

        $skippedRows = [];
        foreach (array_keys($errors) as $key) {
            if (preg_match('/^row_(\d+)\./', $key, $matches)) {
                $skippedRows[$matches[1]] = true;
            }
        }

        return [
            'imported' => count($records),
            'skipped' => count($skippedRows),
            'errors' => $errors,
        ];
    }
    public function exportExcel(array $filters = [])
    {
        return Excel::download(
            new SupplierExport($this->repo, $filters),
            'suppliers_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
    public function exportPdf(array $filters = [])
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $tempDir = storage_path('app/mpdf');
        File::ensureDirectoryExists($tempDir);
        $filePath = tempnam($tempDir, 'supplier-export-');
        if ($filePath === false) {
            throw new \RuntimeException('Unable to create temporary supplier PDF file.');
        }

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        try {
            $pdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'fontDir' => array_merge($defaultConfig['fontDir'], [
                    storage_path('fonts'),
                ]),
                'fontdata' => $defaultFontConfig['fontdata'] + [
                    'notokhmer' => [
                        'R' => 'NotoSansKhmer-Regular.ttf',
                        'B' => 'NotoSansKhmer-Regular.ttf',
                        'useOTL' => 0xFF,
                    ],
                ],
                'default_font' => 'notokhmer',
                'tempDir' => $tempDir,
                'simpleTables' => true,
                'packTableData' => true,
            ]);
            $pdf->autoScriptToLang = true;
            $pdf->autoLangToFont = true;
            $pdf->WriteHTML(view('exports.suppliers-pdf', [
                'date' => now()->format('d-m-Y H:i'),
            ])->render());

            $rowNumber = 0;
            $query = $this->repo->getForPdfExport($filters);
            $query->chunkById(250, function ($suppliers) use ($pdf, &$rowNumber) {
                $pdf->WriteHTML(view('exports.partials.suppliers-pdf-table', [
                    'suppliers' => $suppliers,
                    'startNumber' => $rowNumber,
                ])->render());
                $rowNumber += $suppliers->count();
            }, 'id');

            if ($rowNumber === 0) {
                $pdf->WriteHTML(view('exports.partials.suppliers-pdf-table', [
                    'suppliers' => collect(),
                    'startNumber' => 0,
                ])->render());
            }

            $pdf->WriteHTML('</body></html>');
            $pdf->Output($filePath, \Mpdf\Output\Destination::FILE);

            return response()->download(
                $filePath,
                'suppliers_' . now()->format('Y-m-d') . '.pdf',
                ['Content-Type' => 'application/pdf']
            )->deleteFileAfterSend(true);
        } catch (\Throwable $exception) {
            if (is_file($filePath)) {
                unlink($filePath);
            }

            throw $exception;
        }
    }
}
