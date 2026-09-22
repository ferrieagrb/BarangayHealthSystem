<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackPageViews
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Mark start time right before handling the request
        $startTime = microtime(true);

        // 2. Process the request response
        $response = $next($request);

        // 3. Calculate total elapsed time in milliseconds
        $loadTime = round((microtime(true) - $startTime) * 1000);

        // Only log standard, non-AJAX GET requests
        if ($request->isMethod('GET') && !$request->ajax()) {
            $userAgent = $request->header('user-agent');
            $browser = 'Unknown';

            if ($userAgent) {
                if (str_contains($userAgent, 'Edg')) {
                    $browser = 'Edge';
                } elseif (str_contains($userAgent, 'OPR') || str_contains($userAgent, 'Opera')) {
                    $browser = 'Opera';
                } elseif (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Chromium')) {
                    $browser = 'Chrome';
                } elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) {
                    $browser = 'Safari';
                } elseif (str_contains($userAgent, 'Firefox')) {
                    $browser = 'Firefox';
                } elseif (str_contains($userAgent, 'Trident') || str_contains($userAgent, 'MSIE')) {
                    $browser = 'Internet Explorer';
                }
            }

            DB::table('page_views')->insert([
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_id' => auth()->id(),
                'load_time' => $loadTime,
                'browser' => $browser,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $response;
    }
}