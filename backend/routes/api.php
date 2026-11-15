<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquipmentController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth.token');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth.token');

// Equipment routes
Route::get('/equipment', [EquipmentController::class, 'index']);
Route::post('/equipment', [EquipmentController::class, 'store']);
Route::get('/equipment/{equipment}', [EquipmentController::class, 'show']);
Route::match(['put', 'patch', 'post'], '/equipment/{equipment}', [EquipmentController::class, 'update']);
Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy']);
