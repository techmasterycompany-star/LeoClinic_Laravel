<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DoctorProfile::query()
            ->with(['user:id,name,email', 'specialty'])
            ->where('is_approved', true);

        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }

        if ($request->filled('city')) {
            $city = $request->city;
            $query->whereHas('doctorLocations.location', function ($q) use ($city) {
                $q->where('city', 'like', "%{$city}%");
            });
        }

        if ($request->filled('name')) {
            $name = $request->name;
            $query->whereHas('user', function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%");
            });
        }

        $doctors = $query->paginate(10);

        return response()->json([
            'success' => true,
            'doctors' => $doctors,
        ]);
    }

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
}