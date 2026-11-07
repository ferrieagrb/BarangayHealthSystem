<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use App\Models\citizens;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CitizenPhotoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);
        Storage::fake('local');
        Storage::fake('public');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->nullable();
            $table->string('password');
            $table->string('role');
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->string('theme')->nullable();
            $table->string('font_size')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('citizens', function (Blueprint $table): void {
            $table->id();
            $table->string('Citizen_FName');
            $table->string('Citizen_MName')->nullable();
            $table->string('Citizen_LName');
            $table->date('Citizen_BirthDate');
            $table->string('Citizen_ContactNo')->nullable();
            $table->integer('Citizen_Age');
            $table->string('Citizen_Purok')->nullable();
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->unsignedBigInteger('family_id')->nullable();
            $table->uuid('qr_token')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('Citizen_Sex')->nullable();
            $table->boolean('Citizen_Pregnant')->default(false);
            $table->boolean('Citizen_InPills')->default(false);
            $table->boolean('Citizen_IsDeceased')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('puroks', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
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
    }

    public function test_bhw_can_create_a_citizen_with_a_private_photo(): void
    {
        $bhw = $this->user('bhw');
        $purok = DB::table('puroks')->insertGetId(['name' => 'Purok 1']);
        $subgroup = DB::table('purok_subgroups')->insertGetId([
            'name' => 'Group A', 'purok_id' => $purok, 'user_id' => $bhw->id,
        ]);

        $this->actingAs($bhw)->post(route('citizen.store'), [
            'Citizen_FName' => 'Jamie', 'Citizen_LName' => 'Rivera',
            'Citizen_BirthDate' => '1990-04-02', 'Citizen_Sex' => 'Female',
            'Citizen_Purok' => 'Purok 1', 'subgroup_id' => $subgroup,
            'email' => 'jamie@example.test', 'photo' => UploadedFile::fake()->image('photo.jpg'),
        ])->assertRedirect(route('citizenlist'))->assertSessionHasNoErrors();

        $citizen = citizens::where('Citizen_FName', 'Jamie')->firstOrFail();
        $this->assertNotEmpty($citizen->photo_path);
        Storage::disk('local')->assertExists($citizen->photo_path);
        Storage::disk('public')->assertMissing($citizen->photo_path);
        $this->get(route('citizen.photo', $citizen->id))->assertOk();
        $owner = User::where('citizen_id', $citizen->id)->firstOrFail();
        $owner->update(['must_change_password' => false]);
        $this->actingAs($owner)->get(route('citizen.photo', $citizen->id))->assertOk();
        $this->assertStringContainsString(
            route('citizen.photo', $citizen->id),
            view('partials.health-card', ['citizen' => $citizen, 'qrImage' => null])->render()
        );
    }

    public function test_citizen_can_replace_photo_and_only_their_own_record_changes(): void
    {
        $citizen = $this->citizen('Jamie', 'citizen-photos/old.jpg');
        $other = $this->citizen('Alex', 'citizen-photos/other.jpg');
        Storage::disk('local')->put($citizen->photo_path, 'old');
        Storage::disk('local')->put($other->photo_path, 'other');
        $owner = $this->user('citizen', $citizen->id);

        $this->actingAs($owner)->get(route('citizen.settings'))->assertOk()
            ->assertSee(route('citizen.photo', $citizen->id))
            ->assertSee(route('citizen.photo.update'))
            ->assertSee('id="selected-photo-preview"', false)
            ->assertSee('id="photo-confirm-dialog"', false)
            ->assertSee('js/citizen-photo-settings.js');
        $this->put(route('citizen.photo.update'), [
            'photo' => UploadedFile::fake()->image('new.png'), 'citizen_id' => $other->id,
        ])->assertRedirect(route('citizen.settings'))->assertSessionHasNoErrors();

        $newPath = $citizen->fresh()->photo_path;
        $this->assertNotSame('citizen-photos/old.jpg', $newPath);
        Storage::disk('local')->assertExists($newPath);
        Storage::disk('local')->assertMissing('citizen-photos/old.jpg');
        Storage::disk('local')->assertExists('citizen-photos/other.jpg');
        $this->assertSame('citizen-photos/other.jpg', $other->fresh()->photo_path);
        $this->get(route('citizen.photo', $citizen->id))->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->get(route('citizen.photo', $other->id))->assertNotFound();
    }

    public function test_photo_validation_and_access_are_enforced(): void
    {
        $citizen = $this->citizen('Jamie');
        $owner = $this->user('citizen', $citizen->id);
        $this->actingAs($owner)->put(route('citizen.photo.update'), [])->assertSessionHasErrors('photo');
        $this->put(route('citizen.photo.update'), ['photo' => UploadedFile::fake()->create('bad.txt', 10)])
            ->assertSessionHasErrors('photo');
        $this->put(route('citizen.photo.update'), ['photo' => UploadedFile::fake()->image('large.jpg')->size(2049)])
            ->assertSessionHasErrors('photo');
        $this->assertNull($citizen->fresh()->photo_path);

        $this->actingAs($this->user('admin'))->putJson(route('citizen.photo.update'), [
            'photo' => UploadedFile::fake()->image('new.jpg'),
        ])->assertForbidden();
        $this->actingAs($this->user('citizen'))->put(route('citizen.photo.update'), [
            'photo' => UploadedFile::fake()->image('new.jpg'),
        ])->assertNotFound();
        $this->get(route('citizen.settings'))->assertOk()->assertSee('No citizen record is linked to your account.');
        $this->assertNull($citizen->fresh()->photo_path);
    }

    public function test_citizen_can_add_a_first_photo_and_replace_a_legacy_public_photo(): void
    {
        $citizen = $this->citizen('Jamie');
        $this->actingAs($this->user('citizen', $citizen->id))
            ->get(route('citizen.settings'))->assertOk()->assertSee('You do not have a photo yet.')
            ->assertSee('No current photo.')
            ->assertSee('id="photo-confirm-dialog"', false);
        $this->put(route('citizen.photo.update'), ['photo' => UploadedFile::fake()->image('first.jpg')])
            ->assertRedirect(route('citizen.settings'))->assertSessionHasNoErrors();
        Storage::disk('local')->assertExists($citizen->fresh()->photo_path);

        $citizen->update(['photo_path' => 'citizen-photos/legacy.jpg']);
        Storage::disk('public')->put('citizen-photos/legacy.jpg', 'legacy');
        $this->get(route('citizen.photo', $citizen->id))->assertOk();
        $this->put(route('citizen.photo.update'), ['photo' => UploadedFile::fake()->image('new.webp')])
            ->assertRedirect(route('citizen.settings'))->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing('citizen-photos/legacy.jpg');
        Storage::disk('local')->assertExists($citizen->fresh()->photo_path);
    }

    public function test_guests_cannot_change_photos(): void
    {
        $this->put(route('citizen.photo.update'), ['photo' => UploadedFile::fake()->image('new.jpg')])
            ->assertRedirect(route('login'));
    }

    public function test_assigned_bhw_can_add_and_replace_photo_without_changing_citizen_details(): void
    {
        $bhw = $this->user('bhw');
        $purok = DB::table('puroks')->insertGetId(['name' => 'Purok 1']);
        $subgroup = DB::table('purok_subgroups')->insertGetId([
            'name' => 'Group A', 'purok_id' => $purok, 'user_id' => $bhw->id,
        ]);
        $citizen = $this->citizen('Jamie');
        $citizen->update(['subgroup_id' => $subgroup]);
        $url = route('citizen.photo.bhw-update', $citizen->id);

        $this->actingAs($bhw)->from('/citizendetails/'.$citizen->id)->put($url, [
            'photo' => UploadedFile::fake()->image('first.png'),
            'Citizen_FName' => 'Changed',
        ])->assertRedirect('/citizendetails/'.$citizen->id)->assertSessionHas('photo_success');
        $firstPath = $citizen->fresh()->photo_path;
        Storage::disk('local')->assertExists($firstPath);
        $this->assertSame('Jamie', $citizen->fresh()->Citizen_FName);
        $this->get(route('citizen.photo', $citizen->id))->assertOk();
        $this->put($url, ['photo' => UploadedFile::fake()->image('replacement.jpg')])
            ->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($citizen->fresh()->photo_path);
        $this->assertDatabaseHas('citizen_activity_logs', [
            'user_id' => $bhw->id, 'citizen_id' => $citizen->id, 'description' => 'Updated citizen photo',
        ]);

        $this->put($url, ['photo' => UploadedFile::fake()->create('bad.txt', 10)])
            ->assertSessionHasErrors('photo');
        $this->put($url, ['photo' => UploadedFile::fake()->image('big.jpg')->size(2049)])
            ->assertSessionHasErrors('photo');
    }

    public function test_bhw_photo_permissions_and_deceased_record_lock_are_enforced(): void
    {
        $owner = $this->user('bhw');
        $other = $this->user('bhw');
        $purok = DB::table('puroks')->insertGetId(['name' => 'Purok 1']);
        $subgroup = DB::table('purok_subgroups')->insertGetId([
            'name' => 'Group A', 'purok_id' => $purok, 'user_id' => $owner->id,
        ]);
        $citizen = $this->citizen('Jamie', 'citizen-photos/old.jpg');
        $citizen->update(['subgroup_id' => $subgroup]);
        Storage::disk('local')->put($citizen->photo_path, 'old');
        $url = route('citizen.photo.bhw-update', $citizen->id);

        $this->actingAs($other)->putJson($url, ['photo' => UploadedFile::fake()->image('new.jpg')])
            ->assertForbidden();
        $this->get(route('citizen.photo', $citizen->id))->assertNotFound();
        DB::table('user_permissions')->insert(['user_id' => $other->id, 'feature' => 'view_all_citizens', 'access' => 'read']);
        $this->get(route('citizen.photo', $citizen->id))->assertOk();
        $this->putJson($url, ['photo' => UploadedFile::fake()->image('new.jpg')])->assertForbidden();
        $citizen->update(['Citizen_IsDeceased' => true]);
        $this->actingAs($owner)->putJson($url, ['photo' => UploadedFile::fake()->image('new.jpg')])
            ->assertForbidden();
        $this->actingAs($this->user('citizen', $citizen->id))
            ->putJson($url, ['photo' => UploadedFile::fake()->image('new.jpg')])->assertForbidden();
        $this->assertSame('citizen-photos/old.jpg', $citizen->fresh()->photo_path);
        Storage::disk('local')->assertExists('citizen-photos/old.jpg');
    }

    public function test_bhw_photo_controls_are_shown_only_for_editable_living_citizens(): void
    {
        $bhw = $this->user('bhw');
        $citizen = $this->citizen('Jamie', 'citizen-photos/old.jpg');
        $this->actingAs($bhw);
        $errors = new \Illuminate\Support\ViewErrorBag();
        $editable = view('bhw.partials.citizen-photo', ['citizen' => $citizen, 'canEdit' => true, 'errors' => $errors])->render();
        $this->assertStringContainsString('id="photo-confirm-dialog"', $editable);
        $this->assertStringContainsString('form="citizen-photo-form"', $editable);
        $this->assertStringContainsString(route('citizen.photo', $citizen->id), $editable);
        $citizen->Citizen_IsDeceased = true;
        $locked = view('bhw.partials.citizen-photo', ['citizen' => $citizen, 'canEdit' => true, 'errors' => $errors])->render();
        $this->assertStringNotContainsString('id="photo-confirm-dialog"', $locked);
        $readonly = view('bhw.partials.citizen-photo', ['citizen' => $citizen, 'canEdit' => false, 'errors' => $errors])->render();
        $this->assertStringNotContainsString('id="photo-confirm-dialog"', $readonly);
        $this->assertStringContainsString('Photo unavailable', $readonly);
    }

    private function citizen(string $name, ?string $photoPath = null): citizens
    {
        return citizens::create([
            'Citizen_FName' => $name, 'Citizen_LName' => 'Rivera',
            'Citizen_BirthDate' => '1990-04-02', 'Citizen_Age' => 36,
            'Citizen_Sex' => 'Female', 'photo_path' => $photoPath,
        ]);
    }

    private function user(string $role, ?int $citizenId = null): User
    {
        return User::create([
            'name' => 'Test User', 'email' => uniqid('user', true).'@example.test',
            'password' => 'password', 'role' => $role, 'citizen_id' => $citizenId,
        ]);
    }
}
