<?php

namespace App\Services;

use App\DTO\Category\CreateCategoryDTO;
use App\DTO\Category\ImportCategoryDTO;
use App\DTO\Category\UpdateCategoryDTO;
use App\Exports\CategoryExport;
use App\Imports\CategoryImport;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $repo,
    ) {}

    public function create(CreateCategoryDTO $dto)
    {
        try {
            $result = $this->repo->create([
                "name" => $dto->name,
                "description" => $dto->description,
                "status" => $dto->status,
            ]);
            $this->clearCategoryCache();
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function getCategory($perPage, array $filters = [])
    {
        $page = request()->integer("page", 1);

        try {
            $version = Cache::get('categories_cache_version', 1);
            $cacheKey = 'category:v' . $version . ':' . md5(json_encode([
                'page' => $page,
                'perPage' => $perPage,
                'filter' => $filters,
            ]));

            return Cache::remember($cacheKey, 300, function () use ($perPage, $filters) {
                return $this->repo->all($perPage, $filters);
            });
        } catch (\Throwable $th) {
            return $this->repo->all($perPage, $filters);
        }
    }

    public function getCategoryState()
    {
        try {
            return Cache::remember('category', 86400, function () {
                return $this->repo->getCategorystate();
            });
        } catch (\Throwable $th) {
            return $this->repo->getCategorystate();
        }
    }

    public function getCategoryById($id)
    {
        return $this->repo->findById($id);
    }

    public function delete($id)
    {
        $this->clearCategoryCache();
        return $this->repo->delete($id);
    }

    public function update($id, UpdateCategoryDTO $dto)
    {
        try {
            $data = $this->repo->update(
                $id,
                [
                    "name" => $dto->name,
                    "description" => $dto->description,
                    "status" => $dto->status,
                ]
            );
            $this->clearCategoryCache();
            return $data;
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function importCategory(UploadedFile $file): bool
    {
        $rows = Excel::toCollection(new CategoryImport(), $file)->first();

        if ($rows === null || $rows->isEmpty()) {
            return false;
        }

        $categoryData = $rows
            ->filter(fn ($row) => !empty($row['name']))
            ->map(
                fn ($row) => ImportCategoryDTO::fromExcelRow(
                    $row->toArray()
                )->toArray()
            )
            ->values()
            ->toArray();

        if (empty($categoryData)) {
            return false;
        }

        $saved = $this->repo->insertBulk($categoryData);

        if ($saved) {
            $this->clearCategoryCache();
        }

        return $saved;
    }

    public function exportExcel(array $filters = [])
    {
        return Excel::download(
            new CategoryExport($filters),
            'categories_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportCategoryPdf(array $filters = [])
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '600');

            // 1. បង្កើត Temp Folder និងផ្ដល់ Permission សម្រាប់ Windows/Linux
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

            // 2. Render Header (ត្រូវប្រាកដថា File ឈ្មោះ category-pdf.blade.php ក្នុង resources/views/exports/)
            $headerHtml = view('exports.category-pdf')->render();
            $pdf->WriteHTML($headerHtml);

            // 3. Fetch Data Safe Check
            $query = $this->repo->getForExport($filters);

            if ($query instanceof \Illuminate\Support\Collection || is_array($query)) {
                $categories = $query;
            } else if (is_object($query) && method_exists($query, 'cursor')) {
                $categories = $query->cursor();
            } else if (is_object($query) && method_exists($query, 'get')) {
                $categories = $query->get();
            } else {
                $categories = [];
            }

            $rowsHtml = '';
            $offset = 0;
            $chunkSize = 500;

            foreach ($categories as $category) {
                $offset++;
                $name = e($category->name ?? '');
                $description = e($category->description ?? 'N/A');
                $statusVal = $category->status ?? 'inactive';
                $status = strtolower($statusVal) === 'active' ? 'active' : 'inactive';
                $statusText = e(ucfirst($statusVal));

                $createdAt = 'N/A';
                if (!empty($category->created_at)) {
                    $createdAt = is_string($category->created_at)
                        ? $category->created_at
                        : $category->created_at->format('Y-m-d H:i');
                }

                $rowsHtml .= "
                    <tr>
                        <td style=\"text-align: center;\">{$offset}</td>
                        <td>{$name}</td>
                        <td>{$description}</td>
                        <td style=\"text-align: center;\" class=\"{$status}\">{$statusText}</td>
                        <td>{$createdAt}</td>
                    </tr>
                ";

                if ($offset % $chunkSize === 0) {
                    $pdf->WriteHTML($rowsHtml);
                    $rowsHtml = '';
                }
            }

            if (!empty($rowsHtml)) {
                $pdf->WriteHTML($rowsHtml);
            }

            if ($offset === 0) {
                $pdf->WriteHTML('<tr><td colspan="5" style="text-align: center;">No categories found.</td></tr>');
            }

            // 4. បិទ Tag Table
            $pdf->WriteHTML('</tbody></table></body></html>');

            return response(
                $pdf->Output('categories_' . now()->format('Y-m-d') . '.pdf', 'S'),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="categories_' . now()->format('Y-m-d') . '.pdf"',
                ]
            );
        } catch (\Throwable $th) {
            // កត់ត្រា Error ចូលលាតត្រដាងក្នុង laravel.log
            Log::error('PDF Export Error: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ], 500);
        }
    }

    private function clearCategoryCache(): void
    {
        try {
            $version = Cache::get('categories_cache_version', 1);

            Cache::put(
                'categories_cache_version',
                $version + 1
            );

            Cache::forget('category');
        } catch (\Throwable $th) {
            // Ignore cache errors
        }
    }
}
