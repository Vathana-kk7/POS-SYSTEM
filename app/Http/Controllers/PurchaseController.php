<?php

namespace App\Http\Controllers;

use App\DTO\Purchase\CreatePurchaseDTO;
use App\DTO\Purchase\UpdatePurchaseDTO;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(
        private PurchaseService $PurchaseService
    ){}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result=$this->PurchaseService->all();
            return response()->json([
                "status"=>"GetAll Success",
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
    public function store(StorePurchaseRequest $request)
    {
        try {
            $result=$this->PurchaseService->create(
                new CreatePurchaseDTO(
                    $request->purchase_date,
                    $request->total,
                    $request->status,
                    $request->supplier_id,
                    $request->user_id,
                )
            );
            return response()->json([
                "status"=>"Create Success",
                "data"=>$result,
            ],200);
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
            $result=$this->PurchaseService->findById($id);
            return response()->json([
                "status"=>"Show Success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseRequest $request ,$id)
    {
        try {
            $result=$this->PurchaseService->update(
                $id,
                new UpdatePurchaseDTO(
                    $request->purchase_date,
                    $request->total,
                    $request->status,
                    $request->supplier_id,
                    $request->user_id,
                )
            );
            return response()->json([
                "status"=>"Update Success",
                "data"=>$result,
            ],200);
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
            return $this->PurchaseService->delete($id);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
}
