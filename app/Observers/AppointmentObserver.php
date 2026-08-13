<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\UserNotification;

class AppointmentObserver
{
  public function updated(Appointment $appointment)
{
    if ($appointment->isDirty('status') && $appointment->status === 'cancelled') {

        if ($appointment->cancelled_by === 'doctor') {
            UserNotification::create([
                'user_id' => $appointment->patient->user_id,
                'appointment_id' => $appointment->id,
                'title' => 'Appointment Cancelled',
                'body' => 'Your appointment has been cancelled by the doctor.',
                'type' => 'cancellation',
                'is_read' => false,
            ]);
        } elseif ($appointment->cancelled_by === 'patient') {
            UserNotification::create([
                'user_id' => $appointment->doctor->user_id,
                'appointment_id' => $appointment->id,
                'title' => 'Appointment Cancelled',
                'body' => 'The patient has cancelled the appointment.',
                'type' => 'cancellation',
                'is_read' => false,
            ]);
        }
    }
}
}