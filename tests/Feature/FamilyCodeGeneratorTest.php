<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Services\FamilyCodeGenerator;
use App\Support\FamilyCode;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FamilyCodeGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('puroks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('purok_subgroups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purok_id');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('family_name');
            $table->unsignedBigInteger('purok_id');
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->string('family_code')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_it_generates_separate_sequences_from_the_selected_purok_subgroup(): void
    {
        $purokFour = Purok::create(['name' => 'Purok 4']);
        $subgroupFourA = PurokSubgroup::create([
            'purok_id' => $purokFour->id,
            'name' => 'Purok 4A',
        ]);
        $purokThree = Purok::create(['name' => 'Purok 3']);
        $subgroupThreeC = PurokSubgroup::create([
            'purok_id' => $purokThree->id,
            'name' => 'Purok 3C',
        ]);
        $generator = new FamilyCodeGenerator;

        $this->assertSame('F4A-0001', $generator->create('First Family', $subgroupFourA)->family_code);
        $this->assertSame('F4A-0002', $generator->create('Second Family', $subgroupFourA)->family_code);
        $this->assertSame('F3C-0001', $generator->create('Third Family', $subgroupThreeC)->family_code);
    }

    public function test_it_combines_a_mother_purok_with_a_letter_only_subgroup(): void
    {
        $this->assertSame('4A', FamilyCode::prefixFor('Purok 4', 'A'));
    }

    public function test_archiving_fills_the_gap_only_in_the_same_area(): void
    {
        $purok = Purok::create(['name' => 'Purok 1']);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => '1A']);
        $otherSubgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => '1B']);
        $generator = new FamilyCodeGenerator;
        $first = $generator->create('First', $subgroup);
        $middle = $generator->create('Middle', $subgroup);
        $last = $generator->create('Last', $subgroup);
        $other = $generator->create('Other', $otherSubgroup);

        $generator->archive($middle);

        $this->assertSame('F1A-0001', $first->fresh()->family_code);
        $this->assertSame('F1A-0002', $last->fresh()->family_code);
        $this->assertSame('F1B-0001', $other->fresh()->family_code);
        $this->assertTrue($middle->fresh()->trashed());
        $this->assertNull($middle->fresh()->family_code);
        $this->assertSame('F1A-0003', $generator->create('New', $subgroup)->family_code);
    }

    public function test_restoring_a_family_assigns_the_next_number_without_duplicates(): void
    {
        $purok = Purok::create(['name' => 'Purok 1']);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => '1A']);
        $generator = new FamilyCodeGenerator;
        $archived = $generator->create('Malabad', $subgroup);
        $current = $generator->create('Fabia Family', $subgroup);

        $generator->archive($archived);
        $this->assertSame('F1A-0001', $current->fresh()->family_code);

        $generator->restore($archived->fresh());
        $this->assertFalse($archived->fresh()->trashed());
        $this->assertSame('F1A-0002', $archived->fresh()->family_code);
        $this->assertSame('F1A-0001', $current->fresh()->family_code);

        // A restored record can be archived again, including after another family is removed.
        $generator->archive($current);
        $this->assertSame('F1A-0001', $archived->fresh()->family_code);
        $generator->archive($archived);
        $this->assertSame('F1A-0001', $generator->create('New household', $subgroup)->family_code);
    }

    public function test_migration_renumbers_existing_current_families_and_releases_archived_codes(): void
    {
        $purok = Purok::create(['name' => 'Purok 1']);
        $subgroup = PurokSubgroup::create(['purok_id' => $purok->id, 'name' => '1A']);
        $generator = new FamilyCodeGenerator;
        $archived = $generator->create('Malabad', $subgroup);
        $current = $generator->create('Fabia Family', $subgroup);
        $archived->delete(); // Data saved before automatic renumbering was introduced.

        $migration = require database_path('migrations/2026_11_18_000001_renumber_active_family_codes.php');
        $migration->up();

        $this->assertSame('F1A-0001', $current->fresh()->family_code);
        $this->assertNull($archived->fresh()->family_code);
        $migration->up(); // Running the repair again keeps the same numbering.
        $this->assertSame('F1A-0001', $current->fresh()->family_code);
        $this->assertSame(2, Family::withTrashed()->count());
    }

    public function test_it_strips_the_p_prefix_from_subgroup_labels_like_p1a(): void
    {
        $this->assertSame('1A', FamilyCode::prefixFor('Purok 1', 'P1A'));
    }

    public function test_it_uses_the_mother_purok_number_and_only_the_subgroup_letter(): void
    {
        $this->assertSame('1A', FamilyCode::prefixFor('Purok 1', 'Purok 3A'));
    }

    public function test_migration_backfills_codes_for_existing_families(): void
    {
        Schema::drop('families');
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('family_name');
            $table->unsignedBigInteger('purok_id');
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $purokFour = Purok::create(['name' => 'Purok 4']);
        $subgroupFourA = PurokSubgroup::create([
            'purok_id' => $purokFour->id,
            'name' => 'Purok 4A',
        ]);
        $purokThree = Purok::create(['name' => 'Purok 3']);
        $subgroupThreeC = PurokSubgroup::create([
            'purok_id' => $purokThree->id,
            'name' => 'Purok 3C',
        ]);

        foreach ([
            ['family_name' => 'First Family', 'purok_id' => $purokFour->id, 'subgroup_id' => $subgroupFourA->id],
            ['family_name' => 'Second Family', 'purok_id' => $purokFour->id, 'subgroup_id' => $subgroupFourA->id],
            ['family_name' => 'Third Family', 'purok_id' => $purokThree->id, 'subgroup_id' => $subgroupThreeC->id],
        ] as $family) {
            DB::table('families')->insert($family + [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $migration = require database_path('migrations/2026_11_04_000001_add_family_code_to_families_table.php');
        $migration->up();

        $this->assertSame(
            ['F4A-0001', 'F4A-0002', 'F3C-0001'],
            DB::table('families')->orderBy('id')->pluck('family_code')->all()
        );
    }

    public function test_normalization_migration_corrects_existing_codes_and_resets_each_area_sequence(): void
    {
        $purokOne = Purok::create(['name' => 'Purok 1']);
        $subgroupThreeA = PurokSubgroup::create([
            'purok_id' => $purokOne->id,
            'name' => 'Purok 3A',
        ]);
        $subgroupA = PurokSubgroup::create([
            'purok_id' => $purokOne->id,
            'name' => 'A',
        ]);

        DB::table('families')->insert([
            [
                'family_name' => 'First Family',
                'purok_id' => $purokOne->id,
                'subgroup_id' => $subgroupThreeA->id,
                'family_code' => 'F13A-0001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'family_name' => 'Second Family',
                'purok_id' => $purokOne->id,
                'subgroup_id' => $subgroupA->id,
                'family_code' => 'F1A-0001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $migration = require database_path('migrations/2026_11_04_000002_normalize_family_codes.php');
        $migration->up();

        $this->assertSame(
            ['F1A-0001', 'F1A-0002'],
            DB::table('families')->orderBy('id')->pluck('family_code')->all()
        );
    }
}
