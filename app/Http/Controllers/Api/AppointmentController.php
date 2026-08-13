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

    
    public function index(Request $request): JsonResponse
    {
        $patient = $request->user()->patientProfile;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found.',
            ], 404);
        }

        $query = Appointment::with(['doctor.user', 'doctor.specialty', 'availability'])
            ->where('patient_id', $patient->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'appointments' => $appointments,
        ]);
    }

   
    public function confirm(Request $request, int $id): JsonResponse
    {
        $doctor = $request->user()->doctorProfile;

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found.',
            ], 404);
        }

        $appointment = Appointment::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found.',
            ], 404);
        }

        if ($appointment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => "Only pending appointments can be confirmed. Current status: {$appointment->status}.",
            ], 422);
        }

        $appointment->update(['status' => 'confirmed']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment confirmed successfully.',
            'data' => $appointment->load(['patient.user', 'availability']),
        ]);
    }

    
    public function reject(Request $request, int $id): JsonResponse
    {
        $doctor = $request->user()->doctorProfile;

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found.',
            ], 404);
        }

        $appointment = Appointment::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found.',
            ], 404);
        }

        if ($appointment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => "Only pending appointments can be rejected. Current status: {$appointment->status}.",
            ], 422);
        }

        $appointment->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment rejected.',
            'data' => $appointment->load(['patient.user', 'availability']),
        ]);
    }

    
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $appointment = Appointment::with('availability')->find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found.',
            ], 404);
        }

        
        $isOwningPatient = $user->patientProfile && $appointment->patient_id === $user->patientProfile->id;
        $isOwningDoctor = $user->doctorProfile && $appointment->doctor_id === $user->doctorProfile->id;

        if (!$isOwningPatient && !$isOwningDoctor) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to cancel this appointment.',
            ], 403);
        }

        if (in_array($appointment->status, ['completed', 'cancelled', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => "This appointment can no longer be cancelled. Current status: {$appointment->status}.",
            ], 422);
        }

        DB::transaction(function () use ($appointment) {
            $appointment->update(['status' => 'cancelled']);
            $appointment->availability?->update(['is_booked' => false]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
            'data' => $appointment->fresh()->load(['patient.user', 'doctor.user', 'availability']),
        ]);
    }
}