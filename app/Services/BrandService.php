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
    public function getAllUsers(){
        return $this->repo->all();
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
    public function deletebrand($id){
        return $this->repo->delete($id);
    }
}
