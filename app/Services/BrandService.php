<?php

namespace App\Services;

use App\DTO\Brand\CreateBrandDTO;
use App\DTO\Brand\ImportBrandDTO;
use App\DTO\Brand\UpdateBrandDTO;
use App\Exports\BrandsExport;
use App\Imports\BrandsImport;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class BrandService
{
    public function __construct(
        private BrandRepositoryInterface $repo
    ) {}

    public function CreateBrand(CreateBrandDTO $dto)
    {
        $result = $this->repo->create([
            "name" => $dto->name,
            "status" => $dto->status,
        ]);

        $this->clearBrandCache();

        return $result;
    }

    public function getAllBrand($perPage, array $filters = [])
    {
        $page = request()->integer('page', 1);

        $version = Cache::get('brands_cache_version', 1);

        $cacheKey = 'brands:v' . $version . ':' . md5(
            json_encode([
                'page' => $page,
                'perPage' => $perPage,
                'filters' => $filters,
            ])
        );

        return Cache::remember(
            $cacheKey,
            300,
            function () use ($perPage, $filters) {
                return $this->repo->all(
                    $perPage,
                    $filters
                );
            }
        );
    }

    public function getBrandStats()
    {
        return Cache::remember('brands_stats', 86400, function () {
            return $this->repo->getBrandStats();
        });
    }

    public function getbrandById($id)
    {
        return Cache::remember("brand_by_id_{$id}", 86400, function () use ($id) {
            return $this->repo->findById($id);
        });
    }

    public function update($id, UpdateBrandDTO $dto)
    {
        $data = $this->repo->update(
            $id,
            [
                "name" => $dto->name,
                "status" => $dto->status
            ]
        );

        Cache::forget("brand_by_id_{$id}");
        $this->clearBrandCache();

        return $data;
    }

    public function delete(string $id)
    {
        $result = $this->repo->delete($id);

        Cache::forget("brand_by_id_{$id}");
        $this->clearBrandCache();

        return $result;
    }

    public function importBrands(UploadedFile $file): bool
    {
        $rows = Excel::toCollection(new BrandsImport, $file)->first();

        $brandsData = $rows
            ->filter(fn($row) => !empty($row['name']))
            ->map(fn($row) => ImportBrandDTO::fromExcelRow($row->toArray())->toArray())
            ->values()
            ->toArray();

        if (empty($brandsData)) {
            return false;
        }

        $saved = $this->repo->insertBulk($brandsData);

        if ($saved) {
            $this->clearBrandCache();
        }

        return $saved;
    }

    public function exportBrandsExcel(array $filters = [])
    {
        return Excel::download(
            new BrandsExport($filters),
            'brands_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportBrandsPdf(array $filters = [])
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '600');

            // 1. បង្កើត Directory និងផ្ដល់ Permission សម្រាប់ mPDF Temp Storage
            $tempDir = storage_path('app/mpdf');
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0777, true, true);
            }

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'tempDir' => $tempDir,
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'simpleTables' => true,
                'packTableData' => true,
            ]);

            // 2. Render Header HTML
            $headerHtml = view('exports.brands-pdf')->render();
            $pdf->WriteHTML($headerHtml);

            // 3. Fetch Data Safe Check
            $query = $this->repo->getForExport($filters);

            if ($query instanceof \Illuminate\Support\Collection || is_array($query)) {
                $brands = $query;
            } else if (is_object($query) && method_exists($query, 'cursor')) {
                $brands = $query->cursor();
            } else if (is_object($query) && method_exists($query, 'get')) {
                $brands = $query->get();
            } else {
                $brands = [];
            }

            $rowsHtml = '';
            $offset = 0;
            $chunkSize = 500;

            foreach ($brands as $brand) {
                $offset++;
                $brandName = e($brand->name ?? '');
                $statusVal = $brand->status ?? 'inactive';
                $statusClass = strtolower($statusVal) === 'active' ? 'active' : 'inactive';
                $statusText = e(ucfirst($statusVal));

                $createdAt = 'N/A';
                if (!empty($brand->created_at)) {
                    $createdAt = is_string($brand->created_at)
                        ? $brand->created_at
                        : $brand->created_at->format('Y-m-d H:i');
                }

                $rowsHtml .= "
                    <tr>
                        <td style=\"text-align: center;\">{$offset}</td>
                        <td>{$brandName}</td>
                        <td style=\"text-align: center;\" class=\"{$statusClass}\">{$statusText}</td>
                        <td>{$createdAt}</td>
                    </tr>
                ";

                // បញ្ជូន 500 rows ចូល mPDF រួច clear RAM
                if ($offset % $chunkSize === 0) {
                    $pdf->WriteHTML($rowsHtml);
                    $rowsHtml = '';
                }
            }

            if (!empty($rowsHtml)) {
                $pdf->WriteHTML($rowsHtml);
            }

            if ($offset === 0) {
                $pdf->WriteHTML('<tr><td colspan="4" style="text-align: center;">No Brands found.</td></tr>');
            }

            // 4. បិទ HTML Table
            $pdf->WriteHTML('</tbody></table></body></html>');

            return response(
                $pdf->Output('brands_' . now()->format('Y-m-d') . '.pdf', 'S'),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="brands_' . now()->format('Y-m-d') . '.pdf"',
                ]
            );
        } catch (\Throwable $th) {
            Log::error('Brand PDF Export Error: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ]);

            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ], 500);
        }
    }

    private function clearBrandCache(): void
    {
        Cache::forget('brands_stats');

        if (Cache::has('brands_cache_version')) {
            Cache::increment('brands_cache_version');
        } else {
            Cache::put('brands_cache_version', 2);
        }
    }
}
