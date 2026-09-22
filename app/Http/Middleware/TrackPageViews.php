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
            DB::table('page_views')->insert([
                'ip_address' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_id' => auth()->id(),
                'load_time' => $loadTime,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $response;
    }
}