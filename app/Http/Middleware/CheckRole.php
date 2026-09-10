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

        // Global superadmin bypass: allow superadmin to access any route automatically
        if (method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : $user->role === 'superadmin') {
            return $next($request);
        }

        foreach ($roles as $role) {
            switch ($role) {
                case 'admin':
                    if (method_exists($user, 'isAdmin') ? $user->isAdmin() : $user->role === 'admin') return $next($request);
                    break;
                case 'bhw':
                    if (method_exists($user, 'isBhw') ? $user->isBhw() : $user->role === 'bhw') return $next($request);
                    break;
                case 'citizen':
                    if (method_exists($user, 'isCitizen') ? $user->isCitizen() : $user->role === 'citizen') return $next($request);
                    break;
            }
        }

        abort(403, 'Unauthorized action.');
    }
}