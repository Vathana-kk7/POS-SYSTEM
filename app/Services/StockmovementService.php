<?php
namespace App\Services;

use App\DTO\Brand\UpdateBrandDTO;
use App\DTO\StockMovement\CreateStockMovementDTO;
use App\DTO\StockMovement\UpdateStockMovementDTO;
use App\Repositories\Interfaces\StockmovementRepositoryInterface;

class StockmovementService{
    public function __construct(
        private StockmovementRepositoryInterface $repo
    ){}

    public function create(CreateStockMovementDTO $dto){
        $result =$this->repo->create([
            "product_id"=>$dto->product_id,
            "user_id"=>$dto->user_id,
            "type"=>$dto->type,
            "quantity"=>$dto->quantity,
            "stock_before"=>$dto->stock_before,
            "stock_after"=>$dto->stock_after,
            "reference_type"=>$dto->reference_type,
            "reference_id"=>$dto->reference_id,
        ]);
        return $result;
    }
    public function update(string $id, UpdateStockMovementDTO $dto){
        $result=$this->repo->update(
            $id,
            [
               "product_id"=>$dto->product_id,
                "user_id"=>$dto->user_id,
                "type"=>$dto->type,
                "quantity"=>$dto->quantity,
                "stock_before"=>$dto->stock_before,
                "stock_after"=>$dto->stock_after,
                "reference_type"=>$dto->reference_type,
                "reference_id"=>$dto->reference_id,
            ]
        );
        return $result;
    }
    public function all(){
        return $this->repo->all();
    }
    public function findById(string $id){
        return $this->repo->findById($id);
    }
    public function delete(string $id){
        return $this->repo->delete($id);
    }
}
