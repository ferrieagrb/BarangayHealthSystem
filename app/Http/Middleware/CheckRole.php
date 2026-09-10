<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $userRole = strtolower(trim($user->role ?? ''));

        // Global superadmin bypass
        if ($userRole === 'superadmin' || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            return $next($request);
        }

        // Flatten and normalize allowed roles
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $allowedRoles[] = strtolower(trim($r));
            }
        }

        // Check if user's role is permitted
        if (in_array($userRole, $allowedRoles, true)) {
            return $next($request);
        }

        // Model method fallbacks
        if ($userRole === 'citizen' && method_exists($user, 'isCitizen') && $user->isCitizen()) {
            return $next($request);
        }
        if ($userRole === 'admin' && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }
        if ($userRole === 'bhw' && method_exists($user, 'isBhw') && $user->isBhw()) {
            return $next($request);
        }

        abort(403, 'Unauthorized action. User role: "' . $userRole . '", Expected roles: ' . json_encode($allowedRoles));
    }
}