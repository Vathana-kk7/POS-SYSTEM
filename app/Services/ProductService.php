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

            return $product;

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(string $id, UpdateProductDTO $dto)
    {
        try {
            $result = $this->repo->update(
                $id,
                [
                    "name" => $dto->name,
                    "stock_qty" => $dto->stock_qty,
                    "cost_price" => $dto->cost_price,
                    "description" => $dto->description,
                    "sku" => $dto->sku,
                    "image" => $dto->image,
                    "min_stock_level" => $dto->min_stock_level,
                    "status" => $dto->status,
                    "product_type" => $dto->product_type,
                    "selling_price" => $dto->selling_price,
                    "brand_id" => $dto->brand_id,
                    "category_id" => $dto->category_id,
                ]
            );
            $this->clearProductRelatedCache();
            return $result;

        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function delete($id){
        return $this->repo->delete($id);
    }

    public function getAllProduct(){
        try {
            return $this->repo->all();
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }

    public function getProductById($id){
        try {
            return $this->repo->findById($id);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
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

    private function clearProductRelatedCache(): void
    {
        Cache::forget('categories_stats');
        Cache::forget('brands_stats');
    }
}
