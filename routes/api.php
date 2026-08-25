<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\OrderPaymentMethodController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UniteController;
use App\Http\Controllers\UserController;

Route::middleware('web')->group(function () {
    // Public Routes
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect.alias');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    Route::get('/test-auth', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        // 🔑 បន្ថែម Route នេះសម្រាប់ឲ្យ React ទាញយក User profile តាមរយៈ Sanctum Cookie
        Route::get('/user', function (Request $request) {
            return response()->json($request->user());
        });

        // Admin Only
        Route::middleware('role:Admin')->group(function () {
            Route::get('/brand/stats', [BrandController::class, 'stats']);
            Route::apiResource('users', UserController::class);
            Route::apiResource('brand', BrandController::class);
            Route::apiResource('category', CategoryController::class);
            Route::apiResource('bank', BankController::class);
            Route::apiResource('orderPaymentMethod', OrderPaymentMethodController::class);
            Route::apiResource('unite', UniteController::class);
        });

        // Admin + Manager
        Route::middleware('role:Admin,Manager')->group(function () {
            Route::apiResource('product', ProductController::class);
            Route::apiResource('supplier', SupplierController::class);
            Route::apiResource('purchase', PurchaseController::class);
            Route::apiResource('purchaseItem', PurchaseItemController::class);
            Route::apiResource('stockMovement', StockMovementController::class);
        });

        // Admin + Manager + Staff
        Route::middleware('role:Admin,Manager,Staff')->group(function () {
            Route::apiResource('customer', CustomerController::class);
            Route::apiResource('order', OrderController::class);
            Route::apiResource('orderItem', OrderItemController::class);
        });
    });
});
