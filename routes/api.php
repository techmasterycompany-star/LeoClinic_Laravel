<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Doctor\DoctorProfileController;

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

/*
|--------------------------------------------------------------------------
| Doctor Routes
|--------------------------------------------------------------------------
*/

Route::prefix('doctor')
    ->middleware(['auth:sanctum', 'role:doctor'])
    ->group(function () {

        Route::get('/profile', [DoctorProfileController::class, 'show']);
        Route::post('/profile', [DoctorProfileController::class, 'store']);
        Route::put('/profile', [DoctorProfileController::class, 'update']);
    });
