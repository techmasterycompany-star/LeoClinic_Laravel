<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $doctorProfile = $request->user()
            ->doctorProfile()
            ->with('specialty')
            ->first();

        if (!$doctorProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $doctorProfile,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->doctorProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'specialty_id' => 'required|exists:specialties,id',
            'price' => 'required|numeric|min:0',
            'bio' => 'required|string',
            'contact_number' => 'required|string|max:20',

            'professional_license' =>
                'required|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'profile_image' =>
                'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $doctorProfile = $user->doctorProfile;

        $data = [
            'specialty_id' => $validated['specialty_id'],
            'price' => $validated['price'],
            'bio' => $validated['bio'],
            'contact_number' => $validated['contact_number'],
        ];

        // Professional License
        if ($request->hasFile('professional_license')) {

            $file = $request->file('professional_license');

            $fileName = uniqid('license_') . '.'
                . $file->getClientOriginalExtension();

            $file->storeAs(
                'professional_licenses',
                $fileName,
                'public'
            );

            $data['professional_license'] = $fileName;
        }

        // Profile Image
        if ($request->hasFile('profile_image')) {

            $image = $request->file('profile_image');

            $imageName = uniqid('profile_') . '.'
                . $image->getClientOriginalExtension();

            $image->storeAs(
                'profile_images',
                $imageName,
                'public'
            );

            $data['profile_image'] = $imageName;
        }

        $doctorProfile->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Doctor profile completed successfully.',
            'data' => $doctorProfile->fresh()->load('specialty'),
        ]);
    }
}