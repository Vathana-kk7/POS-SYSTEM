<?php

namespace App\Http\Controllers;

use App\DTO\Supplier\CreateSupplierDTO;
use App\DTO\Supplier\UpdateSupplierDTO;
use App\Http\Requests\Supplier\ExportSupplierRequest;
use App\Http\Requests\Supplier\ImportSupplierRequest;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(
       private SupplierService $SupplierService
    ){}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->validate([
                'page' => ['sometimes', 'integer', 'min:1'],
                'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'search' => ['sometimes', 'nullable', 'string', 'max:255'],
                'status' => ['sometimes', 'nullable', 'string', 'max:20'],
            ]);
            $perPage = (int) ($filters['per_page'] ?? 10);
            unset($filters['page'], $filters['per_page']);
            $result=$this->SupplierService->getAll($perPage, $filters);
            return response()->json([
                "status"=>"success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        try {
            $result=$this->SupplierService->create(
                new CreateSupplierDTO(
                    $request->name,
                    $request->address ?? '',
                    $request->phone,
                    $request->contact_person,
                    $request->email,
                    $request->status ?? 'Active',
                )
            );
            return response()->json([
                "status"=>"success",
                "data"=>$result,
            ],201);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $result=$this->SupplierService->getById($id);
            return response()->json([
                "status"=>"success",
                "data"=>$result,
            ],201);

        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, string $id)
    {
        try {
            $supplier = $this->SupplierService->getById($id);
            $data = $request->validated();
            $result=$this->SupplierService->update(
                $id,
                new UpdateSupplierDTO(
                    $data['name'] ?? $supplier->name,
                    $data['address'] ?? $supplier->address ?? '',
                    $data['phone'] ?? $supplier->phone,
                    $data['contact_person'] ?? $supplier->contact_person,
                    array_key_exists('email', $data) ? $data['email'] : $supplier->email,
                    $data['status'] ?? $supplier->status,
                )
            );
            return response()->json([
                "status"=>"success",
                "data"=>$result,
            ],201);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $result=$this->SupplierService->delete($id);
            return response()->json([
                "status"=>"success",
                "data"=>$result,
            ],201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'មិនអាចលុប Supplier បានទេ ព្រោះនៅមាន Products កំពុងប្រើ Supplier នេះ។'
            ],409);
        }
    }

    public function stats()
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->SupplierService->stats(),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function import(ImportSupplierRequest $request)
    {
        try {
            $result = $this->SupplierService->importSuppliers($request->file('file'));
            if ($result['imported'] === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['errors'] !== []
                        ? 'No valid suppliers could be imported.'
                        : 'The Excel file contains no supplier rows.',
                    'errors' => array_slice($result['errors'], 0, 100, true),
                ], 422);
            }

            return response()->json([
                'status' => 'success',
                'message' => $result['skipped'] > 0
                    ? "{$result['imported']} suppliers imported; {$result['skipped']} invalid or duplicate rows skipped."
                    : "{$result['imported']} suppliers imported successfully.",
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'errors' => array_slice($result['errors'], 0, 100, true),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier import failed: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(ExportSupplierRequest $request)
    {
        try {
            return $this->SupplierService->exportExcel($request->validated());
        } catch (\Throwable $th) {
            \Log::error('Supplier Excel export failed', ['message' => $th->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier Excel export failed.',
            ], 500);
        }
    }

    public function exportPdf(ExportSupplierRequest $request)
    {
        try {
            return $this->SupplierService->exportPdf($request->validated());
        } catch (\Throwable $th) {
            \Log::error('Supplier PDF export failed', ['message' => $th->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier PDF export failed.',
            ], 500);
        }
    }
}
