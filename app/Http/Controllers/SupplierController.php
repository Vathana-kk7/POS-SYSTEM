<?php

namespace App\Http\Controllers;

use App\DTO\Supplier\CreateSupplierDTO;
use App\DTO\Supplier\UpdateSupplierDTO;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;

class SupplierController extends Controller
{
    public function __construct(
       private SupplierService $SupplierService
    ){}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result=$this->SupplierService->getall();
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
                    $request->address,
                    $request->phone,
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
            $result=$this->SupplierService->update(
                $id,
                new UpdateSupplierDTO(
                    $request->name,
                    $request->address,
                    $request->phone,
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
}
