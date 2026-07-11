<?php

namespace App\Http\Controllers;

use App\DTO\Product\CreateProductDTO;
use App\Http\Requests\Product\StoreProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {

            $dto = new CreateProductDTO(
                name: $request->name,
                stock_qty: $request->stock_qty,
                cost_price: $request->cost_price,
                description: $request->description,
                sku: $request->sku,
                image: $request->image,
                min_stock_level: $request->min_stock_level,
                status: $request->status,
                selling_price: $request->selling_price,
                brand_id: $request->brand_id,
                category_id: $request->category_id,
            );


            $result = $this->productService->create($dto);


            return response()->json([
                "status" => "success",
                "message" => "Product created successfully",
                "data" => $result
            ], 201);


        } catch (\Throwable $th) {

            return response()->json([
                "status" => "error",
                "message" => $th->getMessage()
            ], 500);

        }
    }
}
