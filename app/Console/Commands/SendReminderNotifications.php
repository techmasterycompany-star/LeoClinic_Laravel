<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\UserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendReminderNotifications extends Command
{
    protected $signature = 'notifications:send-reminders';
    protected $description = 'Send reminder notifications for appointments in the next hour';

    public function handle()
    {
        // جيب الـ appointments اللي بتحصل في الساعة الجاية
        $upcomingAppointments = Appointment::whereHas('availability', function ($query) {
            $now = now();
            $oneHourLater = now()->addHour();

            $query->whereBetween(
                DB::raw("CONCAT(date, ' ', start_time)"),
                [$now, $oneHourLater]
            );
        })
            ->where('status', 'confirmed')
            ->get();

        foreach ($upcomingAppointments as $appointment) {
            // تأكد إنه ما فيش reminder notification بعتت لسه
            $reminderExists = UserNotification::where('appointment_id', $appointment->id)
                ->where('type', 'reminder')
                ->exists();

            if (!$reminderExists) {
                // بعت notification للدكتور
                UserNotification::create([
                    'user_id' => $appointment->doctor->user_id,
                    'appointment_id' => $appointment->id,
                    'title' => 'Appointment Reminder',
                    'body' => 'You have an appointment in about 1 hour.',
                    'type' => 'reminder',
                    'is_read' => false,
                ]);

                // بعت notification للمريض
                UserNotification::create([
                    'user_id' => $appointment->patient->user_id,
                    'appointment_id' => $appointment->id,
                    'title' => 'Appointment Reminder',
                    'body' => 'Your appointment is in about 1 hour.',
                    'type' => 'reminder',
                    'is_read' => false,
                ]);

                $this->info("Reminder sent for appointment ID: {$appointment->id}");
            }
        }

        $this->info('Reminder notifications sent successfully.');
    }
}