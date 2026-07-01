<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);

// Route សម្រាប់រុញ User ទៅកាន់ផ្ទាំង Login របស់ Google
Route::get('auth/google', [AuthController::class, 'redirectToGoogle']);
// Route សម្រាប់ចាំទទួលទិន្នន័យត្រឡប់មកវិញពី Google (Callback)
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
