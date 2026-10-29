<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AppearancePreferencesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);
        foreach (['user_permissions', 'system_settings', 'audit_logs', 'users'] as $table) {
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
            $table->integer('failed_login_attempts')->default(0);
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
    }

    public function test_every_role_can_preview_without_persisting_and_save_only_its_own_preferences(): void
    {
        DB::table('system_settings')->insert(['key' => 'allow_dark_mode', 'value' => '1']);
        $other = $this->makeUser('citizen');
        foreach (['bhw', 'admin', 'superadmin', 'citizen'] as $role) {
            $user = $this->makeUser($role);
            foreach ($this->settingsUrls($role) as $url) {
                $this->actingAs($user)->get($url)->assertOk()
                    ->assertSee('data-appearance-form', false)
                    ->assertSee('css/theme.css', false)
                    ->assertSee('js/appearance.js', false)
                    ->assertSee('data-theme="light"', false);
            }
            $this->assertSame('light', $user->fresh()->theme);
            $this->actingAs($user)->put(route('user.preferences.update'), [
                'theme' => 'dark', 'font_size' => 'xl', 'user_id' => $other->id,
            ])->assertRedirect()->assertSessionHasNoErrors();
            $this->assertSame('dark', $user->fresh()->theme);
            $this->assertSame('xl', $user->fresh()->font_size);
            $this->assertSame('light', $other->fresh()->theme);
            $this->assertSame('normal', $other->fresh()->font_size);
            $this->assertDatabaseHas('system_settings', ['key' => 'allow_dark_mode', 'value' => '1']);

            foreach ($this->settingsUrls($role) as $index => $url) {
                $response = $this->actingAs($user->fresh())->get($url)->assertOk()
                    ->assertSee('data-theme="dark"', false)->assertSee('data-font="xl"', false);
                // Optional real Blade output for the isolated browser checks; no production data.
                if ($directory = getenv('BHEAMS_APPEARANCE_FIXTURES')) {
                    if (!is_dir($directory)) {
                        mkdir($directory, 0777, true);
                    }
                    file_put_contents($directory . '/' . $role . '-' . $index . '.html', $response->getContent());
                }
            }
            $this->post(route('logout'))->assertRedirect();
            Auth::forgetGuards();
            $this->post(route('login.submit'), ['Username' => $user->email, 'Password' => 'password'])
                ->assertRedirect()->assertSessionHasNoErrors();
            $this->get($this->settingsUrls($role)[0])->assertOk()->assertSee('data-theme="dark"', false);
            $this->put(route('user.preferences.update'), ['theme' => 'light', 'font_size' => 'normal'])
                ->assertRedirect()->assertSessionHasNoErrors();
            $this->get($this->settingsUrls($role)[0])->assertOk()->assertSee('data-theme="light"', false);
        }
    }

    public function test_invalid_preferences_and_anonymous_requests_cannot_change_saved_values(): void
    {
        $user = $this->makeUser('admin');
        $this->actingAs($user)->put(route('user.preferences.update'), [
            'theme' => 'invalid', 'font_size' => 'invalid',
        ])->assertSessionHasErrors(['theme', 'font_size']);
        $this->assertSame('light', $user->fresh()->theme);
        $this->post(route('logout'));
        Auth::forgetGuards();
        $this->put(route('user.preferences.update'), ['theme' => 'dark', 'font_size' => 'xl'])
            ->assertRedirect(route('login'));
        $this->assertSame('light', $user->fresh()->theme);
    }

    public function test_shared_assets_cover_representative_role_sections(): void
    {
        $this->withoutExceptionHandling();
        $empty = new LengthAwarePaginator([], 0, 10);
        $citizen = new \App\Models\citizens([
            'Citizen_FName' => 'Sample', 'Citizen_LName' => 'Resident',
            'Citizen_Purok' => 'Purok 1', 'Citizen_BirthDate' => '1990-01-01',
        ]);
        $citizen->id = 1;
        foreach (['vaccinations', 'vaccinationSchedules', 'healthRecords', 'maintenanceRecords', 'medicationRecords'] as $relation) {
            $citizen->setRelation($relation, collect());
        }
        $citizen->setRelation('family', null)->setRelation('subgroup', null);
        $purok = new \App\Models\Purok(['name' => 'Purok 1', 'description' => 'Sample area']);
        $purok->id = 1;
        $purok->families_count = 0;
        $purok->subgroups_count = 1;
        $purok->purge_citizens_count = 0;
        $worker = $this->makeUser('bhw');
        $subgroup = new \App\Models\PurokSubgroup(['name' => 'Subgroup A', 'purok_id' => 1]);
        $subgroup->id = 1;
        $subgroup->families_count = 0;
        $subgroup->purge_citizens_count = 0;
        $subgroup->setRelation('purok', $purok)->setRelation('bhws', collect([$worker]));
        $purok->setRelation('subgroups', collect([$subgroup]));
        $announcement = new \App\Models\Announcement([
            'title' => 'Community Health Notice', 'description' => 'Sample health advisory content.',
            'content' => 'Sample health advisory content.', 'created_at' => now(),
        ]);
        $announcement->id = 1;
        $announcement->created_at = now();
        $announcement->setRelation('images', collect());
        $data = [
            'puroks' => collect(), 'subgroups' => collect(), 'bhws' => collect(),
            'demographicsMapData' => [], 'citizens' => $empty, 'families' => collect(),
            'availableCitizens' => collect(), 'referrals' => $empty, 'recent' => collect(),
            'announcements' => collect([$announcement]), 'citizen' => $citizen,
            'qrImage' => '', 'healthShare' => null, 'vaccinationSchedulingEnabled' => true,
            'canEdit' => true, 'vaccinationEligibilityOptions' => [], 'availableMedicines' => $empty,
            'calendarVaccineOptions' => [], 'calendarVaccine' => '', 'calendarPuroks' => collect(),
            'calendarCitizens' => collect(), 'canViewDiseaseAnalytics' => true,
            'defaultExportFields' => [], 'exportFieldGroups' => [], 'exportFieldDefinitions' => [],
            'citizenDisplayIds' => [], 'citizenTrends' => [], 'dashboardTrends' => [],
            'supplyTrends' => [], 'batchReleases' => collect(), 'groupedSupplies' => collect(),
            'supplies' => collect(),
            'batchReleaseCitizens' => collect(), 'batchReleaseSupplies' => collect(),
            'upcomingEvents' => collect(), 'lowStockSupplies' => collect(),
            'recentReferrals' => collect(), 'recentAnnouncements' => collect([$announcement]),
            'purokHeatmapData' => [], 'recentLogs' => collect(), 'activeSessions' => collect(),
            'auditLogs' => $empty, 'users' => $empty, 'bhwUsers' => collect(),
            'features' => [], 'availableSections' => ['citizens' => 'Citizens'],
            'selectedSections' => ['citizens'], 'categories' => collect(), 'diagnoses' => [],
            'vaccineTypes' => [], 'reportFilterSummary' => 'None', 'citizensReport' => collect(),
            'reportFilterOptions' => [], 'includeGraphics' => false,
            'announcement' => $announcement, 'returnUrl' => '/citizen/dashboard', 'source' => 'dashboard',
            'showArchived' => false, 'healthTrend' => [], 'monthLabels' => [], 'purokCounts' => [],
            'inventoryRisk' => [], 'referralStatusCounts' => [], 'topInventory' => collect(),
            'recentEvents' => collect(), 'recentSupplyActivity' => collect(),
            'categoryLabels' => [], 'categoryValues' => [], 'popularDisease' => null,
        ];
        foreach (['totalCitizens', 'totalInventoryUnits', 'totalInventory', 'pendingReferrals',
            'upcomingEventsCount', 'totalPosts', 'thisMonth', 'urgentAlerts', 'kids', 'adults',
            'seniors', 'bhwCount', 'lowStockCount', 'writeCount', 'readCount', 'noneCount',
            'activeUserCount', 'failedLoginCount', 'archivedCount', 'totalsupply', 'lowStock',
            'wellStocked', 'totalHealthRecords', 'activeMaintenance', 'lowStockItems',
            'outOfStockItems', 'pageViewsToday', 'totalUsers', 'bhwUsersCount',
            'scheduledReleases', 'claimedReleases'] as $key) {
            $data[$key] = 0;
        }
        $trend = ['state' => 'neutral', 'icon' => '', 'value' => '0'];
        $data['dashboardTrends'] = array_fill_keys(['citizens', 'inventory', 'referrals', 'diagnoses'], $trend);
        $data['citizenTrends'] = array_fill_keys(['total', 'kids', 'adults', 'seniors'], $trend);
        $data['supplyTrends'] = array_fill_keys(['total', 'low', 'well'], $trend);
        $data['purokCounts'] = collect();
        $data['inventoryRisk'] = collect();
        $data['healthTrend'] = collect();
        $data['monthLabels'] = collect();
        $data['referralStatusCounts'] = collect();
        $data['puroks'] = collect([$purok]);
        $data['subgroups'] = collect([$subgroup]);
        $data['bhws'] = collect([$worker]);
        $data['availableSupplies'] = collect();
        foreach (['adminActivityLogs', 'securityLogs', 'citizenLogs', 'supplyLogs',
            'healthRecordLogs', 'vehicleLogs', 'announcementLogs', 'eventLogs',
            'referralLogs', 'maintenanceLogs', 'releaseLogs'] as $key) {
            $data[$key] = $empty;
        }
        foreach (['admins', 'staff', 'forecast', 'citizenAdditions', 'citizenDetailEdits',
            'currentDiagnosisCount', 'currentMaintenanceCount', 'currentVaccinationCount',
            'currentCount', 'previousCount'] as $key) {
            $data[$key] = 0;
        }
        $data['period'] = 'last7';
        $data['periodStart'] = now()->subDays(6);
        $data['periodEnd'] = now();
        $data['periodLabel'] = 'Last 7 days';
        $data['filters'] = [];
        $data['supplyRecommendations'] = collect();
        $data['diseaseMonitoringData'] = array_fill_keys(['privacySuppressed', 'previousSuppressed',
            'missingLocationSuppressed', 'missingDateSuppressed'], false) + array_fill_keys([
            'totalRecords', 'uniqueCitizens', 'previousChange', 'previousRecords',
            'missingLocationRecords', 'missingDateRecords'], 0) + array_fill_keys([
            'diagnosisOptions', 'availablePuroks', 'availableSubgroups', 'diagnosisRankings',
            'mapAreas', 'areaRankings'], []);
        $pages = [
            'bhw' => ['home', 'citizen', 'addcitizen', 'citizen_show', 'healthrecord', 'analytics', 'calendar', 'announcements', 'qr_scanner',
                'referrals/index', 'referrals/create', 'reports', 'families/show', 'supplies',
                'citizendetails', 'ecard', 'announcement_create', 'supply_create', 'logs', 'referral', 'archive'],
            'admin' => ['admin_home', 'admin_analytics', 'admin_users', 'admin_supplies', 'admin_logs', 'user_create', 'demographics', 'permissions', 'archive'],
            'superadmin' => ['dashboard', 'system-analytics', 'system-connections', 'users', 'audit-logs'],
            'citizen' => ['dashboard', 'calendar', 'announcements', 'announcement-detail', 'ecard', 'supplies'],
        ];
        foreach ($pages as $role => $views) {
            $user = $this->makeUser($role);
            $user->update(['theme' => 'dark', 'font_size' => 'normal']);
            foreach ($views as $view) {
                $viewData = $data;
                if ($role === 'admin' && $view === 'admin_analytics') {
                    $viewData['bhwUsers'] = 0;
                }
                if ($role === 'citizen' && $view === 'supplies') {
                    $viewData['scheduledReleases'] = $empty;
                }
                if ($view === 'archive') {
                    $viewData += [
                        'layout' => $role === 'admin' ? 'templates.admin' : 'templates.layout',
                        'tabs' => ['citizens' => ['label' => 'Citizens', 'count' => 0]],
                        'indexRoute' => $role === 'admin' ? 'admin.archive.index' : 'archive.index',
                        'type' => 'citizens', 'items' => $empty,
                    ];
                }
                $viewName = $view === 'archive' ? 'archive.index' : $role . '.' . str_replace('/', '.', $view);
                $path = '/_appearance-test/' . $role . '/' . str_replace('/', '-', $view);
                $testRoute = Route::middleware('web')->get($path, fn () => view($viewName, $viewData));
                if ($role === 'citizen' && $view === 'dashboard') {
                    $testRoute->name('citizen.dashboard');
                }
                $response = $this->actingAs($user)->get($path)->assertOk()
                    ->assertSee('css/theme.css')->assertSee('js/appearance.js')
                    ->assertSee('data-theme="dark"', false);
                if ($directory = getenv('BHEAMS_APPEARANCE_FIXTURES')) {
                    if (!is_dir($directory)) {
                        mkdir($directory, 0777, true);
                    }
                    file_put_contents($directory . '/' . $role . '-' . str_replace('/', '-', $view) . '.html', $response->getContent());
                }
            }
        }
    }

    private function makeUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role) . ' Theme Test',
            'email' => $role . '-' . User::count() . '@example.test',
            'password' => 'password', 'role' => $role,
            'theme' => 'light', 'font_size' => 'normal', 'must_change_password' => false,
        ]);
    }

    private function settingsUrls(string $role): array
    {
        return match ($role) {
            'admin' => [route('admin.settings'), route('settings')],
            'superadmin' => [route('superadmin.system.settings'), route('settings')],
            'citizen' => [route('citizen.settings')],
            default => [route('settings')],
        };
    }
}
