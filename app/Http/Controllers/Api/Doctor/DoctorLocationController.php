<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorLocation;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorLocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $doctorProfile = $request->user()->doctorProfile;

        if (!$doctorProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found.',
            ], 404);
        }

        $locations = $doctorProfile
            ->doctorLocations()
            ->with('location')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $doctorProfile = $request->user()->doctorProfile;

        if (!$doctorProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        $alreadyAssigned = $doctorProfile
            ->doctorLocations()
            ->where('location_id', $validated['location_id'])
            ->exists();

        if ($alreadyAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'Location is already assigned to this doctor.',
            ], 409);
        }

        $doctorLocation = $doctorProfile->doctorLocations()->create([
            'location_id' => $validated['location_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location assigned successfully.',
            'data' => $doctorLocation->load('location'),
        ], 201);
    }

    public function destroy(
        Request $request,
        DoctorLocation $doctorLocation
    ): JsonResponse {
        $doctorProfile = $request->user()->doctorProfile;

        if (!$doctorProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found.',
            ], 404);
        }

        if ($doctorLocation->doctor_id !== $doctorProfile->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $doctorLocation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Location removed successfully.',
        ]);
    }
}
