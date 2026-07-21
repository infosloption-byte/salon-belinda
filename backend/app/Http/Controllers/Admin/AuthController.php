<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Those credentials don\'t match our records.'])->onlyInput('email');
        }

        $user = Auth::user();

        if ($user->isStaffRole() && (! $user->staff_id || ! $user->staff?->is_active)) {
            Auth::logout();

            return back()->withErrors(['email' => 'This staff account has been deactivated. Contact an admin.']);
        }

        if (! $user->isAdminRole() && ! $user->isStaffRole()) {
            Auth::logout();

            return back()->withErrors(['email' => 'This account does not have dashboard access.']);
        }

        $request->session()->regenerate();
        ActivityLogger::log('auth.login', $user->name.' logged in');

        // "admin.dashboard" is the single post-login landing route for both
        // roles — DashboardController sends staff straight on to Jobs,
        // since the full stats dashboard is admin-only.
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
