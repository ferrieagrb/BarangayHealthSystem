<?php

namespace Tests\Feature;

use App\Models\citizens;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CitizenFamilyPlanningEligibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('citizens');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
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
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->string('Citizen_FName')->nullable();
            $table->string('Citizen_MName')->nullable();
            $table->string('Citizen_LName')->nullable();
            $table->date('Citizen_BirthDate')->nullable();
            $table->unsignedInteger('Citizen_Age')->nullable();
            $table->string('Citizen_Sex')->nullable();
            $table->boolean('Citizen_Pregnant')->nullable();
            $table->boolean('Citizen_InPills')->nullable();
            $table->timestamps();
        });

        $this->actingAs(User::create([
            'name' => 'Test User',
            'email' => 'test-user@example.com',
            'password' => 'password',
            'role' => 'bhw',
        ]));
    }

    public function test_male_citizens_cannot_retain_pregnancy_or_pills_statuses(): void
    {
        $citizen = $this->createCitizen('Male', 20, true, true);

        $this->assertFalse((bool) $citizen->Citizen_Pregnant);
        $this->assertFalse((bool) $citizen->Citizen_InPills);
    }

    public function test_female_pills_status_requires_age_sixteen_and_pregnancy_remains_available(): void
    {
        $minor = $this->createCitizen('Female', 15, true, true);
        $adult = $this->createCitizen('Female', 16, true, true);

        $this->assertTrue((bool) $minor->Citizen_Pregnant);
        $this->assertFalse((bool) $minor->Citizen_InPills);
        $this->assertTrue((bool) $adult->Citizen_Pregnant);
        $this->assertTrue((bool) $adult->Citizen_InPills);
    }

    public function test_changing_sex_or_age_clears_newly_ineligible_statuses(): void
    {
        $citizen = $this->createCitizen('Female', 18, true, true);

        $citizen->update(['Citizen_Sex' => 'Male']);
        $this->assertFalse((bool) $citizen->fresh()->Citizen_Pregnant);
        $this->assertFalse((bool) $citizen->fresh()->Citizen_InPills);

        $citizen->update([
            'Citizen_Sex' => 'Female',
            'Citizen_BirthDate' => now()->subYears(15)->subDay()->toDateString(),
            'Citizen_Age' => 15,
            'Citizen_Pregnant' => true,
            'Citizen_InPills' => true,
        ]);
        $this->assertTrue((bool) $citizen->fresh()->Citizen_Pregnant);
        $this->assertFalse((bool) $citizen->fresh()->Citizen_InPills);
    }

    public function test_migration_clears_ineligible_statuses_from_existing_records(): void
    {
        $male = $this->insertCitizen('Male', 20, true, true);
        $minor = $this->insertCitizen('Female', 15, true, true);
        $adult = $this->insertCitizen('Female', 16, true, true);

        $migration = require database_path('migrations/2026_10_27_000001_normalize_citizen_family_planning_eligibility.php');
        $migration->up();

        $this->assertDatabaseHas('citizens', [
            'id' => $male,
            'Citizen_Pregnant' => false,
            'Citizen_InPills' => false,
        ]);
        $this->assertDatabaseHas('citizens', [
            'id' => $minor,
            'Citizen_Pregnant' => true,
            'Citizen_InPills' => false,
        ]);
        $this->assertDatabaseHas('citizens', [
            'id' => $adult,
            'Citizen_Pregnant' => true,
            'Citizen_InPills' => true,
        ]);
    }

    public function test_ecards_hide_family_planning_statuses_for_males_and_pills_for_minors(): void
    {
        foreach (['bhw.ecard', 'citizen.ecard'] as $template) {
            $maleHtml = $this->renderEcard($template, 'Male', 20, true, true);
            $this->assertStringNotContainsString('Pregnancy Status', $maleHtml);
            $this->assertStringNotContainsString('Taking Pills', $maleHtml);

            $minorHtml = $this->renderEcard($template, 'Female', 15, true, true);
            $this->assertStringContainsString('Pregnancy Status', $minorHtml);
            $this->assertStringNotContainsString('Taking Pills', $minorHtml);

            $adultHtml = $this->renderEcard($template, 'Female', 16, true, true);
            $this->assertStringContainsString('Pregnancy Status', $adultHtml);
            $this->assertStringContainsString('Taking Pills', $adultHtml);
        }
    }

    private function createCitizen(string $sex, int $age, bool $pregnant, bool $inPills): citizens
    {
        return citizens::create([
            'Citizen_FName' => 'Test',
            'Citizen_LName' => 'Citizen',
            'Citizen_BirthDate' => now()->subYears($age)->subDay()->toDateString(),
            'Citizen_Age' => $age,
            'Citizen_Sex' => $sex,
            'Citizen_Pregnant' => $pregnant,
            'Citizen_InPills' => $inPills,
        ]);
    }

    private function insertCitizen(string $sex, int $age, bool $pregnant, bool $inPills): int
    {
        return DB::table('citizens')->insertGetId([
            'Citizen_FName' => 'Existing',
            'Citizen_LName' => 'Citizen',
            'Citizen_BirthDate' => now()->subYears($age)->subDay()->toDateString(),
            'Citizen_Age' => $age,
            'Citizen_Sex' => $sex,
            'Citizen_Pregnant' => $pregnant,
            'Citizen_InPills' => $inPills,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function renderEcard(string $template, string $sex, int $age, bool $pregnant, bool $inPills): string
    {
        $citizen = new citizens([
            'Citizen_FName' => 'Test',
            'Citizen_MName' => '',
            'Citizen_LName' => 'Citizen',
            'Citizen_BirthDate' => now()->subYears($age)->subDay()->toDateString(),
            'Citizen_Age' => $age,
            'Citizen_Sex' => $sex,
            'Citizen_Pregnant' => $pregnant,
            'Citizen_InPills' => $inPills,
        ]);
        $citizen->id = 1;
        $citizen->setRelation('family', null);
        $citizen->setRelation('subgroup', null);
        $citizen->setRelation('healthRecords', collect());
        $citizen->setRelation('vaccinations', collect());
        $citizen->setRelation('maintenanceRecords', collect());

        return view($template, [
            'citizen' => $citizen,
            'qrImage' => null,
            'vaccinationSchedulingEnabled' => false,
        ])->render();
    }
}
