<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $publicPath = $request->is('login') || $request->is('up') || $request->is('force-password-change*');

        if (SystemSetting::boolean('maintenance_mode') && !$publicPath && $user && $user->role !== 'superadmin') {
            return response()->view('errors.maintenance', [], 503);
        }

        return $next($request);
    }
}
