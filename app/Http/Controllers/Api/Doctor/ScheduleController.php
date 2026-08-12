<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
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

        $validated = $request->validate([
            'date' => 'sometimes|date',
            'location_id' => 'sometimes|exists:locations,id',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
        ]);

        $appointmentsQuery = $doctorProfile
            ->appointments()
            ->with([
                'patient.user',
                'availability.location',
            ]);

        $availabilitiesQuery = $doctorProfile
            ->availabilities()
            ->with('location')
            ->orderBy('date')
            ->orderBy('start_time');

        if (isset($validated['date'])) {
            $appointmentsQuery->whereHas(
                'availability',
                fn ($query) => $query->whereDate('date', $validated['date'])
            );

            $availabilitiesQuery->whereDate('date', $validated['date']);
        }

        if (isset($validated['date_from'])) {
            $appointmentsQuery->whereHas(
                'availability',
                fn ($query) => $query->whereDate('date', '>=', $validated['date_from'])
            );

            $availabilitiesQuery->whereDate('date', '>=', $validated['date_from']);
        }

        if (isset($validated['date_to'])) {
            $appointmentsQuery->whereHas(
                'availability',
                fn ($query) => $query->whereDate('date', '<=', $validated['date_to'])
            );

            $availabilitiesQuery->whereDate('date', '<=', $validated['date_to']);
        }

        if (isset($validated['location_id'])) {
            $appointmentsQuery->whereHas(
                'availability',
                fn ($query) => $query->where('location_id', $validated['location_id'])
            );

            $availabilitiesQuery->where('location_id', $validated['location_id']);
        }

        $availabilities = $availabilitiesQuery->get();

        return response()->json([
            'success' => true,
            'data' => [
                'appointments' => $appointmentsQuery
                    ->latest()
                    ->get(),
                'booked_slots' => $availabilities
                    ->where('is_booked', true)
                    ->values(),
                'available_slots' => $availabilities
                    ->where('is_booked', false)
                    ->values(),
            ],
        ]);
    }
}
