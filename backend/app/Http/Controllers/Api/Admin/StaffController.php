<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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
