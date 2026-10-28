<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\TrackPageViews;
use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\SecurityHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Automatically track page views and response loading times on web routes
        $middleware->web(append: [
            TrackPageViews::class,
            SystemMaintenance::class,
            SecurityHeaders::class,
        ]);

        // Existing middleware aliases
        $middleware->alias([
            'role' => CheckRole::class,
            'feature.write' => \App\Http\Middleware\CheckFeaturePermission::class,
            'password.changed' => \App\Http\Middleware\EnsurePasswordIsChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request) {
            if (in_array($response->getStatusCode(), [401, 419], true) && ! $request->expectsJson()) {
                return redirect()->route('login');
            }

            if ($response->getStatusCode() === 403 && ! $request->expectsJson()) {
                return redirect()->route('landing');
            }

            return $response;
        });
    })->create();