<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
class UserBlockController extends Controller
{
    public function block(User $user): JsonResponse
    {
        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin accounts cannot be blocked.',
            ], 403);
        }

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot block your own account.',
            ], 403);
        }

        $user->update(['is_blocked' => true]);
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully.',
            'data' => $user,
        ]);
    }

    public function unblock(User $user): JsonResponse
    {
        $user->update(['is_blocked' => false]);

        return response()->json([
            'success' => true,
            'message' => 'User unblocked successfully.',
            'data' => $user,
        ]);
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): JsonResponse
    {
        if ($user->role === 'admin' && $request->boolean('is_blocked')) {
            return response()->json([
                'success' => false,
                'message' => 'Admin accounts cannot be blocked.',
            ], 403);
        }

        if ($user->id === auth()->id() && $request->boolean('is_blocked')) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot block your own account.',
            ], 403);
        }

        $user->update(['is_blocked' => $request->boolean('is_blocked')]);

        if ($user->is_blocked) {
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Account status updated successfully.',
            'data' => $user,
        ]);
    }
}
