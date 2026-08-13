<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientProfileController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\RatingController;

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

Route::middleware('auth:sanctum')->prefix('patient')->group(function () {
    Route::get('/profile', [PatientProfileController::class, 'show']);
    Route::put('/profile', [PatientProfileController::class, 'update']);
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/{id}', [DoctorController::class, 'show']);
    Route::get('/doctors/{id}/reviews', [DoctorController::class, 'reviews']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments/{id}/rating', [RatingController::class, 'store']);
});

Route::middleware('auth:sanctum')->prefix('doctor')->group(function () {
    Route::put('/appointments/{id}/confirm', [AppointmentController::class, 'confirm']);
    Route::put('/appointments/{id}/reject', [AppointmentController::class, 'reject']);
    Route::put('/appointments/{id}/complete', [AppointmentController::class, 'complete']);
});


Route::middleware('auth:sanctum')->put('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);