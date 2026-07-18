<?php
namespace App\Services;

use App\DTO\Purchase\CreatePurchaseDTO;
use App\DTO\Purchase\UpdatePurchaseDTO;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;

class PurchaseService{
    public function __construct(
        private PurchaseRepositoryInterface $repo
    ){}
    public function create(CreatePurchaseDTO $dto){
        try {
            $result=$this->repo->create([
                "purchase_date"=>$dto->purchase_date,
                "total"=>$dto->total,
                "status"=>$dto->status,
                "supplier_id"=>$dto->supplier_id,
                "user_id"=>$dto->user_id,
            ],200);
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function update(string $id,UpdatePurchaseDTO $dto){
        try {
            $result=$this->repo->update(
                $id,
                [
                   "purchase_date"=>$dto->purchase_date,
                   "total"=>$dto->total,
                   "status"=>$dto->status,
                   "supplier_id"=>$dto->supplier_id,
                   "user_id"=>$dto->user_id,
                ]
            );
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function all(){
        try {
            $result=$this->repo->all();
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function findById(string $id){
        try {
            $result=$this->repo->findById($id);
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function delete(string $id){
        return $this->repo->delete($id);
    }

}
