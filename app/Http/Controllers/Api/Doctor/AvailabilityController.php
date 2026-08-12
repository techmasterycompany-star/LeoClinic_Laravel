<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * View doctor's availability.
     */
    public function index(Request $request): JsonResponse
    {
        $doctorProfile = $request->user()->doctorProfile;

        if (!$doctorProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found.',
            ], 404);
        }

        $availabilities = $doctorProfile
            ->availabilities()
            ->with('location')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $availabilities,
        ]);
    }

    /**
     * Add availability.
     */
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
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $locationAssigned = $doctorProfile
            ->doctorLocations()
            ->where('location_id', $validated['location_id'])
            ->exists();

        if (!$locationAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'This location is not assigned to the doctor.',
            ], 403);
        }

        $availability = $doctorProfile->availabilities()->create([
            'location_id' => $validated['location_id'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Availability added successfully.',
            'data' => $availability->load('location'),
        ], 201);
    }

    /**
     * Update availability.
     */
    public function update(
        Request $request,
        Availability $availability
    ): JsonResponse {
        $doctorProfile = $request->user()->doctorProfile;

        if (!$doctorProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found.',
            ], 404);
        }

        if ($availability->doctor_id !== $doctorProfile->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($availability->is_booked) {
            return response()->json([
                'success' => false,
                'message' => 'Booked availability cannot be updated.',
            ], 409);
        }

        $validated = $request->validate([
            'location_id' => 'sometimes|exists:locations,id',
            'date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
        ]);

        if (isset($validated['location_id'])) {
            $locationAssigned = $doctorProfile
                ->doctorLocations()
                ->where('location_id', $validated['location_id'])
                ->exists();

            if (!$locationAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'This location is not assigned to the doctor.',
                ], 403);
            }
        }

        $availability->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Availability updated successfully.',
            'data' => $availability->fresh()->load('location'),
        ]);
    }

    /**
     * Delete availability.
     */
    public function destroy(
        Request $request,
        Availability $availability
    ): JsonResponse {
        $doctorProfile = $request->user()->doctorProfile;

        if (!$doctorProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found.',
            ], 404);
        }

        if ($availability->doctor_id !== $doctorProfile->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($availability->is_booked) {
            return response()->json([
                'success' => false,
                'message' => 'Booked availability cannot be deleted.',
            ], 409);
        }

        $availability->delete();

        return response()->json([
            'success' => true,
            'message' => 'Availability deleted successfully.',
        ]);
    }
}