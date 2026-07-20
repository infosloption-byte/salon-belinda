<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * "My Account" — every admin/staff login manages their own name/email/
     * password here, without needing terminal access to the server.
     */
    public function account(): View
    {
        return view('admin.users.account', ['user' => Auth::user()]);
    }

    public function updateAccount(Request $request): RedirectResponse
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

        return back()->with('success', 'Your account has been updated.');
    }

    /**
     * Managing *other* dashboard logins — both admin and staff accounts.
     */
    public function index(): View
    {
        $users = User::with('staff')->orderByDesc('role')->orderBy('name')->get();

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'editUser' => new User,
            'unlinkedStaff' => Staff::doesntHave('user')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
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

        return redirect()->route('admin.users.index')->with('success', ucfirst($newUser->role).' account created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'editUser' => $user,
            'unlinkedStaff' => Staff::where(function ($q) use ($user) {
                $q->whereDoesntHave('user')->orWhere('id', $user->staff_id);
            })->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
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

        return redirect()->route('admin.users.index')->with('success', 'Account updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => "You can't delete your own account while logged in as it. Ask another admin to remove it."]);
        }

        if ($user->isAdminRole() && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['user' => 'At least one admin account must remain — add another admin before removing this one.']);
        }

        ActivityLogger::log('user.deleted', "Deleted account for {$user->name} ({$user->email})", $user);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Account removed.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
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

        return $data;
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
