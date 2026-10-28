<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Models\User;
use App\Models\citizens;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use Tests\TestCase;

class AdminCitizenPurgeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);
        $this->createTestSchema();
    }

    public function test_admin_can_delete_a_purok_boundary_without_changing_other_puroks(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'must_change_password' => false,
        ]);
        $boundary = [
            'type' => 'Polygon',
            'coordinates' => [[[120.1, 14.1], [120.2, 14.1], [120.2, 14.2], [120.1, 14.1]]],
        ];
        $purok = Purok::create(['name' => 'Purok 1', 'boundary' => $boundary]);
        $otherPurok = Purok::create(['name' => 'Purok 2', 'boundary' => $boundary]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.puroks.boundary.destroy', $purok))
            ->assertOk()
            ->assertJson(['message' => 'Purok boundary deleted.']);

        $this->assertNull($purok->fresh()->boundary);
        $this->assertSame($boundary, $otherPurok->fresh()->boundary);
    }

    public function test_admin_password_is_required_to_delete_puroks_and_subgroups(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'must_change_password' => false,
        ]);
        $purok = Purok::create(['name' => 'Purok 1']);
        $subgroupPurok = Purok::create(['name' => 'Purok 2']);
        $subgroup = PurokSubgroup::create([
            'purok_id' => $subgroupPurok->id,
            'name' => '1A',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.demographics'))
            ->delete(route('admin.puroks.destroy', $purok))
            ->assertRedirect(route('admin.demographics'))
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('puroks', ['id' => $purok->id]);

        $this->delete(route('admin.subgroups.destroy', $subgroup), ['current_password' => 'incorrect'])
            ->assertRedirect(route('admin.demographics'))
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('purok_subgroups', ['id' => $subgroup->id]);

        $this->delete(route('admin.subgroups.destroy', $subgroup), ['current_password' => 'password'])
            ->assertRedirect(route('admin.demographics'))
            ->assertSessionHas('success', 'Purok subgroup deleted successfully!');

        $this->delete(route('admin.puroks.destroy', $purok), ['current_password' => 'password'])
            ->assertRedirect(route('admin.demographics'))
            ->assertSessionHas('success', 'Purok deleted successfully!');
    }

    public function test_admin_can_permanently_delete_citizens_and_citizen_accounts_in_a_purok_only(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'must_change_password' => false,
        ]);
        $purok = Purok::create(['name' => 'Purok 1']);
        $subgroup = PurokSubgroup::create([
            'purok_id' => $purok->id,
            'name' => '1A',
        ]);
        $family = Family::create([
            'family_name' => 'Purok 1 Family',
            'purok_id' => $purok->id,
            'subgroup_id' => $subgroup->id,
        ]);
        $otherPurok = Purok::create(['name' => 'Purok 2']);
        $otherFamily = Family::create([
            'family_name' => 'Purok 2 Family',
            'purok_id' => $otherPurok->id,
        ]);
        $assignedCitizen = $this->createCitizen('Purok 1', $subgroup->id, $family->id);
        $unassignedCitizen = $this->createCitizen('Purok 1');
        $otherCitizen = $this->createCitizen('Purok 2');
        $conflictingAssignmentCitizen = $this->createCitizen('Purok 1', null, $otherFamily->id);
        $healthRecordId = DB::table('health_records')->insertGetId([
            'citizen_id' => $assignedCitizen->id,
            'diagnosis' => 'Test diagnosis',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $citizenAccount = User::factory()->create([
            'role' => User::ROLE_CITIZEN,
            'citizen_id' => $assignedCitizen->id,
        ]);
        $unrelatedCitizenAccount = User::factory()->create([
            'role' => User::ROLE_CITIZEN,
            'citizen_id' => $otherCitizen->id,
        ]);
        $bhwAccount = User::factory()->create([
            'role' => User::ROLE_BHW,
            'citizen_id' => $unassignedCitizen->id,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.demographics'))
            ->delete(route('admin.puroks.citizens.destroy', $purok), [
                'confirmation' => 'Purok 1',
                'current_password' => 'incorrect',
                'demographics_tab' => 'puroks',
            ])
            ->assertRedirect(route('admin.demographics'))
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('citizens', ['id' => $assignedCitizen->id]);

        $this->actingAs($admin)
            ->from(route('admin.demographics'))
            ->delete(route('admin.puroks.citizens.destroy', $purok), [
                'confirmation' => 'Purok 1',
                'current_password' => 'password',
                'demographics_tab' => 'puroks',
            ])
            ->assertRedirect(route('admin.demographics'))
            ->assertSessionHas('demographics_tab', 'puroks')
            ->assertSessionHas('success', '2 citizens and 1 linked citizen accounts were permanently deleted from Purok 1. Family records and BHW accounts were not deleted.');

        $this->assertDatabaseMissing('citizens', ['id' => $assignedCitizen->id]);
        $this->assertDatabaseMissing('citizens', ['id' => $unassignedCitizen->id]);
        $this->assertDatabaseHas('citizens', ['id' => $otherCitizen->id]);
        $this->assertDatabaseHas('citizens', ['id' => $conflictingAssignmentCitizen->id]);
        $this->assertDatabaseMissing('health_records', ['id' => $healthRecordId]);
        $this->assertDatabaseMissing('users', ['id' => $citizenAccount->id]);
        $this->assertDatabaseHas('users', ['id' => $unrelatedCitizenAccount->id]);
        $this->assertDatabaseHas('users', ['id' => $bhwAccount->id]);
        $this->assertDatabaseHas('families', ['id' => $family->id]);
    }

    public function test_subgroup_purge_uses_family_membership_and_requires_an_exact_confirmation(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'must_change_password' => false,
        ]);
        $purok = Purok::create(['name' => 'Purok 1']);
        $subgroup = PurokSubgroup::create([
            'purok_id' => $purok->id,
            'name' => '1A',
        ]);
        $otherSubgroup = PurokSubgroup::create([
            'purok_id' => $purok->id,
            'name' => '1B',
        ]);
        $family = Family::create([
            'family_name' => '1A Family',
            'purok_id' => $purok->id,
            'subgroup_id' => $subgroup->id,
        ]);
        $otherFamily = Family::create([
            'family_name' => '1B Family',
            'purok_id' => $purok->id,
            'subgroup_id' => $otherSubgroup->id,
        ]);
        $familyCitizen = $this->createCitizen('Purok 1', null, $family->id);
        $otherCitizen = $this->createCitizen('Purok 1', $otherSubgroup->id);
        $conflictingAssignmentCitizen = $this->createCitizen('Purok 1', $subgroup->id, $otherFamily->id);

        $this->actingAs($admin)
            ->from(route('admin.demographics'))
            ->delete(route('admin.subgroups.citizens.destroy', $subgroup), [
                'confirmation' => '1a - Purok 1',
                'current_password' => 'password',
                'demographics_tab' => 'subgroups',
            ])
            ->assertRedirect(route('admin.demographics'))
            ->assertSessionHasErrors('confirmation');

        $this->assertDatabaseHas('citizens', ['id' => $familyCitizen->id]);

        $this->actingAs($admin)
            ->from(route('admin.demographics'))
            ->delete(route('admin.subgroups.citizens.destroy', $subgroup), [
                'confirmation' => '1A - Purok 1',
                'current_password' => 'incorrect',
                'demographics_tab' => 'subgroups',
            ])
            ->assertRedirect(route('admin.demographics'))
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('citizens', ['id' => $familyCitizen->id]);

        $this->actingAs($admin)
            ->from(route('admin.demographics'))
            ->delete(route('admin.subgroups.citizens.destroy', $subgroup), [
                'confirmation' => '1A - Purok 1',
                'current_password' => 'password',
                'demographics_tab' => 'subgroups',
            ])
            ->assertRedirect(route('admin.demographics'))
            ->assertSessionHas('demographics_tab', 'subgroups');

        $this->assertDatabaseMissing('citizens', ['id' => $familyCitizen->id]);
        $this->assertDatabaseHas('citizens', ['id' => $otherCitizen->id]);
        $this->assertDatabaseHas('citizens', ['id' => $conflictingAssignmentCitizen->id]);
        $this->assertDatabaseHas('families', ['id' => $family->id]);
    }

    public function test_non_admin_cannot_access_the_citizen_purge_route(): void
    {
        $bhw = User::factory()->create([
            'role' => User::ROLE_BHW,
            'must_change_password' => false,
        ]);
        $purok = Purok::create(['name' => 'Purok 1']);

        $this->actingAs($bhw)
            ->delete(route('admin.puroks.citizens.destroy', $purok), [
                'confirmation' => 'Purok 1',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_delete_subgroup_after_its_citizens_are_removed_and_empty_families_are_cleaned_up(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'must_change_password' => false,
        ]);
        $purok = Purok::create(['name' => 'Purok 1']);
        $subgroup = PurokSubgroup::create([
            'purok_id' => $purok->id,
            'name' => '1B',
        ]);
        $family = Family::create([
            'family_name' => '1B Family',
            'purok_id' => $purok->id,
            'subgroup_id' => $subgroup->id,
        ]);
        $citizen = $this->createCitizen('Purok 1', $subgroup->id, $family->id);

        $this->actingAs($admin)
            ->from(route('admin.demographics'))
            ->delete(route('admin.subgroups.destroy', $subgroup), ['current_password' => 'password'])
            ->assertRedirect(route('admin.demographics'))
            ->assertSessionHasErrors('subgroup');

        $this->assertDatabaseHas('families', ['id' => $family->id]);
        $this->assertDatabaseHas('purok_subgroups', ['id' => $subgroup->id]);

        $citizen->delete();

        $this->actingAs($admin)
            ->from(route('admin.demographics'))
            ->delete(route('admin.subgroups.destroy', $subgroup), ['current_password' => 'password'])
            ->assertRedirect(route('admin.demographics'))
            ->assertSessionHas('success', 'Purok subgroup deleted successfully!');

        $this->assertDatabaseMissing('families', ['id' => $family->id]);
        $this->assertDatabaseMissing('purok_subgroups', ['id' => $subgroup->id]);
    }

    private function createCitizen(string $purok, ?int $subgroupId = null, ?int $familyId = null): citizens
    {
        return citizens::create([
            'Citizen_FName' => 'Test',
            'Citizen_LName' => 'Citizen',
            'Citizen_BirthDate' => '1990-01-01',
            'Citizen_ContactNo' => '09123456789',
            'Citizen_Age' => 35,
            'Citizen_Purok' => $purok,
            'subgroup_id' => $subgroupId,
            'family_id' => $familyId,
        ]);
    }

    private function createTestSchema(): void
    {
        foreach (['health_records', 'families', 'citizens', 'purok_subgroups', 'puroks', 'users'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('role')->default('bhw');
            $table->unsignedBigInteger('citizen_id')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->timestamps();
        });

        Schema::create('puroks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('boundary')->nullable();
            $table->timestamps();
        });

        Schema::create('purok_subgroups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purok_id');
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('family_name');
            $table->unsignedBigInteger('purok_id');
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->timestamps();
        });

        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->string('Citizen_FName');
            $table->string('Citizen_LName');
            $table->date('Citizen_BirthDate');
            $table->string('Citizen_ContactNo');
            $table->integer('Citizen_Age');
            $table->string('Citizen_Purok')->nullable();
            $table->string('Citizen_Sex')->nullable();
            $table->boolean('Citizen_Pregnant')->nullable();
            $table->boolean('Citizen_InPills')->nullable();
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->unsignedBigInteger('family_id')->nullable();
            $table->timestamps();
        });

        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->constrained('citizens')->cascadeOnDelete();
            $table->text('diagnosis');
            $table->timestamps();
        });
    }
}
