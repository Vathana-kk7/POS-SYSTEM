<?php
namespace App\Services;

use App\DTO\Product\CreateProductDTO;
use App\DTO\Product\UpdateProductDTO;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductService{
    public function __construct(private ProductRepositoryInterface $repo){}
    public function create(CreateProductDTO $dto)
{
    try {

        $imagePath = $dto->image;

        return $this->repo->create([
            "name" => $dto->name,
            "stock_qty" => $dto->stock_qty,
            "cost_price" => $dto->cost_price,
            "description" => $dto->description,
            "sku" => $dto->sku,
            "image" => $imagePath,
            "min_stock_level" => $dto->min_stock_level,
            "status" => $dto->status,
            "selling_price" => $dto->selling_price,
            "brand_id" => $dto->brand_id,
            "category_id" => $dto->category_id,
        ]);

    } catch (\Throwable $th) {

        throw $th;

    }
}

    public function update(UpdateProductDTO $dto){

    }
    public function delete($id){

    }
    public function getAllProduct(){

    }
    public function getProductById(){

    }
}
