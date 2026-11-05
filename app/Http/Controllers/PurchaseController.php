<?php

namespace App\Http\Controllers;

use App\DTO\Purchase\CreatePurchaseDTO;
use App\DTO\Purchase\UpdatePurchaseDTO;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function __construct(
        private PurchaseService $PurchaseService
    ){}
    /**
     * Display a listing of the resource.
     */
    public function getstate(){
        try {
            $result=$this->PurchaseService->getstate();
            return response()->json([
                "status"=>"Getstate Success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
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
            $data = $request->validated();

            $result = $this->PurchaseService->create(
                new CreatePurchaseDTO(
                    purchase_date: $data['purchase_date'],
                    supplier_id: (int) $data['supplier_id'],
                    // Get user ID from authenticated user
                    user_id: (int) $request->user()->id,

                    expected_delivery_date: $data['expected_delivery_date'] ?? null,
                    discount: (float) ($data['discount'] ?? 0),
                    tax: (float) ($data['tax'] ?? 0),
                    shipping_cost: (float) ($data['shipping_cost'] ?? 0),
                    payment_terms: $data['payment_terms'] ?? null,
                    notes: $data['notes'] ?? null,
                    status: $data['status'] ?? 'draft',
                ),
                $data['items'],
            );

            return response()->json([
                'status' => 'Create Success',
                'data' => $result,
            ], 201);

        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
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
    public function update(UpdatePurchaseRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $result = $this->PurchaseService->update(
                $id,
                new UpdatePurchaseDTO(
                    purchase_date: $data['purchase_date'],
                    supplier_id: (int) $data['supplier_id'],
                    user_id: (int) $request->user()->id,
                    expected_delivery_date: $data['expected_delivery_date'] ?? null,
                    discount: (float) ($data['discount'] ?? 0),
                    tax: (float) ($data['tax'] ?? 0),
                    shipping_cost: (float) ($data['shipping_cost'] ?? 0),
                    payment_terms: $data['payment_terms'] ?? null,
                    notes: $data['notes'] ?? null,
                    status: $data['status'] ?? 'draft',
                ),
                $data['items'],   // ← បន្ថែមនេះ
            );

            return response()->json([
                'status' => 'Update Success',
                'data' => $result,
            ], 200);

        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
{
    try {
        $this->PurchaseService->delete($id);

        return response()->json([
            'status' => 'Delete Success',
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => collect($e->errors())->flatten()->first(),
        ], 422);
    } catch (\Throwable $th) {
        return response()->json([
            'message' => $th->getMessage(),
        ], 500);
    }
}
}
