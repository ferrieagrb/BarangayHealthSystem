<?php

namespace Tests\Feature;

use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\User;
use App\Models\VaccinationRecord;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicHealthIdSummaryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('vaccination_records');
        Schema::dropIfExists('health_records');
        Schema::dropIfExists('maintenance_records');
        Schema::dropIfExists('families');
        Schema::dropIfExists('purok_subgroups');
        Schema::dropIfExists('citizen_health_shares');
        Schema::dropIfExists('citizens');
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('users');

        Schema::create('citizens', function (Blueprint $table): void {
            $table->id();
            $table->string('Citizen_FName');
            $table->string('Citizen_MName')->nullable();
            $table->string('Citizen_LName');
            $table->date('Citizen_BirthDate');
            $table->string('Citizen_ContactNo');
            $table->integer('Citizen_Age');
            $table->string('Citizen_Sex')->nullable();
            $table->string('Citizen_Purok')->nullable();
            $table->decimal('Citizen_Height', 5, 2)->nullable();
            $table->decimal('Citizen_Weight', 5, 2)->nullable();
            $table->string('Citizen_HousingStatus')->nullable();
            $table->boolean('Citizen_Pregnant')->nullable();
            $table->boolean('Citizen_InPills')->nullable();
            $table->boolean('Citizen_IsDeceased')->nullable();
            $table->unsignedBigInteger('family_id')->nullable();
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->uuid('qr_token')->unique();
            $table->boolean('public_health_id_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('health_records', function (Blueprint $table): void {
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
            $table->string('blood_pressure')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vaccination_records', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->string('vaccine_name');
            $table->string('dose_number');
            $table->date('date_administered');
            $table->string('administered_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('maintenance_records', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->string('medicine_name');
            $table->unsignedInteger('quantity_dispensed')->nullable();
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('families', function (Blueprint $table): void {
            $table->id();
            $table->string('family_name');
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->timestamps();
        });

        Schema::create('purok_subgroups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('page_views', function (Blueprint $table): void {
            $table->id();
            $table->string('ip_address')->nullable();
            $table->text('url')->nullable();
            $table->string('browser')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('load_time')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('citizen_health_shares', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('citizen_id');
            $table->unsignedBigInteger('granted_by_user_id')->nullable();
            $table->timestamp('granted_at');
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_guest_can_get_the_recorded_health_summary_without_private_identifiers(): void
    {
        $subgroupId = DB::table('purok_subgroups')->insertGetId([
            'name' => 'North Group',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $familyId = DB::table('families')->insertGetId([
            'family_name' => 'Rivera Family',
            'subgroup_id' => $subgroupId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $citizen = citizens::create([
            'Citizen_FName' => 'Jamie',
            'Citizen_MName' => 'Lee',
            'Citizen_LName' => 'Rivera',
            'Citizen_BirthDate' => '1990-04-02',
            'Citizen_ContactNo' => '555-0100',
            'Citizen_Age' => 36,
            'Citizen_Sex' => 'Female',
            'Citizen_Purok' => 'Purok 4',
            'Citizen_Height' => 162.5,
            'Citizen_Weight' => 58.2,
            'Citizen_HousingStatus' => 'Permanent Resident',
            'Citizen_Pregnant' => false,
            'Citizen_InPills' => true,
            'Citizen_IsDeceased' => false,
            'family_id' => $familyId,
            'subgroup_id' => $subgroupId,
            'qr_token' => '2c1f934d-7fb7-43be-a0a1-c76d746cf7a2',
        ]);

        HealthRecord::create([
            'citizen_id' => $citizen->id,
            'diagnosis' => 'Earlier diagnosis',
            'record_date' => '2025-01-10',
        ]);
        HealthRecord::create([
            'citizen_id' => $citizen->id,
            'diagnosis' => 'Latest diagnosis',
            'record_date' => '2026-03-15',
            'bmi' => 22.0,
            'weight' => 58.2,
            'height' => 162.5,
            'temperature' => 36.5,
            'blood_pressure' => '120/80',
            'comments' => 'Recorded follow-up',
        ]);
        VaccinationRecord::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'Vaccine A',
            'dose_number' => 'Dose 2',
            'date_administered' => '2026-02-20',
            'administered_by' => 'Clinic',
        ]);
        DB::table('maintenance_records')->insert([
            'citizen_id' => $citizen->id,
            'medicine_name' => 'Medicine B',
            'quantity_dispensed' => 30,
            'dosage' => '10 mg',
            'frequency' => 'Daily',
            'start_date' => '2026-01-01',
            'end_date' => null,
            'notes' => 'Take with food',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $response = $this->getJson(route('health-id.summary', $citizen->qr_token));

        $response
            ->assertOk()
            ->assertJsonPath('citizen.name', 'Jamie Lee Rivera')
            ->assertJsonPath('citizen.age', 36)
            ->assertJsonPath('citizen.sex', 'Female')
            ->assertJsonPath('citizen.purok', 'Purok 4')
            ->assertJsonPath('citizen.family_name', 'Rivera Family')
            ->assertJsonPath('citizen.subgroup', 'North Group')
            ->assertJsonPath('citizen.height', 162.5)
            ->assertJsonPath('citizen.weight', 58.2)
            ->assertJsonPath('citizen.housing_status', 'Permanent Resident')
            ->assertJsonPath('citizen.pregnant', false)
            ->assertJsonPath('citizen.taking_pills', true)
            ->assertJsonPath('latest_diagnosis.diagnosis', 'Latest diagnosis')
            ->assertJsonPath('latest_diagnosis.date', '2026-03-15')
            ->assertJsonPath('health_records.0.bmi', 22)
            ->assertJsonPath('health_records.0.blood_pressure', '120/80')
            ->assertJsonPath('health_records.0.comments', 'Recorded follow-up')
            ->assertJsonPath('vaccinations.0.vaccine_name', 'Vaccine A')
            ->assertJsonPath('vaccinations.0.dose_number', 'Dose 2')
            ->assertJsonPath('vaccinations.0.date_administered', '2026-02-20')
            ->assertJsonPath('maintenance_medicines.0.medicine_name', 'Medicine B')
            ->assertJsonPath('maintenance_medicines.0.dosage', '10 mg')
            ->assertJsonMissingPath('citizen.id')
            ->assertJsonMissingPath('citizen.family_id')
            ->assertJsonMissingPath('citizen.contact_number')
            ->assertJsonMissingPath('health_records.0.id')
            ->assertJsonMissingPath('vaccinations.0.id')
            ->assertJsonMissingPath('maintenance_medicines.0.id');

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertGuest();
    }

    public function test_citizen_with_no_health_history_receives_empty_record_collections(): void
    {
        $citizen = citizens::create([
            'Citizen_FName' => 'Taylor',
            'Citizen_LName' => 'Morgan',
            'Citizen_BirthDate' => '2000-01-01',
            'Citizen_ContactNo' => '555-0101',
            'Citizen_Age' => 26,
            'qr_token' => '10d20f39-4eb6-4eef-8ccf-04801cd5fd2e',
        ]);
        $this->getJson(route('health-id.summary', $citizen->qr_token))
            ->assertOk()
            ->assertJsonPath('latest_diagnosis', null)
            ->assertJsonPath('health_records', [])
            ->assertJsonPath('vaccinations', [])
            ->assertJsonPath('maintenance_medicines', []);
    }

    public function test_direct_health_id_url_shows_the_shared_record_without_login(): void
    {
        $citizen = citizens::create([
            'Citizen_FName' => 'Jordan',
            'Citizen_LName' => 'Lee',
            'Citizen_BirthDate' => '1994-06-02',
            'Citizen_ContactNo' => '555-0102',
            'Citizen_Age' => 32,
            'qr_token' => '1f0f09f9-646e-42ef-9718-a2ed32eb26f6',
        ]);
        HealthRecord::create([
            'citizen_id' => $citizen->id,
            'diagnosis' => 'Recorded diagnosis',
            'record_date' => '2026-03-10',
            'comments' => 'Recorded clinical note',
        ]);
        VaccinationRecord::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => 'Vaccine C',
            'dose_number' => 'Booster',
            'date_administered' => '2026-03-11',
            'administered_by' => 'Health Center',
        ]);
        DB::table('maintenance_records')->insert([
            'citizen_id' => $citizen->id,
            'medicine_name' => 'Medicine D',
            'quantity_dispensed' => 14,
            'dosage' => '5 mg',
            'frequency' => 'Daily',
            'start_date' => '2026-02-01',
            'end_date' => null,
            'notes' => 'Use as directed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('health-id.public', $citizen->qr_token))
            ->assertOk()
            ->assertSee('Lee, Jordan')
            ->assertSee('Amuyong Health Record')
            ->assertSee('Recorded diagnosis')
            ->assertSee('Recorded clinical note')
            ->assertSee('Booster')
            ->assertSee('Medicine D')
            ->assertDontSee('555-0102');

        $this->assertGuest();
    }

    public function test_public_access_is_on_by_default_and_citizen_can_turn_it_off(): void
    {
        $citizen = citizens::create([
            'Citizen_FName' => 'Alex',
            'Citizen_LName' => 'Cruz',
            'Citizen_BirthDate' => '1995-02-01',
            'Citizen_ContactNo' => '555-0103',
            'Citizen_Age' => 31,
            'qr_token' => '3c1fc18a-37ab-455d-b0c0-dd5815112e56',
        ]);
        HealthRecord::create([
            'citizen_id' => $citizen->id,
            'diagnosis' => 'Public diagnosis',
        ]);

        $this->assertTrue($citizen->fresh()->public_health_id_enabled);
        $this->getJson(route('health-id.summary', $citizen->qr_token))
            ->assertOk()
            ->assertJsonPath('health_records.0.diagnosis', 'Public diagnosis');
        $this->get(route('health-id.public', $citizen->qr_token))
            ->assertOk()
            ->assertSee('Public diagnosis');

        $user = User::create([
            'name' => 'Alex Cruz',
            'email' => 'alex@example.test',
            'password' => 'password',
            'role' => User::ROLE_CITIZEN,
            'citizen_id' => $citizen->id,
            'must_change_password' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('citizen.health-sharing.revoke'))
            ->assertRedirect();
        $this->assertDatabaseHas('citizens', [
            'id' => $citizen->id,
            'public_health_id_enabled' => false,
        ]);
        $this->getJson(route('health-id.summary', $citizen->qr_token))
            ->assertForbidden()
            ->assertJsonMissing(['diagnosis' => 'Public diagnosis']);

        $this->get(route('health-id.public', $citizen->qr_token))
            ->assertOk()
            ->assertSee('Public viewing of this Health ID record is turned off.')
            ->assertDontSee('Public diagnosis');
    }

    public function test_unknown_direct_health_id_token_returns_not_found(): void
    {
        $this->get(route('health-id.public', 'a430be32-ab13-4781-901a-fbf9e990d8c1'))
            ->assertNotFound()
            ->assertSee('invalid or no longer available');
    }

    public function test_linked_citizen_can_turn_public_access_back_on(): void
    {
        $citizen = citizens::create([
            'Citizen_FName' => 'Casey',
            'Citizen_LName' => 'Santos',
            'Citizen_BirthDate' => '1997-05-01',
            'Citizen_ContactNo' => '555-0104',
            'Citizen_Age' => 29,
            'qr_token' => '8cd0e41c-9a8b-476e-bcc5-f29bc040ec94',
        ]);
        $user = User::create([
            'name' => 'Casey Santos',
            'email' => 'casey@example.test',
            'password' => 'password',
            'role' => User::ROLE_CITIZEN,
            'citizen_id' => $citizen->id,
            'must_change_password' => false,
        ]);

        $this->actingAs($user)
            ->post(route('citizen.health-sharing.grant'))
            ->assertRedirect();

        $this->assertDatabaseHas('citizens', [
            'id' => $citizen->id,
            'public_health_id_enabled' => true,
        ]);
        $this->getJson(route('health-id.summary', $citizen->qr_token))->assertOk();

        $this->delete(route('citizen.health-sharing.revoke'))
            ->assertRedirect();

        $this->assertDatabaseHas('citizens', [
            'id' => $citizen->id,
            'public_health_id_enabled' => false,
        ]);
        $this->getJson(route('health-id.summary', $citizen->qr_token))->assertForbidden();

        $this->post(route('citizen.health-sharing.grant'))
            ->assertRedirect();
        $this->getJson(route('health-id.summary', $citizen->qr_token))->assertOk();
    }

    public function test_bhw_cannot_grant_citizen_sharing(): void
    {
        $citizen = citizens::create([
            'Citizen_FName' => 'Morgan',
            'Citizen_LName' => 'Diaz',
            'Citizen_BirthDate' => '1990-01-01',
            'Citizen_ContactNo' => '555-0105',
            'Citizen_Age' => 36,
            'qr_token' => '1d2f2cbe-74f6-4833-bec9-14e787e64022',
            'public_health_id_enabled' => false,
        ]);
        $user = User::create([
            'name' => 'Health Worker',
            'email' => 'bhw@example.test',
            'password' => 'password',
            'role' => User::ROLE_BHW,
            'citizen_id' => $citizen->id,
            'must_change_password' => false,
        ]);

        $this->withoutMiddleware(\App\Http\Middleware\EnsurePasswordIsChanged::class);
        $this->actingAs($user)->post(route('citizen.health-sharing.grant'))->assertRedirect();

        $this->assertDatabaseHas('citizens', [
            'id' => $citizen->id,
            'public_health_id_enabled' => false,
        ]);
    }

    public function test_unknown_uuid_does_not_return_a_citizen_summary(): void
    {
        $response = $this->getJson(route('health-id.summary', 'a430be32-ab13-4781-901a-fbf9e990d8c1'));

        $response
            ->assertNotFound()
            ->assertJsonPath('message', 'No citizen record matches this QR code.');

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_public_health_routes_do_not_depend_on_the_legacy_sharing_table(): void
    {
        Schema::dropIfExists('citizen_health_shares');

        $citizen = citizens::create([
            'Citizen_FName' => 'Riley',
            'Citizen_LName' => 'Santos',
            'Citizen_BirthDate' => '1990-01-01',
            'Citizen_ContactNo' => '555-0106',
            'Citizen_Age' => 36,
            'qr_token' => 'a430be32-ab13-4781-901a-fbf9e990d8c1',
        ]);

        $this->getJson(route('health-id.summary', $citizen->qr_token))->assertOk();
        $this->get(route('health-id.public', $citizen->qr_token))
            ->assertOk()
            ->assertSee('Santos, Riley');
    }

    public function test_public_qr_scanner_navigation_works_for_guests_and_authenticated_users(): void
    {
        $this->get(route('public.qr-scanner', [], false))
            ->assertOk()
            ->assertSee('id="public-qr-start"', false)
            ->assertSee('href="/health-scanner"', false)
            ->assertSee('class="active"', false);

        $this->assertGuest();
        $this->get('/qr-scanner')->assertRedirect(route('login'));

        $bhw = User::create([
            'name' => 'Test BHW',
            'email' => 'bhw@example.test',
            'password' => 'password',
            'role' => 'bhw',
        ]);

        $this->actingAs($bhw)
            ->get(route('public.qr-scanner', [], false))
            ->assertOk()
            ->assertSee('id="public-qr-start"', false);
    }

}
