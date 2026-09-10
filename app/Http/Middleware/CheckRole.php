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

        // Normalize database role to lowercase and trim spaces
        $userRole = strtolower(trim($user->role ?? ''));

        // Global superadmin bypass
        if ($userRole === 'superadmin' || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            return $next($request);
        }

        foreach ($roles as $role) {
            $targetRole = strtolower(trim($role));
            
            if ($userRole === $targetRole) {
                return $next($request);
            }

            // Fallback to model methods if available
            switch ($targetRole) {
                case 'admin':
                    if (method_exists($user, 'isAdmin') && $user->isAdmin()) return $next($request);
                    break;
                case 'bhw':
                    if (method_exists($user, 'isBhw') && $user->isBhw()) return $next($request);
                    break;
                case 'citizen':
                    if (method_exists($user, 'isCitizen') && $user->isCitizen()) return $next($request);
                    break;
            }
        }

        // This will show you the exact string stored in your database if it fails again
        abort(403, 'Unauthorized action. Database role found was: "' . ($user->role ?? 'null') . '"');
    }
}