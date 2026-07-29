<?php
namespace App\Repositories;

use App\Models\OrderPaymentMethod;
use App\Repositories\Interfaces\OrderPaymentMethodRepositoryInterface;

class OrderPaymentMethodRepository implements OrderPaymentMethodRepositoryInterface{
    public function create(array $data)
    {
        return OrderPaymentMethod::create($data);
    }
    public function update(string $id,array $data)
    {
        $result=OrderPaymentMethod::findOrFail($id);
        $result->update($data);
        return $result;
    }
    public function all()
    {
        return OrderPaymentMethod::all();
    }
    public function findById(string $id)
    {
        return OrderPaymentMethod::findOrFail($id);
    }
    public function delete(string $id)
    {
        $result= OrderPaymentMethod::findOrFail($id);
        $result->delete($id);
        return $result;
    }
}
