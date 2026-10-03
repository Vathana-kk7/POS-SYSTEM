<?php
namespace App\Services;

use App\DTO\Purchase\CreatePurchaseDTO;
use App\DTO\Purchase\UpdatePurchaseDTO;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use App\Services\SupplierService;

class PurchaseService{
    public function __construct(
        private PurchaseRepositoryInterface $repo,
        private SupplierService $supplierService
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
            $this->supplierService->clearStatsCache();
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
            $this->supplierService->clearStatsCache();
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
        $result = $this->repo->delete($id);
        $this->supplierService->clearStatsCache();
        return $result;
    }

}
