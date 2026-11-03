<?php

namespace Tests\Feature;

use App\Models\VaccinationRecord;
use App\Models\User;
use App\Models\citizens;
use App\Services\VaccinationEligibility;
use App\Services\MedicalTerminologySearch;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VaccinationEligibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('vaccination_schedules');
        Schema::dropIfExists('vaccination_records');
        Schema::dropIfExists('purok_subgroup_user');
        Schema::dropIfExists('purok_subgroups');
        Schema::dropIfExists('families');
        Schema::dropIfExists('citizen_activity_logs');
        Schema::dropIfExists('users');
        Schema::dropIfExists('citizens');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->date('Citizen_BirthDate')->nullable();
            $table->unsignedInteger('Citizen_Age')->nullable();
            $table->string('Citizen_Sex')->nullable();
            $table->boolean('Citizen_Pregnant')->nullable();
            $table->boolean('Citizen_InPills')->nullable();
            $table->boolean('Citizen_IsDeceased')->default(false);
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->unsignedBigInteger('family_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purok_subgroups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('citizen_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action');
            $table->string('module');
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('vaccination_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->string('vaccine_name');
            $table->string('cvx_code', 20)->nullable();
            $table->text('vaccine_formulation')->nullable();
            $table->string('dose_number');
            $table->date('date_administered');
            $table->string('administered_by');
            $table->boolean('historical_recording')->default(false);
            $table->boolean('eligibility_review_required')->default(false);
            $table->string('eligibility_review_reason')->nullable();
            $table->boolean('catch_up_reviewed')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vaccination_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->string('vaccine_name');
            $table->string('dose_number')->nullable();
            $table->date('scheduled_date');
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled');
            $table->unsignedBigInteger('scheduled_by')->nullable();
            $table->timestamps();
        });
    }

    public function test_routine_vaccine_age_windows_are_specific_and_out_of_range_doses_are_unavailable(): void
    {
        $eligibility = app(VaccinationEligibility::class);
        $newborn = $this->createCitizen(now()->toDateString());
        $olderChild = $this->createCitizen(now()->subYears(2)->toDateString());

        $this->assertTrue($eligibility->evaluate($newborn, 'BCG', 'Single Dose', now()->toDateString())['eligible']);
        $lateBcg = $eligibility->evaluate($olderChild, 'BCG', 'Single Dose', now()->toDateString());
        $this->assertFalse($lateBcg['eligible']);
        $this->assertStringContainsString('historical recording', $lateBcg['reason']);

        $hpvEligible = $this->createCitizen(now()->subYears(10)->toDateString());
        $hpvIneligible = $this->createCitizen(now()->subYears(20)->toDateString());
        $this->assertTrue($eligibility->evaluate($hpvEligible, 'Human Papillomavirus (HPV)', 'Single Dose', now()->toDateString())['eligible']);
        $this->assertFalse($eligibility->evaluate($hpvIneligible, 'HPV', 'Single Dose', now()->toDateString())['eligible']);
    }

    public function test_multi_dose_vaccines_require_the_next_dose_and_its_routine_age_window(): void
    {
        $eligibility = app(VaccinationEligibility::class);
        $citizen = $this->createCitizen(now()->subDays(42)->toDateString());
        $today = now()->toDateString();

        $this->assertTrue($eligibility->evaluate($citizen, 'Penta', 'Dose 1', $today)['eligible']);
        $this->assertFalse($eligibility->evaluate($citizen, 'Penta', 'Dose 2', $today)['eligible']);

        $citizen = $this->createCitizen(now()->subDays(70)->toDateString());
        VaccinationRecord::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'Pentavalent (Penta)',
            'dose_number' => 'Dose 1',
            'date_administered' => now()->subDays(28)->toDateString(),
            'administered_by' => 'Test',
        ]);
        $this->assertTrue($eligibility->evaluate($citizen, 'Penta', 'Dose 2', $today)['eligible']);
    }

    public function test_unknown_birth_date_and_unknown_vaccines_are_rejected(): void
    {
        $eligibility = app(VaccinationEligibility::class);
        $citizen = $this->createCitizen(null);

        $this->assertFalse($eligibility->evaluate($citizen, 'BCG', 'Single Dose', now()->toDateString())['eligible']);
        $this->assertFalse($eligibility->evaluate($citizen, 'Unlisted vaccine', 'Dose 1', now()->toDateString())['eligible']);
        $this->assertSame('BCG', $eligibility->codeFor('Bacillus Calmette Guerin'));
    }

    public function test_vaccine_outside_its_configured_age_window_is_not_a_candidate(): void
    {
        $eligibility = app(VaccinationEligibility::class);
        $citizen = $this->createCitizen(now()->subYear()->toDateString());

        $result = $eligibility->evaluate($citizen, 'Penta', 'Dose 1', now()->toDateString());
        $this->assertFalse($result['eligible']);

        $options = $eligibility->optionsFor($citizen);
        $this->assertFalse($options['Penta']['eligible']);
        $this->assertFalse($options['BCG']['eligible']);
        $this->assertArrayNotHasKey('review_required', $options['Penta']);
    }

    public function test_historical_vaccination_eligibility_uses_age_on_the_administration_date(): void
    {
        $eligibility = app(VaccinationEligibility::class);
        $birthDate = now()->subYears(5)->toDateString();
        $citizen = $this->createCitizen($birthDate);

        $historicalResult = $eligibility->evaluate($citizen, 'BCG', 'Single Dose', $birthDate);

        $this->assertTrue($historicalResult['eligible']);
        $this->assertFalse($eligibility->evaluate($citizen, 'BCG', 'Single Dose', now()->toDateString())['eligible']);
    }

    public function test_direct_submission_requires_historical_path_for_out_of_range_records(): void
    {
        $citizen = $this->createCitizen(now()->subYears(2)->toDateString());
        $user = $this->assignCitizenToBhw($citizen);

        $response = $this->withoutMiddleware()
            ->actingAs($user)
            ->post(route('vaccination.store'), [
                'citizen_id' => $citizen->id,
                'vaccine_name' => 'BCG',
                'dose_number' => 'Single Dose',
                'date_administered' => now()->toDateString(),
                'catch_up_reviewed' => '1',
            ]);

        $response->assertSessionHasErrors('vaccine_name');
        $this->assertDatabaseCount('vaccination_records', 0);

        $historicalResponse = $this->actingAs($user)->post(route('vaccination.store'), [
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 'Single Dose',
            'date_administered' => now()->toDateString(),
            'historical_recording' => '1',
        ]);
        $historicalResponse->assertSessionHas('success', 'Vaccination added successfully.');
        $this->assertSame(
            1,
            DB::table('vaccination_records')
                ->where('citizen_id', $citizen->id)
                ->where('vaccine_name', 'BCG')
                ->whereDate('date_administered', now()->toDateString())
                ->where('historical_recording', true)
                ->where('eligibility_review_required', true)
                ->where('catch_up_reviewed', false)
                ->count()
        );

        $duplicateResponse = $this->actingAs($user)->post(route('vaccination.store'), [
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 'Single Dose',
            'date_administered' => now()->toDateString(),
            'historical_recording' => '1',
        ]);
        $duplicateResponse->assertSessionHasErrors('vaccine_name');

        $manualResponse = $this->actingAs($user)->post(route('vaccination.store'), [
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'Unmapped online vaccine',
            'dose_number' => 'Single Dose',
            'date_administered' => $citizen->Citizen_BirthDate,
        ]);
        $manualResponse->assertSessionHasErrors('vaccine_name');
        $this->assertDatabaseCount('vaccination_records', 1);
    }

    public function test_cdc_cvx_vaccine_can_be_recorded_historically_with_its_code_and_formulation(): void
    {
        Cache::flush();
        Http::fake([
            '*www2a.cdc.gov/vaccines/iis/iisstandards/XML2.asp*' => Http::response(
                '<?xml version="1.0" encoding="ISO-8859-1"?><CVXCodes><CVXInfo><ShortDescription>Pneumococcal conjugate vaccine, 13 valent</ShortDescription><FullVaccinename>Pneumococcal conjugate vaccine, 13 valent</FullVaccinename><CVXCode>133</CVXCode><Notes>13-valent formulation</Notes><Status>Active</Status></CVXInfo></CVXCodes>',
                200,
                ['Content-Type' => 'text/xml']
            ),
        ]);

        $citizen = $this->createCitizen(now()->subYears(40)->toDateString());
        $user = $this->assignCitizenToBhw($citizen);
        $suggestion = collect(app(MedicalTerminologySearch::class)
            ->search('vaccine', 'pneumococcal', $user)['suggestions'])
            ->firstWhere('classification_code', '133');

        $this->assertSame('recognized', $suggestion['recognition_status']);
        $this->assertSame('conditional', $suggestion['eligibility_status']);
        $this->assertSame('133', $suggestion['classification_code']);
        $this->assertNotEmpty($suggestion['selection_token']);

        $payload = [
            'vaccine_name' => $suggestion['name'],
            'dose_number' => 'Dose not specified',
            'date_administered' => now()->toDateString(),
            'vaccine_selection_token' => $suggestion['selection_token'],
        ];

        $this->withoutMiddleware()->actingAs($user)
            ->post(route('citizen.vaccination.store', $citizen->id), $payload)
            ->assertSessionHasErrors('vaccine_name');
        $this->assertDatabaseCount('vaccination_records', 0);

        $payload['historical_recording'] = '1';
        $response = $this->actingAs($user)->post(route('citizen.vaccination.store', $citizen->id), $payload);

        $response->assertSessionHas('success', 'Vaccination record added successfully.');
        $this->assertDatabaseHas('vaccination_records', [
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'Pneumococcal conjugate vaccine, 13 valent',
            'cvx_code' => '133',
            'vaccine_formulation' => 'Pneumococcal conjugate vaccine, 13 valent',
            'historical_recording' => true,
            'eligibility_review_required' => true,
            'eligibility_review_reason' => 'Eligibility may depend on clinical risk factors; age alone does not establish eligibility.',
        ]);

        $this->actingAs($user)
            ->post(route('citizen.vaccination.store', $citizen->id), $payload)
            ->assertSessionHasErrors('vaccine_name');
        $this->assertDatabaseCount('vaccination_records', 1);
    }

    public function test_existing_records_are_preserved_and_flagged_for_review_when_outside_routine_window(): void
    {
        $citizen = $this->createCitizen(now()->subYears(2)->toDateString());
        $recordId = DB::table('vaccination_records')->insertGetId([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 'Single Dose',
            'date_administered' => now()->toDateString(),
            'administered_by' => 'Legacy',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_10_28_000001_add_vaccination_eligibility_review.php');
        Schema::table('vaccination_records', function (Blueprint $table) {
            $table->dropColumn(['eligibility_review_required', 'eligibility_review_reason', 'catch_up_reviewed']);
        });
        $migration->up();

        $this->assertDatabaseHas('vaccination_records', [
            'id' => $recordId,
            'eligibility_review_required' => true,
        ]);
        $this->assertSame(1, DB::table('vaccination_records')->where('id', $recordId)->count());
        $this->assertTrue(Schema::hasColumn('vaccination_records', 'catch_up_reviewed'));
        $this->assertTrue(Schema::hasColumn('vaccination_schedules', 'catch_up_reviewed'));
    }

    public function test_cvx_metadata_migration_adds_historical_recording_fields(): void
    {
        Schema::table('vaccination_records', function (Blueprint $table) {
            $table->dropColumn(['cvx_code', 'vaccine_formulation', 'historical_recording']);
        });

        $migration = require database_path('migrations/2026_11_03_000001_add_cvx_historical_fields_to_vaccination_records.php');
        $migration->up();

        $this->assertTrue(Schema::hasColumn('vaccination_records', 'cvx_code'));
        $this->assertTrue(Schema::hasColumn('vaccination_records', 'vaccine_formulation'));
        $this->assertTrue(Schema::hasColumn('vaccination_records', 'historical_recording'));
    }

    private function createCitizen(?string $birthDate): citizens
    {
        return citizens::create([
            'Citizen_BirthDate' => $birthDate,
            'Citizen_Age' => $birthDate ? \Carbon\Carbon::parse($birthDate)->age : null,
            'Citizen_Sex' => 'Female',
        ]);
    }

    private function assignCitizenToBhw(citizens $citizen): User
    {
        $user = User::create([
            'name' => 'Test BHW',
            'email' => 'bhw-' . $citizen->id . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'bhw',
        ]);
        $subgroupId = DB::table('purok_subgroups')->insertGetId([
            'name' => 'Test subgroup',
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('citizens')->where('id', $citizen->id)->update(['subgroup_id' => $subgroupId]);
        DB::table('purok_subgroup_user')->insert([
            'purok_subgroup_id' => $subgroupId,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }
}
