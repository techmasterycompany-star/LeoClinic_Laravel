<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'appointment_id' => 'required|integer|exists:appointments,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,wallet',        ]);

        $patient = $request->user()->patientProfile;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found.',
            ], 404);
        }

        $appointment = Appointment::where('id', $data['appointment_id'])
            ->where('patient_id', $patient->id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found.',
            ], 404);
        }

        if ($appointment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Payment can only be made for completed appointments.',
            ], 422);
        }

        if (Payment::where('appointment_id', $data['appointment_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment for this appointment already recorded.',
            ], 409);
        }

        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'appointment_id' => $data['appointment_id'],
                'patient_id' => $patient->id,
                'doctor_id' => $appointment->doctor_id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'status' => 'paid', 
                'paid_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'data' => $payment->load(['appointment', 'patient.user', 'doctor.user']),
            ], 201);

        } catch (\Exception $e) {
             DB::rollBack();

             return response()->json([
            'success' => false,
            'message' => 'Something went wrong while processing payment.',
        ], 500);

        }
    }

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