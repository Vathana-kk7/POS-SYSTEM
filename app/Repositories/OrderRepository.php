<?php
namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface{

    // #[Override]
    public function create(array $data)
    {
        return Order::create($data);
    }
    public function update(string $id,array $data)
    {
        $result=Order::findOrFail($id);
        $result->update($data);
        return $result;
    }
    public function all()
    {
        return Order::all();
    }
    public function findById(string $id)
    {
        return Order::findOrFail($id);
    }
    public function delete(string $id)
    {
        $result= Order::findOrFail($id);
        $result->delete($id);
        return $result;
    }
}
