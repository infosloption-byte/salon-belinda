<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffShift;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SALON-OPS-ENHANCEMENTS.md, "Staff" — working-hours/shift/leave.
 * /api/admin/staff-shifts* for CRUD on individual shift/leave entries, plus
 * /api/admin/staff/roster for the "who's on today" rollup.
 */
class StaffShiftController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $shifts = StaffShift::query()
            ->when($request->query('staff_id'), fn ($q, $id) => $q->where('staff_id', $id))
            ->when($request->query('date_from'), fn ($q, $date) => $q->whereDate('date', '>=', $date))
            ->when($request->query('date_to'), fn ($q, $date) => $q->whereDate('date', '<=', $date))
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()->json(['shifts' => $shifts]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $shift = StaffShift::create($data);
        $shift->load('staff:id,name');
        ActivityLogger::log('staff_shift.created', $this->describe($shift), $shift);

        return response()->json(['shift' => $shift, 'message' => 'Schedule entry added.'], 201);
    }

    public function update(Request $request, StaffShift $shift): JsonResponse
    {
        $data = $this->validated($request);

        $shift->update($data);
        $shift->load('staff:id,name');
        ActivityLogger::log('staff_shift.updated', $this->describe($shift), $shift);

        return response()->json(['shift' => $shift, 'message' => 'Schedule entry updated.']);
    }

    public function destroy(StaffShift $shift): JsonResponse
    {
        $shift->load('staff:id,name');
        ActivityLogger::log('staff_shift.deleted', $this->describe($shift), $shift);
        $shift->delete();

        return response()->json(['message' => 'Schedule entry removed.']);
    }

    /**
     * "Who's on today" (or any given date) — every active staff member,
     * annotated with their shift/leave entry for that date if one exists.
     * A staff member with no entry at all is reported as 'unscheduled'
     * rather than assumed working or off, since neither is safe to assume.
     */
    public function roster(Request $request): JsonResponse
    {
        $date = $request->query('date') ?: now()->toDateString();

        $shiftsByStaff = StaffShift::query()
            ->whereDate('date', $date)
            ->get()
            ->keyBy('staff_id');

        $roster = Staff::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Staff $member) use ($shiftsByStaff, $date) {
                $shift = $shiftsByStaff->get($member->id);

                return [
                    'staff_id' => $member->id,
                    'name' => $member->name,
                    'role_title' => $member->role_title,
                    'status' => $shift ? $shift->type : 'unscheduled',
                    'start_time' => $shift?->start_time,
                    'end_time' => $shift?->end_time,
                    'notes' => $shift?->notes,
                ];
            });

        return response()->json(['date' => $date, 'roster' => $roster]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'date' => ['required', 'date'],
            'type' => ['required', 'in:work,leave'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function describe(StaffShift $shift): string
    {
        $label = $shift->type === 'leave' ? 'leave' : 'a shift';
        $when = $shift->date->format('d M Y');
        $hours = $shift->start_time && $shift->end_time ? " ({$shift->start_time}–{$shift->end_time})" : '';

        return "Set {$shift->staff->name} for {$label} on {$when}{$hours}";
    }
}
