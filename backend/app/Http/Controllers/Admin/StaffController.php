<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $staff = Staff::query()
            ->withCount('jobItems')
            ->when($request->query('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->query('status') !== 'inactive', fn ($q) => $q->where('is_active', true))
            ->when($request->query('q'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.staff.index', ['staffMembers' => $staff, 'status' => $request->query('status', 'active')]);
    }

    public function create(): View
    {
        return view('admin.staff.form', ['staffMember' => new Staff]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $staff = Staff::create($data);
        ActivityLogger::log('staff.created', "Added staff member {$staff->name}", $staff);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member added.');
    }

    public function edit(Staff $staff): View
    {
        return view('admin.staff.form', ['staffMember' => $staff]);
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $data = $this->validated($request);

        $staff->update($data);
        ActivityLogger::log('staff.updated', "Updated staff member {$staff->name}", $staff);

        return redirect()->route('admin.staff.index')->with('success', 'Staff member updated.');
    }

    /**
     * Staff who have performed treatments (job_items rows reference them,
     * with RESTRICT on delete) can't be hard-deleted without losing job
     * history integrity — offer deactivation instead in that case.
     */
    public function destroy(Staff $staff): RedirectResponse
    {
        if ($staff->jobItems()->exists()) {
            return back()->with('error', "{$staff->name} has job history and can't be deleted — deactivate them instead so they drop out of \"assign to\" pickers but their records stay intact.");
        }

        ActivityLogger::log('staff.deleted', "Deleted staff member {$staff->name}", $staff);
        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff member removed.');
    }

    public function toggleActive(Staff $staff): RedirectResponse
    {
        $staff->update(['is_active' => ! $staff->is_active]);
        $action = $staff->is_active ? 'reactivated' : 'deactivated';
        ActivityLogger::log("staff.{$action}", ucfirst($action)." staff member {$staff->name}", $staff);

        return back()->with('success', "{$staff->name} {$action}.");
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
