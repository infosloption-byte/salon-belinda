<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Issue a Sanctum bearer token. Same credential/role checks as the old
     * session-based Admin\AuthController::login, but JSON in/out — the
     * admin app lives on its own subdomain and can't rely on first-party
     * session cookies, so we use token auth instead of Sanctum's SPA mode.
     *
     * Contract (fixed by admin/src/lib/api.ts — do not change shape):
     *   POST /api/admin/login  → { token, user }
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::once($credentials)) {
            throw ValidationException::withMessages([
                'email' => "Those credentials don't match our records.",
            ]);
        }

        $user = Auth::user();

        if ($user->isStaffRole() && (! $user->staff_id || ! $user->staff?->is_active)) {
            throw ValidationException::withMessages([
                'email' => 'This staff account has been deactivated. Contact an admin.',
            ]);
        }

        if (! $user->isAdminRole() && ! $user->isStaffRole()) {
            throw ValidationException::withMessages([
                'email' => 'This account does not have dashboard access.',
            ]);
        }

        // One token per login; named after the client so an admin can tell
        // sessions apart in `personal_access_tokens` if one needs revoking.
        $token = $user->createToken($request->userAgent() ?: 'admin-app')->plainTextToken;

        ActivityLogger::log('auth.login', $user->name.' logged in');

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    /** POST /api/admin/logout — revokes only the token used for this request. */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    /**
     * GET /api/admin/me — returns the user object directly (not wrapped in
     * `{user: ...}`), matching `fetchCurrentUser(): Promise<AdminUser>` in
     * admin/src/lib/api.ts.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($this->userPayload($request->user()));
    }

    private function userPayload($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_admin' => $user->isAdminRole(),
            'staff_id' => $user->staff_id,
        ];
    }
}
