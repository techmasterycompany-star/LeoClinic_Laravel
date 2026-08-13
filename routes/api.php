<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\SpecialtyController;
use App\Http\Controllers\Api\Admin\LocationController;
use App\Http\Controllers\Api\Doctor\DoctorProfileController;
use App\Http\Controllers\Api\Admin\DoctorApprovalController;
use App\Http\Controllers\Api\Admin\PatientApprovalController;
use App\Http\Controllers\Api\Admin\UserBlockController;
use App\Http\Controllers\Api\Doctor\DoctorLocationController;
use App\Http\Controllers\Api\Doctor\AvailabilityController;
use App\Http\Controllers\Api\Doctor\ScheduleController;


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/



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
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        // Manage Specialties
        Route::get('/specialties', [SpecialtyController::class, 'index']);
        Route::post('/specialties', [SpecialtyController::class, 'store']);
        Route::put('/specialties/{specialty}', [SpecialtyController::class, 'update']);
        Route::delete('/specialties/{specialty}', [SpecialtyController::class, 'destroy']);


        // Manage Locations
        Route::get('/locations', [LocationController::class, 'index']);
        Route::post('/locations', [LocationController::class, 'store']);
        Route::put('/locations/{location}', [LocationController::class, 'update']);
        Route::delete('/locations/{location}', [LocationController::class, 'destroy']);


        // Approve / Reject Doctors
        Route::get('/doctors/pending', [DoctorApprovalController::class, 'pending']);
        Route::patch('/doctors/{doctorProfile}/approve', [DoctorApprovalController::class, 'approve']);
        Route::patch('/doctors/{doctorProfile}/reject', [DoctorApprovalController::class, 'reject']);


        Route::get('/patients/pending', [PatientApprovalController::class, 'pending']);
        Route::patch('/patients/{patientProfile}/approve', [PatientApprovalController::class, 'approve']);
        Route::patch('/patients/{patientProfile}/reject', [PatientApprovalController::class, 'reject']);


        Route::patch('/users/{user}/block', [UserBlockController::class, 'block']);
        Route::patch('/users/{user}/unblock', [UserBlockController::class, 'unblock']);
        Route::patch('/users/{user}/status', [UserBlockController::class, 'updateStatus']);


        Route::get('/users', [AdminUserController::class, 'index']);
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

        Route::get('/locations', [DoctorLocationController::class, 'index']);
        Route::post('/locations', [DoctorLocationController::class, 'store']);
        Route::delete('/locations/{doctorLocation}', [DoctorLocationController::class, 'destroy']);

        Route::get('/availabilities', [AvailabilityController::class, 'index']);
        Route::post('/availabilities', [AvailabilityController::class, 'store']);
        Route::put('/availabilities/{availability}', [AvailabilityController::class, 'update']);
        Route::delete('/availabilities/{availability}', [AvailabilityController::class, 'destroy']);

        Route::get('/schedule', [ScheduleController::class, 'index']);


    });


