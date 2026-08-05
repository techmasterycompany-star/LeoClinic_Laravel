<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
        ]);

        if ($user->role === 'doctor') {

            $user->doctorProfile()->create([
            'specialty_id' => $data['specialty_id'],
            'price' => 0,
            'bio' => null,
            'contact_number' => null,
            'is_approved' => false,
        ]);

        } else {

            $user->patientProfile()->create([
                'contact_number' => null,
                'date_of_birth' => null,
                'address' => null,
                'is_approved' => false,
            ]);

        }

     $plainCode = random_int(100000, 999999);

        VerificationCode::create([
           'user_id' => $user->id,
           'code' => Hash::make($plainCode),
           'expires_at' => now()->addMinutes(10),
        ]);
try {
    Mail::to($user->email)
        ->send(new VerificationCodeMail((string) $plainCode));
} catch (\Exception $e) {
    return response()->json([
        'mail_error' => $e->getMessage(),
    ], 500);
}
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }
    public function login(LoginRequest $request): JsonResponse
{
    $data = $request->validated();

    $user = User::where('email', $data['email'])->first();

    if (!$user || !Hash::check($data['password'], $user->password)) {

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.'
        ], 401);

    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful.',
        'token' => $token,
        'token_type' => 'Bearer',
        'user' => $user
    ]);
}

public function logout(Request $request): JsonResponse
{
    $request->user()->tokens()->delete();

    return response()->json([
        'success' => true,
        'message' => 'Logged out successfully.',
    ]);
}

public function verifyEmail(Request $request): JsonResponse
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found.'
        ], 404);
    }

    $verification = VerificationCode::where('user_id', $user->id)
        ->latest()
        ->first();

    if (!$verification) {
        return response()->json([
            'success' => false,
            'message' => 'Verification code not found.'
        ], 404);
    }

    if ($verification->is_used) {
        return response()->json([
            'success' => false,
            'message' => 'Verification code already used.'
        ], 400);
    }

    if ($verification->expires_at < now()) {
        return response()->json([
            'success' => false,
            'message' => 'Verification code expired.'
        ], 400);
    }

    if (!Hash::check($request->code, $verification->code)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid verification code.'
        ], 400);
    }

    $user->update([
        'email_verified_at' => now(),
    ]);

    $verification->update([
        'is_used' => true,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Email verified successfully.',
    ]);
}
}