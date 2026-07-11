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
}
