<?php

namespace Tests\Feature;

use App\Imports\CitizensImport;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CitizensImportFormattingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // The importer uses an explicit cost of 8 for its shared temporary password.
        config(['hashing.bcrypt.rounds' => 8]);

        Schema::create('citizens', function (Blueprint $table) {
            $table->id();
            foreach (['Citizen_FName', 'Citizen_MName', 'Citizen_LName', 'Citizen_BirthDate', 'Citizen_ContactNo', 'Citizen_Sex', 'Citizen_Purok', 'qr_token'] as $column) {
                $table->string($column)->nullable();
            }
            $table->integer('Citizen_Age')->nullable();
            $table->unsignedBigInteger('subgroup_id')->nullable();
            $table->boolean('Citizen_Pregnant')->default(false);
            $table->boolean('Citizen_InPills')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'email', 'username', 'password', 'role'] as $column) {
                $table->string($column)->nullable();
            }
            $table->unsignedBigInteger('citizen_id');
            $table->boolean('must_change_password');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function row(array $values = []): array
    {
        return array_replace([
            'first_name' => '  josh   adrian ',
            'middle_name' => '',
            'last_name' => 'DE LA PEÑA JR.',
            'birthdate' => '2000-01-11',
            'contact_number' => '+639123456789',
        ], $values);
    }

    public function test_import_saves_capitalized_names_and_allows_blank_middle_name(): void
    {
        $import = new CitizensImport();
        $citizen = $import->model($this->row());

        $citizen->refresh();
        $this->assertSame('Josh Adrian', $citizen->Citizen_FName);
        $this->assertSame('', $citizen->Citizen_MName);
        $this->assertSame('De La Peña Jr.', $citizen->Citizen_LName);
        $this->assertSame('09123456789', $citizen->Citizen_ContactNo);
        $this->assertSame('Josh Adrian De La Peña Jr.', User::first()->name);
        $this->assertSame(1, $import->createdAccounts);
    }

    #[DataProvider('invalidNames')]
    public function test_invalid_required_name_skips_row(string $value): void
    {
        $import = new CitizensImport();
        $this->assertNull($import->model($this->row(['first_name' => $value])));
        $this->assertSame(1, $import->skippedRows);
        $this->assertDatabaseCount('citizens', 0);
        $this->assertDatabaseCount('users', 0);
    }

    public static function invalidNames(): array
    {
        return array_map(fn ($name) => [$name], ['Josh@', 'Josh2', 'J. Adrian', 'Josh-Adrian', "O'Neil", 'Josh.', 'Josh Jr. Adrian']);
    }

    public function test_invalid_optional_middle_name_and_contact_are_left_blank(): void
    {
        $import = new CitizensImport();
        $citizen = $import->model($this->row(['middle_name' => 'A.', 'contact_number' => '09123abc789']));

        $this->assertSame('', $citizen->Citizen_MName);
        $this->assertSame('', $citizen->Citizen_ContactNo);
        $this->assertSame(0, $import->skippedRows);
        $this->assertStringContainsString('Middle name', implode(' ', $import->issues));
        $this->assertStringContainsString('Contact number', implode(' ', $import->issues));
    }

    #[DataProvider('contacts')]
    public function test_contact_formats_are_normalized_or_reported(mixed $input, string $expected): void
    {
        $import = new CitizensImport();
        $citizen = $import->model($this->row(['middle_name' => 'reYES', 'last_name' => 'santos sr.', 'contact_number' => $input]));

        $this->assertSame($expected, $citizen->Citizen_ContactNo);
        $this->assertSame('Reyes', $citizen->Citizen_MName);
        $this->assertSame('Santos Sr.', $citizen->Citizen_LName);
        $this->assertSame($input !== '' && $expected === '', str_contains(implode(' ', $import->issues), 'Contact number'));
    }

    public static function contacts(): array
    {
        return [
            ['09123456789', '09123456789'],
            [9123456789.0, '09123456789'],
            ['639123456789', '09123456789'],
            ['+63 912-345-6789', '09123456789'],
            ['', ''],
            ['08123456789', ''],
            ['0912345678', ''],
            ['091234567890', ''],
            ['0912.345.6789', ''],
            ['09123@56789', ''],
        ];
    }
}
