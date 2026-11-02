<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CitizenAnnouncementReturnNavigationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('announcement_images');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->string('theme')->nullable();
            $table->string('font_size')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('content')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('announcement_images', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('announcement_id');
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('page_views', function (Blueprint $table): void {
            $table->id();
            $table->string('ip_address')->nullable();
            $table->text('url')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('load_time')->nullable();
            $table->string('browser')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function test_dashboard_entry_returns_to_dashboard_and_restores_scroll(): void
    {
        $announcement = $this->createAnnouncement();

        $this->actingAs($this->createCitizen())
            ->get(route('citizen.announcements.show', [
                'announcement' => $announcement->id,
                'from' => 'dashboard',
                'scroll' => 420,
            ]))
            ->assertOk()
            ->assertSee('href="' . route('citizen.dashboard', ['scroll' => 420]) . '#announcements"', false)
            ->assertSee('Back to Dashboard');
    }

    public function test_announcement_list_entry_returns_to_list_and_restores_scroll(): void
    {
        $announcement = $this->createAnnouncement();

        $this->actingAs($this->createCitizen())
            ->get(route('citizen.announcements.show', [
                'announcement' => $announcement->id,
                'from' => 'announcements',
                'scroll' => 735,
            ]))
            ->assertOk()
            ->assertSee('href="' . route('citizen.announcements', ['scroll' => 735]) . '"', false)
            ->assertSee('Back to Announcements');
    }

    public function test_missing_or_invalid_source_falls_back_to_dashboard(): void
    {
        $announcement = $this->createAnnouncement();
        $citizen = $this->createCitizen();

        foreach ([[], ['from' => 'https://example.com'], ['from' => 'unexpected']] as $query) {
            $this->actingAs($citizen)
                ->get(route('citizen.announcements.show', ['announcement' => $announcement->id] + $query))
                ->assertOk()
                ->assertSee('href="' . route('citizen.dashboard') . '#announcements"', false)
                ->assertSee('Back to Dashboard')
                ->assertDontSee('https://example.com');
        }
    }

    public function test_announcement_list_cards_open_the_full_detail_with_list_source(): void
    {
        $announcement = $this->createAnnouncement();

        $this->actingAs($this->createCitizen())
            ->get(route('citizen.announcements'))
            ->assertOk()
            ->assertSee(
                'href="' . route('citizen.announcements.show', [
                    'announcement' => $announcement->id,
                    'from' => 'announcements',
                ]) . '"',
                false
            )
            ->assertSee('Full announcement details.')
            ->assertDontSee('citizenAnnouncementModal');
    }

    private function createCitizen(): User
    {
        return User::create([
            'name' => 'Test Citizen',
            'email' => 'citizen@example.test',
            'password' => 'password',
            'role' => User::ROLE_CITIZEN,
            'must_change_password' => false,
        ]);
    }

    private function createAnnouncement(): Announcement
    {
        $id = DB::table('announcements')->insertGetId([
            'title' => 'Community Health Notice',
            'description' => 'A health notice for the community.',
            'content' => 'Full announcement details.',
            'category' => 'General',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Announcement::findOrFail($id);
    }
}
