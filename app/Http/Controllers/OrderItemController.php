<?php

namespace App\Http\Controllers;

use App\DTO\OrderItem\CreateOrderItemDTO;
use App\DTO\OrderItem\UpdateOrderItemDTO;
use App\Http\Requests\OrderItem\StoreOrderItemRequest;
use App\Http\Requests\OrderItem\UpdateOrderItemRequest;
use App\Services\OrderItemService;

class OrderItemController extends Controller
{
    public function __construct(
        private OrderItemService $OrderItemService
    ){}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $result=$this->OrderItemService->all();
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
    public function store(StoreOrderItemRequest $request)
    {
        try {
            $result=$this->OrderItemService->create(
                new CreateOrderItemDTO(
                    $request->product_id,
                    $request->order_id,
                    $request->quantity,
                    $request->unit_price,
                    $request->discount,
                    $request->subtotal,
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
            $result=$this->OrderItemService->findById($id);
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
    public function update(string $id, UpdateOrderItemRequest $request)
    {
        try {
            $result=$this->OrderItemService->update(
                $id,
                new UpdateOrderItemDTO(
                    $request->product_id,
                    $request->order_id,
                    $request->quantity,
                    $request->unit_price,
                    $request->discount,
                    $request->subtotal,
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
            $result=$this->OrderItemService->delete($id);
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
