<?php

namespace App\Services;

use App\DTO\Category\CreateCategoryDTO;
use App\DTO\Category\ImportCategoryDTO;
use App\DTO\Category\UpdateCategoryDTO;
use App\Imports\CategoryImport;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;


class CategoryService{
    public function __construct(
        private CategoryRepositoryInterface $repo,
    ){}

    public function create(CreateCategoryDTO $dto){
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

    public function getCategory($perPage, array $filters = []){
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

    public function getCategoryState(){
        try {
            return Cache::remember('category', 86400, function () {
                return $this->repo->getCategorystate();
            });
        } catch (\Throwable $th) {
            return $this->repo->getCategorystate();
        }
    }

    public function getCategoryById($id){
        return $this->repo->findById($id);
    }

    public function delete($id){
        $this->clearCategoryCache();
        return $this->repo->delete($id);
    }

    public function update($id, UpdateCategoryDTO $dto){
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

    // public function importCategory(UploadedFile $file): bool
    // {
    //     $rows = Excel::toCollection(new CategoryImport, $file)->first();

    //     if ($rows === null) {
    //         return false;
    //     }

    //     $categoryData = $rows
    //         ->filter(fn($row) => !empty($row['name']))
    //         ->map(fn($row) => ImportCategoryDTO::fromExcelRow($row->toArray())->toArray())
    //         ->values()
    //         ->toArray();

    //     if (empty($categoryData)) {
    //         return false;
    //     }

    //     $saved = $this->repo->insertBulk($categoryData);
    //     if ($saved) {
    //         $this->clearCategoryCache();
    //     }

    //     return $saved;
    // }
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
