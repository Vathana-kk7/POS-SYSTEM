<?php
namespace App\Services;

use App\DTO\Category\CrateCategoryDTO;
use App\DTO\Category\UpdateCategoryDTO;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class CategoryService{
    public function __construct(
        private CategoryRepositoryInterface $repo,
    ){}
    public function create(CrateCategoryDTO $dto){
        try {
            $result=$this->repo->create([
                "name"=>$dto->name,
                "description"=>$dto->description,
                "status"=>$dto->status,
            ])  ;
            $this->clearCategoryCache();
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function getCategory($perPage,array $filters=[]){
        // return $this->repo->all();
        $page=request()->integer("page",1);
        $version = Cache::get('categories_cache_version', 1);

        $cacheKey = 'category:v' . $version . ':' . md5(
            json_encode([
                'page'=>$page,
                'perPage'=>$perPage,
                'filter'=>$filters,
            ])
        );
        return Cache::remember(
            $cacheKey,
            300, //5minute
            function () use ($perPage,$filters){
                return $this->repo->all(
                    $perPage,
                    $filters
                );
            }
        );

    }
    public function getCategoryById($id){
        return $this->repo->findById($id);
    }
    public function delete($id){
         $this->clearCategoryCache();
        return $this->repo->delete($id);
    }
    public function update($id,UpdateCategoryDTO $dto){
        try {
            $data=$this->repo->update(
                $id,
                [
                "name"=>$dto->name,
                "description"=>$dto->description,
                "status"=>$dto->status,
            ]);
            $this->clearCategoryCache();
            return $data;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    private function clearCategoryCache(): void
    {
        Cache::forget('categories_cache_version');

        // លុប Key ទាំងអស់ដែលផ្តើមដោយ brands_all_
        if (config('cache.default') === 'redis') {
            try {
                $redis = Cache::redis();
                $prefix = config('database.redis.options.prefix', '');
                $keys = $redis->keys($prefix . 'category:*');
                foreach ($keys as $key) {
                    $cleanKey = str_replace($prefix, '', $key);
                    Cache::forget($cleanKey);
                }
            } catch (\Throwable $e) {
                // ករណីមានបញ្ហាជាមួយ Redis keys scan
                Cache::flush();
            }
        }
    }
}
