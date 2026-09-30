<?php
namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Override;

class ProductRepository implements ProductRepositoryInterface{
    public function create(array $data){
        return Product::create($data);
    }
    public function getState(){
        return [
            'total_products' => Product::count(),
            'low_stock' => Product::whereColumn('stock_qty', '<=', 'min_stock_level')->count(), // 👈 ប្តូរពី low_of_stock មក low_stock
            'out_of_stock' => Product::where('stock_qty', '<=', 0)->count(),
            'total_value' => Product::sum(\DB::raw('stock_qty * cost_price')),
        ];
    }
    public function update(string $id,array $data){
        $result=Product::findOrFail($id);
        $result->update($data);
        return $result;
    }
    // ផ្លាស់ប្តូរពី $fillter មកជា $filters ឱ្យដូចគ្នា
    public function all($perPage, array $filters = []) {
        $query = Product::query()->latest();

        if (!empty($filters['search'])) {
            $query->where(
                'name',
                'like',
                '%' . trim($filters['search']) . '%'
            );
        }

        if (!empty($filters['status']) && $filters['status'] !== "all") {
            $query->where(
                'status',
                $filters['status']
            );
        }

        return $query->paginate($perPage);
    }
    public function delete(string $id){
        $result= Product::findOrFail($id);
        $result->delete($id);
        return $result;
    }
    public function findById(string $id){
        return Product::findOrFail($id);
    }
     public function existsByBarcode(string $barcode): bool
    {
        return Product::where('barcode', $barcode)->exists();
    }

    public function insertBulk(array $data): bool
    {
        return Product::insert($data);
    }
    public function getForExport(array $filters = [])
{
    $query = Product::query();

    // Filter តាម Search (ឈ្មោះ, barcode, sku)
    if (!empty($filters['search'])) {
        $search = trim($filters['search']);
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('barcode', 'like', '%' . $search . '%')
              ->orWhere('sku', 'like', '%' . $search . '%');
        });
    }

    // Filter តាម Status
    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $query->where('status', $filters['status']);
    }

    return $query->orderBy('id', 'desc')->limit(5000)->get();
}
}
