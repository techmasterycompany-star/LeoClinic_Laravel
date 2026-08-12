<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use Illuminate\Http\JsonResponse;

class DoctorApprovalController extends Controller
{
    public function pending(): JsonResponse
    {
        $doctors = DoctorProfile::with(['user', 'specialty'])
            ->where('is_approved', false)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $doctors,
        ]);
    }

    public function approve(DoctorProfile $doctorProfile): JsonResponse
    {
        if ($doctorProfile->user->role !== 'doctor') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid doctor account.',
            ], 404);
        }

        $doctorProfile->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Doctor approved successfully.',
            'data' => $doctorProfile->load(['user', 'specialty']),
        ]);
    }

    public function reject(DoctorProfile $doctorProfile): JsonResponse
    {
        if ($doctorProfile->user->role !== 'doctor') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid doctor account.',
            ], 404);
        }

        if ($doctorProfile->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject an approved doctor.',
            ], 409);
        }

        $doctorProfile->user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Doctor rejected successfully.',
        ]);
    }
}
