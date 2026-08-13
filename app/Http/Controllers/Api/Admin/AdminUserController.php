<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('is_blocked')) {
            $blocked = filter_var($request->is_blocked, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($blocked !== null) {
                $query->where('is_blocked', $blocked);
            }
        }

        $users = $query->orderBy('name')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }
}