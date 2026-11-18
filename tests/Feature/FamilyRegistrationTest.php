<?php

namespace Tests\Feature;

use App\Http\Controllers\FamilyController;
use App\Http\Controllers\ArchiveController;
use App\Models\citizens;
use App\Models\Family;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Models\User;
use App\Models\UserPermission;
use App\Services\FamilyCodeGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FamilyRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role');
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
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('purok_subgroups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purok_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('purok_subgroup_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('purok_subgroup_id');
            $table->timestamps();
        });
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('family_name');
            $table->unsignedBigInteger('purok_id');
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->unsignedBigInteger('head_id')->nullable();
            $table->string('family_code')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->string('Citizen_FName')->nullable();
            $table->string('Citizen_MName')->nullable();
            $table->string('Citizen_LName')->nullable();
            $table->string('Citizen_Purok')->nullable();
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->unsignedBigInteger('family_id')->nullable();
            $table->boolean('Citizen_Pregnant')->default(false);
            $table->boolean('Citizen_InPills')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('citizen_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action');
            $table->string('module');
            $table->unsignedBigInteger('citizen_id');
            $table->text('description');
            $table->timestamps();
        });
    }

    public function test_add_citizen_view_exposes_configuration_to_its_external_script(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
        });
        Auth::login(User::create(['role' => 'bhw']));
        request()->setLaravelSession(session()->driver());
        session()->flashInput(['family_id' => '12', 'subgroup_id' => '34']);

        $html = view('bhw.addcitizen', [
            'puroks' => collect(),
            'families' => collect(),
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('data-quick-family-url="'.route('bhw.families.quick.store').'"', $html);
        $this->assertStringContainsString('data-old-family-id="12"', $html);
        $this->assertStringContainsString('data-old-subgroup-id="34"', $html);
        $this->assertStringContainsString('js/bhw/addcitizen.js', $html);
        $this->assertStringContainsString('js/templates/session-timeout.js', $html);
        $this->assertStringContainsString('data-session-lifetime="'.config('session.lifetime', 120).'"', $html);
    }

    public function test_searching_a_citizen_returns_their_whole_family_within_assigned_subgroups(): void
    {
        $user = User::create(['role' => 'bhw']);
        $purok = Purok::create(['name' => 'Purok 2']);
        $assigned = PurokSubgroup::create(['purok_id' => $purok->id, 'user_id' => $user->id, 'name' => '2A']);
        $other = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => '2B']);
        $family = \App\Models\Family::create(['family_name' => 'Household One', 'purok_id' => $purok->id, 'subgroup_id' => $assigned->id]);
        $hidden = \App\Models\Family::create(['family_name' => 'Household Two', 'purok_id' => $purok->id, 'subgroup_id' => $other->id]);
        citizens::create(['Citizen_FName' => 'Jose', 'Citizen_LName' => 'Santos', 'family_id' => $family->id]);
        $companion = citizens::create(['Citizen_FName' => 'Ana', 'Citizen_LName' => 'Cruz', 'family_id' => $family->id]);
        citizens::create(['Citizen_FName' => 'Jose', 'Citizen_LName' => 'Santos', 'family_id' => $hidden->id]);
        // Tokens from different citizens must not combine into a match.
        $unrelated = \App\Models\Family::create(['family_name' => 'Jose Santos', 'purok_id' => $purok->id, 'subgroup_id' => $assigned->id]);
        citizens::create(['Citizen_FName' => 'Jose', 'Citizen_LName' => 'Reyes', 'family_id' => $unrelated->id]);
        citizens::create(['Citizen_FName' => 'Maria', 'Citizen_LName' => 'Santos', 'family_id' => $unrelated->id]);
        Auth::login($user);
        $this->app->instance('request', Request::create('/bhw/families', 'GET', ['search' => 'Jose Santos']));

        $data = app(FamilyController::class)->index()->getData();

        $this->assertSame([$family->id], $data['families']->pluck('id')->all());
        $this->assertCount(2, $data['families']->first()->members);
        $this->assertTrue($data['families']->first()->members->contains('id', $companion->id));
        $this->assertSame(2, $data['familyOverview'][0][1]);
    }

    public function test_it_creates_family_and_assigns_unique_selected_members_with_generated_code(): void
    {
        $user = User::create(['role' => 'bhw']);
        UserPermission::create([
            'user_id' => $user->id,
            'feature' => 'family',
            'access' => 'write',
        ]);
        $purok = Purok::create(['name' => 'Purok 2']);
        $subgroup = PurokSubgroup::create([
            'purok_id' => $purok->id,
            'user_id' => $user->id,
            'name' => '2A',
        ]);
        $head = citizens::create([
            'Citizen_FName' => 'Maria',
            'Citizen_LName' => 'Santos',
            'Citizen_Purok' => 'Purok 2',
            'subgroup_id' => $subgroup->id,
        ]);
        $member = citizens::create([
            'Citizen_FName' => 'Jose',
            'Citizen_LName' => 'Santos',
            'Citizen_Purok' => 'Purok 2',
            'subgroup_id' => $subgroup->id,
        ]);
        Auth::login($user);

        $response = app(FamilyController::class)->store(Request::create('/bhw/families', 'POST', [
            'subgroup_id' => $subgroup->id,
            'citizen_ids' => [$head->id, $member->id],
        ]));

        $family = \App\Models\Family::firstOrFail();
        $this->assertSame('F2A-0001', $family->family_code);
        $this->assertSame('Santos Family', $family->family_name);
        $this->assertSame($head->id, $family->head_id);
        $this->assertSame($family->id, $head->fresh()->family_id);
        $this->assertSame($family->id, $member->fresh()->family_id);
        $this->assertSame(2, $family->members()->count());
        $this->assertSame('Family and selected household members created successfully.', $response->getSession()->get('success'));
    }

    public function test_archiving_and_restoring_renumbers_codes_without_changing_member_assignments(): void
    {
        $user = User::create(['role' => 'bhw']);
        UserPermission::create(['user_id' => $user->id, 'feature' => 'family', 'access' => 'write']);
        $purok = Purok::create(['name' => 'Purok 1']);
        $subgroup = PurokSubgroup::create([
            'purok_id' => $purok->id, 'user_id' => $user->id, 'name' => '1A',
        ]);
        $generator = app(FamilyCodeGenerator::class);
        $archived = $generator->create('Malabad', $subgroup);
        $current = $generator->create('Fabia Family', $subgroup);
        $member = citizens::create([
            'Citizen_FName' => 'Lolita', 'Citizen_LName' => 'Fabia',
            'family_id' => $current->id, 'subgroup_id' => $subgroup->id,
        ]);
        $archivedMember = citizens::create([
            'Citizen_FName' => 'Maria', 'Citizen_LName' => 'Malabad',
            'family_id' => $archived->id, 'subgroup_id' => $subgroup->id,
        ]);
        $current->update(['head_id' => $member->id]);
        Auth::login($user);

        app(FamilyController::class)->archive($archived);

        $this->assertSame('F1A-0001', $current->fresh()->family_code);
        $this->assertSame($current->id, $member->fresh()->family_id);
        $this->assertSame($member->id, $current->fresh()->head_id);
        $this->assertSame($archived->id, $archivedMember->fresh()->family_id);

        app(ArchiveController::class)->restore('families', $archived->id);

        $this->assertSame('F1A-0002', $archived->fresh()->family_code);
        $this->assertFalse($archived->fresh()->trashed());
        $this->assertSame($archived->id, $archivedMember->fresh()->family_id);
        $this->assertSame('F1A-0001', $current->fresh()->family_code);
    }

    public function test_family_pickers_include_members_of_previously_archived_families_within_the_bhw_scope(): void
    {
        [$subgroup, $archived, $current] = $this->familyAssignmentContext();
        $available = $this->familyCitizen($subgroup, null, 'Unassigned');
        $archivedMember = $this->familyCitizen($subgroup, $archived, 'Lolita');
        $this->familyCitizen($subgroup, $current, 'Already assigned');
        $deletedCitizen = $this->familyCitizen($subgroup, $archived, 'Archived citizen');
        $deletedCitizen->delete();
        $otherSubgroup = PurokSubgroup::create(['purok_id' => $subgroup->purok_id, 'name' => '1B']);
        $otherFamily = app(FamilyCodeGenerator::class)->create('Other Family', $otherSubgroup);
        $this->familyCitizen($otherSubgroup, $otherFamily, 'Outside assigned area');
        $otherFamily->delete();
        $archived->delete(); // Existing archived records still have a non-null family_id.

        $controller = app(FamilyController::class);
        $expected = [$available->id, $archivedMember->id];
        $this->assertEqualsCanonicalizing($expected, $controller->index()->getData()['availableCitizens']->pluck('id')->all());
        $this->assertEqualsCanonicalizing($expected, $controller->show($current)->getData()['availableCitizens']->pluck('id')->all());
        $this->assertSame($archived->id, $archivedMember->fresh()->family_id);
    }

    public function test_archived_family_members_can_join_an_existing_family_and_are_not_reclaimed_on_restore(): void
    {
        [$subgroup, $archived, $current] = $this->familyAssignmentContext();
        $member = $this->familyCitizen($subgroup, $archived, 'Lolita');
        $remaining = $this->familyCitizen($subgroup, $archived, 'Remaining');
        $archived->update(['head_id' => $member->id]);
        app(FamilyController::class)->archive($archived);

        app(FamilyController::class)->addMember(Request::create('/bhw/families/members', 'POST', [
            'citizen_id' => $member->id,
        ]), $current);

        $this->assertSame($current->id, $member->fresh()->family_id);
        $this->assertSame($subgroup->id, $member->fresh()->subgroup_id);
        $this->assertSame('Purok 1', $member->fresh()->Citizen_Purok);
        $this->assertDatabaseHas('citizen_activity_logs', [
            'citizen_id' => $member->id, 'description' => 'Assigned citizen to family Casal Family',
        ]);

        app(ArchiveController::class)->restore('families', $archived->id);

        $this->assertSame($current->id, $member->fresh()->family_id);
        $this->assertSame([$remaining->id], $archived->members()->pluck('id')->all());
        $this->assertNull($archived->fresh()->head_id);
        $this->assertNotContains($member->id, app(FamilyController::class)->index()->getData()['availableCitizens']->pluck('id')->all());
    }

    public function test_new_family_registration_accepts_citizens_linked_to_an_archived_family(): void
    {
        [$subgroup, $archived] = $this->familyAssignmentContext();
        $member = $this->familyCitizen($subgroup, $archived, 'Lolita');
        app(FamilyController::class)->archive($archived);

        app(FamilyController::class)->store(Request::create('/bhw/families', 'POST', [
            'subgroup_id' => $subgroup->id, 'citizen_ids' => [$member->id],
        ]));

        $newFamily = Family::findOrFail($member->fresh()->family_id);
        $this->assertNotSame($archived->id, $newFamily->id);
        $this->assertSame('Fabia Family', $newFamily->family_name);
        $this->assertSame($member->id, $newFamily->head_id);
        $this->assertSame(0, $archived->members()->count());
        $this->assertTrue($archived->fresh()->trashed());
    }

    public function test_registration_rechecks_availability_if_the_old_family_was_restored(): void
    {
        [$subgroup, $archived] = $this->familyAssignmentContext();
        $member = $this->familyCitizen($subgroup, $archived, 'Lolita');
        $codes = app(FamilyCodeGenerator::class);
        $codes->archive($archived);
        $codes->restore($archived);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try {
            app(FamilyController::class)->store(Request::create('/bhw/families', 'POST', [
                'subgroup_id' => $subgroup->id, 'citizen_ids' => [$member->id],
            ]));
        } finally {
            $this->assertSame($archived->id, $member->fresh()->family_id);
            $this->assertSame(2, Family::count());
        }
    }

    private function familyAssignmentContext(): array
    {
        $user = User::create(['role' => 'bhw']);
        UserPermission::create(['user_id' => $user->id, 'feature' => 'family', 'access' => 'write']);
        $purok = Purok::create(['name' => 'Purok 1']);
        $subgroup = PurokSubgroup::create([
            'purok_id' => $purok->id, 'user_id' => $user->id, 'name' => '1A',
        ]);
        $codes = app(FamilyCodeGenerator::class);
        $archived = $codes->create('Fabia Family', $subgroup);
        $current = $codes->create('Casal Family', $subgroup);
        Auth::login($user);

        return [$subgroup, $archived, $current];
    }

    private function familyCitizen(PurokSubgroup $subgroup, ?Family $family, string $name): citizens
    {
        return citizens::create([
            'Citizen_FName' => $name, 'Citizen_LName' => 'Fabia',
            'Citizen_Purok' => 'Purok 1', 'subgroup_id' => $subgroup->id,
            'family_id' => $family?->id,
        ]);
    }
}
