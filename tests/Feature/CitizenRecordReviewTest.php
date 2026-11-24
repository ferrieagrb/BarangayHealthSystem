<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use App\Models\citizens;
use App\Models\User;
use App\Services\CitizenRecordReview;
use App\Services\VaccinationEligibility;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class CitizenRecordReviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);
        $this->travelTo(now()->setDate(2026, 11, 24)->startOfDay());

        Schema::create('citizens', function (Blueprint $table): void {
            $table->id();
            foreach (['Citizen_FName', 'Citizen_MName', 'Citizen_LName', 'Citizen_BirthDate', 'Citizen_Sex', 'Citizen_Purok', 'Citizen_ContactNo', 'Citizen_HousingStatus'] as $column) {
                $table->string($column)->nullable();
            }
            $table->integer('Citizen_Age')->nullable();
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->unsignedBigInteger('family_id')->nullable();
            $table->boolean('Citizen_IsDeceased')->default(false);
            $table->boolean('Citizen_Pregnant')->default(false);
            $table->boolean('Citizen_InPills')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->boolean('must_change_password')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
        });
        Schema::create('user_permissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('feature');
            $table->string('access');
        });
        Schema::create('puroks', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->softDeletes();
        });
        Schema::create('purok_subgroups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('purok_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->softDeletes();
        });
        Schema::create('purok_subgroup_user', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('purok_subgroup_id');
        });
        Schema::create('families', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('purok_id')->nullable();
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->softDeletes();
        });
        Schema::create('supplies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('quantity')->default(0);
            $table->softDeletes();
        });
        Schema::create('health_records', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->string('diagnosis')->nullable();
            $table->softDeletes();
        });
    }

    public function test_one_filter_combines_missing_sex_invalid_dates_and_both_duplicate_records(): void
    {
        $expected = [];
        foreach ([null, '', '   '] as $sex) {
            $expected[] = $this->citizen(['Citizen_Sex' => $sex]);
        }
        foreach ([null, '', '0000-00-00', '2023-02-29', '2024-04-31', 'not a date', '2026-11-25'] as $date) {
            $expected[] = $this->citizen(['Citizen_BirthDate' => $date]);
        }
        $expected[] = $this->citizen(['Citizen_FName' => '  Maria   Luisa ', 'Citizen_MName' => 'Santos', 'Citizen_LName' => 'Cruz']);
        $expected[] = $this->citizen(['Citizen_FName' => 'maria luisa', 'Citizen_MName' => ' SANTOS ', 'Citizen_LName' => ' CRUZ ']);
        $this->citizen(['Citizen_FName' => 'Maria Luisa', 'Citizen_MName' => 'Reyes', 'Citizen_LName' => 'Cruz']);
        $this->citizen(['Citizen_BirthDate' => '2024-02-29']);
        $this->citizen(['Citizen_BirthDate' => '2026-11-24']);
        $this->citizen(['Citizen_Sex' => null, 'deleted_at' => now()]);

        $query = app(CitizenRecordReview::class)->apply(citizens::query());
        $this->assertEqualsCanonicalizing($expected, $query->pluck('id')->all());
        $this->assertSame(count($expected), $query->paginate(3)->total());
    }

    public function test_duplicate_detection_respects_the_accessible_set_and_ignores_archived_records(): void
    {
        $this->citizen(['Citizen_FName' => 'Shared', 'Citizen_Purok' => 'Purok 1']);
        $this->citizen(['Citizen_FName' => 'Shared', 'Citizen_Purok' => 'Purok 2']);
        $this->citizen(['Citizen_FName' => 'Archived pair', 'Citizen_Purok' => 'Purok 1']);
        $this->citizen(['Citizen_FName' => 'Archived pair', 'Citizen_Purok' => 'Purok 1', 'deleted_at' => now()]);
        $missingSex = $this->citizen(['Citizen_Sex' => null, 'Citizen_Purok' => 'Purok 1']);
        $query = citizens::where('Citizen_Purok', 'Purok 1');

        $this->assertSame([$missingSex], app(CitizenRecordReview::class)->apply($query)->pluck('id')->all());
    }

    public function test_invalid_birthdates_do_not_crash_age_display_or_roll_over_to_another_day(): void
    {
        foreach (['not a date', '2023-02-29', '0000-00-00', '2026-11-25'] as $date) {
            $citizen = new citizens(['Citizen_BirthDate' => $date, 'Citizen_Age' => 30]);
            $this->assertNull($citizen->validBirthDate());
            $this->assertSame(30, $citizen->ageForEligibility());
        }
        $this->assertSame(0, (new citizens(['Citizen_BirthDate' => '2026-11-24']))->ageForEligibility());
    }

    public function test_both_lists_keep_the_review_filter_and_combine_it_with_search(): void
    {
        $user = User::create(['name' => 'Review Worker', 'role' => 'bhw']);
        DB::table('user_permissions')->insert(['user_id' => $user->id, 'feature' => 'view_all_citizens', 'access' => 'read']);
        $flagged = $this->citizen(['Citizen_FName' => 'Review', 'Citizen_BirthDate' => 'not a date']);
        $this->citizen(['Citizen_FName' => 'Review', 'Citizen_LName' => 'Other']);
        $this->citizen(['Citizen_FName' => 'Another', 'Citizen_Sex' => null]);
        $this->actingAs($user);

        foreach (['citizenlist', 'healthrecord'] as $route) {
            $response = $this->get(route($route, ['filter_needs_review' => 1, 'search' => 'Review']));
            $response->assertOk()->assertSee('Missing sex, invalid/missing birthdate, or duplicate names');
            if ($route === 'citizenlist') {
                $response->assertSee('Records needing review');
            }
            $this->assertSame([$flagged], $response->viewData('citizens')->pluck('id')->all());
            $dom = new \DOMDocument;
            @$dom->loadHTML($response->getContent());
            $xpath = new \DOMXPath($dom);
            $this->assertSame(1, $xpath->query('//input[@type="checkbox" and @name="filter_needs_review" and @checked]')->length);
            $this->assertSame(1, $xpath->query('//input[@type="hidden" and @name="filter_needs_review" and @value="1"]')->length);
        }
    }

    public function test_flagged_records_with_bad_dates_can_still_open_for_correction(): void
    {
        foreach (['vaccination_records', 'vaccination_schedules'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('citizen_id');
                $table->string('status')->nullable();
                $table->softDeletes();
            });
        }
        $this->actingAs(User::create(['name' => 'Review Worker', 'role' => 'bhw']));
        $citizen = new citizens(['Citizen_FName' => 'Review', 'Citizen_LName' => 'Patient', 'Citizen_BirthDate' => 'not a date']);
        $citizen->id = 1;
        foreach (['healthRecords', 'vaccinations', 'vaccinationSchedules', 'medications', 'maintenanceRecords'] as $relation) {
            $citizen->setRelation($relation, collect());
        }
        $citizen->setRelation('family', null)->setRelation('subgroup', null);
        $options = app(VaccinationEligibility::class)->optionsFor($citizen);
        foreach ($options as $option) {
            $this->assertFalse($option['eligible']);
        }

        foreach (['bhw.citizendetails', 'bhw.citizen_show'] as $view) {
            $html = view($view, [
                'citizen' => $citizen, 'canEdit' => false, 'families' => collect(), 'puroks' => collect(),
                'availableMedicines' => new LengthAwarePaginator([], 0, 10),
                'vaccinationSchedulingEnabled' => false, 'vaccinationEligibilityOptions' => $options,
                'errors' => new ViewErrorBag,
            ])->render();
            $this->assertStringContainsString('data-citizen-birth-date=""', $html);
            $this->assertStringContainsString('name="Citizen_BirthDate"', $html);
        }
    }

    private function citizen(array $attributes = []): int
    {
        static $number = 0;

        return DB::table('citizens')->insertGetId($attributes + [
            'Citizen_FName' => 'Citizen '.++$number, 'Citizen_MName' => null, 'Citizen_LName' => 'Test',
            'Citizen_BirthDate' => '1990-06-15', 'Citizen_Age' => 36, 'Citizen_Sex' => 'Female',
            'Citizen_Purok' => 'Purok 1', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
