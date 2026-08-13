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
}