<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class AppointmentScheduler
{
    /**
     * Default sitting length (minutes) used when a service has no
     * `duration_minutes` set (e.g. it hasn't been filled in yet, or the
     * service is a multi-session course where a single-sitting duration
     * doesn't cleanly apply). Conservative default so we still get some
     * overlap protection rather than none.
     */
    public const DEFAULT_DURATION_MINUTES = 60;

    /**
     * Find any other appointment already booked against this staff member
     * that overlaps the given date/time/duration window. Only appointments
     * with a status of pending or confirmed block the slot — completed,
     * cancelled, and no_show appointments don't hold the calendar.
     *
     * Returns the conflicting Appointment (with its service loaded) if
     * found, otherwise null.
     */
    public static function findConflict(
        int $staffId,
        string $date,
        string $time,
        int $durationMinutes,
        ?int $ignoreAppointmentId = null,
    ): ?Appointment {
        $start = Carbon::parse("{$date} {$time}");
        $end = (clone $start)->addMinutes($durationMinutes);

        return Appointment::query()
            ->with('service')
            ->where('staff_id', $staffId)
            ->whereDate('date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($ignoreAppointmentId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->get()
            ->first(function (Appointment $existing) use ($start, $end) {
                $existingStart = Carbon::parse("{$existing->date->format('Y-m-d')} {$existing->time}");
                $existingDuration = $existing->service?->duration_minutes ?? self::DEFAULT_DURATION_MINUTES;
                $existingEnd = (clone $existingStart)->addMinutes($existingDuration);

                // Overlap if the new window starts before the existing one
                // ends, and ends after the existing one starts.
                return $start->lt($existingEnd) && $end->gt($existingStart);
            });
    }
}
