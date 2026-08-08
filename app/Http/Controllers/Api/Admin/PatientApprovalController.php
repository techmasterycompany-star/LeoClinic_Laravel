<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use Illuminate\Http\JsonResponse;

class PatientApprovalController extends Controller
{
    public function pending(): JsonResponse
    {
        $patients = PatientProfile::with('user')
            ->where('is_approved', false)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $patients,
        ]);
    }

    public function approve(PatientProfile $patientProfile): JsonResponse
    {
        if ($patientProfile->user->role !== 'patient') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid patient account.',
            ], 404);
        }

        $patientProfile->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Patient approved successfully.',
            'data' => $patientProfile->load('user'),
        ]);
    }

    public function reject(PatientProfile $patientProfile): JsonResponse
    {
        if ($patientProfile->user->role !== 'patient') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid patient account.',
            ], 404);
        }

        if ($patientProfile->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reject an approved patient.',
            ], 409);
        }

        $patientProfile->user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient rejected successfully.',
        ]);
    }
}
