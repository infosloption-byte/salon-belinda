<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * JSON port of Admin\UserController — see routes/api.php
 * /api/admin/account* (self-service, every role) and /api/admin/users*
 * (admin-only management of other dashboard logins). Same
 * last-admin-standing and self-delete guards as the Blade version.
 */
class UserController extends Controller
{
    /**
     * "My Account" — every admin/staff login manages their own name/email/
     * password here, without needing terminal access to the server.
     */
    public function account(): JsonResponse
    {
        return response()->json(['user' => Auth::user()]);
    }

    public function updateAccount(Request $request): JsonResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['required_with:password', 'nullable', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return response()->json(['user' => $user->fresh(), 'message' => 'Your account has been updated.']);
    }

    /**
     * Managing *other* dashboard logins — both admin and staff accounts.
     */
    public function index(): JsonResponse
    {
        $users = User::with('staff')->orderByDesc('role')->orderBy('name')->get();

        return response()->json(['users' => $users]);
    }

    /**
     * Staff profiles available to link to a new/edited user account —
     * exposed separately so the React form can populate its dropdown
     * without needing an "edit" payload first.
     */
    public function unlinkedStaff(Request $request): JsonResponse
    {
        $keepUserId = $request->query('user_id');
        $keepStaffId = $keepUserId ? User::find($keepUserId)?->staff_id : null;

        $staff = Staff::where(function ($q) use ($keepStaffId) {
            $q->whereDoesntHave('user');
            if ($keepStaffId) {
                $q->orWhere('id', $keepStaffId);
            }
        })->where('is_active', true)->orderBy('name')->get();

        return response()->json(['unlinkedStaff' => $staff]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $newUser = DB::transaction(function () use ($request, $data) {
            $staffId = $this->resolveStaffId($request, $data);

            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'],
                'is_admin' => $data['role'] === 'admin',
                'staff_id' => $staffId,
            ]);
        });

        ActivityLogger::log('user.created', "Created {$newUser->role} account for {$newUser->name} ({$newUser->email})", $newUser);

        return response()->json(['user' => $newUser->load('staff'), 'message' => ucfirst($newUser->role).' account created.'], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(['user' => $user->load('staff')]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $this->validated($request, $user->id);

        DB::transaction(function () use ($request, $user, $data) {
            $staffId = $data['role'] === 'staff' ? $this->resolveStaffId($request, $data, $user) : null;

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->role = $data['role'];
            $user->is_admin = $data['role'] === 'admin';
            $user->staff_id = $staffId;

            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            $user->save();
        });

        ActivityLogger::log('user.updated', "Updated account for {$user->name} ({$user->email})", $user);

        return response()->json(['user' => $user->fresh()->load('staff'), 'message' => 'Account updated.']);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === Auth::id()) {
            return response()->json(['message' => "You can't delete your own account while logged in as it. Ask another admin to remove it."], 422);
        }

        if ($user->isAdminRole() && User::where('role', 'admin')->count() <= 1) {
            return response()->json(['message' => 'At least one admin account must remain — add another admin before removing this one.'], 422);
        }

        ActivityLogger::log('user.deleted', "Deleted account for {$user->name} ({$user->email})", $user);
        $user->delete();

        return response()->json(['message' => 'Account removed.']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($ignoreId)],
            'password' => [$ignoreId ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'role' => ['required', 'in:admin,staff'],
            'staff_id' => ['nullable', 'exists:staff,id'],
            'new_staff_name' => ['nullable', 'string', 'max:120'],
            'new_staff_role_title' => ['nullable', 'string', 'max:100'],
            'new_staff_phone' => ['nullable', 'string', 'max:30'],
            'new_staff_commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    /**
     * Either use the staff profile picked from the dropdown, or create a
     * new one on the fly from the inline "new staff" fields.
     */
    private function resolveStaffId(Request $request, array $data, ?User $editingUser = null): ?int
    {
        if ($data['role'] !== 'staff') {
            return null;
        }

        if (! empty($data['staff_id'])) {
            return (int) $data['staff_id'];
        }

        if ($editingUser === null || ! $editingUser->staff_id) {
            $staff = Staff::create([
                'name' => $data['new_staff_name'] ?: $data['name'],
                'role_title' => $data['new_staff_role_title'] ?? null,
                'phone' => $data['new_staff_phone'] ?? null,
                'commission_percent' => $data['new_staff_commission_percent'] ?? 0,
            ]);

            return $staff->id;
        }

        // Editing an existing staff-role user without changing the link.
        return $editingUser->staff_id;
    }
}
