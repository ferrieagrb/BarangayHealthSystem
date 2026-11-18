<?php

namespace Tests\Feature;

use App\Http\Middleware\SystemMaintenance;
use App\Http\Middleware\TrackPageViews;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InputCapitalizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([SystemMaintenance::class, TrackPageViews::class]);

        Route::middleware('web')->match(['GET', 'POST', 'PUT'], '/test/capitalization', function (Request $request) {
            return response()->json([
                'input' => $request->except('photo'),
                'query' => $request->query(),
                'photo_name' => $request->file('photo')?->getClientOriginalName(),
            ]);
        });
    }

    public function test_form_names_and_descriptions_are_capitalized_while_technical_values_are_preserved(): void
    {
        $protected = [
            'email' => 'Josh.Adrian@example.test', 'username' => 'josh.adrian',
            'password' => 'sEcret123!', 'password_confirmation' => 'sEcret123!',
            'confirm_password' => 'sEcret123!', 'current_password' => 'sEcret123!',
            'Citizen_ContactNo' => '+639123456789', 'Citizen_Purok' => 'Purok 1a',
            'family_code' => 'F1A-0001', 'qr_token' => 'abc-def-ghi',
            'vaccine_selection_token' => 'abc123.signature',
            'dosage' => '500 mg', 'frequency_period' => 'per day',
            'Citizen_Sex' => 'Female', 'role' => 'bhw', 'status' => 'approved',
            'confirmation' => 'EXACT confirmation Name',
        ];
        $response = $this->post('/test/capitalization', $protected + [
            'Citizen_FName' => 'jUaN carlos', 'Citizen_MName' => 'DE LA',
            'Citizen_LName' => 'peña', 'family_name' => 'dela cruz family',
            'address' => 'purok 1 barangay san jose',
            'title' => 'barangay health day', 'description' => 'free check-up for BHW volunteers',
            'medicine_name' => 'paracetamol 500 mg oral tablet',
            'vaccine_name' => 'mRNA COVID-19 vaccine',
            'photo' => UploadedFile::fake()->create('lowercase-photo.jpg', 1, 'image/jpeg'),
        ])->assertOk()
            ->assertJsonPath('input.Citizen_FName', 'Juan Carlos')
            ->assertJsonPath('input.Citizen_MName', 'De La')
            ->assertJsonPath('input.Citizen_LName', 'Peña')
            ->assertJsonPath('input.family_name', 'Dela Cruz Family')
            ->assertJsonPath('input.address', 'Purok 1 Barangay San Jose')
            ->assertJsonPath('input.title', 'Barangay Health Day')
            ->assertJsonPath('input.description', 'Free Check-Up For BHW Volunteers')
            ->assertJsonPath('input.medicine_name', 'Paracetamol 500 mg Oral Tablet')
            ->assertJsonPath('input.vaccine_name', 'mRNA COVID-19 Vaccine')
            ->assertJsonPath('photo_name', 'lowercase-photo.jpg');

        foreach ($protected as $field => $value) {
            $response->assertJsonPath('input.'.$field, $value);
        }
    }

    public function test_json_and_nested_dynamic_fields_are_normalized_without_changing_types(): void
    {
        $this->putJson('/test/capitalization', [
            'name' => 'barangay supplies',
            'packs' => [['supplier' => 'san jose supplies', 'unit' => 'mL', 'serial_number' => 'abc-def', 'quantity' => 5]],
            'schedules' => [['notes' => 'bring BCG card', 'vaccine_name' => 'BCG', 'dose_number' => 'Dose 1']],
            'Citizen_Pregnant' => false, 'Citizen_MName' => null,
        ])->assertOk()
            ->assertJsonPath('input.name', 'Barangay Supplies')
            ->assertJsonPath('input.packs.0.supplier', 'San Jose Supplies')
            ->assertJsonPath('input.packs.0.unit', 'mL')
            ->assertJsonPath('input.packs.0.serial_number', 'abc-def')
            ->assertJsonPath('input.packs.0.quantity', 5)
            ->assertJsonPath('input.schedules.0.notes', 'Bring BCG Card')
            ->assertJsonPath('input.schedules.0.vaccine_name', 'BCG')
            ->assertJsonPath('input.schedules.0.dose_number', 'Dose 1')
            ->assertJsonPath('input.Citizen_Pregnant', false)
            ->assertJsonPath('input.Citizen_MName', null);
    }

    public function test_search_parameters_are_not_rewritten(): void
    {
        $this->get('/test/capitalization?name=lowercase&search=abc-def')
            ->assertOk()->assertJsonPath('input.name', 'lowercase')
            ->assertJsonPath('input.search', 'abc-def');

        $this->post('/test/capitalization?name=lowercase', ['title' => 'health day'])
            ->assertOk()->assertJsonPath('query.name', 'lowercase')
            ->assertJsonPath('input.title', 'Health Day');
    }
}
