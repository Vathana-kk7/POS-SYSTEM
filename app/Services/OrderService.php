<?php
namespace App\Services;

use App\DTO\Order\CreateOrderDTO;
use App\DTO\Order\UpdateOrderDTO;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class OrderService{
    public function __construct(
        private OrderRepositoryInterface $repo
    ){}
    public function create(CreateOrderDTO $dto)
    {
        $result=$this->repo->create([
            "total"=>$dto->total,
            "discount"=>$dto->discount,
            "tax"=>$dto->tax,
            "grand_total"=>$dto->grand_total,
            "payment_status"=>$dto->payment_status,
            "order_status"=>$dto->order_status,
            "customer_id"=>$dto->customer_id,
        ]);
        return $result;
    }
    public function update(string $id,UpdateOrderDTO $dto)
    {
        $result=$this->repo->update(
            $id,
            [
                "total"=>$dto->total,
                "discount"=>$dto->discount,
                "tax"=>$dto->tax,
                "grand_total"=>$dto->grand_total,
                "payment_status"=>$dto->payment_status,
                "order_status"=>$dto->order_status,
                "customer_id"=>$dto->customer_id,
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
