<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use App\Jobs\DispatchAnnouncementPush;
use App\Jobs\SendWebPushNotification;
use App\Models\Announcement;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WebPushNotificationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);
        config(['app.key' => 'base64:' . base64_encode(str_repeat('k', 32))]);

        Schema::disableForeignKeyConstraints();
        foreach (['push_subscriptions', 'announcements', 'users'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        $migration = require database_path('migrations/2026_11_07_000000_create_push_subscriptions_table.php');
        $migration->up();
        $deviceMigration = require database_path('migrations/2026_11_08_000000_add_device_details_to_push_subscriptions.php');
        $deviceMigration->up();

        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('category');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_a_user_can_subscribe_and_unsubscribe_only_their_own_device(): void
    {
        $owner = $this->createUser('owner');
        $other = $this->createUser('other');
        $payload = $this->subscriptionPayload('owner-device');
        $endpointHash = hash('sha256', $payload['endpoint']);

        $this->actingAs($owner)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/123.0.0.0 Safari/537.36',
        ])
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertOk();

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $owner->id,
            'endpoint_hash' => $endpointHash,
            'device_name' => 'Chrome on Windows',
        ]);
        $this->assertNotNull($owner->pushSubscriptions()->firstOrFail()->last_seen_at);
        $this->assertNotSame($payload['endpoint'], DB::table('push_subscriptions')->value('endpoint'));
        $this->assertSame($payload['endpoint'], $owner->pushSubscriptions()->firstOrFail()->endpoint);

        $this->actingAs($other)
            ->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $payload['endpoint']])
            ->assertOk();
        $this->assertDatabaseHas('push_subscriptions', ['endpoint_hash' => $endpointHash]);

        $this->actingAs($owner)
            ->deleteJson(route('push-subscriptions.destroy'), ['endpoint' => $payload['endpoint']])
            ->assertOk();
        $this->assertDatabaseMissing('push_subscriptions', ['endpoint_hash' => $endpointHash]);
    }

    public function test_subscription_endpoint_must_be_a_supported_push_service(): void
    {
        $user = $this->createUser('owner');
        $payload = $this->subscriptionPayload('unsupported-device');
        $payload['endpoint'] = 'https://127.0.0.1/internal';

        $this->actingAs($user)
            ->postJson(route('push-subscriptions.store'), $payload)
            ->assertUnprocessable();

        $this->assertDatabaseCount('push_subscriptions', 0);
    }

    public function test_users_can_only_list_and_revoke_their_own_devices(): void
    {
        $owner = $this->createUser('device-owner');
        $other = $this->createUser('device-other');
        $device = $this->createSubscription($owner, 'owner-device');
        $this->createSubscription($other, 'other-device');

        $this->actingAs($owner)
            ->getJson(route('push-subscriptions.index'))
            ->assertOk()
            ->assertJsonCount(1, 'devices')
            ->assertJsonPath('devices.0.device_name', 'Chrome on Windows')
            ->assertJsonMissingPath('devices.0.endpoint')
            ->assertJsonMissingPath('devices.0.public_key')
            ->assertJsonMissingPath('devices.0.auth_token');

        $this->actingAs($other)
            ->deleteJson(route('push-subscriptions.revoke', $device->id))
            ->assertNotFound();
        $this->assertDatabaseHas('push_subscriptions', ['id' => $device->id]);

        $this->actingAs($owner)
            ->deleteJson(route('push-subscriptions.revoke', $device->id))
            ->assertOk();
        $this->assertDatabaseMissing('push_subscriptions', ['id' => $device->id]);
    }

    public function test_new_announcement_fans_out_a_generic_push_to_active_subscriptions(): void
    {
        $firstUser = $this->createUser('first');
        $secondUser = $this->createUser('second');
        $this->createSubscription($firstUser, 'first-device');
        $this->createSubscription($secondUser, 'second-device');
        $announcement = Announcement::create([
            'title' => 'Private title must not be sent',
            'description' => 'Private description must not be sent',
            'category' => 'Health',
        ]);

        config([
            'webpush.vapid.public_key' => 'configured-public-key',
            'webpush.vapid.private_key' => 'configured-private-key',
        ]);
        Queue::fake();

        (new DispatchAnnouncementPush($announcement->id))->handle();

        Queue::assertPushed(SendWebPushNotification::class, 2);
        Queue::assertPushed(SendWebPushNotification::class, function (SendWebPushNotification $job) use ($announcement): bool {
            return $job->payload['title'] === 'New barangay health announcement'
                && $job->payload['body'] === 'An update is available. Sign in to view the announcement.'
                && $job->payload['url'] === route('public.announcements.show', $announcement)
                && !str_contains(json_encode($job->payload), 'Private title');
        });
    }

    private function createUser(string $suffix): User
    {
        return User::create([
            'name' => ucfirst($suffix),
            'email' => $suffix . '@example.test',
            'password' => 'password',
            'role' => User::ROLE_CITIZEN,
            'must_change_password' => false,
        ]);
    }

    private function createSubscription(User $user, string $suffix): PushSubscription
    {
        $payload = $this->subscriptionPayload($suffix);

        return $user->pushSubscriptions()->create([
            'endpoint_hash' => hash('sha256', $payload['endpoint']),
            'endpoint' => $payload['endpoint'],
            'public_key' => $payload['keys']['p256dh'],
            'auth_token' => $payload['keys']['auth'],
            'content_encoding' => 'aes128gcm',
            'device_name' => 'Chrome on Windows',
            'last_seen_at' => now(),
        ]);
    }

    private function subscriptionPayload(string $suffix): array
    {
        return [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/' . $suffix,
            'keys' => [
                'p256dh' => 'test-public-key-' . $suffix,
                'auth' => 'test-auth-token-' . $suffix,
            ],
        ];
    }
}