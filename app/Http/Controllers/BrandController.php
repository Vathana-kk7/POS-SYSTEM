<?php

namespace App\Http\Controllers;

use App\DTO\Brand\CreateBrandDTO;
use App\DTO\Brand\UpdateBrandDTO;
use App\Http\Requests\Brand\ExportBrandRequest;
use App\Http\Requests\Brand\ImportBrandRequest;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(private BrandService $BrandService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->integer('per_page', 10);
            $filter = [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
            ];
            $brand = $this->BrandService->getAllBrand($perPage, $filter);

            return response()->json([
                "status" => "success",
                "data" => $brand
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        try {
            $result = $this->BrandService->CreateBrand(
                new CreateBrandDTO(
                    $request->name,
                    $request->status,
                )
            );
            return response()->json([
                "status" => "success",
                "message" => "Create Successfully",
                "data" => $result
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $result = $this->BrandService->getbrandById($id);
            return response()->json([
                "status" => "success",
                "data" => $result,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, string $id)
    {
        try {
            $result = $this->BrandService->update(
                $id,
                new UpdateBrandDTO(
                    $request->name,
                    $request->status,
                )
            );
            return response()->json([
                "status" => "Success",
                "message" => "Update Successfully",
                "data" => $result
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function stats()
    {
        try {
            $stats = $this->BrandService->getBrandStats();

            return response()->json([
                "status" => "success",
                "data" => $stats,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->BrandService->delete($id);

            return response()->json([
                "status" => "success",
                "message" => "Brand deleted successfully",
            ], 200);
        } catch (\Throwable $th) {
            \Log::error("Delete Brand Failed", [
                "brand_id" => $id,
                "error" => $th->getMessage(),
                "file" => $th->getFile(),
                "line" => $th->getLine(),
            ]);

            return response()->json([
                "status" => "error",
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Import brands from Excel file.
     */
    public function import(ImportBrandRequest $request): JsonResponse
    {
        try {
            $status = $this->BrandService->importBrands($request->file('file'));

            if (!$status) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'គ្មានទិន្នន័យត្រូវបានបញ្ចូលទេ ឬ File ទទេ!'
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'ការបញ្ចូលទិន្នន័យ Brand ពី Excel បានជោគជ័យ!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'មានបញ្ហាក្នុងការ Import: ' . $th->getMessage(),
            ], 500);
        }
    }


    public function export(
    ExportBrandRequest $request,
    string $type
) {
    return $this->BrandService->exportBrands(
        $type,
        $request->validated()
    );
}
}
