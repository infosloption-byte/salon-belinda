<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaffOrAdmin
{
    /**
     * Handle an incoming request. Unlike EnsureUserIsAdmin, this allows both
     * 'admin' and 'staff' role users through — used for the Jobs/Customers
     * area that both roles need day-to-day access to.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isAdminRole() && ! $user->isStaffRole())) {
            abort(403, 'You do not have access to this area.');
        }

        return $next($request);
    }
}
