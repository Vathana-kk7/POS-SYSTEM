<?php
namespace App\Repositories;

use App\Models\Customer;
use App\Repositories\Interfaces\CustomerRepositoryInterface;


class CustomerRepository implements CustomerRepositoryInterface{

    // #[Override]
    public function create(array $data)
    {
        return Customer::create($data);
    }
    public function update(string $id,array $data)
    {
        $result=Customer::findOrFail($id);
        $result->update($data);
        return $result;
    }
    public function all()
    {
        return Customer::all();
    }
    public function findById(string $id)
    {
        return Customer::findOrFail($id);
    }
    public function delete(string $id)
    {
        $result= Customer::findOrFail($id);
        $result->delete($id);
        return $result;
    }
}
