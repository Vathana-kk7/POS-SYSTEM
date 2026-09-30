<?php

namespace App\Http\Controllers;

use App\DTO\Product\CreateProductDTO;
use App\DTO\Product\UpdateProductDTO;
use App\Http\Requests\Product\ExportProductRequest;
use App\Http\Requests\Product\ImportProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProdctRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function state()
    {
        try {
            $state=$this->ProductService->getState();
            return response()->json([
                "status"=>"success",
                "data"=>$state
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "status"=>"error state product",
                "message"=>$th->getMessage(),
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
                    $request->file('image'), // ទាញយក File object
                    $request->min_stock_level,
                    $request->status,
                    $request->product_type,
                    $request->selling_price,
                    $request->brand_id,
                    $request->category_id,
                    // $request->supplier_ids,
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
    public function update(UpdateProdctRequest $request, $id)
{
    try {
        // Retrieve the existing product to get its old image path if no new file is uploaded
        $product = $this->ProductService->getProductById($id);
        $existingImage = is_object($product) ? ($product->image ?? null) : null;

        // Use the new uploaded file if present, otherwise fallback to the existing image string path
        $imageInput = $request->hasFile('image') ? $request->file('image') : $existingImage;

        $result = $this->ProductService->update(
            $id,
            new UpdateProductDTO(
                $request->name,
                $request->stock_qty,
                $request->cost_price,
                $request->description,
                $request->sku,
                $imageInput, // Pass UploadedFile or existing image string/null safely
                $request->min_stock_level,
                $request->status,
                $request->product_type,
                $request->selling_price,
                $request->brand_id,
                $request->category_id,
            )
        );

        return response()->json([
            "status" => "Update success",
            "data" => $result,
        ], 200);

    } catch (\Throwable $th) {
        return response()->json([
            "message" => $th->getMessage()
        ], 500);
    }
}
    public function index(Request $request){
        try {
            $perPage= $request->integer('per_page',10);
            $filter = array_filter([
                'search' => $request->input('search'),
                'status' => $request->input('status'),
            ], fn($value) => !is_null($value) && $value !== "");

            $result=$this->ProductService->getAllProduct($perPage,$filter);
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
    public function import(ImportProductRequest $request): JsonResponse
    {
        try {
            $status = $this->ProductService->importProduct($request->file('file'));
            if(!$status){
                return response()->json([
                    "status"=>"error",
                    "message"=>"Nothing data input or File Emty",
                ],400);
            }
            return response()->json([
                "status"=>"success",
                "message"=>"Input Data from product to excel successfully",
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "status"=>"error",
                "message"=>'មានបញ្ហាក្នុងការ Import: ' . $th->getMessage(),
            ],500);
        }
    }

    public function exportExcel(ExportProductRequest $request)
    {
        try {
            return $this->ProductService->exportExcel($request->validated());

        } catch (\Throwable $th) {
            \Log::error('Product Excel Export Failed', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }
    public function exportPdf(ExportProductRequest $request)
    {
        try {
            return $this->ProductService->exportPdf($request->validated());
        } catch (\Throwable $th) {
            \Log::error('Product PDF Export Failed', [
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
