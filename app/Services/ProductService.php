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

            $product = $this->repo->create([
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

            // insert data into product_supplier
            $product->suppliers()->attach($dto->supplier_ids);

            return $product;

        } catch (\Throwable $th) {

            throw $th;

        }
    }

    public function update(string $id, UpdateProductDTO $dto)
    {
        try {

            $result = $this->repo->update(
                $id,
                [
                    "name" => $dto->name,
                    "stock_qty" => $dto->stock_qty,
                    "cost_price" => $dto->cost_price,
                    "description" => $dto->description,
                    "sku" => $dto->sku,
                    "image" => $dto->image,
                    "min_stock_level" => $dto->min_stock_level,
                    "status" => $dto->status,
                    "selling_price" => $dto->selling_price,
                    "brand_id" => $dto->brand_id,
                    "category_id" => $dto->category_id,
                ]
            );
            return $result;

        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function delete($id){
        return $this->repo->delete($id);
    }
    public function getAllProduct(){
        try {
            $result=$this->repo->all();
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function getProductById($id){
        try {
            $result=$this->repo->findById($id);
            return $result;
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
}
