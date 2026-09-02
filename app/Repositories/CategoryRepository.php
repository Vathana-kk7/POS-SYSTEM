<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function create(array $data)
    {
        return Category::create($data);
    }

    public function all($perPage, array $filters = [])
    {
        $query = Category::query()->latest();

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

    public function getCategoryState()
    {
        $total = Category::count();

        $thisMonth = Category::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonth = Category::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        \Log::info('Category Growth Debug', [
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
            'active' => Category::where('status', 'active')->count(),
            'inactive' => Category::where('status', 'inactive')->count(),
            'with_products' => Category::has('products')->count(),
            'growth' => $growth !== null ? round($growth, 2) : null,
        ];
    }

    public function findById(string $id)
    {
        return Category::findOrFail($id);
    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return $category;
    }

    public function update(string $id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }

    public function insertBulk(array $data): bool
    {
        return Category::insert($data);
    }

    public function getForExport(array $filters = [])
    {
        $query = Category::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . trim($filters['search']) . '%');
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->get();
    }
}
