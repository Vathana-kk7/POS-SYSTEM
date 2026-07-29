<?php
namespace App\Services;

use App\DTO\OrderItem\CreateOrderItemDTO;
use App\DTO\OrderItem\UpdateOrderItemDTO;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;

class OrderItemService{
    public function __construct(
        private OrderItemRepositoryInterface $repo
    ){}
    public function create(CreateOrderItemDTO $dto)
    {
        $result=$this->repo->create([
            "product_id"=>$dto->product_id,
            "order_id"=>$dto->order_id,
            "quantity"=>$dto->quantity,
            "unit_price"=>$dto->unit_price,
            "discount"=>$dto->discount,
            "subtotal"=>$dto->subtotal,
        ]);
        return $result;
    }
    public function update(string $id,UpdateOrderItemDTO $dto)
    {
        $result=$this->repo->update(
            $id,
            [
                "product_id"=>$dto->product_id,
                "order_id"=>$dto->order_id,
                "quantity"=>$dto->quantity,
                "unit_price"=>$dto->unit_price,
                "discount"=>$dto->discount,
                "subtotal"=>$dto->subtotal,
            ]
        );
        return $result;
    }
    public function all()
    {
        return $this->repo->all();
    }
    public function findById(string $id)
    {
        return $this->repo->findById($id);
    }
    public function delete(string $id)
    {
        return $this->repo->delete($id);
    }
}
