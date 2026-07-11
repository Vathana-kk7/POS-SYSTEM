<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::apiResource('brand', BrandController::class);
Route::apiResource("category",CategoryController::class);
Route::apiResource("product",ProductController::class);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (LOGIN REQUIRED)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:Admin'])->get('/admin', function () {
    return response()->json([
        'message' => 'Welcome Admin 👑',
        'user' => auth()->user()->name,
        'role' => auth()->user()->role->name
    ]);
});
Route::middleware(['auth:sanctum', 'role:Manager'])->get('/manager-test', function () {
    return response()->json([
        'message' => 'Welcome Manager',
        'user' => auth()->user()->name,
        'role' => auth()->user()->role->name
    ]);
});
Route::middleware('auth:sanctum')->group(function () {

    // 👤 Current user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // 🚪 Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN ONLY
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Admin')->group(function () {

        Route::apiResource('users', UserController::class);

    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN + MANAGER
    |--------------------------------------------------------------------------
    */
    // Route::middleware('role:Admin,Manager')->group(function () {

    //     Route::get('/products', [ProductController::class, 'index']);
    //     Route::post('/products', [ProductController::class, 'store']);
    //     Route::put('/products/{id}', [ProductController::class, 'update']);
    //     Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    // });

    /*
    |--------------------------------------------------------------------------
    | ALL ROLES (SALE SYSTEM)
    |--------------------------------------------------------------------------
    */
    // Route::middleware('role:Admin,Manager,Staff')->group(function () {

    //     Route::post('/sales', [\App\Http\Controllers\SaleController::class, 'store']);
    //     Route::get('/sales', [\App\Http\Controllers\SaleController::class, 'index']);
    // });

});
