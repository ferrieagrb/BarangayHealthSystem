<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupplyStockMovementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([TrackPageViews::class, SystemMaintenance::class]);
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->integer('quantity');
            $table->date('expiration_date')->nullable();
            $table->string('status')->default('Available');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('supply_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->unsignedBigInteger('supply_id');
            $table->integer('quantity');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        $this->actingAs($this->worker(true));
    }

    public function test_excess_release_is_rejected_without_changing_stock_or_logs(): void
    {
        $supply = $this->stock(20);
        $this->post('/supplies/release', ['id' => $supply->id, 'quantity' => 21, 'citizen_id' => 42])
            ->assertSessionHasErrors('quantity');
        $this->assertSame(20, $supply->fresh()->quantity);
        $this->assertDatabaseCount('supply_logs', 0);
    }

    public function test_release_deducts_only_the_recorded_quantity(): void
    {
        $supply = $this->stock(20);
        $this->post('/supplies/release', ['id' => $supply->id, 'quantity' => 3, 'citizen_id' => 42])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(17, $supply->fresh()->quantity);
        $this->assertDatabaseHas('supply_logs', [
            'action' => 'release', 'supply_id' => $supply->id, 'quantity' => 3, 'citizen_id' => 42,
        ]);
    }

    public function test_item_withdrawal_uses_earliest_expiring_packs_and_keeps_other_items(): void
    {
        $earlier = $this->stock(5, ['expiration_date' => '2027-01-01']);
        $later = $this->stock(7, ['expiration_date' => '2028-01-01']);
        $other = $this->stock(9, ['name' => 'Other item']);
        $otherCategory = $this->stock(10, ['category' => 'Other category']);

        $this->post('/supplies/item/withdraw', ['supply_id' => $later->id, 'quantity' => 8])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSame(0, $earlier->fresh()->quantity);
        $this->assertSame('Out of Stock', $earlier->fresh()->status);
        $this->assertSame(4, $later->fresh()->quantity);
        $this->assertSame(9, $other->fresh()->quantity);
        $this->assertSame(10, $otherCategory->fresh()->quantity);
        $this->assertNotSoftDeleted($earlier);
        $this->assertDatabaseCount('supply_logs', 2);
        $this->assertSame(8, (int) DB::table('supply_logs')->sum('quantity'));
    }

    public function test_excess_item_withdrawal_keeps_every_pack_unchanged(): void
    {
        $first = $this->stock(5);
        $second = $this->stock(7);
        $this->post('/supplies/item/withdraw', ['supply_id' => $first->id, 'quantity' => 13])
            ->assertSessionHasErrors('quantity');
        $this->assertSame(5, $first->fresh()->quantity);
        $this->assertSame(7, $second->fresh()->quantity);
        $this->assertDatabaseCount('supply_logs', 0);
    }

    public function test_stock_deduction_rolls_back_when_its_audit_log_cannot_be_saved(): void
    {
        $supply = $this->stock(20);
        Schema::drop('supply_logs');
        $this->postJson('/supplies/release', ['id' => $supply->id, 'quantity' => 3, 'citizen_id' => 42])
            ->assertServerError();
        $this->assertSame(20, $supply->fresh()->quantity);
    }

    public function test_read_only_worker_cannot_release_or_withdraw_stock(): void
    {
        $supply = $this->stock(20);
        $this->actingAs($this->worker(false));
        $this->postJson('/supplies/release', ['id' => $supply->id, 'quantity' => 3, 'citizen_id' => 42])->assertForbidden();
        $this->postJson('/supplies/item/withdraw', ['supply_id' => $supply->id, 'quantity' => 3])->assertForbidden();
        $this->postJson('/supplies/batch/withdraw', ['supply_id' => $supply->id, 'quantity' => 3])->assertForbidden();
        $this->assertSame(20, $supply->fresh()->quantity);
        $this->assertDatabaseCount('supply_logs', 0);
    }

    private function stock(int $quantity, array $extra = []): Supply
    {
        return Supply::create($extra + ['name' => 'Test item', 'category' => 'Medicine', 'quantity' => $quantity]);
    }

    private function worker(bool $write): User
    {
        $user = new class extends User {
            public bool $canWrite = false;

            public function hasWriteAccess(string $feature): bool
            {
                return $this->canWrite;
            }
        };
        $user->forceFill(['id' => 1, 'role' => 'bhw', 'must_change_password' => false]);
        $user->canWrite = $write;

        return $user;
    }
}
