<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use App\Models\citizens;
use App\Models\Event;
use App\Models\HealthRecord;
use App\Models\MaintenanceRecord;
use App\Models\MedicationRecord;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Models\Referral;
use App\Models\Supply;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\Vaccination;
use App\Models\VaccinationRecord;
use App\Models\VaccinationSchedule;
use App\Services\BhwCitizenAccess;
use App\Services\BhwDiseaseMonitoringService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Tests\TestCase;

class BhwDiseaseMonitoringTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['purok_subgroup_user', 'referrals', 'events', 'vaccination_schedules', 'vaccinations', 'medication_records', 'maintenance_records', 'health_records', 'vaccination_records', 'supplies', 'citizens', 'families', 'purok_subgroups', 'puroks', 'user_permissions', 'users'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
            $table->softDeletes();
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
            $table->string('family_name')->nullable();
            $table->unsignedBigInteger('purok_id')->nullable();
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->string('Citizen_FName');
            $table->string('Citizen_MName')->nullable();
            $table->string('Citizen_LName');
            $table->date('Citizen_BirthDate');
            $table->string('Citizen_ContactNo');
            $table->integer('Citizen_Age');
            $table->string('Citizen_Purok')->nullable();
            $table->string('Citizen_Sex')->nullable();
            $table->boolean('Citizen_Pregnant')->default(false);
            $table->boolean('Citizen_InPills')->default(false);
            $table->boolean('Citizen_IsDeceased')->default(false);
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->unsignedBigInteger('family_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->text('diagnosis');
            $table->date('record_date')->nullable();
            $table->text('comments')->nullable();
            $table->string('severity')->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->decimal('height', 6, 2)->nullable();
            $table->decimal('bmi', 5, 1)->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->string('blood_pressure', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('vaccination_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->string('vaccine_name')->nullable();
            $table->string('dose_number')->nullable();
            $table->date('date_administered')->nullable();
            $table->string('administered_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->string('vaccine_name');
            $table->date('date_administered');
            $table->text('notes')->nullable();
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
        Schema::create('medication_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->string('medicine_name');
            $table->string('dosage');
            $table->integer('quantity_dispensed');
            $table->date('date_dispensed');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->string('medicine_name');
            $table->unsignedInteger('quantity_dispensed')->default(1);
            $table->string('dosage');
            $table->string('frequency');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('min_stock')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->dateTime('start');
            $table->string('time')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->date('date_of_referral');
            $table->string('name');
            $table->integer('age');
            $table->string('gender');
            $table->text('address');
            $table->text('requests_for');
            $table->text('vital_signs')->nullable();
            $table->text('treatment_given')->nullable();
            $table->text('medication_given')->nullable();
            $table->text('self_medication')->nullable();
            $table->text('maintenance_schedule')->nullable();
            $table->string('referred_by');
            $table->string('status')->default('approved');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
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
            $citizenAccess->scopeCitizens(citizens::query(), $bhw)->pluck('id')->sort()->values()->all()
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
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);

        $bhw = $this->createBhw();
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports_health', 'access' => 'read']);

        $purok = Purok::create(['name' => 'Purok 3', 'latitude' => 14.08, 'longitude' => 120.86]);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => 'Group D']);
        $bhw->assignedSubgroups()->attach($subgroup->id);
        citizens::create([
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

    public function test_custom_report_can_be_downloaded_as_a_filtered_excel_workbook(): void
    {
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);

        $bhw = $this->createBhw();
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports_demographics', 'access' => 'read']);

        $purok = Purok::create(['name' => 'Purok 4', 'latitude' => 14.09, 'longitude' => 120.87]);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => 'Group E', 'user_id' => $bhw->id]);
        $bhw->assignedSubgroups()->attach($subgroup->id);
        $this->createCitizen($purok, $subgroup, 'Excel Export');
        $youngerCitizen = $this->createCitizen($purok, $subgroup, 'Excluded by Filter');
        $youngerCitizen->update(['Citizen_Age' => 10]);

        $response = $this->actingAs($bhw)->get(route('bhw.reports', [
            'report_type' => 'age_by_purok',
            'age_min' => 30,
            'export_format' => 'xlsx',
        ]));

        $response->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('content-disposition');
        $content = $response->streamedContent();
        $this->assertStringStartsWith('PK', $content);
        $this->assertStringContainsString('age-bracket-breakdown-per-purok-'.now()->format('Y-m-d').'.xlsx', $response->headers->get('content-disposition'));

        $workbookPath = tempnam(sys_get_temp_dir(), 'bhw-report-');
        $this->assertNotFalse($workbookPath);
        file_put_contents($workbookPath, $content);

        try {
            $sheet = IOFactory::load($workbookPath)->getActiveSheet();
            $this->assertSame('BARANGAY HEALTH MANAGEMENT SYSTEM', $sheet->getCell('B1')->getValue());
            $this->assertSame('BARANGAY AMUYONG | BHEAMS', $sheet->getCell('B2')->getValue());
            $this->assertSame('Barangay Health Management System - Age-Bracket Breakdown per Purok Report', $sheet->getCell('A3')->getValue());
            $this->assertSame('Purok', $sheet->getCell('A7')->getValue());
            $this->assertEquals(0, $sheet->getCell('D8')->getValue());
            $this->assertEquals(1, $sheet->getCell('F8')->getValue());
            $this->assertSame(DataType::TYPE_NUMERIC, $sheet->getCell('D8')->getDataType());
            $this->assertSame('0', $sheet->getCell('D8')->getStyle()->getNumberFormat()->getFormatCode());
            $this->assertSame('A8', $sheet->getFreezePane());
            $this->assertSame('A7:H9', $sheet->getAutoFilter()->getRange());
            $this->assertSame('Total', $sheet->getCell('A9')->getValue());
            $this->assertCount(1, $sheet->getDrawingCollection());
            $this->assertSame('Barangay Amuyong BHEAMS logo', $sheet->getDrawingCollection()[0]->getDescription());
            $this->assertSame('A1', $sheet->getDrawingCollection()[0]->getCoordinates());
            $this->assertSame('landscape', $sheet->getPageSetup()->getOrientation());
            $this->assertSame('A1:H9', $sheet->getPageSetup()->getPrintArea());
        } finally {
            unlink($workbookPath);
        }
    }

    public function test_all_report_sections_render_and_export_the_same_filtered_data(): void
    {
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);

        $bhw = $this->createBhw();
        foreach (['reports', 'reports_demographics', 'reports_health', 'reports_operations', 'view_all_citizens'] as $feature) {
            UserPermission::create(['user_id' => $bhw->id, 'feature' => $feature, 'access' => 'read']);
        }

        $purok = Purok::create(['name' => 'Purok 5', 'latitude' => 14.10, 'longitude' => 120.88]);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => 'Group F', 'user_id' => $bhw->id]);
        $bhw->assignedSubgroups()->attach($subgroup->id);

        $fourYearOld = $this->createCitizen($purok, $subgroup, 'Young One');
        $fourYearOld->update(['Citizen_Age' => 4, 'Citizen_Sex' => 'Female', 'Citizen_Pregnant' => true]);
        $fiveYearOld = $this->createCitizen($purok, $subgroup, 'Young Two');
        $fiveYearOld->update(['Citizen_Age' => 5, 'Citizen_Sex' => 'Male']);
        $sixYearOld = $this->createCitizen($purok, $subgroup, 'Older Child');
        $sixYearOld->update(['Citizen_Age' => 6, 'Citizen_Sex' => 'Female']);

        HealthRecord::create([
            'citizen_id' => $fourYearOld->id,
            'diagnosis' => 'Fever',
            'record_date' => today()->toDateString(),
            'comments' => 'Follow-up needed',
        ]);
        VaccinationRecord::create([
            'citizen_id' => $fourYearOld->id,
            'vaccine_name' => 'B.C.G.',
            'dose_number' => '1',
            'date_administered' => today()->toDateString(),
            'administered_by' => 'BHW',
        ]);
        VaccinationRecord::create([
            'citizen_id' => $fiveYearOld->id,
            'vaccine_name' => 'BCG',
            'dose_number' => '1',
            'date_administered' => today()->subDay()->toDateString(),
            'administered_by' => 'BHW',
        ]);
        Supply::create(['name' => 'Bandage', 'category' => 'Medical', 'quantity' => 12, 'min_stock' => 3]);
        Event::create(['title' => 'Health Day', 'start' => now()->startOfDay(), 'description' => 'Community clinic', 'user_id' => $bhw->id]);
        Referral::create([
            'date_of_referral' => today()->toDateString(),
            'name' => 'Owned Referral',
            'age' => 4,
            'gender' => 'Female',
            'address' => 'Purok 5, Group F',
            'requests_for' => 'Medical assessment',
            'referred_by' => $bhw->name,
            'status' => 'approved',
            'user_id' => $bhw->id,
        ]);
        Referral::create([
            'date_of_referral' => today()->toDateString(),
            'name' => 'Other BHW Referral',
            'age' => 4,
            'gender' => 'Female',
            'address' => 'Purok 5, Group F',
            'requests_for' => 'Medical assessment',
            'referred_by' => 'Other BHW',
            'status' => 'approved',
            'user_id' => $this->createBhw()->id,
        ]);

        $sections = [
            'citizens', 'health_records', 'supplies', 'events', 'referrals', 'age_by_purok',
            'immunization', 'sex_by_purok', 'pregnancy_by_purok', 'diagnosis_summary', 'diagnosis_by_purok',
        ];
        $filters = [
            'sections' => $sections,
            'purok' => $purok->id,
            'subgroup' => $subgroup->id,
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
            'diagnosis' => 'Fever',
            'vaccine_type' => 'BCG',
            'include_graphics' => 1,
        ];

        $previewResponse = $this->actingAs($bhw)->get(route('bhw.reports', $filters));
        $previewResponse
            ->assertOk()
            ->assertSee('Health Records')
            ->assertDontSee('Follow-up needed')
            ->assertSee('Sex')
            ->assertSee('Female')
            ->assertSee('data-no-motion', false)
            ->assertSee('overflow: visible !important', false)
            ->assertSee('Male and Female Counts by Purok')
            ->assertSee('class="report-graphic"', false)
            ->assertSee('reportSupplySearch', false)
            ->assertSee('data-no-loader="true"', false)
            ->assertSee('id="reportExportFeedback"', false);

        $response = $this->actingAs($bhw)->get(route('bhw.reports', $filters + ['export_format' => 'xlsx']));
        $response->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $workbookPath = tempnam(sys_get_temp_dir(), 'bhw-all-reports-');
        $this->assertNotFalse($workbookPath);
        file_put_contents($workbookPath, $response->streamedContent());

        try {
            $workbook = IOFactory::load($workbookPath);
            $this->assertCount(13, $workbook->getAllSheets());
            $citizenSheet = $workbook->getSheetByName('Citizen Profiles');
            $this->assertSame('Sex', $citizenSheet->getCell('C7')->getValue());
            $this->assertSame('Female', $citizenSheet->getCell('C8')->getValue());
            $this->assertSame('Male', $citizenSheet->getCell('C9')->getValue());
            $this->assertSame('Fever', $workbook->getSheetByName('Health Records')->getCell('E8')->getValue());
            $this->assertSame('Owned Referral', $workbook->getSheetByName('Referrals')->getCell('B8')->getValue());
            $this->assertSame('Total', $workbook->getSheetByName('Male and Female Counts by Purok')->getCell('A9')->getValue());
            $this->assertEquals(1, $workbook->getSheetByName('Male and Female Counts by Purok')->getCell('B9')->getValue());
            $immunizationSheet = $workbook->getSheetByName('Immunization and Vaccination Co');
            $this->assertNotNull($immunizationSheet, implode(', ', array_map(fn ($sheet) => $sheet->getTitle(), $workbook->getAllSheets())));
            $this->assertEquals(2, $immunizationSheet->getCell('A8')->getValue());
            $this->assertEquals(1, $immunizationSheet->getCell('B8')->getValue());
            $this->assertEquals(1, $immunizationSheet->getCell('C8')->getValue());
            $vaccinationSummarySheet = $workbook->getSheetByName('Vaccination Summary');
            $this->assertSame('Dose number(s)', $vaccinationSummarySheet->getCell('H7')->getValue());
            $this->assertSame('C-0001', $vaccinationSummarySheet->getCell('A8')->getValue());
            $this->assertSame('Young One Test', $vaccinationSummarySheet->getCell('B8')->getValue());
            $this->assertSame('1 (Administered)', $vaccinationSummarySheet->getCell('H8')->getValue());
            $this->assertEquals(1, $vaccinationSummarySheet->getCell('I8')->getValue());
            $this->assertSame('0', $vaccinationSummarySheet->getCell('I8')->getStyle()->getNumberFormat()->getFormatCode());
            $this->assertSame('Administered', $vaccinationSummarySheet->getCell('K8')->getValue());
            $this->assertSame('Total', $workbook->getSheetByName('Age-Bracket Breakdown per Purok')->getCell('A9')->getValue());
        } finally {
            unlink($workbookPath);
        }

        $this->actingAs($bhw)
            ->get(route('bhw.reports', ['report_type' => 'age_by_purok', 'purok' => $purok->id]))
            ->assertOk()
            ->assertDontSee('class="report-graphic"', false);
    }

    public function test_report_filters_validate_ranges_and_ignore_inapplicable_sex_filter(): void
    {
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);

        $bhw = $this->createBhw();
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports_demographics', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports_health', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'view_all_citizens', 'access' => 'read']);
        $purok = Purok::create(['name' => 'Purok 6']);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => 'Group G']);
        $this->createCitizen($purok, $subgroup, 'Male')->update(['Citizen_Sex' => 'Male']);
        $this->createCitizen($purok, $subgroup, 'Female')->update(['Citizen_Sex' => 'Female']);

        $this->actingAs($bhw)
            ->get(route('bhw.reports', ['report_type' => 'sex_by_purok', 'sex' => 'Male']))
            ->assertOk()
            ->assertSee('<td>1</td><td>1</td>', false)
            ->assertSee('<tr><th>Total</th><th>1</th><th>1</th><th>0</th></tr>', false);

        $this->actingAs($bhw)
            ->get(route('bhw.reports', ['report_type' => 'health_records', 'start_date' => '2026-10-28', 'end_date' => '2026-10-27']))
            ->assertRedirect()
            ->assertSessionHasErrors('end_date');

        $idSearchResponse = $this->actingAs($bhw)->get(route('bhw.reports', [
            'report_type' => 'citizens',
            'search' => 'C-0001',
            'export_format' => 'xlsx',
        ]));
        $emptySearchResponse = $this->actingAs($bhw)->get(route('bhw.reports', [
            'report_type' => 'citizens',
            'search' => 'No matching citizen',
            'export_format' => 'xlsx',
        ]));

        foreach ([
            [$idSearchResponse, 'Male Test (ID: C-0001)', 8],
            [$emptySearchResponse, 'Name', 7],
        ] as [$response, $expectedCellValue, $expectedLastRow]) {
            $response->assertOk();
            $workbookPath = tempnam(sys_get_temp_dir(), 'bhw-search-report-');
            $this->assertNotFalse($workbookPath);
            file_put_contents($workbookPath, $response->streamedContent());

            try {
                $sheet = IOFactory::load($workbookPath)->getActiveSheet();
                $this->assertSame($expectedCellValue, $sheet->getCell($expectedLastRow === 7 ? 'A7' : 'A8')->getValue());
                $this->assertSame($expectedLastRow, $sheet->getHighestDataRow());
            } finally {
                unlink($workbookPath);
            }
        }
    }

    public function test_health_history_reports_apply_citizen_name_and_id_search(): void
    {
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);

        $bhw = $this->createBhw();
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports_health', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'view_all_citizens', 'access' => 'read']);

        $purok = Purok::create(['name' => 'Purok Search']);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => 'Search Group']);
        $matchingCitizen = $this->createCitizen($purok, $subgroup, 'Matching');
        $matchingCitizen->update(['Citizen_MName' => 'Unique']);
        $otherCitizen = $this->createCitizen($purok, $subgroup, 'Other');
        $this->createDiagnosis($matchingCitizen->id, 'Matching diagnosis', today()->toDateString());
        $this->createDiagnosis($otherCitizen->id, 'Other diagnosis', today()->toDateString());

        $this->actingAs($bhw)
            ->get(route('bhw.reports', ['report_type' => 'health_records', 'search' => 'Unique']))
            ->assertOk()
            ->assertSee('Matching Unique Test')
            ->assertDontSee('Other Test');

        $this->actingAs($bhw)
            ->get(route('bhw.reports', ['report_type' => 'health_records', 'search' => 'C-0002']))
            ->assertOk()
            ->assertSee('Other Test')
            ->assertDontSee('Matching Unique Test');
    }

    public function test_all_puroks_citizen_profiles_match_in_preview_and_excel(): void
    {
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);

        $bhw = $this->createBhw();
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports_demographics', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'view_all_citizens', 'access' => 'read']);

        $firstPurok = Purok::create(['name' => 'Purok 1']);
        $secondPurok = Purok::create(['name' => 'Purok 2']);
        $firstSubgroup = PurokSubgroup::create(['purok_id' => $firstPurok->id, 'name' => 'Group A']);
        $secondSubgroup = PurokSubgroup::create(['purok_id' => $secondPurok->id, 'name' => 'Group B']);
        $female = $this->createCitizen($firstPurok, $firstSubgroup, 'All Purok Female');
        $female->update(['Citizen_Sex' => 'Female']);
        $male = $this->createCitizen($secondPurok, $secondSubgroup, 'All Purok Male');
        $male->update(['Citizen_Sex' => 'Male']);
        $unrecorded = $this->createCitizen($secondPurok, $secondSubgroup, 'All Purok Unrecorded');

        $filters = ['report_type' => 'citizens', 'purok' => 'all', 'subgroup' => 'all'];
        $this->actingAs($bhw)
            ->get(route('bhw.reports', $filters))
            ->assertOk()
            ->assertSee('All Purok Female')
            ->assertSee('All Purok Male')
            ->assertSee('All Purok Unrecorded')
            ->assertSee('Female')
            ->assertSee('Male')
            ->assertSee('Not recorded')
            ->assertSee('data-no-motion', false);

        $response = $this->actingAs($bhw)->get(route('bhw.reports', $filters + ['export_format' => 'xlsx']));
        $response->assertOk();
        $workbookPath = tempnam(sys_get_temp_dir(), 'bhw-all-puroks-');
        $this->assertNotFalse($workbookPath);
        file_put_contents($workbookPath, $response->streamedContent());

        try {
            $sheet = IOFactory::load($workbookPath)->getActiveSheet();
            $this->assertSame('Name', $sheet->getCell('A7')->getValue());
            $this->assertSame('Age', $sheet->getCell('B7')->getValue());
            $this->assertSame('Sex', $sheet->getCell('C7')->getValue());
            $this->assertSame('Filters: Purok: All Puroks | Subgroup: All subgroups', $sheet->getCell('A5')->getValue());

            $sexByCitizen = collect($sheet->rangeToArray('A8:C'.$sheet->getHighestDataRow()))
                ->mapWithKeys(fn ($row) => [$row[0] => $row[2]]);
            $this->assertSame('Female', $sexByCitizen->first(fn ($sex, $name) => str_contains($name, 'All Purok Female')));
            $this->assertSame('Male', $sexByCitizen->first(fn ($sex, $name) => str_contains($name, 'All Purok Male')));
            $this->assertSame('Not recorded', $sexByCitizen->first(fn ($sex, $name) => str_contains($name, 'All Purok Unrecorded')));
        } finally {
            unlink($workbookPath);
        }
    }

    public function test_health_and_immunization_reports_preserve_individual_history_and_schedule_status(): void
    {
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);

        $bhw = $this->createBhw();
        foreach (['reports', 'reports_health', 'view_all_citizens'] as $feature) {
            UserPermission::create(['user_id' => $bhw->id, 'feature' => $feature, 'access' => 'read']);
        }

        $purok = Purok::create(['name' => 'Purok 8']);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => 'Group I']);
        $citizen = $this->createCitizen($purok, $subgroup, 'Detailed Health');
        $citizen->update(['Citizen_MName' => 'Report', 'Citizen_Sex' => 'Female']);

        HealthRecord::create([
            'citizen_id' => $citizen->id,
            'diagnosis' => 'Fever',
            'record_date' => today()->toDateString(),
            'comments' => 'Return for follow-up',
            'severity' => 'Moderate',
            'blood_pressure' => '120/80',
            'temperature' => 38.2,
            'weight' => 62.5,
            'height' => 160,
            'bmi' => 24.4,
        ]);
        HealthRecord::create([
            'citizen_id' => $citizen->id,
            'diagnosis' => 'Cough',
            'record_date' => today()->subDay()->toDateString(),
        ]);
        HealthRecord::create([
            'citizen_id' => $citizen->id,
            'diagnosis' => 'Outside date range',
            'record_date' => today()->subYear()->toDateString(),
        ]);
        VaccinationRecord::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 'Dose 1',
            'date_administered' => today()->toDateString(),
            'administered_by' => 'Test BHW',
        ]);
        VaccinationRecord::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 'Dose 1',
            'date_administered' => today()->toDateString(),
            'administered_by' => 'Test BHW',
        ]);
        VaccinationRecord::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 'Dose 2',
            'date_administered' => today()->toDateString(),
            'administered_by' => 'Test BHW',
        ]);
        VaccinationRecord::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'Polio',
            'dose_number' => 'Dose 1',
            'date_administered' => today()->subYear()->toDateString(),
            'administered_by' => 'Test BHW',
        ]);
        VaccinationRecord::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'Hepatitis B',
            'dose_number' => 'Dose 1',
            'date_administered' => today()->toDateString(),
            'administered_by' => 'Test BHW',
        ]);
        Vaccination::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'B.C.G.',
            'date_administered' => today()->toDateString(),
            'notes' => 'Duplicate legacy entry',
        ]);
        VaccinationSchedule::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 'Dose 2',
            'scheduled_date' => today()->toDateString(),
            'status' => 'completed',
        ]);
        Vaccination::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'MMR',
            'date_administered' => today()->toDateString(),
            'notes' => 'Legacy record note',
        ]);
        VaccinationSchedule::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'BCG',
            'dose_number' => 'Dose 3',
            'scheduled_date' => today()->addDays(3)->toDateString(),
            'status' => 'scheduled',
        ]);
        VaccinationSchedule::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'MMR',
            'dose_number' => 'Dose 2',
            'scheduled_date' => today()->subDay()->toDateString(),
            'status' => 'scheduled',
        ]);
        VaccinationSchedule::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'Pentavalent',
            'dose_number' => 'Dose 3',
            'scheduled_date' => today()->addDay()->toDateString(),
            'notes' => 'Return tomorrow',
            'status' => 'scheduled',
        ]);
        VaccinationSchedule::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'Rotavirus',
            'dose_number' => 'Dose 1',
            'scheduled_date' => today()->toDateString(),
            'status' => 'completed',
        ]);
        MedicationRecord::create([
            'citizen_id' => $citizen->id,
            'medicine_name' => 'Paracetamol',
            'dosage' => '500 mg',
            'quantity_dispensed' => 10,
            'date_dispensed' => today()->toDateString(),
        ]);
        MaintenanceRecord::create([
            'citizen_id' => $citizen->id,
            'medicine_name' => 'Maintenance medicine',
            'quantity_dispensed' => 30,
            'dosage' => '1 tablet',
            'frequency' => 'Daily',
            'start_date' => today()->toDateString(),
            'notes' => 'Continue as directed',
        ]);

        $filters = [
            'sections' => ['health_records', 'immunization'],
            'purok' => $purok->id,
            'subgroup' => $subgroup->id,
            'start_date' => today()->subDays(2)->toDateString(),
            'end_date' => today()->addDay()->toDateString(),
        ];
        $previewResponse = $this->actingAs($bhw)->get(route('bhw.reports', $filters));
        $previewResponse->assertOk()
            ->assertSee('Detailed Health Report Test')
            ->assertSee('Fever')
            ->assertSee('Cough')
            ->assertDontSee('<td>Outside date range</td>', false)
            ->assertDontSee('<td>Polio</td>', false)
            ->assertSee('120/80')
            ->assertSee('24.4')
            ->assertDontSee('Return for follow-up')
            ->assertSee('Pentavalent')
            ->assertSee('Rotavirus')
            ->assertSee('Completed')
            ->assertSee('Scheduled')
            ->assertDontSee('Overdue')
            ->assertSee('Total administered doses')
            ->assertSee('Next dose due date')
            ->assertSee('Dose 1 (Administered)')
            ->assertSee('Dose 3 (Scheduled)')
            ->assertSee('C-0001')
            ->assertSee('Group I')
            ->assertSee('Paracetamol')
            ->assertSee('Maintenance medicine')
            ->assertDontSee('<th>Notes</th>', false)
            ->assertDontSee('Continue as directed');
        $this->assertSame(1, substr_count($previewResponse->getContent(), '<td>BCG</td>'));

        $response = $this->actingAs($bhw)->get(route('bhw.reports', $filters + ['export_format' => 'xlsx']));
        $response->assertOk();
        $workbookPath = tempnam(sys_get_temp_dir(), 'bhw-detailed-health-');
        $this->assertNotFalse($workbookPath);
        file_put_contents($workbookPath, $response->streamedContent());

        try {
            $workbook = IOFactory::load($workbookPath);
            $healthSheet = $workbook->getSheetByName('Health Records');
            $this->assertSame('Citizen name', $healthSheet->getCell('A7')->getValue());
            $this->assertSame('Diagnosis', $healthSheet->getCell('E7')->getValue());
            $this->assertSame('Temperature °C', $healthSheet->getCell('K7')->getValue());
            $this->assertSame('Detailed Health Report Test', $healthSheet->getCell('A8')->getValue());
            $this->assertEquals(36, $healthSheet->getCell('B8')->getValue());
            $this->assertSame('0', $healthSheet->getCell('B8')->getStyle()->getNumberFormat()->getFormatCode());
            $this->assertSame('Female', $healthSheet->getCell('C8')->getValue());
            $this->assertSame('Purok 8', $healthSheet->getCell('D8')->getValue());
            $this->assertSame('Fever', $healthSheet->getCell('E8')->getValue());
            $this->assertSame(today()->format('Y-m-d'), Date::excelToDateTimeObject($healthSheet->getCell('F8')->getValue())->format('Y-m-d'));
            $this->assertSame('120/80', $healthSheet->getCell('G8')->getValue());
            $this->assertSame(62.5, (float) $healthSheet->getCell('H8')->getValue());
            $this->assertSame(160.0, (float) $healthSheet->getCell('I8')->getValue());
            $this->assertSame(24.4, (float) $healthSheet->getCell('J8')->getValue());
            $this->assertSame(38.2, (float) $healthSheet->getCell('K8')->getValue());
            $this->assertSame(9, $healthSheet->getHighestDataRow());
            $this->assertSame('Cough', $healthSheet->getCell('E9')->getValue());

            $immunizationSheet = $workbook->getSheetByName('Vaccination Summary');
            $this->assertSame('Citizen ID', $immunizationSheet->getCell('A7')->getValue());
            $this->assertSame('Age', $immunizationSheet->getCell('C7')->getValue());
            $this->assertSame('Subgroup', $immunizationSheet->getCell('F7')->getValue());
            $this->assertSame('Dose number(s)', $immunizationSheet->getCell('H7')->getValue());
            $this->assertSame('Total administered doses', $immunizationSheet->getCell('I7')->getValue());
            $this->assertSame('C-0001', $immunizationSheet->getCell('A8')->getValue());
            $this->assertSame(36, (int) $immunizationSheet->getCell('C8')->getValue());
            $this->assertSame('Female', $immunizationSheet->getCell('D8')->getValue());
            $this->assertSame('Purok 8', $immunizationSheet->getCell('E8')->getValue());
            $this->assertSame('Group I', $immunizationSheet->getCell('F8')->getValue());
            $this->assertSame('BCG', $immunizationSheet->getCell('G8')->getValue());
            $this->assertSame('Dose 1 (Administered); Dose 2 (Administered); Dose 3 (Scheduled)', $immunizationSheet->getCell('H8')->getValue());
            $this->assertEquals(2, $immunizationSheet->getCell('I8')->getValue());
            $this->assertSame('Administered; Scheduled', $immunizationSheet->getCell('K8')->getValue());
            $this->assertSame(today()->format('Y-m-d'), Date::excelToDateTimeObject($immunizationSheet->getCell('J8')->getValue())->format('Y-m-d'));
            $this->assertSame(today()->addDays(3)->format('Y-m-d'), Date::excelToDateTimeObject($immunizationSheet->getCell('L8')->getValue())->format('Y-m-d'));
            $this->assertSame('mmm d, yyyy', $immunizationSheet->getCell('J8')->getStyle()->getNumberFormat()->getFormatCode());
            $this->assertSame('Hepatitis B', $immunizationSheet->getCell('G9')->getValue());
            $this->assertSame('Administered', $immunizationSheet->getCell('K9')->getValue());
            $this->assertSame('MMR', $immunizationSheet->getCell('G10')->getValue());
            $this->assertEquals(1, $immunizationSheet->getCell('I10')->getValue());
            $this->assertSame('Not recorded (Administered); Dose 2 (Scheduled)', $immunizationSheet->getCell('H10')->getValue());
            $this->assertSame('Administered; Scheduled', $immunizationSheet->getCell('K10')->getValue());
            $this->assertSame(today()->subDay()->format('Y-m-d'), Date::excelToDateTimeObject($immunizationSheet->getCell('L10')->getValue())->format('Y-m-d'));
            $this->assertSame('Pentavalent', $immunizationSheet->getCell('G11')->getValue());
            $this->assertEquals(0, $immunizationSheet->getCell('I11')->getValue());
            $this->assertSame('Scheduled', $immunizationSheet->getCell('K11')->getValue());
            $this->assertNotNull($immunizationSheet->getCell('L11')->getValue());
            $this->assertSame('Rotavirus', $immunizationSheet->getCell('G12')->getValue());
            $this->assertEquals(0, $immunizationSheet->getCell('I12')->getValue());
            $this->assertSame('Dose 1 (Completed)', $immunizationSheet->getCell('H12')->getValue());
            $this->assertSame('Completed', $immunizationSheet->getCell('K12')->getValue());
            $this->assertNull($immunizationSheet->getCell('J12')->getValue());
            $this->assertNull($immunizationSheet->getCell('L12')->getValue());
            $medicationSheet = $workbook->getSheetByName('Medication History');
            $this->assertSame('Medication History', $medicationSheet->getTitle());
            $this->assertSame('Recorded by', $medicationSheet->getCell('I7')->getValue());
            $this->assertSame('Source', $medicationSheet->getCell('J7')->getValue());
            $this->assertSame('J', $medicationSheet->getHighestDataColumn());
            $this->assertNotContains('Notes', $medicationSheet->rangeToArray('A7:J7')[0]);
            $this->assertStringNotContainsString('Continue as directed', json_encode($medicationSheet->toArray()));
        } finally {
            unlink($workbookPath);
        }
    }

    public function test_diagnosis_summary_also_includes_individual_history_in_preview_and_excel(): void
    {
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);

        $bhw = $this->createBhw();
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'reports_health', 'access' => 'read']);
        UserPermission::create(['user_id' => $bhw->id, 'feature' => 'view_all_citizens', 'access' => 'read']);
        $purok = Purok::create(['name' => 'Purok 9']);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => 'Group J']);
        $citizen = $this->createCitizen($purok, $subgroup, 'Diagnosis');
        HealthRecord::create([
            'citizen_id' => $citizen->id,
            'diagnosis' => 'Asthma review',
            'record_date' => today()->toDateString(),
            'comments' => 'Monitor symptoms',
        ]);

        $filters = ['report_type' => 'diagnosis_summary', 'purok' => $purok->id, 'subgroup' => $subgroup->id];
        $this->actingAs($bhw)
            ->get(route('bhw.reports', $filters))
            ->assertOk()
            ->assertSee('Diagnosis Summary')
            ->assertSee('Diagnosis History Details')
            ->assertSee('Asthma review')
            ->assertSee('Diagnosis Test')
            ->assertDontSee('Monitor symptoms');

        $response = $this->actingAs($bhw)->get(route('bhw.reports', $filters + ['export_format' => 'xlsx']));
        $response->assertOk();
        $workbookPath = tempnam(sys_get_temp_dir(), 'bhw-diagnosis-history-');
        $this->assertNotFalse($workbookPath);
        file_put_contents($workbookPath, $response->streamedContent());

        try {
            $workbook = IOFactory::load($workbookPath);
            $historySheet = $workbook->getSheetByName('Diagnosis History');
            $this->assertNotNull($historySheet);
            $this->assertSame('Asthma review', $historySheet->getCell('E8')->getValue());
            $this->assertSame('Temperature °C', $historySheet->getCell('K7')->getValue());
            $this->assertSame('K', $historySheet->getHighestDataColumn());
            $this->assertSame(1, $historySheet->getHighestDataRow() - 7);
        } finally {
            unlink($workbookPath);
        }
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

    private function createCitizen(Purok $purok, PurokSubgroup $subgroup, string $firstName): citizens
    {
        return citizens::create([
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
