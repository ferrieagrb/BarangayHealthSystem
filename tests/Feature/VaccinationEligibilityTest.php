<?php

namespace Tests\Feature;

use App\Models\VaccinationRecord;
use App\Models\citizens;
use App\Services\VaccinationEligibility;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VaccinationEligibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('vaccination_schedules');
        Schema::dropIfExists('vaccination_records');
        Schema::dropIfExists('citizens');

        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->date('Citizen_BirthDate')->nullable();
            $table->unsignedInteger('Citizen_Age')->nullable();
            $table->string('Citizen_Sex')->nullable();
            $table->boolean('Citizen_Pregnant')->nullable();
            $table->boolean('Citizen_InPills')->nullable();
            $table->timestamps();
        });

        Schema::create('vaccination_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->string('vaccine_name');
            $table->string('dose_number');
            $table->date('date_administered');
            $table->string('administered_by');
            $table->timestamps();
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

    public function test_routine_vaccine_age_windows_are_specific_and_late_doses_are_referred(): void
    {
        $eligibility = app(VaccinationEligibility::class);
        $newborn = $this->createCitizen(now()->toDateString());
        $olderChild = $this->createCitizen(now()->subYears(2)->toDateString());

        $this->assertTrue($eligibility->evaluate($newborn, 'BCG', 'Single Dose', now()->toDateString())['eligible']);
        $lateBcg = $eligibility->evaluate($olderChild, 'BCG', 'Single Dose', now()->toDateString());
        $this->assertFalse($lateBcg['eligible']);
        $this->assertStringContainsString('health center', $lateBcg['reason']);

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
    }

    public function test_one_year_old_has_catch_up_candidates_that_require_health_center_review(): void
    {
        $eligibility = app(VaccinationEligibility::class);
        $citizen = $this->createCitizen(now()->subYear()->toDateString());

        $routineResult = $eligibility->evaluate($citizen, 'Penta', 'Dose 1', now()->toDateString());
        $catchUpResult = $eligibility->evaluate(
            $citizen,
            'Penta',
            'Dose 1',
            now()->toDateString(),
            allowCatchUp: true
        );

        $this->assertFalse($routineResult['eligible']);
        $this->assertTrue($catchUpResult['eligible']);
        $this->assertTrue($catchUpResult['review_required']);
        $this->assertStringContainsString('health center', strtolower($catchUpResult['reason']));

        $options = $eligibility->optionsFor($citizen);
        $this->assertTrue($options['Penta']['eligible']);
        $this->assertTrue($options['Penta']['review_required']);
        $this->assertTrue($options['BCG']['review_required']);
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
        $migration->up();

        $this->assertDatabaseHas('vaccination_records', [
            'id' => $recordId,
            'eligibility_review_required' => true,
        ]);
        $this->assertSame(1, DB::table('vaccination_records')->where('id', $recordId)->count());
        $this->assertTrue(Schema::hasColumn('vaccination_records', 'catch_up_reviewed'));
        $this->assertTrue(Schema::hasColumn('vaccination_schedules', 'catch_up_reviewed'));
    }

    private function createCitizen(?string $birthDate): citizens
    {
        return citizens::create([
            'Citizen_BirthDate' => $birthDate,
            'Citizen_Age' => $birthDate ? \Carbon\Carbon::parse($birthDate)->age : null,
            'Citizen_Sex' => 'Female',
        ]);
    }
}
