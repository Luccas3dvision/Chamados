<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rota pública para obter o token
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Rotas protegidas por token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::apiResource('tickets', TicketController::class);
});