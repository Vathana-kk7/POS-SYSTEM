<?php
namespace App\Services;

use App\DTO\OrderPaymentMethod\CreateOrderPaymentMethodDTO;
use App\DTO\OrderPaymentMethod\UpdateOrderPaymentMethodDTO;
use App\Repositories\Interfaces\OrderPaymentMethodRepositoryInterface;

class OrderPaymentMethodService{
    public function __construct(
        private OrderPaymentMethodRepositoryInterface $repo
    ){}
    public function create(CreateOrderPaymentMethodDTO $dto)
    {
        $result=$this->repo->create([
            "amount"=>$dto->amount,
            "transaction_id"=>$dto->transaction_id,
            "payment_status"=>$dto->payment_status,
            "paid_at"=>$dto->paid_at,
            "order_id"=>$dto->order_id,
            "bank_id"=>$dto->bank_id,
        ]);
        return $result;
    }
    public function update(string $id,UpdateOrderPaymentMethodDTO $dto)
    {
        $result=$this->repo->update(
            $id,
            [
                "amount"=>$dto->amount,
                "transaction_id"=>$dto->transaction_id,
                "payment_status"=>$dto->payment_status,
                "paid_at"=>$dto->paid_at,
                "order_id"=>$dto->order_id,
                "bank_id"=>$dto->bank_id,
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
