<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSpecialtyRequest;
use App\Http\Requests\Admin\UpdateSpecialtyRequest;
use App\Models\Specialty;
use Illuminate\Http\JsonResponse;

class SpecialtyController extends Controller
{
    public function index(): JsonResponse
    {
        $specialties = Specialty::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $specialties,
        ]);
    }

    public function store(StoreSpecialtyRequest $request): JsonResponse
    {
        $specialty = Specialty::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Specialty created successfully.',
            'data' => $specialty,
        ], 201);
    }

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): JsonResponse
    {
        $specialty->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Specialty updated successfully.',
            'data' => $specialty,
        ]);
    }

    public function destroy(Specialty $specialty): JsonResponse
    {
        if ($specialty->doctorProfiles()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete specialty assigned to doctors.',
            ], 409);
        }

        $specialty->delete();

        return response()->json([
            'success' => true,
            'message' => 'Specialty deleted successfully.',
        ]);
    }
}
