<?php
namespace App\Repositories;

use App\Models\Purchase;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;

class PurchaseRepository implements PurchaseRepositoryInterface{
    public function create(array $data){
        return Purchase::create($data);
    }
    public function update(string $id,array $data){
        $result=Purchase::findOrFail($id);
        $result->update($data);
        return $result;
    }
    public function all(){
        return Purchase::all();
    }
    public function findById(string $id){
        return Purchase::findOrFail($id);
    }
    public function delete(string $id){
        $result= Purchase::findOrFail($id);
        return $result->refresh();
    }
}
