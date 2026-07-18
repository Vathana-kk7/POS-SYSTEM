<?php

namespace App\Http\Controllers;

use App\DTO\Product\CreateProductDTO;
use App\DTO\Product\UpdateProductDTO;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProdctRequest;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $ProductService
    ){}
    public function show(string $id){
        try {
            $result=$this->ProductService->getProductById($id);
            return response()->json([
                "status"=>"success",
                "data"=>$result
            ],201);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage()
            ],500);
        }
    }
    public function store(StoreProductRequest $request){
        try {
            $result=$this->ProductService->create(
                new CreateProductDTO(
                    $request->name,
                    $request->stock_qty,
                    $request->cost_price,
                    $request->description,
                    $request->sku,
                    $request->image,
                    $request->min_stock_level,
                    $request->status,
                    $request->selling_price,
                    $request->brand_id,
                    $request->category_id,
                    $request->supplier_ids,
                )
            );
            return response()->json([
                "status"=>"Create Success",
                "data"=>$result,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage()
            ],500);
        }
    }
    public function update(UpdateProdctRequest $request,$id){
        try {
            $result=$this->ProductService->update(
                $id,
                new UpdateProductDTO(
                    $request->name,
                    $request->stock_qty,
                    $request->cost_price,
                    $request->description,
                    $request->sku,
                    $request->image,
                    $request->min_stock_level,
                    $request->status,
                    $request->selling_price,
                    $request->brand_id,
                    $request->category_id,
                    $request->supplier_ids,
                )
            );
            return response()->json([
                "status"=>"Update success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage()
            ],500);
        }
    }
    public function index(){
        try {
            $result=$this->ProductService->getAllProduct();
            return response()->json([
                "status"=>"Get Success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage()
            ],500);
        }
    }
    public function destroy(string $id){
        try {
            $result=$this->ProductService->delete($id);
            return response()->json([
                "status"=>"delete Success",
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage()
            ],500);
        }
    }
}
