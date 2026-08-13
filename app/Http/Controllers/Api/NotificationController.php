<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $notifications = UserNotification::where(
            'user_id',
            $request->user()->id
        )
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }


    
    public function markAsRead(
        Request $request,
        int $id
    ): JsonResponse {
        $notification = UserNotification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->update([
            'is_read' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data' => $notification,
        ]);
    }
}