<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * "My Account" — every admin manages their own name/email/password here,
     * without needing terminal access to the server.
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
     * Managing *other* admin accounts.
     */
    public function index(): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['editUser' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $newUser = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_admin' => true,
        ]);
        ActivityLogger::log('user.created', "Created admin account for {$newUser->name} ({$newUser->email})", $newUser);

        return redirect()->route('admin.users.index')->with('success', 'Admin account created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['editUser' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        ActivityLogger::log('user.updated', "Updated admin account for {$user->name} ({$user->email})", $user);

        return redirect()->route('admin.users.index')->with('success', 'Admin account updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => "You can't delete your own account while logged in as it. Ask another admin to remove it."]);
        }

        if (User::where('is_admin', true)->count() <= 1) {
            return back()->withErrors(['user' => 'At least one admin account must remain — add another admin before removing this one.']);
        }

        ActivityLogger::log('user.deleted', "Deleted admin account for {$user->name} ({$user->email})", $user);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Admin account removed.');
    }
}
