<?php
namespace App\Services;

use App\DTO\Brand\CreateBrandDTO;
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
}
