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

        // Allow superadmin access to everything
        if ($userRole === 'superadmin') {
            return $next($request);
        }

        // Check if user's role matches any allowed role passed to the route
        foreach ($roles as $role) {
            if ($userRole === strtolower(trim($role))) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized action.');
    }
}