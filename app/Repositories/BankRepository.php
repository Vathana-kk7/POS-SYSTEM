<?php
namespace App\Repositories;

use App\Models\Bank;
use App\Repositories\Interfaces\BankRepositoryInterface;

class BankRepository implements BankRepositoryInterface{
    public function create(array $data)
    {
        return Bank::create($data);
    }
    public function update(string $id,array $data)
    {
        $result=Bank::findOrFail($id);
        $result->update($data);
        return $result;
    }
    public function all()
    {
        return Bank::all();
    }
    public function findById(string $id)
    {
        return Bank::findOrFail($id);
    }
    public function delete(string $id)
    {
        $result= Bank::findOrFail($id);
        $result->delete($id);
        return $result;
    }
}
