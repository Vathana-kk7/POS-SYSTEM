<?php
namespace App\Services;

use App\DTO\Brand\CreateBrandDTO;
use App\DTO\Brand\UpdateBrandDTO;
use App\Repositories\Interfaces\BrandRepositoryInterface;

class BrandService{
    public function __construct(
        private BrandRepositoryInterface $repo
    ){}
    public function CreateBrand(CreateBrandDTO $dto)
    {
        $result=$this->repo->create([
            "name"=>$dto->name,
            "status"=>$dto->status,
        ]);
        return $result;
    }
    // public function getAllBrand(int $perPage = 10){
    //     // return $this->repo->all();
    //     return $this->repo->paginate($perPage);
    // }
    public function getAllBrand($perPage, array $filters = [])
    {
        return $this->repo->all(
            $perPage,
            $filters
        );
    }
    public function getBrandStats()
    {
        return $this->repo->getBrandStats();
    }
    public function getbrandById($id){
        return $this->repo->findById($id);
    }
    public function update($id,UpdateBrandDTO $dto){
        $data=$this->repo->update(
              $id,
            [
            "name"=>$dto->name,
            "status"=>$dto->status
        ]);
        return $data;
    }
    //Delete Brand
    public function delete(string $id){
        return $this->repo->delete($id);
    }

}
