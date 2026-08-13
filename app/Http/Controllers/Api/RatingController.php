<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
   
    public function store(Request $request, int $appointmentId): JsonResponse
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $patient = $request->user()->patientProfile;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found.',
            ], 404);
        }

        $appointment = Appointment::with('rating')
            ->where('id', $appointmentId)
            ->where('patient_id', $patient->id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found.',
            ], 404);
        }

        if ($appointment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => "Only completed appointments can be rated. Current status: {$appointment->status}.",
            ], 422);
        }

        if ($appointment->rating) {
            return response()->json([
                'success' => false,
                'message' => 'This appointment has already been rated.',
            ], 409);
        }

        $rating = Rating::create([
            'appointment_id' => $appointment->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback.',
            'data' => $rating,
        ], 201);
    }


    
    public function averageRating(int $doctorId): JsonResponse
    {
        
        $averageRating = Rating::whereHas('appointment', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })->avg('rating');

        return response()->json([
            'success' => true,
            'doctor_id' => $doctorId,
            'average_rating' => round($averageRating ?? 0, 2),
        ]);
    }
}