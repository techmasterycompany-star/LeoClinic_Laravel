<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    
    public function show(int $id): JsonResponse
    {
        $doctor = DoctorProfile::with([
            'user:id,name,email',
            'specialty:id,name,description',
            'doctorLocations.location',
        ])
            ->where('is_approved', true)
            ->find($id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found.',
            ], 404);
        }

        
        $availability = $doctor->availabilities()
            ->where('is_booked', false)
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get(['id', 'location_id', 'date', 'start_time', 'end_time']);

        $ratings = $doctor->ratings()
            ->latest('ratings.created_at')
            ->get(['ratings.id', 'ratings.rating', 'ratings.comment', 'ratings.created_at']);

        $averageRating = round($ratings->avg('rating'), 1);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $doctor->id,
                'name' => $doctor->user->name,
                'email' => $doctor->user->email,
                'price' => $doctor->price,
                'bio' => $doctor->bio,
                'contact_number' => $doctor->contact_number,
                'specialty' => $doctor->specialty,
                'locations' => $doctor->doctorLocations->pluck('location'),
                'availability' => $availability,
                'ratings' => [
                    'average' => $averageRating ?: 0,
                    'count' => $ratings->count(),
                    'items' => $ratings,
                ],
            ],
        ]);
    }

    
    public function reviews(int $id): JsonResponse
    {
        $doctor = DoctorProfile::where('is_approved', true)->find($id);

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found.',
            ], 404);
        }

        $reviews = $doctor->ratings()
            ->with('appointment.patient.user:id,name')
            ->latest('ratings.created_at')
            ->paginate(10);

        $reviews->getCollection()->transform(function ($rating) {
            return [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'comment' => $rating->comment,
                'patient_name' => $rating->appointment->patient->user->name ?? 'Anonymous',
                'created_at' => $rating->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
        ]);
    }
}