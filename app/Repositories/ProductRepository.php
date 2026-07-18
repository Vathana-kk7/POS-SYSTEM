<?php
namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface{
    public function create(array $data){
        return Product::create($data);
    }
    public function update(string $id,array $data){
        $result=Product::findOrFail($id);
        $result->update($data);
        return $result;
    }
    public function all(){
        return Product::all();
    }
    public function delete(string $id){
        $result= Product::findOrFail($id);
        $result->delete($id);
        return $result;
    }
    public function findById(string $id){
        return Product::findOrFail($id);
    }
}
