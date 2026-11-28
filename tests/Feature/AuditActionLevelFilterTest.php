<?php

namespace Tests\Feature;

use App\Support\AuditActionLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditActionLevelFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('level_filter_logs', function ($table) {
            $table->id();
            $table->string('action')->nullable();
        });
        DB::table('level_filter_logs')->insert(array_map(fn ($action) => ['action' => $action], [
            'delete', 'permissions.updated', 'Import', 'create', 'view', 'unknown', null,
        ]));
    }

    public function test_each_level_filters_before_pagination_using_the_badge_classification(): void
    {
        foreach (AuditActionLevel::LEVELS as $level) {
            $this->app->instance('request', Request::create('/logs', 'GET', ['action_level' => $level]));
            $query = DB::table('level_filter_logs');
            AuditActionLevel::applyFilter($query);
            $expected = DB::table('level_filter_logs')->pluck('action')
                ->filter(fn ($action) => AuditActionLevel::forAction($action) === $level)->values()->all();
            $result = $query->orderBy('id')->paginate(1);
            $this->assertSame(count($expected), $result->total());
            $this->assertSame($expected[0], $result->items()[0]->action);
        }
    }

    public function test_all_and_invalid_levels_leave_all_records_visible(): void
    {
        foreach ([[], ['action_level' => 'all'], ['action_level' => 'invalid']] as $parameters) {
            $this->app->instance('request', Request::create('/logs', 'GET', $parameters));
            $query = DB::table('level_filter_logs');
            AuditActionLevel::applyFilter($query);
            $this->assertSame(7, $query->count());
            $this->assertSame('all', AuditActionLevel::selected());
        }
    }

    public function test_records_without_an_action_are_only_in_all_or_unclassified(): void
    {
        foreach (['all' => 7, 'unclassified' => 7, 'high' => 0] as $level => $count) {
            $this->app->instance('request', Request::create('/logs', 'GET', ['action_level' => $level]));
            $query = DB::table('level_filter_logs');
            AuditActionLevel::applyFilter($query, null);
            $this->assertSame($count, $query->count());
        }
    }
}
