<?php
namespace App\Services;

use App\DTO\Supplier\CreateSupplierDTO;
use App\DTO\Supplier\UpdateSupplierDTO;
use App\Repositories\Interfaces\SupplierRepositoryInterface;

class SupplierService{
    public function __construct(private SupplierRepositoryInterface $repo){}

    public function create(CreateSupplierDTO $dto){
        try {
            $result=$this->repo->create([
                "name"=>$dto->name,
                "address"=>$dto->address,
                "phone"=>$dto->phone,
            ]);
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }

    public function getAll(){
        return $this->repo->all();
    }
    public function getById($id){
        return $this->repo->findById($id);
    }
    public function update($id,UpdateSupplierDTO $dto){
        try {
            $result=$this->repo->update(
                $id,
                    [
                        "name"=>$dto->name,
                        "address"=>$dto->address,
                        "phone"=>$dto->phone,
                    ]
            );
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function delete(string $id){
        try {
            $result=$this->repo->delete($id);
            return response()->json([
                "status"=>"Delete Success",
                "data"=>$result,
            ],201);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
}
