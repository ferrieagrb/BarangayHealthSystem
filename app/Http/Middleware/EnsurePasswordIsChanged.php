<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->must_change_password) {
            // Allow access only to the password reset routes and logout
            if (!$request->routeIs('password.force-change', 'password.force-update', 'logout')) {
                return redirect()->route('password.force-change');
            }
        }

        return $next($request);
    }
}
