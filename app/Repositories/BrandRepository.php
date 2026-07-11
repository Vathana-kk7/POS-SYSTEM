<?php
namespace App\Repositories;

use App\Models\Brand;
use App\Repositories\Interfaces\BrandRepositoryInterface;

class BrandRepository implements BrandRepositoryInterface{
    public function create(array $data):Brand
    {
        return Brand::create($data);
    }
    public function all(){
        return Brand::all();
    }
    public function findById(string $id){
        return Brand::findOrFail($id);
    }
    public function update(string $id,array $data){
        $brand=Brand::findOrFail($id);
        $brand->update($data);
        return $brand;
    }
    public function delete($id){
        $brand= Brand::findOrFail($id);
        $brand->delete($id);
        return $brand;
    }
}
