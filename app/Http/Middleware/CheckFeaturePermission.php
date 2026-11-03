<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFeaturePermission
{
    public function handle(Request $request, Closure $next, $feature)
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->role === 'admin' || $user->role === 'superadmin') {
            return $next($request);
        }

        if (!$user->hasWriteAccess($feature)) {
            abort(403, 'Unauthorized action. Your account has read-only access to this feature.');
        }

        return $next($request);
    }
}
