<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');
        Paginator::defaultSimpleView('partials.pagination');
        // Usage: @canWrite('supplies') ... @endcanWrite
        Blade::if('canWrite', function ($feature) {
            $user = auth()->user();
            if ($user->role === 'admin') return true; // Admins always have access

            // Checking from a relation or JSON column:
            return $user->permissions[$feature] ?? 'read' === 'write'; 
            // Adjust the lookup logic based on whether you chose Option A or B
        });
    }
}
