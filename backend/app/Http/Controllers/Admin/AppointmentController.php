<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $appointments = Appointment::query()
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('q'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->query('date_from'), fn ($q, $date) => $q->whereDate('date', '>=', $date))
            ->when($request->query('date_to'), fn ($q, $date) => $q->whereDate('date', '<=', $date))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.appointments.index', compact('appointments'));
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]);

        $previousStatus = $appointment->status;
        $appointment->update(['status' => $request->status]);

        if ($previousStatus !== $appointment->status) {
            ActivityLogger::log(
                'appointment.status_updated',
                "Changed {$appointment->name}'s appointment from \"{$previousStatus}\" to \"{$appointment->status}\"",
                $appointment,
                ['from' => $previousStatus, 'to' => $appointment->status]
            );
        }

        if ($appointment->email && $previousStatus !== $appointment->status) {
            try {
                if ($appointment->status === 'confirmed') {
                    Mail::to($appointment->email)->send(new AppointmentConfirmed($appointment));
                } elseif ($appointment->status === 'cancelled') {
                    Mail::to($appointment->email)->send(new AppointmentCancelled($appointment));
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send appointment status email', ['appointment_id' => $appointment->id, 'error' => $e->getMessage()]);

                return back()->with('success', 'Appointment updated, but the notification email failed to send.');
            }
        }

        return back()->with('success', 'Appointment updated.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        ActivityLogger::log('appointment.deleted', "Deleted {$appointment->name}'s appointment for {$appointment->date->format('d M Y')}", $appointment);
        $appointment->delete();

        return back()->with('success', 'Appointment removed.');
    }
}
