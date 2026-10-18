<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;

// Endpoint Publik
Route::post('/login', [AuthController::class, 'login']);

// Endpoint Terproteksi Token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('buku', BukuController::class);
});