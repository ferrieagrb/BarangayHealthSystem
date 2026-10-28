<?php

namespace Tests\Feature;

use App\Models\HealthRecord;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Models\User;
use App\Models\UserPermission;
use App\Services\BhwCitizenAccess;
use App\Services\BhwDiseaseMonitoringService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BhwDiseaseMonitoringTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['purok_subgroup_user', 'health_records', 'vaccination_records', 'supplies', 'citizens', 'families', 'purok_subgroups', 'puroks', 'user_permissions', 'users'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->timestamps();
        });
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('feature');
            $table->string('access');
            $table->timestamps();
        });
        Schema::create('puroks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('boundary')->nullable();
            $table->timestamps();
        });
        Schema::create('purok_subgroups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purok_id');
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('boundary')->nullable();
            $table->timestamps();
        });
        Schema::create('purok_subgroup_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purok_subgroup_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subgroup_id')->nullable();
        });
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->string('Citizen_FName');
            $table->string('Citizen_LName');
            $table->date('Citizen_BirthDate');
            $table->string('Citizen_ContactNo');
            $table->integer('Citizen_Age');
            $table->string('Citizen_Purok')->nullable();
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->unsignedBigInteger('family_id')->nullable();
            $table->timestamps();
        });
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->text('diagnosis');
            $table->date('record_date')->nullable();
            $table->string('severity')->nullable();
            $table->timestamps();
        });
        Schema::create('vaccination_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->string('vaccine_name')->nullable();
            $table->date('date_administered')->nullable();
            $table->timestamps();
        });
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('min_stock')->default(0);
            $table->timestamps();
        });
    }

    public function test_monitoring_uses_record_date_assigned_subgroups_and_distinct_citizens(): void
    {
        $bhw = $this->createBhw();
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports', 'access' => 'read']);

        $purok = Purok::create([
            'name' => 'Purok 1',
            'latitude' => 14.0668,
            'longitude' => 120.8531,
        ]);
        $assignedSubgroup = PurokSubgroup::create([
            'purok_id' => $purok->id,
            'name' => 'Group A',
            'latitude' => 14.067,
            'longitude' => 120.854,
        ]);
        $otherSubgroup = PurokSubgroup::create([
            'purok_id' => $purok->id,
            'name' => 'Group B',
        ]);
        $bhw->assignedSubgroups()->attach($assignedSubgroup->id);

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $citizens = collect(range(1, 3))->map(
            fn ($number) => $this->createCitizen($purok, $assignedSubgroup, "Assigned {$number}")
        );
        foreach ($citizens as $citizen) {
            $this->createDiagnosis($citizen->id, 'Dengue', $today);
        }
        $this->createDiagnosis($citizens->first()->id, 'Dengue', $yesterday);

        $outsideCitizen = $this->createCitizen($purok, $otherSubgroup, 'Outside assignment');
        foreach (range(1, 3) as $unused) {
            $this->createDiagnosis($outsideCitizen->id, 'Dengue', $today);
        }
        $citizenAccess = app(BhwCitizenAccess::class);
        $this->assertTrue($citizenAccess->canAccessCitizen($bhw, $citizens->first()));
        $this->assertFalse($citizenAccess->canAccessCitizen($bhw, $outsideCitizen));
        $this->assertSame(
            $citizens->pluck('id')->sort()->values()->all(),
            $citizenAccess->scopeCitizens(\App\Models\citizens::query(), $bhw)->pluck('id')->sort()->values()->all()
        );

        $todayStart = Carbon::today();
        $data = app(BhwDiseaseMonitoringService::class)->build(
            $bhw,
            $todayStart,
            $todayStart->copy()->endOfDay(),
            'dengue',
            null,
            null
        );

        $this->assertSame(3, $data['totalRecords']);
        $this->assertSame(3, $data['uniqueCitizens']);
        $this->assertCount(1, $data['mapAreas']);
        $this->assertSame('subgroup', $data['mapAreas'][0]['type']);
        $this->assertSame(3, $data['mapAreas'][0]['records_count']);
        $this->assertTrue($data['previousSuppressed']);

        $changedRecord = HealthRecord::query()
            ->where('citizen_id', $citizens->first()->id)
            ->where('record_date', $today)
            ->firstOrFail();
        $changedRecord->update(['diagnosis' => 'Influenza']);
        $updatedData = app(BhwDiseaseMonitoringService::class)->build(
            $bhw,
            $todayStart,
            $todayStart->copy()->endOfDay(),
            'dengue',
            null,
            null
        );
        $this->assertSame(2, $updatedData['totalRecords']);
        $this->assertTrue($updatedData['privacySuppressed']);

        HealthRecord::query()
            ->where('citizen_id', $citizens->get(1)->id)
            ->where('record_date', $today)
            ->firstOrFail()
            ->delete();
        $deletedData = app(BhwDiseaseMonitoringService::class)->build(
            $bhw,
            $todayStart,
            $todayStart->copy()->endOfDay(),
            'dengue',
            null,
            null
        );
        $this->assertSame(1, $deletedData['totalRecords']);
        $this->assertTrue($deletedData['privacySuppressed']);
    }

    public function test_small_results_are_suppressed_from_aggregate_details(): void
    {
        $bhw = $this->createBhw();
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'view_all_citizens', 'access' => 'read']);

        $purok = Purok::create(['name' => 'Purok 2', 'latitude' => 14.07, 'longitude' => 120.85]);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => 'Group C']);
        $citizen = $this->createCitizen($purok, $subgroup, 'Single citizen');
        $this->createDiagnosis($citizen->id, 'Influenza', now()->toDateString());

        $todayStart = Carbon::today();
        $data = app(BhwDiseaseMonitoringService::class)->build(
            $bhw,
            $todayStart,
            $todayStart->copy()->endOfDay(),
            null,
            null,
            null
        );

        $this->assertTrue($data['privacySuppressed']);
        $this->assertSame([], $data['mapAreas']);
        $this->assertTrue($data['diagnosisRankings']->isEmpty());
        $this->assertNull($data['missingLocationRecords']);
    }

    public function test_reports_page_loads_when_vaccine_filter_is_active(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\SystemMaintenance::class, \App\Http\Middleware\TrackPageViews::class]);

        $bhw = $this->createBhw();
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports_health', 'access' => 'read']);

        $purok = Purok::create(['name' => 'Purok 3', 'latitude' => 14.08, 'longitude' => 120.86]);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => 'Group D']);
        $bhw->assignedSubgroups()->attach($subgroup->id);
        \App\Models\citizens::create([
            'Citizen_FName' => 'Filtered',
            'Citizen_LName' => 'Citizen',
            'Citizen_BirthDate' => '2018-01-01',
            'Citizen_ContactNo' => fake()->unique()->numerify('09#########'),
            'Citizen_Age' => 4,
            'Citizen_Purok' => $purok->name,
            'subgroup_id' => $subgroup->id,
        ]);

        $this->actingAs($bhw)
            ->get(route('bhw.reports', ['sections' => ['immunization'], 'vaccine_type' => 'bcg']))
            ->assertOk();
    }

    private function createBhw(): User
    {
        return User::create([
            'name' => 'Test BHW',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => 'bhw',
        ]);
    }

    private function createCitizen(Purok $purok, PurokSubgroup $subgroup, string $firstName): \App\Models\citizens
    {
        return \App\Models\citizens::create([
            'Citizen_FName' => $firstName,
            'Citizen_LName' => 'Test',
            'Citizen_BirthDate' => '1990-01-01',
            'Citizen_ContactNo' => fake()->unique()->numerify('09#########'),
            'Citizen_Age' => 36,
            'Citizen_Purok' => $purok->name,
            'subgroup_id' => $subgroup->id,
        ]);
    }

    private function createDiagnosis(int $citizenId, string $diagnosis, string $date): void
    {
        HealthRecord::create([
            'citizen_id' => $citizenId,
            'diagnosis' => $diagnosis,
            'record_date' => $date,
            'severity' => 'Low / Mild',
        ]);
    }
}
