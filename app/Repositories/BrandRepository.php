<?php
namespace App\Repositories;

use App\Models\Brand;
use App\Repositories\Interfaces\BrandRepositoryInterface;

class BrandRepository implements BrandRepositoryInterface{
    public function create(array $data):Brand
    {
        return Brand::create($data);
    }
    // public function all(){
    //     return Brand::all();
    // }
    public function all($perPage, array $filters = [])
    {
        $query = Brand::query();
        if (!empty($filters['search'])) {
            $query->where(
                'name',
                'like',
                '%' . $filters['search'] . '%'
            );
        }

        if (!empty($filters['status'])) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        return $query->paginate($perPage);
    }
    public function findById(string $id){
        return Brand::findOrFail($id);
    }
    public function update(string $id,array $data){
        $brand=Brand::findOrFail($id);
        $brand->update($data);
        return $brand;
    }
    public function getBrandStats()
    {
        $total = Brand::count();

        $thisMonth = Brand::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonth = Brand::whereMonth(
                'created_at',
                now()->subMonth()->month
            )
            ->whereYear(
                'created_at',
                now()->subMonth()->year
            )
            ->count();

        \Log::info('Brand Growth Debug', [
            'thisMonth' => $thisMonth,
            'lastMonth' => $lastMonth,
        ]);

        if ($lastMonth === 0 && $thisMonth > 0) {
            $growth = null;
        } elseif ($lastMonth === 0) {
            $growth = 0;
        } else {
            $growth = (($thisMonth - $lastMonth) / $lastMonth) * 100;
        }

        return [
            'total' => $total,
            'active' => Brand::where('status', 'active')->count(),
            'inactive' => Brand::where('status', 'inactive')->count(),
            'with_products' => Brand::has('products')->count(),
            'growth' => $growth !== null ? round($growth, 2) : null,
        ];
    }
    public function delete(string $id): bool
    {
        $brand = Brand::findOrFail($id);

        return $brand->delete();
    }

    public function paginate(int $perPage = 10)
    {
        return Brand::query()->latest()->paginate($perPage);
    }
    public function insertBulk(array $data): bool
    {
        return Brand::insert($data);
    }

   public function getForExport(array $filters = [])
{
    $query = Brand::query();

    if (!empty($filters['search'])) {
        $query->where(
            'name',
            'like',
            '%' . $filters['search'] . '%'
        );
    }

    if (!empty($filters['status'])) {
        $query->where(
            'status',
            $filters['status']
        );
    }

    return $query
        ->orderBy('name')
        ->get();
}
}
