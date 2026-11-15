<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\RentalPointController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth.token');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth.token');

// Public catalog
Route::get('/equipment', [EquipmentController::class, 'index']);
Route::get('/equipment/{equipment}', [EquipmentController::class, 'show']);

// Renter area: bookings the current user makes
Route::middleware(['auth.token', 'role:renter'])->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::match(['put', 'patch', 'post'], '/bookings/{booking}', [BookingController::class, 'update']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
});

// Owner area: rental points, the equipment listed at them and incoming bookings
Route::middleware(['auth.token', 'role:owner'])->group(function () {
    Route::post('/equipment', [EquipmentController::class, 'store']);
    Route::match(['put', 'patch', 'post'], '/equipment/{equipment}', [EquipmentController::class, 'update']);
    Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy']);

    Route::prefix('my')->group(function () {
        Route::get('/rental-points', [RentalPointController::class, 'index']);
        Route::post('/rental-points', [RentalPointController::class, 'store']);
        Route::match(['put', 'patch'], '/rental-points/{rentalPoint}', [RentalPointController::class, 'update']);
        Route::delete('/rental-points/{rentalPoint}', [RentalPointController::class, 'destroy']);

        Route::get('/equipment', [EquipmentController::class, 'mine']);
        Route::get('/bookings', [BookingController::class, 'incoming']);
    });
});
