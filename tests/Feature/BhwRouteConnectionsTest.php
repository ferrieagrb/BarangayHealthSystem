<?php

namespace Tests\Feature;

use App\Http\Controllers\EventController;
use App\Http\Controllers\QRScannerController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BhwRouteConnectionsTest extends TestCase
{
    public function test_citizen_show_name_resolves_to_the_bhw_only_route(): void
    {
        $namedRoutes = collect(Route::getRoutes())
            ->filter(fn ($route) => $route->getName() === 'citizen.show');

        $this->assertCount(1, $namedRoutes);

        $route = $namedRoutes->first();
        $this->assertSame('citizen/{id}', $route->uri());
        $this->assertContains('role:bhw', $route->gatherMiddleware());
    }

    public function test_calendar_event_edit_route_calls_the_update_action(): void
    {
        $route = collect(Route::getRoutes())->first(
            fn ($route) => $route->uri() === 'events/{id}'
                && in_array('PUT', $route->methods(), true)
        );

        $this->assertNotNull($route);
        $this->assertSame(EventController::class . '@updateEvent', $route->getActionName());
    }

    public function test_family_routes_are_limited_to_bhw_users(): void
    {
        $familyRoutes = collect(Route::getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'bhw/families'));

        $this->assertNotEmpty($familyRoutes);

        foreach ($familyRoutes as $route) {
            $this->assertContains('role:bhw', $route->gatherMiddleware());
        }
    }

    public function test_qr_checker_diagnosis_route_has_a_controller_action(): void
    {
        $route = collect(Route::getRoutes())->first(
            fn ($route) => $route->getName() === 'qr.scanner.diagnosis.store'
        );

        $this->assertNotNull($route);
        $this->assertSame(QRScannerController::class . '@storeDiagnosis', $route->getActionName());
        $this->assertTrue(method_exists(QRScannerController::class, 'storeDiagnosis'));
    }

    public function test_qr_lookup_accepts_legacy_health_card_ids(): void
    {
        $route = collect(Route::getRoutes())->first(
            fn ($route) => $route->getName() === 'qr.scanner.lookup'
        );

        $this->assertNotNull($route);
        $this->assertSame(QRScannerController::class . '@lookup', $route->getActionName());
        $this->assertSame([], $route->wheres);
        $this->assertContains('role:bhw', $route->gatherMiddleware());
    }
}
