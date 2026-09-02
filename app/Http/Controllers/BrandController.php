<?php

namespace App\Http\Controllers;

use App\DTO\Brand\CreateBrandDTO;
use App\DTO\Brand\UpdateBrandDTO;
use App\Http\Requests\Brand\ExportBrandRequest;
use App\Http\Requests\Brand\ImportBrandRequest;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(
        private BrandService $brandService
    ) {}

    public function index(Request $request)
    {
        try {
            $perPage = $request->integer('per_page', 10);
            $filter = array_filter([
                'search' => $request->input('search'),
                'status' => $request->input('status'),
            ], fn($value) => !is_null($value) && $value !== "");

            $result = $this->brandService->getAllBrand($perPage, $filter);

            return response()->json([
                "status" => "success",
                "data" => $result,
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
            $stats = $this->brandService->getBrandStats();

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

    public function store(StoreBrandRequest $request)
    {
        try {
            $result = $this->brandService->CreateBrand(
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
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $result = $this->brandService->getbrandById($id);

            return response()->json([
                "status" => "success",
                "data" => $result,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateBrandRequest $request, string $id)
    {
        try {
            $result = $this->brandService->update(
                $id,
                new UpdateBrandDTO(
                    $request->name,
                    $request->status,
                )
            );

            return response()->json([
                "status" => "success",
                "message" => "Update Successfully",
                "data" => $result,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->brandService->delete($id);

            return response()->json([
                "status" => "success",
                "message" => "Brand was deleted",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function import(ImportBrandRequest $request): JsonResponse
    {
        try {
            $status = $this->brandService->importBrands($request->file('file'));

            if (!$status) {
                return response()->json([
                    "status" => "error",
                    "message" => "គ្មានទិន្នន័យត្រូវបានបញ្ចូលទេ ឬ File ទទេ!",
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

    public function exportExcel(ExportBrandRequest $request)
    {
        try {
            return $this->brandService->exportBrandsExcel($request->validated());
        } catch (\Throwable $th) {
            \Log::error('Excel Export Failed', [
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

    public function exportPdf(ExportBrandRequest $request)
    {
        try {
            return $this->brandService->exportBrandsPdf(
                $request->validated()
            );
        } catch (\Throwable $th) {
            \Log::error('PDF Export Failed', [
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
}
