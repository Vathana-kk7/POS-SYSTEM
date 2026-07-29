<?php

namespace App\Http\Controllers;

use App\DTO\OrderPaymentMethod\CreateOrderPaymentMethodDTO;
use App\DTO\OrderPaymentMethod\UpdateOrderPaymentMethodDTO;
use App\Http\Requests\OrderPaymentMethod\StoreOrderPaymentMethodRequest;
use App\Http\Requests\OrderPaymentMethod\UpdateOrderPaymentMethodRequest;
use App\Services\OrderPaymentMethodService;

class OrderPaymentMethodController extends Controller
{
    public function __construct(
        private OrderPaymentMethodService $OrderPaymentMethodService
    ){}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result=$this->OrderPaymentMethodService->all();
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderPaymentMethodRequest $request)
    {
        try {
            $result=$this->OrderPaymentMethodService->create(
                new CreateOrderPaymentMethodDTO(
                    $request->amount,
                    $request->transaction_id,
                    $request->payment_status,
                    $request->paid_at,
                    $request->order_id,
                    $request->bank_id,
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
            $result=$this->OrderPaymentMethodService->findById($id);
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
     * Show the form for editing the specified resource.
     */


    /**
     * Update the specified resource in storage.
     */
    public function update(string $id, UpdateOrderPaymentMethodRequest $request)
    {
        try {
            $result=$this->OrderPaymentMethodService->update(
                $id,
                new UpdateOrderPaymentMethodDTO(
                    $request->amount,
                    $request->transaction_id,
                    $request->payment_status,
                    $request->paid_at,
                    $request->order_id,
                    $request->bank_id,
                )
            );
            return $result;
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
            $result=$this->OrderPaymentMethodService->delete($id);
            return response()->json([
                "message"=>"Delete Success",
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
}
