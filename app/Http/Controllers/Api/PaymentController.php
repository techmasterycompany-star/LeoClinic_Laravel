<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $patient = $request->user()->patientProfile;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found.',
            ], 404);
        }

        $query = Payment::with(['appointment', 'doctor.user'])
            ->where('patient_id', $patient->id);

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('paid_at', [$request->from_date, $request->to_date]);
        }

        $payments = $query->latest('paid_at')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }
}