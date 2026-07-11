<?php

namespace App\Http\Controllers;

use App\DTO\Brand\CreateBrandDTO;
use App\DTO\Brand\UpdateBrandDTO;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(private BrandService $BrandService){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $brand=$this->BrandService->getAllUsers();
            return response()->json([
                "status" => "success",
                "data" => $brand
            ]);
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

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        try {
            $result=$this->BrandService->CreateBrand(
                new CreateBrandDTO(
                    $request->name,
                    $request->status,
                )
            );
            return response()->json([
                "status" => "success",
                "message" => "Create Successfully",
                "data" => $result
            ], 201);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $result=$this->BrandService->getbrandById($id);
            return response()->json([
                "status"=>"success",
                "data"=>$result,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, string $id)
    {
        try {
            $result=$this->BrandService->update(
                $id,
                new UpdateBrandDTO(
                    $request->name,
                    $request->status,
                )
            );
            return response()->json([
                "status"=>"Success",
                "message"=>"Update Successfully",
                "data"=>$result
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
            $this->BrandService->deletebrand($id);
            return response()->json([
                "status"=>"success",
               "message" => "User deleted"
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "message"=>$th->getMessage(),
            ],500);
        }
    }
}
