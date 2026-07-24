<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\JobItem;
use App\Models\Service;
use App\Models\Staff;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON port of Admin\StaffController — see routes/api.php
 * /api/admin/staff*. Same deactivate-instead-of-delete guard as the
 * Blade version when a staff member has job history or a linked login.
 */
class StaffController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $staff = Staff::query()
            ->withCount(['jobItems', 'user'])
            ->when($request->query('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->query('status') !== 'inactive', fn ($q) => $q->where('is_active', true))
            ->when($request->query('q'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return response()->json(['staff' => $staff]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $staff = Staff::create($data);
        ActivityLogger::log('staff.created', "Added staff member {$staff->name}", $staff);

        return response()->json(['staffMember' => $staff, 'message' => 'Staff member added.'], 201);
    }

    public function update(Request $request, Staff $staff): JsonResponse
    {
        $data = $this->validated($request);

        $staff->update($data);
        ActivityLogger::log('staff.updated', "Updated staff member {$staff->name}", $staff);

        return response()->json(['staffMember' => $staff, 'message' => 'Staff member updated.']);
    }

    /**
     * Staff who have performed treatments (job_items rows reference them,
     * with RESTRICT on delete) can't be hard-deleted without losing job
     * history integrity — offer deactivation instead in that case.
     */
    public function destroy(Staff $staff): JsonResponse
    {
        if ($staff->jobItems()->exists()) {
            return response()->json([
                'message' => "{$staff->name} has job history and can't be deleted — deactivate them instead so they drop out of \"assign to\" pickers but their records stay intact.",
            ], 422);
        }

        if ($staff->user()->exists()) {
            return response()->json([
                'message' => "{$staff->name} still has a dashboard login linked to this profile. Remove or relink that account first (Users), then delete this staff profile.",
            ], 422);
        }

        ActivityLogger::log('staff.deleted', "Deleted staff member {$staff->name}", $staff);
        $staff->delete();

        return response()->json(['message' => 'Staff member removed.']);
    }

    public function toggleActive(Staff $staff): JsonResponse
    {
        $staff->update(['is_active' => ! $staff->is_active]);
        $action = $staff->is_active ? 'reactivated' : 'deactivated';
        ActivityLogger::log("staff.{$action}", ucfirst($action)." staff member {$staff->name}", $staff);

        return response()->json(['staffMember' => $staff, 'message' => "{$staff->name} {$action}."]);
    }

    /**
     * SALON-OPS-ENHANCEMENTS.md, "Staff" — qualification mapping. Returns
     * every service with a flag for whether this staff member is qualified
     * to perform it, so the admin UI can render one list of checkboxes
     * rather than fetching all services and all staff separately and
     * cross-referencing client-side.
     */
    public function services(Staff $staff): JsonResponse
    {
        $qualifiedIds = $staff->services()->pluck('services.id')->all();

        $services = Service::query()
            ->with('category:id,title')
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service) => [
                'id' => $service->id,
                'name' => $service->name,
                'category' => $service->category?->title,
                'qualified' => in_array($service->id, $qualifiedIds, true),
            ]);

        return response()->json(['services' => $services]);
    }

    public function syncServices(Request $request, Staff $staff): JsonResponse
    {
        $data = $request->validate([
            'service_ids' => ['present', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ]);

        $staff->services()->sync($data['service_ids']);
        ActivityLogger::log('staff.services_updated', "Updated {$staff->name}'s qualified services (".count($data['service_ids']).' services)', $staff);

        return response()->json(['message' => 'Qualified services updated.']);
    }

    /**
     * SALON-OPS-ENHANCEMENTS.md, "Staff" — performance visibility beyond
     * commission $. Bookings-completed count and no-show rate come from
     * appointments assigned to this staff member; average ticket size comes
     * from their own job_items (not the whole job total, since a job can
     * have multiple staff on different line items).
     */
    public function performance(Request $request, Staff $staff): JsonResponse
    {
        $from = $request->query('date_from') ?: now()->subDays(89)->toDateString();
        $to = $request->query('date_to') ?: now()->toDateString();

        $appointmentCounts = Appointment::query()
            ->where('staff_id', $staff->id)
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $completed = (int) ($appointmentCounts['completed'] ?? 0);
        $noShow = (int) ($appointmentCounts['no_show'] ?? 0);
        $cancelled = (int) ($appointmentCounts['cancelled'] ?? 0);
        $bookedTotal = $completed + $noShow + $cancelled + (int) ($appointmentCounts['confirmed'] ?? 0) + (int) ($appointmentCounts['pending'] ?? 0);

        $ticketStats = JobItem::query()
            ->join('jobs_salon', 'jobs_salon.id', '=', 'job_items.job_id')
            ->where('job_items.staff_id', $staff->id)
            ->where('jobs_salon.status', '!=', 'cancelled')
            ->whereDate('jobs_salon.job_date', '>=', $from)
            ->whereDate('jobs_salon.job_date', '<=', $to)
            ->selectRaw('COUNT(*) as line_items, AVG(job_items.final_price) as avg_ticket, SUM(job_items.commission_amount) as total_commission')
            ->first();

        return response()->json([
            'from' => $from,
            'to' => $to,
            'bookingsCompleted' => $completed,
            'noShowCount' => $noShow,
            'noShowRate' => $bookedTotal > 0 ? round($noShow / $bookedTotal * 100, 1) : 0,
            'servicesPerformed' => (int) ($ticketStats->line_items ?? 0),
            'averageTicket' => (float) ($ticketStats->avg_ticket ?? 0),
            'totalCommission' => (int) ($ticketStats->total_commission ?? 0),
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role_title' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
    }
}
