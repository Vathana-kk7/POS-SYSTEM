<?php

namespace App\Http\Controllers;

use App\DTO\Order\CreateOrderDTO;
use App\DTO\Order\UpdateOrderDTO;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $OrderService
    ){}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result=$this->OrderService->all();
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
    public function store(StoreOrderRequest $request)
    {
        try {
            $result=$this->OrderService->create(
                new CreateOrderDTO(
                    $request->total,
                    $request->discount,
                    $request->tax,
                    $request->grand_total,
                    $request->payment_status,
                    $request->order_status,
                    $request->customer_id,
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
            $result=$this->OrderService->findById($id);
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
    public function update(string $id, UpdateOrderRequest $request)
    {
        try {
            $result=$this->OrderService->update(
                $id,
                new UpdateOrderDTO(
                    $request->total,
                    $request->discount,
                    $request->tax,
                    $request->grand_total,
                    $request->payment_status,
                    $request->order_status,
                    $request->customer_id,
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
            $result=$this->OrderService->delete($id);
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
