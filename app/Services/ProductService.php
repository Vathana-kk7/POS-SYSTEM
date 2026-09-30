<?php
namespace App\Services;

use App\DTO\Product\CreateProductDTO;
use App\DTO\Product\ImportProductDTO;
use App\DTO\Product\UpdateProductDTO;
use App\Imports\ProductImport;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Exports\ProductExport;
use App\Models\Product;

class ProductService{
    public function __construct(private ProductRepositoryInterface $repo){}

    private function generateBarcode(): string
    {
        do {
            $barcode = '890' . random_int(100000000, 999999999);
        } while ($this->repo->existsByBarcode($barcode));

        return $barcode;
    }

    public function create(CreateProductDTO $dto)
    {
        try {
            $imagePath = null;

            if ($dto->image && $dto->image instanceof UploadedFile) {
                $imagePath = $dto->image->store('products', 'public');
            } elseif (is_string($dto->image)) {
                $imagePath = $dto->image;
            }

            $product = $this->repo->create([
                "name" => $dto->name,
                "stock_qty" => $dto->stock_qty,
                "cost_price" => $dto->cost_price,
                "description" => $dto->description,
                "sku" => $dto->sku,
                "image" => $imagePath,
                "min_stock_level" => $dto->min_stock_level,
                "status" => $dto->status,
                "product_type" => $dto->product_type,
                "selling_price" => $dto->selling_price,
                "brand_id" => $dto->brand_id,
                "category_id" => $dto->category_id,
                "barcode" => $this->generateBarcode(),
            ]);
            $this->clearProductRelatedCache();
            return $product;

        } catch (\Throwable $th) {
            throw $th;
        }
    }




public function update(string $id, UpdateProductDTO $dto)
{
    try {
        $data = [
            'name' => $dto->name,
            'stock_qty' => $dto->stock_qty,
            'cost_price' => $dto->cost_price,
            'description' => $dto->description,
            'sku' => $dto->sku,
            'min_stock_level' => $dto->min_stock_level,
            'status' => $dto->status,
            'product_type' => $dto->product_type,
            'selling_price' => $dto->selling_price,
            'brand_id' => $dto->brand_id,
            'category_id' => $dto->category_id,
        ];

        // Handle image: Check if a new file is uploaded
        if ($dto->image instanceof UploadedFile) {
            // Optional: Delete old image from storage if needed here
            $data['image'] = $dto->image->store('products', 'public');
        } elseif (is_string($dto->image)) {
            // If it's already a string, keep the existing path
            $data['image'] = $dto->image;
        }
        // If it's null, you can choose to omit it from $data so the database column isn't overwritten with null

        $result = $this->repo->update($id, $data);

        $this->clearProductRelatedCache();

        return $result;

    } catch (\Throwable $th) {
        return response()->json([
            'message' => $th->getMessage(),
        ], 500);
    }
}


    public function getState(){
        try {
            return Cache::remember('product_state', 300, function(){
                return $this->repo->getState();
            });
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }


   public function delete($id){
        try {
            $result = $this->repo->delete($id);

            // 👉 បន្ថែមត្រង់នេះ ដើម្បីឱ្យវាសម្អាត Cache ចាស់ចេញ ពេលមានការលុបផលិតផល
            $this->clearProductRelatedCache();

            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function getAllProduct($perPage, array $filters = [])
    {
        try {
            $page = request()->integer('page', 1);

            $version = Cache::get('products_cache_version', 1);

            $cacheKey = 'product:v' . $version . ':' . md5(
                json_encode([
                    'page' => $page,
                    'perPage' => $perPage,
                    'filter' => $filters,
                ])
            );

            return Cache::remember(
                $cacheKey,
                now()->addMinutes(5),
                function () use ($perPage, $filters) {
                    return $this->repo->all($perPage, $filters);
                }
            );

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'GetAllProduct Error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }


    public function getProductById($id){
        try {
            return $this->repo->findById($id);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    public function exportExcel(array $filters = [])
    {
        return Excel::download(
            new ProductExport($filters),
            'products_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
    /**
     * Handle Import Product from ZIP (Excel + Images Folder)
     */
    public function importProduct(UploadedFile $file)
    {
        $zip = new ZipArchive();
        $extractPath = storage_path('app/temp/import_' . uniqid());

        if ($zip->open($file->getRealPath()) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            throw new \Exception("មិនអាចបើកឯកសារ ZIP នេះបានទេ។");
        }

        try {
            // 1. ស្វែងរក File Excel ខាងក្នុង Folder និង Sub-folder ទាំងអស់ដោយស្វ័យប្រវត្តិ (Recursive)
            $excelFile = null;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($extractPath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $path => $info) {
                if ($info->isFile()) {
                    $extension = strtolower($info->getExtension());
                    // ឆែករក file ដែលមាន extension ត្រឹមត្រូវ និងមិនមែនជា hidden file របស់ Mac/Windows
                    if (in_array($extension, ['xlsx', 'xls', 'csv']) && !str_starts_with($info->getFilename(), '~$')) {
                        $excelFile = $path;
                        break;
                    }
                }
            }

            if (!$excelFile) {
                throw new \Exception("រកមិនឃើញ File Excel នៅក្នុង ZIP ទេ។ (សូមពិនិត្យមើលថាតើមាន file .xlsx ឬ .csv ក្នុង zip ដែរឬទេ)");
            }

            // 2. អានទិន្នន័យពី Excel
            $rows = Excel::toCollection(new ProductImport(), $excelFile)->first();

            if ($rows === null || $rows->isEmpty()) {
                return false;
            }

            // 3. Process Rows & Handle Images
            $productData = $rows
                ->filter(fn ($row) => !empty(trim($row['name'] ?? '')))
                ->map(function ($row) use ($extractPath) {
                    $row = $row->toArray();

                    // Brand Check
                    $brandName = trim($row['brand'] ?? '');
                    if (empty($brandName)) {
                        throw new \Exception("Brand មិនអាចទទេបានទេ.");
                    }
                    $brand = Brand::firstOrCreate(['name' => $brandName], ['status' => 'active']);

                    // Category Check
                    $categoryName = trim($row['category'] ?? '');
                    if (empty($categoryName)) {
                        throw new \Exception("Category មិនអាចទទេបានទេ។");
                    }
                    $category = Category::firstOrCreate(['name' => $categoryName], ['status' => 'active']);

                    // 4. Handle Image from ZIP if provided in Excel
                    $imageFilename = trim($row['image'] ?? '');
                    $storedImagePath = null;

                    if (!empty($imageFilename)) {
                        $sourceImagePath = null;

                        // ស្វែងរក file រូបភាពគ្រប់ទីកន្លែងក្នុង ZIP (Recursive search សម្រាប់ image)
                        $imageIterator = new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($extractPath, \RecursiveDirectoryIterator::SKIP_DOTS)
                        );

                        foreach ($imageIterator as $imgPath => $imgInfo) {
                            if ($imgInfo->isFile() && strtolower($imgInfo->getFilename()) === strtolower($imageFilename)) {
                                $sourceImagePath = $imgPath;
                                break;
                            }
                        }

                        if ($sourceImagePath && file_exists($sourceImagePath)) {
                            $targetDir = 'products';
                            Storage::disk('public')->makeDirectory($targetDir);
                            $newImageName = 'products/' . uniqid() . '_' . $imageFilename;

                            // Copy រូបភាពចូល storage/app/public/products/
                            Storage::disk('public')->put($newImageName, file_get_contents($sourceImagePath));
                            $storedImagePath = $newImageName;
                        }
                    }

                    // យកតម្លៃ image ដែលបាន Store រួចដាក់ចូល DTO
                    $row['image'] = $storedImagePath;

                    $dto = ImportProductDTO::fromExcelRow(
                        $row,
                        $brand->id,
                        $category->id
                    );

                    return $dto->toArray();
                })
                ->values()
                ->toArray();

            if (empty($productData)) {
                return false;
            }

            // Generate Barcode សម្រាប់ row នីមួយៗ
            foreach ($productData as &$product) {
                $product['barcode'] = $this->generateBarcode();
            }
            unset($product);

            // Insert ចូល Database ជា Bulk
            $save = $this->repo->insertBulk($productData);

            if ($save) {
                $this->clearProductRelatedCache();
            }

            return $save;

        } finally {
            // សំអាត folder បណ្តោះអាសន្នចេញវិញក្រោយពេល Import រួចរាល់
            if (is_dir($extractPath)) {
                $this->deleteDirectory($extractPath);
            }
        }
    }

    private function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

public function exportPdf(array $filters = [])
{
    ini_set('memory_limit', '512M');
    ini_set('pcre.backtrack_limit', '10000000');
    ini_set('pcre.recursion_limit', '1000000');
    set_time_limit(120);

    $query = Product::with(['brand', 'category']);

    // Search
    if (!empty($filters['search'])) {
        $search = trim($filters['search']);

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    // Status
    if (
        isset($filters['status']) &&
        $filters['status'] !== '' &&
        $filters['status'] !== 'all'
    ) {
        $query->where('status', $filters['status']);
    }

    // Brand
    if (!empty($filters['brand_id'])) {
        $query->where('brand_id', $filters['brand_id']);
    }

    // Category
    if (!empty($filters['category_id'])) {
        $query->where('category_id', $filters['category_id']);
    }

    $products = $query
        ->orderBy('id', 'asc')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Prepare PDF data
    |--------------------------------------------------------------------------
    */

    $products->each(function ($product, $index) {

        $product->image_base64 = $this->imageToBase64(
            $product->image
        );

        $product->pdf_index = $index + 1;
    });

    /*
    |--------------------------------------------------------------------------
    | mPDF
    |--------------------------------------------------------------------------
    */

    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();

    $fontDirs = $defaultConfig['fontDir'];
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',

        'format' => 'A4',

        'orientation' => 'P',

        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 15,
        'margin_bottom' => 15,

        'fontDir' => array_merge(
            $fontDirs,
            [
                storage_path('fonts')
            ]
        ),

        'fontdata' => $fontData + [
            'notokhmer' => [
                'R' => 'NotoSansKhmer-Regular.ttf',
                'B' => 'NotoSansKhmer-Regular.ttf',
                'useOTL' => 0xFF,
            ],
        ],

        'default_font' => 'notokhmer',

        'tempDir' => storage_path('app/mpdf'),
    ]);

    /*
    |--------------------------------------------------------------------------
    | Khmer support
    |--------------------------------------------------------------------------
    */

    $mpdf->autoScriptToLang = true;
    $mpdf->autoLangToFont = true;

    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    $headerHtml = view('exports.products-pdf', [
        'products' => collect(),
        'date' => now()->format('d-m-Y H:i'),
        'headerOnly' => true,
        'chunkOnly' => false,
    ])->render();

    $mpdf->WriteHTML($headerHtml);

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    foreach ($products->chunk(5) as $chunk) {

        $html = view('exports.products-pdf', [
            'products' => $chunk,
            'date' => now()->format('d-m-Y H:i'),

            'headerOnly' => false,
            'chunkOnly' => true,
        ])->render();

        $mpdf->WriteHTML(
            $html,
            \Mpdf\HTMLParserMode::HTML_BODY
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Output
    |--------------------------------------------------------------------------
    */

    return response(
        $mpdf->Output(
            'products_' . now()->format('Y-m-d') . '.pdf',
            \Mpdf\Output\Destination::STRING_RETURN
        ),
        200
    )
    ->header('Content-Type', 'application/pdf')
    ->header(
        'Content-Disposition',
        'inline; filename="products_' . now()->format('Y-m-d') . '.pdf"'
    );
}

/**
 * បម្លែងរូបភាពក្នុង storage/app/public ជា base64 សម្រាប់ PDF
 */
private function imageToBase64(?string $imagePath): ?string
{
    if (empty($imagePath)) {
        return null;
    }

    if (!Storage::disk('public')->exists($imagePath)) {
        return null;
    }

    $fullPath = Storage::disk('public')->path($imagePath);

    if (!file_exists($fullPath)) {
        return null;
    }

    $imageInfo = @getimagesize($fullPath);

    if (!$imageInfo) {
        return null;
    }

    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $type = $imageInfo[2];

    /*
    |--------------------------------------------------------------------------
    | Load image
    |--------------------------------------------------------------------------
    */

    switch ($type) {

        case IMAGETYPE_JPEG:
            $source = @imagecreatefromjpeg($fullPath);
            break;

        case IMAGETYPE_PNG:
            $source = @imagecreatefrompng($fullPath);
            break;

        case IMAGETYPE_WEBP:
            $source = function_exists('imagecreatefromwebp')
                ? @imagecreatefromwebp($fullPath)
                : false;
            break;

        case IMAGETYPE_GIF:
            $source = @imagecreatefromgif($fullPath);
            break;

        default:
            return null;
    }

    if (!$source) {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Resize
    |--------------------------------------------------------------------------
    */

    $maxWidth = 120;
    $maxHeight = 120;

    $ratio = min(
        $maxWidth / $width,
        $maxHeight / $height,
        1
    );

    $newWidth = max(1, (int) ($width * $ratio));
    $newHeight = max(1, (int) ($height * $ratio));

    $canvas = imagecreatetruecolor(
        $newWidth,
        $newHeight
    );

    /*
    |--------------------------------------------------------------------------
    | White background
    |--------------------------------------------------------------------------
    */

    $white = imagecolorallocate(
        $canvas,
        255,
        255,
        255
    );

    imagefill(
        $canvas,
        0,
        0,
        $white
    );

    /*
    |--------------------------------------------------------------------------
    | Resize
    |--------------------------------------------------------------------------
    */

    imagecopyresampled(
        $canvas,
        $source,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $width,
        $height
    );

    /*
    |--------------------------------------------------------------------------
    | Convert to JPEG
    |--------------------------------------------------------------------------
    */

    ob_start();

    imagejpeg(
        $canvas,
        null,
        70
    );

    $imageData = ob_get_clean();

    /*
    |--------------------------------------------------------------------------
    | Cleanup
    |--------------------------------------------------------------------------
    */

    imagedestroy($source);
    imagedestroy($canvas);

    if (!$imageData) {
        return null;
    }

    return 'data:image/jpeg;base64,' . base64_encode($imageData);
}
private function clearProductRelatedCache(): void
    {
        // =========================================
        // Product List Cache
        // =========================================

        if (Cache::has('products_cache_version')) {
            Cache::increment('products_cache_version');
        } else {
            Cache::put('products_cache_version', 2);
        }

        // =========================================
        // Product Statistics Cache
        // =========================================

        Cache::forget('product_state');

        // =========================================
        // Category Statistics Cache
        // =========================================

        Cache::forget('categories_stats');

        // =========================================
        // Brand Statistics Cache
        // =========================================

        Cache::forget('brands_stats');
    }


}
