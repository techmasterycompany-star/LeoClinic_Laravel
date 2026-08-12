<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Availability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'availability_id' => 'required|integer|exists:availabilities,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        $patient = $request->user()->patientProfile;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found.',
            ], 404);
        }

        DB::beginTransaction();

        try {
            
            $availability = Availability::where('id', $data['availability_id'])
                ->lockForUpdate()
                ->first();

            if (!$availability) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Availability slot not found.',
                ], 404);
            }

            if ($availability->is_booked) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This slot is already booked.',
                ], 409);
            }

            if ($availability->date->toDateString() < now()->toDateString()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This slot is in the past.',
                ], 422);
            }

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $availability->doctor_id,
                'availability_id' => $availability->id,
                'status' => 'pending',
                'reason' => $data['reason'] ?? null,
            ]);

            $availability->update(['is_booked' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully.',
                'data' => $appointment->load(['doctor.user', 'availability']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while booking the appointment.',
            ], 500);
        }
    }
}