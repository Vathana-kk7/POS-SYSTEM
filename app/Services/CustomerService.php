<?php
namespace App\Services;

use App\DTO\Customer\CreateCustomerDTO;
use App\DTO\Customer\UpdateCustomerDTO;
use App\Exports\CustomerExport;
use App\Imports\CustomerImport;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class CustomerService{
    private const STATS_CACHE_KEY = 'customer:stats:v1';

    public function __construct(
        private CustomerRepositoryInterface $repo
    ){}
    public function create(CreateCustomerDTO $dto)
    {
        $result=$this->repo->create([
            "name"=>$dto->name,
            "phone"=>$dto->phone,
            "address"=>$dto->address,
            "email"=>$dto->email,
            "group"=>$dto->group ?? "Standard",
            "status"=>$dto->status ?? "Active",
        ]);
        $this->clearStatsCache();
        return $result;
    }
    public function update(string $id,UpdateCustomerDTO $dto)
    {
        $result=$this->repo->update(
            $id,
            [
                "name"=>$dto->name,
                "phone"=>$dto->phone,
                "address"=>$dto->address,
                "email"=>$dto->email,
                "group"=>$dto->group ?? "Standard",
                "status"=>$dto->status ?? "Active",
            ]
        );
        $this->clearStatsCache();
        return $result;
    }
    public function all(int $perPage = 10, array $filters = [])
    {
        return $this->repo->all($perPage, $filters);
    }
    public function stats(): array
    {
        return Cache::store('redis')->remember(
            self::STATS_CACHE_KEY,
            300,
            fn () => $this->repo->stats()
        );
    }
    public function importCustomers(UploadedFile $file): array
    {
        $rows = Excel::toCollection(new CustomerImport(), $file)->first();
        $customers = $rows?->map(function ($row, $index) {
            return [
                'row_number' => $index + 2,
                'data' => $row->toArray(),
            ];
        })->filter(function ($customer) {
            $row = $customer['data'];
            $name = trim((string) ($row['name'] ?? $row['customer_name'] ?? ''));
            $phone = trim((string) ($row['phone'] ?? ''));

            return $name !== '' || $phone !== '';
        }) ?? collect();

        if ($customers->isEmpty()) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => []];
        }

        $records = [];
        $errors = [];
        $seenEmails = [];

        foreach ($customers as $customer) {
            $row = $customer['data'];
            $row['name'] = trim((string) ($row['name'] ?? $row['customer_name'] ?? ''));
            $row['email'] = isset($row['email']) && trim((string) $row['email']) !== ''
                ? trim((string) $row['email'])
                : null;
            $row['phone'] = trim((string) ($row['phone'] ?? ''));
            $row['address'] = isset($row['address']) && trim((string) $row['address']) !== ''
                ? trim((string) $row['address'])
                : null;

            $group = strtolower(trim((string) ($row['group'] ?? '')));
            $row['group'] = match ($group) {
                '', 'standard' => 'Standard',
                'vip' => 'VIP',
                'wholesale' => 'Wholesale',
                default => ucfirst($group),
            };

            $status = strtolower(trim((string) ($row['status'] ?? '')));
            $row['status'] = match ($status) {
                '', 'active' => 'Active',
                'inactive' => 'Inactive',
                default => ucfirst($status),
            };

            $validator = Validator::make($row, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['required', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:255'],
                'group' => ['required', 'in:Standard,VIP,Wholesale'],
                'status' => ['required', 'in:Active,Inactive'],
            ]);

            $rowNumber = $customer['row_number'];
            if ($row['email'] !== null) {
                $normalizedEmail = strtolower($row['email']);
                if (isset($seenEmails[$normalizedEmail])) {
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
                'email' => $row['email'],
                'phone' => $row['phone'],
                'address' => $row['address'],
                'group' => $row['group'],
                'status' => $row['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $emailsToCheck = collect($records)
            ->pluck('email')
            ->filter()
            ->map(fn ($email) => strtolower($email))
            ->unique()
            ->values()
            ->all();

        if ($emailsToCheck !== []) {
            $existingEmails = array_fill_keys($this->repo->existingEmails($emailsToCheck), true);
            $records = collect($records)->reject(function ($record) use (&$errors, &$existingEmails) {
                $email = $record['email'];
                if ($email === null || !isset($existingEmails[strtolower($email)])) {
                    return false;
                }

                $errors["row_{$record['_row_number']}.email"] = [
                    'This email already belongs to an existing customer.',
                ];
                return true;
            })->values()->all();
        }

        $skippedRowNumbers = [];
        foreach (array_keys($errors) as $errorKey) {
            if (preg_match('/^row_(\d+)\./', $errorKey, $matches)) {
                $skippedRowNumbers[$matches[1]] = true;
            }
        }

        DB::transaction(function () use ($records) {
            foreach (array_chunk($records, 500) as $recordChunk) {
                $cleanChunk = array_map(function ($record) {
                    unset($record['_row_number']);
                    return $record;
                }, $recordChunk);

                $this->repo->insertBulk($cleanChunk);
            }
        });

        if ($records !== []) {
            $this->clearStatsCache();
        }

        return [
            'imported' => count($records),
            'skipped' => count($skippedRowNumbers),
            'errors' => $errors,
        ];
    }
    public function exportExcel(array $filters = [])
    {
        return Excel::download(
            new CustomerExport($this->repo, $filters),
            'customers_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
    public function exportPdf(array $filters = [])
    {
        $customers = $this->repo->getForExport($filters);
        $tempDir = storage_path('app/mpdf');
        File::ensureDirectoryExists($tempDir);
        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'tempDir' => $tempDir,
        ]);
        $pdf->autoScriptToLang = true;
        $pdf->autoLangToFont = true;
        $pdf->WriteHTML(view('exports.customers-pdf', [
            'customers' => $customers,
            'date' => now()->format('d-m-Y H:i'),
        ])->render());

        return response($pdf->Output(
            'customers_' . now()->format('Y-m-d') . '.pdf',
            \Mpdf\Output\Destination::STRING_RETURN
        ), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="customers_' . now()->format('Y-m-d') . '.pdf"',
        ]);
    }
    public function clearStatsCache(): void
    {
        Cache::store('redis')->forget(self::STATS_CACHE_KEY);
    }
    public function findById(string $id)
    {
        return $this->repo->findById($id);
    }
    public function delete(string $id)
    {
        $result = $this->repo->delete($id);
        $this->clearStatsCache();
        return $result;
    }
}
