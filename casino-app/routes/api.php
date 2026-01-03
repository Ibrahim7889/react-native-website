<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BetController;
use App\Http\Controllers\Api\RoundController;
use App\Http\Controllers\Api\WalletController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/bets', [BetController::class, 'store']);
    Route::get('/bets/history', [BetController::class, 'history']);
    Route::get('/wallet', [WalletController::class, 'show']);
    Route::get('/rounds/current', [RoundController::class, 'current']);
});
