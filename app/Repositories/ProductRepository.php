<?php
namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface{
    public function create(array $data){
        return Product::create($data);
    }
    public function update(string $id,array $data){

    }
    public function all(){

    }
    public function delete(string $id){

    }
    public function findById(string $id){

    }
}
