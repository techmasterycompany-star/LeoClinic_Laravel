<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePatientProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'patient') {
            return response()->json([
                'success' => false,
                'message' => 'Only patients can access this resource.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'profile' => $user->patientProfile,
        ]);
    }

    public function update(UpdatePatientProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        $profile = $request->user()->patientProfile;

        $profile->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'profile' => $profile->fresh(),
        ]);
    }
}