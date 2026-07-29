<?php
namespace App\Repositories;

use App\Models\Unite;
use App\Repositories\Interfaces\UniteRepositoryInterface;
use Override;

class UniteRepository implements UniteRepositoryInterface{

    public function create(array $data)
    {
        return Unite::create($data);
    }
    public function update(string $id,array $data)
    {
        $result=Unite::findOrFail($id);
        $result->update($data);
        return $result;
    }
    public function findById(string $id)
    {
        return Unite::findOrFail($id);
    }
    public function delete(string $id)
    {
        $result=Unite::findOrFail($id);
        $result->delete($id);
        return $result;
    }
    public function all()
    {
        return Unite::all();
    }
}
