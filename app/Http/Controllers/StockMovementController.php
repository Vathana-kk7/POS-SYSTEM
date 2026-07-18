<?php

namespace App\Http\Controllers;

use App\DTO\StockMovement\CreateStockMovementDTO;
use App\DTO\StockMovement\UpdateStockMovementDTO;
use App\Http\Requests\StockMovement\StoreStockMovementRequest;
use App\Http\Requests\StockMovement\UpdateStockMovementRequest;
use App\Services\StockmovementService;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function __construct(
        private StockmovementService $stockmovementService
    ){}
    public function index(){
        try {
            $result=$this->stockmovementService->all();

            return response()->json([
                "status"=>"GetAll Success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage()
            ],500);
        }
    }
    public function show(string $id){
        try {
            $result=$this->stockmovementService->findById($id);

            return response()->json([
                "status"=>"Get Success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage()
            ],500);
        }
    }
    public function destroy(string $id){
        try {
            $result=$this->stockmovementService->delete($id);

            return response()->json([
                "status"=>"Delete Success",
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage()
            ],500);
        }
    }
    public function store(StoreStockMovementRequest $request){
        try {
            $result=$this->stockmovementService->create(
                new CreateStockMovementDTO(
                    $request->product_id,
                    $request->user_id,
                    $request->type,
                    $request->quantity,
                    $request->stock_before,
                    $request->stock_after,
                    $request->reference_type,
                    $request->reference_id,
                )
            );
            return response()->json([
                "status"=>"Create Success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage()
            ],500);
        }
    }
    public function update(string $id, UpdateStockMovementRequest $request){
        try {
            $result=$this->stockmovementService->update(
                $id,
                new UpdateStockMovementDTO(
                    $request->product_id,
                    $request->user_id,
                    $request->type,
                    $request->quantity,
                    $request->stock_before,
                    $request->stock_after,
                    $request->reference_type,
                    $request->reference_id,
                )
            );
            return response()->json([
                "status"=>"Update Success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage()
            ],500);
        }
    }
}
