<?php
namespace App\Services;

use App\DTO\Brand\CreateBrandDTO;
use App\DTO\Brand\ExportBrandDTO;
use App\DTO\Brand\UpdateBrandDTO;
use App\DTO\Brand\ImportBrandDTO;
use App\Exports\BrandsExport;
use App\Imports\BrandsImport;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache; // 1. បន្ថែម Cache Facade
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class BrandService {
    public function __construct(
        private BrandRepositoryInterface $repo
    ){}

    public function CreateBrand(CreateBrandDTO $dto)
    {
        $result = $this->repo->create([
            "name" => $dto->name,
            "status" => $dto->status,
        ]);

        // លុប Cache របស់បញ្ជី Brand និង Stats ចោល
        $this->clearBrandCache();

        return $result;
    }

    // public function getAllBrand($perPage, array $filters = [])
    // {
    //     // បង្កើត Cache Key ផ្អែកលើ perPage និង Filters
    //     $cacheKey = 'brands_all_page_' . $perPage . '_' . md5(json_encode($filters));

    //     // រក្សាទុកក្នុង Redis រយៈពេល 1 ថ្ងៃ (86400s)
    //     return Cache::remember($cacheKey, 86400, function () use ($perPage, $filters) {
    //         return $this->repo->all($perPage, $filters);
    //     });
    // }
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
        300, // 5 minutes
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
        // Cache Stats រយៈពេល 1 ថ្ងៃ
        return Cache::remember('brands_stats', 86400, function () {
            return $this->repo->getBrandStats();
        });
    }

    public function getbrandById($id)
    {
        // Cache Single Brand តាម ID
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

        // លុប Cache របស់ ID នោះ និង បញ្ជី Brand
        Cache::forget("brand_by_id_{$id}");
        $this->clearBrandCache();

        return $data;
    }

    // Delete Brand
    public function delete(string $id)
    {
        $result = $this->repo->delete($id);

        Cache::forget("brand_by_id_{$id}");
        $this->clearBrandCache();

        return $result;
    }

    public function importBrands(UploadedFile $file): bool
    {
        // 1. អានទិន្នន័យពី Excel
        $rows = Excel::toCollection(new BrandsImport, $file)->first();

        // 2. Filter ជួរដែលគ្មានឈ្មោះចេញ រួច Map តាម DTO
        $brandsData = $rows
            ->filter(fn($row) => !empty($row['name']))
            ->map(fn($row) => ImportBrandDTO::fromExcelRow($row->toArray())->toArray())
            ->values()
            ->toArray();

        if (empty($brandsData)) {
            return false;
        }

        // 3. Save ចូល DB តាម Repository
        $saved = $this->repo->insertBulk($brandsData);

        if ($saved) {
            // លុប Cache ចោលបន្ទាប់ពី Import រួច
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
    $pdf = new \Mpdf\Mpdf([
        'format' => 'A4',
        'orientation' => 'P',
        'tempDir' => storage_path('app/mpdf'),
    ]);

    $query = $this->repo->getForExport($filters);

    $query->chunk(500, function ($brands) use ($pdf) {

        $html = view('exports.brands-pdf', [
            'brands' => $brands,
        ])->render();

        $pdf->WriteHTML($html);

        unset($html);
        unset($brands);
    });

    return response(
        $pdf->Output(
            'brands_' . now()->format('Y-m-d') . '.pdf',
            'S'
        ),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>
                'attachment; filename="brands_' .
                now()->format('Y-m-d') .
                '.pdf"',
        ]
    );
}
    /**
     * Helper Function សម្រាប់លុប Cache បញ្ជី Brands ទាំងអស់ និង Stats
     */
    // private function clearBrandCache(): void
    // {
    //     Cache::forget('brands_stats');

    //     // លុប Key ទាំងអស់ដែលផ្តើមដោយ brands_all_
    //     if (config('cache.default') === 'redis') {
    //         try {
    //             $redis = Cache::redis();
    //             $prefix = config('database.redis.options.prefix', '');
    //             $keys = $redis->keys($prefix . 'brands_all_*');

    //             foreach ($keys as $key) {
    //                 $cleanKey = str_replace($prefix, '', $key);
    //                 Cache::forget($cleanKey);
    //             }
    //         } catch (\Throwable $e) {
    //             // ករណីមានបញ្ហាជាមួយ Redis keys scan
    //             Cache::flush();
    //         }
    //     }
    // }
    private function clearBrandCache(): void
{
    // 1. លុប Stats Cache
    Cache::forget('brands_stats');

    // 2. ដំឡើង Version របស់ Brand Cache (ធ្វើឱ្យ Cache Key ចាស់ៗទាំងអស់ត្រូវផុតកំណត់ភ្លាមៗ)
    if (Cache::has('brands_cache_version')) {
        Cache::increment('brands_cache_version');
    } else {
        Cache::put('brands_cache_version', 2);
    }
}
}
