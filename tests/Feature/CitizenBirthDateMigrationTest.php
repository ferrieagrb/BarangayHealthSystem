<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CitizenBirthDateMigrationTest extends TestCase
{
    public function test_migration_allows_unknown_birth_dates_and_ages_without_losing_existing_data(): void
    {
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            $table->date('Citizen_BirthDate');
            $table->integer('Citizen_Age');
        });
        DB::table('citizens')->insert(['Citizen_BirthDate' => '2000-01-11', 'Citizen_Age' => 26]);

        $migration = require database_path('migrations/2026_11_18_000002_make_citizen_birth_date_and_age_optional.php');
        $migration->up();

        $this->assertDatabaseHas('citizens', ['Citizen_BirthDate' => '2000-01-11', 'Citizen_Age' => 26]);
        DB::table('citizens')->insert(['Citizen_BirthDate' => null, 'Citizen_Age' => null]);
        $this->assertDatabaseHas('citizens', ['Citizen_BirthDate' => null, 'Citizen_Age' => null]);

        $migration->down();
        $this->assertDatabaseHas('citizens', ['Citizen_BirthDate' => null, 'Citizen_Age' => null]);
    }
}
