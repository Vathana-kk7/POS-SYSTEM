<?php
namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Override;

class CategoryRepository implements CategoryRepositoryInterface{

    public function create(array $data)
    {
        return Category::create($data);
    }
    public function all(){
        return Category::all();
    }
    public function findByid(string $id){
        return Category::findOrFail($id);
    }
    public function delete($id){
        $brand= Category::findOrFail($id);
        $brand->delete($id);
        return $brand;
    }
    public function update(string $id,array $data){
        $category=Category::findOrFail($id);
        $category->update($data);
        return $category;
    }
}
