<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use App\Models\citizens;
use App\Models\Referral;
use App\Models\SystemSetting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReferralCreationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);
        Storage::fake('local');
        Storage::fake('public');
        $this->travelTo(now()->setDate(2026, 11, 19)->setTime(14, 30));

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('role');
            $table->boolean('must_change_password')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('citizens', function (Blueprint $table): void {
            $table->id();
            $table->string('Citizen_FName');
            $table->string('Citizen_MName')->nullable();
            $table->string('Citizen_LName');
            $table->date('Citizen_BirthDate')->nullable();
            $table->integer('Citizen_Age')->nullable();
            $table->string('Citizen_Sex');
            $table->string('Citizen_Purok')->nullable();
            $table->string('Citizen_ContactNo')->nullable();
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->unsignedBigInteger('family_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
            $table->unsignedBigInteger('purok_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->softDeletes();
        });
        Schema::create('purok_subgroup_user', function (Blueprint $table): void {
            $table->unsignedBigInteger('purok_subgroup_id');
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('families', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->unsignedBigInteger('purok_id')->nullable();
            $table->softDeletes();
        });
        Schema::create('user_permissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('feature');
            $table->string('access');
        });
        Schema::create('citizen_activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action');
            $table->string('module');
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_05_14_203925_create_referrals_table.php'))->up();
        Schema::table('referrals', fn (Blueprint $table) => $table->unsignedBigInteger('user_id')->nullable());
        (require database_path('migrations/2026_11_19_000001_add_patient_snapshot_to_referrals.php'))->up();
        (require database_path('migrations/2026_11_20_000001_make_referral_approval_status_optional.php'))->up();
    }

    public function test_picker_search_and_selection_autofill_the_preview(): void
    {
        $user = $this->bhw();
        $citizen = $this->citizen();
        $other = $this->citizen(['Citizen_FName' => 'Ana', 'Citizen_MName' => null]);
        $this->actingAs($user)->get(route('referrals.create'))
            ->assertOk()->assertSee('Choose a citizen')->assertSee('disabled', false);
        $response = $this->get(route('referrals.create', [
            'search' => 'Maria Cruz', 'citizen_id' => $citizen->id,
        ]));
        $response->assertOk()->assertSee('Maria Santos Cruz')->assertSee('09171234567')
            ->assertSee('Jun 15, 1990')->assertSee('36')->assertSee('Purok 1')
            ->assertSee('CLINICAL INFORMATION');
        $this->assertSame([$citizen->id], $response->viewData('citizens')->pluck('id')->all());
        $this->get(route('referrals.create', ['citizen_id' => $other->id]))
            ->assertOk()->assertSee('Ana Cruz')->assertDontSee('Maria Santos Cruz');
    }

    public function test_generation_uses_the_citizen_record_and_leaves_doctor_fields_blank(): void
    {
        $citizen = $this->citizen();
        $this->actingAs($this->bhw())->post(route('referrals.store'), [
            'citizen_id' => $citizen->id,
            'name' => 'Forged Name', 'age' => 99,
            'requests_for' => 'Forged diagnosis', 'referred_by' => 'Forged Doctor', 'status' => 'approved',
        ])->assertRedirect(route('referrals.index'))->assertSessionHasNoErrors();

        $referral = Referral::sole();
        $this->assertSame($citizen->id, $referral->citizen_id);
        $this->assertSame('Maria Santos Cruz', $referral->name);
        $this->assertEquals(36, $referral->age);
        $this->assertSame('1990-06-15', $referral->birth_date->toDateString());
        $this->assertSame('09171234567', $referral->contact_number);
        $this->assertSame('2026-11-19 14:30', $referral->referred_at->format('Y-m-d H:i'));
        foreach (['requests_for', 'vital_signs', 'treatment_given', 'referred_by', 'status'] as $field) {
            $this->assertNull($referral->$field);
        }
        $this->assertDatabaseHas('citizen_activity_logs', [
            'citizen_id' => $citizen->id, 'module' => 'referral', 'action' => 'create',
        ]);
        Storage::disk('local')->assertExists($referral->file_path);
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($referral->file_path));
        $this->get(route('referrals.download', $referral))->assertDownload(basename($referral->file_path));

        DB::table('citizens')->where('id', $citizen->id)->update(['Citizen_FName' => 'Changed']);
        $this->assertSame('Maria Santos Cruz', $referral->fresh()->name);
    }

    public function test_pdf_is_one_a4_page_with_all_sections_and_no_application_layout(): void
    {
        $referral = new Referral([
            'citizen_id' => 12, 'name' => 'Maria Santos Cruz', 'age' => 36,
            'birth_date' => '1990-06-15', 'gender' => 'Female', 'address' => 'Purok 1',
            'contact_number' => '09171234567', 'referred_at' => now(),
        ]);
        $referral->id = 123;
        $referral->exists = true;
        $html = view('bhw.referrals.pdf', compact('referral'))->render();
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<link', $html);
        $this->assertStringNotContainsString('<form', $html);
        $this->assertStringNotContainsString('N/A', strip_tags($html));
        $this->assertStringContainsString('REF-2026-000123', $html);
        $this->assertStringContainsString('FINDINGS AND RECOMMENDATIONS', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        $content = $pdf->output();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('/Width 1254', $content);
        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }

    public function test_referral_records_support_direct_generation_without_requests_or_status_updates(): void
    {
        $citizen = $this->citizen();
        $user = $this->bhw();
        $this->actingAs($user)->post(route('referrals.store'), ['citizen_id' => $citizen->id])
            ->assertRedirect(route('referrals.index'))->assertSessionHasNoErrors();
        $referral = Referral::sole();
        $response = $this->get(route('referrals.index'))
            ->assertOk()->assertSee('Generate Referral')->assertSee('Maria Santos Cruz')
            ->assertSee('Generated By')->assertSee($user->name)
            ->assertSee(route('referrals.download', $referral), false);
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $headings = array_map(
            fn ($node) => trim($node->textContent),
            iterator_to_array($xpath->query('//table[@class="bhw-referrals-table"]//th'))
        );
        $this->assertSame(['Date', 'Patient', 'Generated By', 'Referral PDF'], $headings);
        $this->assertSame(0, $xpath->query('//select[@name="status"]')->length);
        $this->patchJson('/referrals/'.$referral->id.'/status', ['status' => 'released'])->assertNotFound();
        $this->assertNull($referral->fresh()->status);

        $this->get(route('referrals.index', ['search' => 'Missing Patient']))->assertOk()
            ->assertDontSee('Maria Santos Cruz')->assertSee('No referral records found.');
    }

    public function test_download_renders_the_latest_logo_instead_of_the_previously_saved_pdf(): void
    {
        $citizen = $this->citizen();
        $this->actingAs($this->bhw())->post(route('referrals.store'), ['citizen_id' => $citizen->id])
            ->assertRedirect(route('referrals.index'));
        $referral = Referral::sole();
        $savedPdf = Storage::disk('local')->get($referral->file_path);
        $this->assertStringContainsString('/Width 1254', $savedPdf);

        $upload = UploadedFile::fake()->image('updated-logo.png', 4, 4);
        Storage::disk('public')->put('site-logo/updated-logo.png', file_get_contents($upload->getRealPath()));
        SystemSetting::create(['key' => 'site_logo', 'value' => 'site-logo/updated-logo.png']);
        DB::table('citizens')->where('id', $citizen->id)->update(['Citizen_FName' => 'Changed']);

        $response = $this->get(route('referrals.download', $referral))
            ->assertOk()->assertDownload(basename($referral->file_path));
        $this->assertStringContainsString('/Width 4', $response->getContent());
        $this->assertStringNotContainsString('/Width 1254', $response->getContent());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame('Maria Santos Cruz', $referral->fresh()->name);
        $this->assertSame($savedPdf, Storage::disk('local')->get($referral->file_path));
    }

    public function test_legacy_download_updates_the_logo_and_preserves_all_saved_clinical_fields(): void
    {
        $referral = Referral::create([
            'date_of_referral' => '2026-05-20', 'name' => 'Legacy Patient', 'age' => 35,
            'gender' => 'Female', 'address' => 'Purok 1', 'requests_for' => 'Specialist consultation',
            'vital_signs' => 'BP 120/80', 'treatment_given' => 'Oral hydration',
            'medication_given' => 'Medication recorded', 'self_medication' => 'None reported',
            'maintenance_schedule' => 'Monthly follow-up', 'referred_by' => 'Dr. Test',
            'file_path' => 'referrals/legacy.pdf', 'status' => 'released',
        ]);
        Storage::disk('local')->put($referral->file_path, 'previously generated PDF');
        $html = view('bhw.referrals.legacy-pdf', compact('referral'))->render();
        foreach (['requests_for', 'vital_signs', 'treatment_given', 'medication_given',
            'self_medication', 'maintenance_schedule', 'referred_by'] as $field) {
            $this->assertStringContainsString($referral->{$field}, $html);
        }
        $response = $this->actingAs($this->bhw())->get(route('referrals.download', $referral))
            ->assertOk()->assertDownload('legacy.pdf');
        $this->assertStringContainsString('/Width 1254', $response->getContent());
        $this->assertSame('released', $referral->fresh()->status);
    }

    public function test_unknown_birth_date_age_and_contact_are_preserved_as_blank(): void
    {
        $citizen = $this->citizen([
            'Citizen_BirthDate' => null, 'Citizen_Age' => null, 'Citizen_ContactNo' => null,
        ]);
        $this->actingAs($this->bhw())->get(route('referrals.create', ['citizen_id' => $citizen->id]))
            ->assertOk();
        $this->post(route('referrals.store'), ['citizen_id' => $citizen->id])
            ->assertRedirect(route('referrals.index'))->assertSessionHasNoErrors();
        $referral = Referral::sole();
        $this->assertNull($referral->age);
        $this->assertNull($referral->birth_date);
        $this->assertNull($referral->contact_number);
    }

    public function test_inaccessible_and_archived_citizens_cannot_be_selected_or_submitted(): void
    {
        $user = $this->bhw(false);
        $citizen = $this->citizen();
        $this->actingAs($user)->get(route('referrals.create'))->assertOk()
            ->assertDontSee('Maria Santos Cruz');
        $this->getJson(route('referrals.create', ['citizen_id' => $citizen->id]))->assertNotFound();
        $this->postJson(route('referrals.store'), ['citizen_id' => $citizen->id])->assertNotFound();
        $this->actingAs($this->bhw());
        DB::table('citizens')->where('id', $citizen->id)->update(['deleted_at' => now()]);
        $this->postJson(route('referrals.store'), ['citizen_id' => $citizen->id])->assertNotFound();
        $this->assertDatabaseCount('referrals', 0);
    }

    public function test_a_citizen_is_required_and_non_bhw_users_cannot_generate_referrals(): void
    {
        $this->actingAs($this->bhw())->postJson(route('referrals.store'), [])
            ->assertUnprocessable()->assertJsonValidationErrors('citizen_id');
        $citizen = $this->citizen();
        $user = User::create(['name' => 'Citizen User', 'password' => 'password', 'role' => 'citizen']);
        $this->actingAs($user)->getJson(route('referrals.create'))->assertForbidden();
        $this->postJson(route('referrals.store'), ['citizen_id' => $citizen->id])->assertForbidden();
    }

    public function test_assigned_bhw_can_select_household_members_and_uses_the_household_purok(): void
    {
        $user = $this->bhw(false);
        $purok = DB::table('puroks')->insertGetId(['name' => 'Purok 2']);
        $subgroup = DB::table('purok_subgroups')->insertGetId([
            'name' => 'Group A', 'purok_id' => $purok, 'user_id' => $user->id,
        ]);
        $family = DB::table('families')->insertGetId(['subgroup_id' => $subgroup, 'purok_id' => $purok]);
        $citizen = $this->citizen(['family_id' => $family]);
        $other = $this->citizen(['Citizen_FName' => 'Unassigned']);
        $response = $this->actingAs($user)->get(route('referrals.create', ['citizen_id' => $citizen->id]));
        $response->assertOk()->assertSee('Maria Santos Cruz');
        $this->assertSame([$citizen->id], $response->viewData('citizens')->pluck('id')->all());
        $this->assertSame('Purok 2', $response->viewData('referral')->address);
        $this->post(route('referrals.store'), ['citizen_id' => $citizen->id])
            ->assertRedirect(route('referrals.index'));
        $this->assertSame('Purok 2', Referral::sole()->address);
        $this->postJson(route('referrals.store'), ['citizen_id' => $other->id])->assertNotFound();
    }

    private function bhw(bool $all = true): User
    {
        $user = User::create(['name' => 'Test BHW', 'password' => 'password', 'role' => 'bhw']);
        if ($all) {
            DB::table('user_permissions')->insert([
                'user_id' => $user->id, 'feature' => 'view_all_citizens', 'access' => 'read',
            ]);
        }
        return $user;
    }

    private function citizen(array $attributes = []): citizens
    {
        $id = DB::table('citizens')->insertGetId(array_replace([
            'Citizen_FName' => 'Maria', 'Citizen_MName' => 'Santos', 'Citizen_LName' => 'Cruz',
            'Citizen_BirthDate' => '1990-06-15', 'Citizen_Age' => 18,
            'Citizen_Sex' => 'Female', 'Citizen_Purok' => 'Purok 1',
            'Citizen_ContactNo' => '09171234567',
        ], $attributes));
        return citizens::findOrFail($id);
    }
}
