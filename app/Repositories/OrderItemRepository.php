<?php
namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;

class OrderItemRepository implements OrderItemRepositoryInterface{

    // #[Override]
    public function create(array $data)
    {
        return OrderItem::create($data);
    }
    public function update(string $id,array $data)
    {
        $result=OrderItem::findOrFail($id);
        $result->update($data);
        return $result;
    }
    public function all()
    {
        return OrderItem::all();
    }
    public function findById(string $id)
    {
        return OrderItem::findOrFail($id);
    }
    public function delete(string $id)
    {
        $result= OrderItem::findOrFail($id);
        $result->delete($id);
        return $result;
    }
}
