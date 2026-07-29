<?php

namespace App\Http\Controllers;

use App\DTO\Unite\CreateUniteDTO;
use App\DTO\Unite\UpdateUniteDTO;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Http\Requests\Unite\StoreUniteRequest;
use App\Http\Requests\Unite\UpdateUniteRequest;
use App\Services\UniteService;

class UniteController extends Controller
{
    public function __construct(
        private UniteService $UniteService
    ){}
    public function index(){
        try {
            $result=$this->UniteService->all();
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
    public function show(string $id){
        try {
            $result=$this->UniteService->findById($id);
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
    public function store(StoreUniteRequest $request){
        try {
            $result=$this->UniteService->create(
                new CreateUniteDTO(
                    $request->name,
                    $request->symbol,
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
    public function update(string $id,UpdateUniteRequest $request){
        try {
            $result=$this->UniteService->update(
                $id,
                new UpdateUniteDTO(
                    $request->name,
                    $request->symbol,
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
}
