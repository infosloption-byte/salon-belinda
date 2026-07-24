<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffShift;
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

    /**
     * Whether a new booking for the given service/date/time/duration would
     * find literally no staff member free — i.e. it should go on the
     * waitlist instead of a normal pending slot.
     *
     * "Candidate" staff = active staff qualified for the service, or every
     * active staff member if nobody's been marked qualified for it yet
     * (consistent with how the assign-staff dropdown treats an
     * unconfigured service — see AppointmentController::staffListWithQualifications).
     * A staff member on explicit full-day leave that date doesn't count as
     * available; a staff member with no shift entry at all does (an
     * unscheduled day isn't the same as a day off — see the roster
     * feature's own "unscheduled" semantics).
     */
    public static function isFullyBooked(?int $serviceId, string $date, string $time, int $durationMinutes): bool
    {
        $qualifiedStaffIds = $serviceId
            ? (Service::find($serviceId)?->staff()->pluck('staff.id') ?? collect())
            : collect();

        $candidateStaffIds = Staff::query()
            ->where('is_active', true)
            ->when($qualifiedStaffIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $qualifiedStaffIds))
            ->pluck('id');

        if ($candidateStaffIds->isEmpty()) {
            // No active staff at all to book against — treat as fully
            // booked rather than silently accepting an unbookable request.
            return true;
        }

        $onLeaveStaffIds = StaffShift::query()
            ->whereIn('staff_id', $candidateStaffIds)
            ->whereDate('date', $date)
            ->where('type', 'leave')
            ->pluck('staff_id');

        $availableStaffIds = $candidateStaffIds->diff($onLeaveStaffIds);

        if ($availableStaffIds->isEmpty()) {
            return true; // everyone who could take this is on leave that day
        }

        foreach ($availableStaffIds as $staffId) {
            if (! self::findConflict($staffId, $date, $time, $durationMinutes)) {
                return false; // at least one candidate is free at that time
            }
        }

        return true;
    }
}

