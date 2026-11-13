<?php

use App\Http\Controllers\EquipmentController;
use Illuminate\Support\Facades\Route;

Route::get('/equipment', [EquipmentController::class, 'index']);
Route::post('/equipment', [EquipmentController::class, 'store']);
Route::get('/equipment/{equipment}', [EquipmentController::class, 'show']);
Route::match(['put', 'patch', 'post'], '/equipment/{equipment}', [EquipmentController::class, 'update']);
Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy']);
