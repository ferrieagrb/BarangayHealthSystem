<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use App\Models\LoginChallenge;
use App\Models\User;
use App\Notifications\LoginConfirmation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginConfirmationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);
        Cache::flush();
        Notification::fake();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('password');
            $table->string('role');
            $table->boolean('must_change_password')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
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
        (require database_path('migrations/2026_11_28_000001_create_login_challenges_table.php'))->up();
    }

    private function user(array $attributes = []): User
    {
        return User::create($attributes + [
            'name' => 'Test BHW', 'email' => 'bhw@example.test', 'username' => 'test_bhw',
            'password' => 'Password123!', 'role' => 'bhw',
        ]);
    }

    private function start(User $user): LoginConfirmation
    {
        $this->post(route('login.submit'), ['Username' => $user->username, 'Password' => 'Password123!'])
            ->assertRedirect(route('login.confirmation'));
        $this->assertGuest();

        return Notification::sent($user, LoginConfirmation::class)->last();
    }

    public function test_password_and_code_require_explicit_email_approval(): void
    {
        $user = $this->user();
        $email = $this->start($user);
        $this->get(route('login.confirmation'))->assertOk()->assertSee('b***@example.test');
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get($email->reviewUrl())->assertOk()->assertSee('Yes, this is me')->assertSee('No, this is not me');
        $this->assertNull($email->challenge->fresh()->approved_at);
        $this->post(route('login.confirmation.verify'), ['code' => $email->code])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->post($email->reviewUrl(), ['decision' => 'yes'])->assertOk();
        $this->assertGuest();
        $this->post(route('login.confirmation.verify'), ['code' => $email->code])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($email->challenge->fresh()->consumed_at);
        $this->post(route('logout'));
        $this->withSession(['login_challenge_id' => $email->challenge->id])
            ->post(route('login.confirmation.verify'), ['code' => $email->code])->assertSessionHasErrors('code');
        $this->assertGuest();
        $mail = $email->toMail($user);
        $this->assertStringContainsString($email->code, implode(' ', $mail->outroLines));
        $this->assertStringContainsString('Yes / No', $mail->actionText);
    }

    public function test_no_blocks_the_attempt_even_with_the_correct_code(): void
    {
        $email = $this->start($this->user());
        $this->post($email->reviewUrl(), ['decision' => 'no'])->assertOk()->assertSee('has been blocked');
        $this->post($email->reviewUrl(), ['decision' => 'yes'])->assertOk();
        $this->post(route('login.confirmation.verify'), ['code' => $email->code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_five_incorrect_codes_block_further_verification(): void
    {
        $email = $this->start($this->user());
        $this->post($email->reviewUrl(), ['decision' => 'yes']);
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.confirmation.verify'), ['code' => '000000'])->assertSessionHasErrors('code');
        }
        $this->assertSame(5, $email->challenge->fresh()->attempts);
        $this->post(route('login.confirmation.verify'), ['code' => $email->code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_expired_and_tampered_links_cannot_approve(): void
    {
        $email = $this->start($this->user());
        $this->post($email->reviewUrl().'tampered', ['decision' => 'yes']);
        $this->assertNull($email->challenge->fresh()->approved_at);
        $this->travel(11)->minutes();
        $this->post($email->reviewUrl(), ['decision' => 'yes']);
        $this->assertNull($email->challenge->fresh()->approved_at);
        $this->post(route('login.confirmation.verify'), ['code' => $email->code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_resend_is_throttled_and_replaces_both_old_link_and_code(): void
    {
        $user = $this->user();
        $old = $this->start($user);
        $this->post(route('login.confirmation.resend'))->assertSessionHasErrors('login_confirmation');
        $this->assertSame(1, Notification::sent($user, LoginConfirmation::class)->count());
        $this->travel(61)->seconds();
        $this->post(route('login.confirmation.resend'))->assertRedirect(route('login.confirmation'));
        $new = Notification::sent($user, LoginConfirmation::class)->last();
        $this->assertNotSame($old->challenge->id, $new->challenge->id);
        $this->assertNotNull($old->challenge->fresh()->consumed_at);
        $this->post($old->reviewUrl(), ['decision' => 'yes'])->assertOk()->assertSee('expired');
        $this->post($new->reviewUrl(), ['decision' => 'yes']);
        $this->post(route('login.confirmation.verify'), ['code' => $new->code])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_code_only_works_in_the_browser_that_started_the_login(): void
    {
        $email = $this->start($this->user());
        $this->post($email->reviewUrl(), ['decision' => 'yes']);
        $this->flushSession();
        $this->post(route('login.confirmation.verify'), ['code' => $email->code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_missing_email_and_wrong_password_do_not_issue_challenges(): void
    {
        $user = $this->user(['email' => null]);
        $this->post(route('login.submit'), ['Username' => $user->username, 'Password' => 'wrong'])->assertSessionHas('error');
        $this->post(route('login.submit'), ['Username' => $user->username, 'Password' => 'Password123!'])
            ->assertSessionHasErrors('login_confirmation');
        Notification::assertNothingSent();
        $this->assertSame(0, LoginChallenge::count());
        $this->assertGuest();
    }

    public function test_password_email_lock_and_deletion_changes_invalidate_pending_login(): void
    {
        foreach (['password', 'email', 'lock', 'delete'] as $change) {
            $user = $this->user(['email' => $change.'@example.test', 'username' => $change]);
            $email = $this->start($user);
            $this->post($email->reviewUrl(), ['decision' => 'yes']);
            match ($change) {
                'password' => $user->update(['password' => 'ChangedPassword!']),
                'email' => $user->update(['email' => 'changed@example.test']),
                'lock' => $user->update(['is_locked' => true]),
                'delete' => $user->delete(),
            };
            $this->post(route('login.confirmation.verify'), ['code' => $email->code])->assertSessionHasErrors('code');
            $this->assertGuest();
        }
    }

    public function test_mail_delivery_failure_never_logs_in(): void
    {
        $user = $this->user();
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('Mail unavailable'));
        $this->post(route('login.submit'), ['Username' => $user->username, 'Password' => 'Password123!'])
            ->assertSessionHasErrors('login_confirmation');
        $this->assertGuest();
        $this->assertSame(0, LoginChallenge::count());
    }

    public function test_cancel_invalidates_the_pending_attempt(): void
    {
        $email = $this->start($this->user());
        $this->post(route('login.confirmation.cancel'))->assertRedirect(route('login'));
        $this->assertNotNull($email->challenge->fresh()->consumed_at);
        $this->assertNull(session('login_challenge_id'));
        $this->assertGuest();
    }
}
