<?php
namespace App\Repositories;

use App\Models\PurchaseItem;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;

class PurchaseItemRepository implements PurchaseItemRepositoryInterface{
    public function create(array $data){
        return PurchaseItem::create($data);
    }
    public function update(string $id,array $data){
        $result=PurchaseItem::findOrFail($id);
        $result->update($data);
        return $result;
    }
    public function all(){
        return PurchaseItem::all();
    }
    public function findById(string $id){
        return PurchaseItem::findOrFail($id);
    }
    public function delete(string $id){
        $result=PurchaseItem::findOrFail($id);
        $result->delete($id);
        return $result;
    }
}
