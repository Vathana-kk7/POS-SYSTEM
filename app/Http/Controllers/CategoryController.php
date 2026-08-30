<?php

namespace App\Http\Controllers;

use App\DTO\Brand\UpdateBrandDTO;
use App\DTO\Category\CrateCategoryDTO;
use App\DTO\Category\UpdateCategoryDTO;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $CategoryService
    ){}
    public function index(Request $request){
        try {
            $perPage=$request->integer('per_page',10);
            $filter=array_filter([
                'search' => $request->input('search'),
                'status' => $request->input('status'),
            ],fn($value)=>!is_null($value) && $value !=="");

            $result=$this->CategoryService->getCategory($perPage,$filter);
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

    public function store(StoreCategoryRequest $request){
        try {
            $result=$this->CategoryService->create(
                new CrateCategoryDTO(
                    $request->name,
                    $request->description,
                    $request->status,
                )
            );
            return response()->json([
                "status"=>"success",
                "data"=>$result
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function show(string $id){
        try {
            $result=$this->CategoryService->getCategoryById($id);
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
    public function destroy(string $id){
        try {
            $this->CategoryService->delete($id);
            return response()->json([
                "status"=>"success",
                "message"=>"Category was deleted",
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
    public function update(UpdateCategoryRequest $request, string $id)
{
    try {
        $result = $this->CategoryService->update(
            $id,
            new UpdateCategoryDTO(
                $request->name,
                $request->description,
                $request->status,

            )
        );

        return response()->json([
            "status" => "Update success",
            "data" => $result,
        ], 200);

    } catch (\Throwable $th) {
        return response()->json([
            "message" => $th->getMessage(),
        ], 500);
    }
}
}
