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
    public function all($perPage,array $filters=[]){
        $query= Category::query()->latest();
        //1ត្រួតពិនិត្យSearch Filter
        if(!empty($filters['search'])){
            $query->where(
                'name',
                'like',
                '%'.trim($filters['search']).'%'
            );
        }
        if(!empty($filters['status']) && $filters['status']!=="all"){
           $query->where(
                'status',
                $filters['status'],
            );
        }
        return $query->paginate($perPage);
    }
    public function findByid(string $id){
        return Category::findOrFail($id);
    }
    public function delete($id){
        $category= Category::findOrFail($id);
        $category->delete();
        return $category;
    }
    public function update(string $id,array $data){
        $category=Category::findOrFail($id);
        $category->update($data);
        return $category;
    }
}
