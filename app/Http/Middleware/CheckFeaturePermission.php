<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFeaturePermission
{
    public function handle(Request $request, Closure $next, $feature)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return $next($request);
        }

        // Check if user has 'write' permission for this specific feature
        $hasWriteAccess = /* query user_permissions or check json field */;

        if (!$hasWriteAccess) {
            abort(403, 'Unauthorized action. Your account has read-only access to this feature.');
        }

        return $next($request);
    }
}
