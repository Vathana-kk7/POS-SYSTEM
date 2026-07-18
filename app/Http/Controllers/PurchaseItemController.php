<?php

namespace App\Http\Controllers;

use App\DTO\PurchaseItem\CreatePurchaseItemDTO;
use App\DTO\PurchaseItem\UpdatePurchaseItemDTO;
use App\Http\Requests\PurchaseItem\StorePurchaseItemRequest;
use App\Http\Requests\PurchaseItem\UpdatePurchaseItemRequest;
use App\Services\PurchaseItemService;
use Illuminate\Http\Request;

class PurchaseItemController extends Controller
{
    public function __construct(
        private PurchaseItemService $PurchaseItemService
    ){}
    public function index(){
        try {
            $result=$this->PurchaseItemService->all();
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
    public function store(StorePurchaseItemRequest $request){
        try {
            $result=$this->PurchaseItemService->create(
            new CreatePurchaseItemDTO(
                $request->purchase_id,
                $request->product_id,
                $request->quantity,
                $request->cost_price,
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
    public function show(string $id){
        try {
            $result=$this->PurchaseItemService->findById($id);
            return response()->json([
                "status"=>"Get Success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function update(string $id,UpdatePurchaseItemRequest $request){
        try {
            $result=$this->PurchaseItemService->update(
                $id,
                new UpdatePurchaseItemDTO(
                    $request->purchase_id,
                    $request->product_id,
                    $request->quantity,
                    $request->cost_price,
                    $request->subtotal,
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
    public function destroy(string $id){
        try {
            $this->PurchaseItemService->delete($id);
            return response()->json([
                "status"=>"Delete Success",
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
}
