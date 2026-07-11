<?php
namespace App\Services;

use App\DTO\Category\CrateCategoryDTO;
use App\DTO\Category\UpdateCategoryDTO;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryService{
    public function __construct(
        private CategoryRepositoryInterface $repo,
    ){}
    public function create(CrateCategoryDTO $dto){
        try {
            $result=$this->repo->create([
                "name"=>$dto->name,
                "description"=>$dto->description,
            ])  ;
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function getCategory(){
        return $this->repo->all();
    }
    public function getCategoryById($id){
        return $this->repo->findById($id);
    }
    public function delete($id){
        return $this->repo->delete($id);
    }
    public function update($id,UpdateCategoryDTO $dto){
        try {
            $data=$this->repo->update(
                $id,
                [
                "name"=>$dto->name,
                "description"=>$dto->description,
            ]);
            return $data;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
}
