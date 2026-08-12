<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLocationRequest;
use App\Http\Requests\Admin\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function index(): JsonResponse
    {
        $locations = Location::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $location = Location::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Location created successfully.',
            'data' => $location,
        ], 201);
    }

    public function update(UpdateLocationRequest $request, Location $location): JsonResponse
    {
        $location->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully.',
            'data' => $location,
        ]);
    }

    public function destroy(Location $location): JsonResponse
    {
        if ($location->doctorLocations()->exists() || $location->availabilities()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete location assigned to doctors or availabilities.',
            ], 409);
        }

        $location->delete();

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully.',
        ]);
    }
}
