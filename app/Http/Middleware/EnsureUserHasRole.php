<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        // Support comma-separated role arguments, e.g. role:admin,staff
        $expandedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $expandedRoles[] = trim($r);
            }
        }

        foreach ($expandedRoles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden. Required role: '.implode(' or ', $expandedRoles)], 403);
        }

        abort(403, 'Forbidden. Required role: '.implode(' or ', $expandedRoles));
    }
}
