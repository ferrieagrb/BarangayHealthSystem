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

        foreach ($roles as $role) {
            switch ($role) {
                case 'superadmin':
                    if ($user->isSuperAdmin()) return $next($request);
                    break;
                case 'admin':
                    if ($user->isAdmin()) return $next($request);
                    break;
                case 'bhw':
                    if ($user->isBhw()) return $next($request);
                    break;
                case 'citizen':
                    if ($user->isCitizen()) return $next($request);
                    break;
            }
        }

        abort(403, 'Unauthorized action.');
    }
}
