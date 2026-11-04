<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use App\Models\User;
use App\Services\CitizenAccessAuditor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityControlsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);
        Cache::flush();
        RateLimiter::clear('x');

        foreach (['user_permissions', 'system_settings', 'audit_logs', 'admin_activity_logs', 'users'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->nullable();
            $table->string('password');
            $table->string('role');
            $table->string('theme')->nullable();
            $table->string('font_size')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_until')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('feature');
            $table->string('access');
        });
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->text('description');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('action');
            $table->string('scope')->nullable();
            $table->text('description')->nullable();
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
        DB::table('system_settings')->insert(['key' => 'max_failed_login_attempts', 'value' => '3']);
    }

    private function makeUser(string $role, array $overrides = []): User
    {
        static $n = 0;
        $n++;

        return User::create(array_merge([
            'name' => ucfirst($role) . " {$n}",
            'email' => "{$role}{$n}@example.test",
            'password' => Hash::make('password'),
            'role' => $role,
        ], $overrides));
    }

    public function test_repeated_failures_lock_temporarily_and_the_lock_expires_on_its_own(): void
    {
        $user = $this->makeUser('bhw');

        foreach (range(1, 3) as $attempt) {
            $this->post(route('login.submit'), ['Username' => $user->email, 'Password' => 'wrong']);
        }

        $user->refresh();
        $this->assertTrue($user->locked_until->isFuture());
        $this->assertFalse((bool) $user->is_locked);

        $this->post(route('login.submit'), ['Username' => $user->email, 'Password' => 'password'])
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'Too many failed sign-in attempts'));
        $this->assertGuest();

        $this->travel(16)->minutes();
        RateLimiter::clear(strtolower($user->email) . '|127.0.0.1');

        $this->post(route('login.submit'), ['Username' => $user->email, 'Password' => 'password']);
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->locked_until);
    }

    public function test_unknown_identifiers_receive_the_same_messages_as_real_accounts(): void
    {
        $messages = [];
        foreach (range(1, 3) as $attempt) {
            $response = $this->post(route('login.submit'), ['Username' => 'ghost@example.test', 'Password' => 'wrong']);
            $messages[] = session('error');
        }

        $this->assertStringContainsString('2 attempt(s) left', $messages[0]);
        $this->assertStringContainsString('Too many failed sign-in attempts', $messages[2]);
    }

    public function test_unlocking_does_not_require_a_password_change_and_reset_does(): void
    {
        $admin = $this->makeUser('admin');
        $target = $this->makeUser('bhw', ['locked_until' => now()->addMinutes(10), 'failed_login_attempts' => 3]);

        $this->actingAs($admin)->patch(route('admin.users.unlock', $target->id))->assertSessionHas('success');
        $target->refresh();
        $this->assertNull($target->locked_until);
        $this->assertSame(0, $target->failed_login_attempts);
        $this->assertFalse((bool) $target->must_change_password);

        $oldHash = $target->password;

        $this->actingAs($admin)->patch(route('admin.users.reset-password', $target->id), ['confirm_password' => 'nope'])
            ->assertSessionHasErrors('confirm_password');
        $this->assertSame($oldHash, $target->fresh()->password);

        $this->actingAs($admin)->patch(route('admin.users.reset-password', $target->id), ['confirm_password' => 'password'])
            ->assertSessionHas('success');
        $target->refresh();
        $this->assertNotSame($oldHash, $target->password);
        $this->assertTrue((bool) $target->must_change_password);
    }

    public function test_temporary_password_from_admin_reset_can_log_in_as_the_target_user(): void
    {
        $admin = $this->makeUser('admin');
        $target = $this->makeUser('citizen', ['username' => 'citizen_0101']);

        $response = $this->actingAs($admin)->patch(route('admin.users.reset-password', $target->id), [
            'confirm_password' => 'password',
        ]);
        $response->assertSessionHas('success');

        $successMessage = $response->getSession()->get('success');
        $this->assertStringContainsString("Log in using {$target->username}.", $successMessage);
        preg_match('/Temporary password \(shown once\): ([A-Za-z0-9]+)\./', $successMessage, $matches);
        $this->assertArrayHasKey(1, $matches);

        $this->post(route('login.submit'), [
            'Username' => $target->username,
            'Password' => $matches[1],
        ])->assertRedirect(route('password.force-change'));

        $this->assertAuthenticatedAs($target);
    }

    public function test_admin_cannot_reset_a_superadmin_password(): void
    {
        $admin = $this->makeUser('admin');
        $super = $this->makeUser('superadmin');

        $this->actingAs($admin)->patch(route('admin.users.reset-password', $super->id), ['confirm_password' => 'password'])
            ->assertRedirect();
        $this->assertTrue(Hash::check('password', $super->fresh()->password));
    }

    public function test_changing_a_role_requires_the_acting_users_password(): void
    {
        $admin = $this->makeUser('admin');
        $target = $this->makeUser('citizen');
        $payload = ['name' => $target->name, 'email' => $target->email, 'role' => 'bhw'];

        $this->actingAs($admin)->put(route('admin.users.update', $target->id), $payload)
            ->assertSessionHasErrors('confirm_password');
        $this->assertSame('citizen', $target->fresh()->role);

        $this->actingAs($admin)->put(route('admin.users.update', $target->id), $payload + ['confirm_password' => 'password'])
            ->assertSessionHasNoErrors();
        $this->assertSame('bhw', $target->fresh()->role);
    }

    public function test_exports_require_password_confirmation(): void
    {
        $bhw = $this->makeUser('bhw');

        $this->actingAs($bhw)->get(route('citizen.export', ['fields' => ['name']]))
            ->assertRedirect(route('password.confirm'));

        $this->actingAs($bhw)->post(route('password.confirm.store'), ['password' => 'wrong'])
            ->assertSessionHasErrors('password');
        $this->assertNull(session('auth.password_confirmed_at'));

        $this->actingAs($bhw)->post(route('password.confirm.store'), ['password' => 'password'])
            ->assertRedirect();
        $this->assertNotNull(session('auth.password_confirmed_at'));
    }

    public function test_password_reentry_can_be_switched_off_in_system_settings(): void
    {
        $admin = $this->makeUser('admin');
        $target = $this->makeUser('bhw');
        DB::table('system_settings')->insert(['key' => 'password_reentry_required', 'value' => '0']);

        $this->actingAs($admin)->patch(route('admin.users.reset-password', $target->id))
            ->assertSessionHas('success');
        $this->assertTrue((bool) $target->fresh()->must_change_password);

        $response = $this->actingAs($admin)->get(route('citizen.export', ['fields' => ['name']]));
        $this->assertNotSame(route('password.confirm'), $response->headers->get('Location'));
    }

    public function test_bulk_views_and_exports_raise_an_alert_once(): void
    {
        $bhw = $this->makeUser('bhw');
        $this->actingAs($bhw);
        $auditor = app(CitizenAccessAuditor::class);

        foreach (range(1, CitizenAccessAuditor::VIEW_ALERT_THRESHOLD + 5) as $i) {
            $auditor->recordView($i, 'test');
        }

        $this->assertSame(CitizenAccessAuditor::VIEW_ALERT_THRESHOLD + 5, DB::table('audit_logs')->where('action', 'citizen.viewed')->count());
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'security.alert')->count());

        $auditor->recordExport('Excel', CitizenAccessAuditor::EXPORT_ALERT_ROWS);
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'citizen.exported')->count());
        $this->assertSame(2, DB::table('audit_logs')->where('action', 'security.alert')->count());
    }
}
