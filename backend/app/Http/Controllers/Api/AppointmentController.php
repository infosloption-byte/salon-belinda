<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NewAppointmentNotification;
use App\Models\Appointment;
use App\Models\Service;
use App\Services\AppointmentScheduler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:150'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service = Service::find($data['service_id']);
        $duration = $service?->duration_minutes ?? AppointmentScheduler::DEFAULT_DURATION_MINUTES;

        // SALON-OPS-ENHANCEMENTS.md, "Appointments" — waitlist for
        // fully-booked slots. If literally nobody who could take this
        // service is free at the requested time, still accept the request
        // (rather than reject it outright) but flag it for the admin to
        // resolve manually — see AppointmentScheduler::isFullyBooked().
        $isWaitlisted = AppointmentScheduler::isFullyBooked($service?->id, $data['date'], $data['time'], $duration);

        $appointment = Appointment::create([
            ...$data,
            'service_name' => $service?->name,
            'status' => 'pending',
            'is_waitlisted' => $isWaitlisted,
        ]);

        try {
            Mail::to(config('notifications.salon_email'))->send(new NewAppointmentNotification($appointment));
        } catch (\Throwable $e) {
            // Never let a mail outage break the booking itself — the
            // appointment is already saved either way.
            Log::error('Failed to send new appointment notification', ['appointment_id' => $appointment->id, 'error' => $e->getMessage()]);
        }

        $message = $isWaitlisted
            ? "Thank you, {$appointment->name}. That time is fully booked right now, so we've added you to our waitlist — we'll call you if a slot opens up, or to arrange another time."
            : "Thank you, {$appointment->name}. Your request has been received and the salon will call you to confirm.";

        return response()->json([
            'message' => $message,
            'appointment' => $appointment,
        ], 201);
    }
}

