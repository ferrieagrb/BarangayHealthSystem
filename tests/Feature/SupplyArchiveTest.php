<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupplyArchiveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([TrackPageViews::class, SystemMaintenance::class]);
        Schema::create('supplies', function ($table) {
            $table->id(); $table->string('name'); $table->string('category');
            $table->integer('quantity'); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('supply_logs', function ($table) {
            $table->id(); $table->string('action'); $table->unsignedBigInteger('supply_id');
            $table->integer('quantity'); $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('citizen_id')->nullable(); $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function test_bhw_can_archive_a_catalog_and_all_its_packs_without_removing_other_items(): void
    {
        $catalog = $this->supply('Medicine A', 0);
        $pack = $this->supply('Medicine A', 12);
        $other = $this->supply('Medicine B', 7);
        $this->actingAs($this->user(true))->delete(route('supplies.archive-item', $catalog))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSoftDeleted($catalog);
        $this->assertSoftDeleted($pack);
        $this->assertNotSoftDeleted($other);
        $this->assertDatabaseCount('supply_logs', 2);
        $this->assertDatabaseHas('supply_logs', ['supply_id' => $pack->id, 'action' => 'archive', 'quantity' => 12]);
        $this->assertSame(12, $pack->fresh()->quantity, 'Archive preserves quantity for restoration.');
        $pack->fresh()->restore();
        $this->assertNotSoftDeleted($pack);
    }

    public function test_empty_catalog_can_be_archived_from_the_main_row(): void
    {
        $catalog = $this->supply('Empty supply', 0);
        $this->actingAs($this->user(true))->delete(route('supplies.archive-item', $catalog))->assertRedirect();
        $this->assertSoftDeleted($catalog);
    }

    public function test_read_only_bhw_cannot_archive_a_catalog_or_single_pack(): void
    {
        $pack = $this->supply('Medicine', 10);
        $this->actingAs($this->user(false))->deleteJson(route('supplies.archive-item', $pack))->assertForbidden();
        $this->deleteJson(route('supplies.destroy', $pack))->assertForbidden();
        $this->assertNotSoftDeleted($pack);
        $this->assertDatabaseCount('supply_logs', 0);
    }

    public function test_single_pack_archive_keeps_other_packs_and_catalog(): void
    {
        $catalog = $this->supply('Medicine', 0);
        $pack = $this->supply('Medicine', 10);
        $this->actingAs($this->user(true))->delete(route('supplies.destroy', $pack))->assertRedirect();
        $this->assertSoftDeleted($pack);
        $this->assertNotSoftDeleted($catalog);
        $this->assertDatabaseCount('supply_logs', 1);
    }

    public function test_admin_uses_shared_archive_routes_with_supply_write_permission(): void
    {
        $catalog = $this->supply('Medicine', 0);
        $pack = $this->supply('Medicine', 10);
        $user = $this->user(true); $user->role = 'admin';
        $this->actingAs($user)->delete('/supplies/' . $pack->id)->assertRedirect();
        $this->assertSoftDeleted($pack);
        $this->delete(route('supplies.archive-item', $catalog))->assertRedirect();
        $this->assertSoftDeleted($catalog);
    }

    public function test_failed_audit_write_leaves_all_packs_available(): void
    {
        $catalog = $this->supply('Medicine', 0);
        $pack = $this->supply('Medicine', 10);
        Schema::drop('supply_logs');
        $this->actingAs($this->user(true))->deleteJson(route('supplies.archive-item', $catalog))->assertServerError();
        $this->assertNotSoftDeleted($catalog);
        $this->assertNotSoftDeleted($pack);
    }

    private function supply(string $name, int $quantity): Supply
    {
        return Supply::create(['name' => $name, 'category' => 'Medicine', 'quantity' => $quantity]);
    }

    private function user(bool $write): User
    {
        $user = new class extends User {
            public bool $canWriteSupplies = false;
            public function hasWriteAccess(string $feature): bool { return $this->canWriteSupplies; }
        };
        $user->forceFill(['id' => 1, 'role' => 'bhw', 'must_change_password' => false]);
        $user->canWriteSupplies = $write;
        return $user;
    }
}
