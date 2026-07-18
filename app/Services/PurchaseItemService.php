<?php
namespace App\Services;

use App\DTO\PurchaseItem\CreatePurchaseItemDTO;
use App\DTO\PurchaseItem\UpdatePurchaseItemDTO;
use App\Http\Requests\PurchaseItem\UpdatePurchaseItemRequest;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;

class PurchaseItemService{
    public function __construct(private PurchaseItemRepositoryInterface $repo){}
    public function create(CreatePurchaseItemDTO $dto){
        try {
            $result=$this->repo->create([
                "purchase_id"=>$dto->purchase_id,
                "product_id"=>$dto->product_id,
                "quantity"=>$dto->quantity,
                "cost_price"=>$dto->cost_price,
                "subtotal"=>$dto->subtotal,
            ]);
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function update(string $id,UpdatePurchaseItemDTO $dto){
        try {
            $result=$this->repo->update(
                $id,
                [
                    "purchase_id"=>$dto->purchase_id,
                    "product_id"=>$dto->product_id,
                    "quantity"=>$dto->quantity,
                    "cost_price"=>$dto->cost_price,
                    "subtotal"=>$dto->subtotal,
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
            return $this->repo->all();
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
