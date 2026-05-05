<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
{
    if (!auth()->check()) {
        return redirect('/login');
    }

    if (auth()->user()->admin != 1) {
        return redirect('/home');
    }

    return $next($request);
}
}