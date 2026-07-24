<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Models\Staff;
use App\Services\ActivityLogger;
use App\Services\AppointmentScheduler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * JSON port of Admin\AppointmentController — see routes/api.php
 * /api/admin/appointments*. Same filtering, status-change email
 * notifications, and activity-log behaviour as the Blade version, plus
 * staff assignment + overlap checking (see SALON-OPS-ENHANCEMENTS.md,
 * "Appointments" section).
 */
class AppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $appointments = Appointment::query()
            ->with(['jobs', 'staff', 'service'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('staff_id'), fn ($q, $staffId) => $q->where('staff_id', $staffId))
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

        return response()->json([
            'appointments' => $appointments,
            'staffList' => $this->staffListWithQualifications(),
        ]);
    }

    /**
     * All appointments for a single day, unpaginated, for the calendar/
     * day-grid view — the paginated index() endpoint is wrong for this
     * since a busy day could span multiple pages and the grid needs every
     * appointment on screen at once to show real availability.
     */
    public function calendar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $appointments = Appointment::query()
            ->with(['staff', 'service'])
            ->whereDate('date', $data['date'])
            ->orderBy('time')
            ->get();

        return response()->json([
            'appointments' => $appointments,
            'staffList' => $this->staffListWithQualifications(),
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled,no_show'],
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

                return response()->json(['appointment' => $appointment, 'message' => 'Appointment updated, but the notification email failed to send.']);
            }
        }

        return response()->json(['appointment' => $appointment, 'message' => 'Appointment updated.']);
    }

    /**
     * Assign (or unassign, with staff_id = null) a staff member to an
     * appointment. Blocks the assignment with a 422 if it would overlap
     * another pending/confirmed appointment already booked against that
     * staff member — see AppointmentScheduler.
     */
    public function assignStaff(Request $request, Appointment $appointment): JsonResponse
    {
        $data = $request->validate([
            'staff_id' => ['nullable', 'exists:staff,id'],
        ]);

        if (($data['staff_id'] ?? null)) {
            $service = $appointment->service;
            $duration = $service?->duration_minutes ?? AppointmentScheduler::DEFAULT_DURATION_MINUTES;

            $conflict = AppointmentScheduler::findConflict(
                staffId: $data['staff_id'],
                date: $appointment->date->format('Y-m-d'),
                time: $appointment->time,
                durationMinutes: $duration,
                ignoreAppointmentId: $appointment->id,
            );

            if ($conflict) {
                return response()->json([
                    'message' => "That staff member already has {$conflict->name}'s appointment at {$conflict->time} on this day.",
                    'conflict' => $conflict,
                ], 422);
            }
        }

        $appointment->update(['staff_id' => $data['staff_id'] ?? null]);
        $appointment->load('staff');

        $staffName = $appointment->staff?->name;
        ActivityLogger::log(
            'appointment.staff_assigned',
            $staffName
                ? "Assigned {$staffName} to {$appointment->name}'s appointment"
                : "Unassigned staff from {$appointment->name}'s appointment",
            $appointment
        );

        return response()->json(['appointment' => $appointment, 'message' => 'Appointment updated.']);
    }

    /**
     * SALON-OPS-ENHANCEMENTS.md, "Staff" — qualification mapping. Each
     * staff entry carries the service ids they're qualified for, so the
     * admin UI can filter the "assign staff" dropdown to only people who
     * can actually perform the appointment's service, instead of listing
     * every active staff member regardless of skill.
     */
    private function staffListWithQualifications()
    {
        return Staff::query()
            ->where('is_active', true)
            ->with('services:id')
            ->orderBy('name')
            ->get(['id', 'name', 'role_title'])
            ->map(fn (Staff $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'role_title' => $s->role_title,
                'service_ids' => $s->services->pluck('id'),
            ]);
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        ActivityLogger::log('appointment.deleted', "Deleted {$appointment->name}'s appointment for {$appointment->date->format('d M Y')}", $appointment);
        $appointment->delete();

        return response()->json(['message' => 'Appointment removed.']);
    }
}

