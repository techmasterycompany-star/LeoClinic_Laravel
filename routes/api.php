<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\SpecialtyController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

    Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);

    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

});
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {

    Route::get('/specialties', [SpecialtyController::class, 'index']);
    Route::post('/specialties', [SpecialtyController::class, 'store']);
    Route::put('/specialties/{specialty}', [SpecialtyController::class, 'update']);
    Route::delete('/specialties/{specialty}', [SpecialtyController::class, 'destroy']);

});

Route::middleware(['auth:sanctum', 'role:doctor'])->group(function () {

    // Doctor Routes

});

Route::middleware(['auth:sanctum', 'role:patient'])->group(function () {

    // Patient Routes

});