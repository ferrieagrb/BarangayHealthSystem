<?php

namespace Tests\Feature;

use App\Models\User;
use App\Http\Middleware\TrackPageViews;
use App\Http\Middleware\SystemMaintenance;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MedicalTerminologySearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([TrackPageViews::class, SystemMaintenance::class]);
        Cache::flush();
        config([
            'services.who_icd.client_id' => null,
            'services.who_icd.client_secret' => null,
        ]);
    }

    public function test_bhw_can_search_rxnorm_without_sending_citizen_data(): void
    {
        Http::fake([
            '*rxnav.nlm.nih.gov/REST/approximateTerm.json*' => Http::response([
                'approximateGroup' => [
                    'candidate' => [
                        ['name' => 'metformin', 'rxcui' => '6809', 'rank' => '1', 'source' => 'RXNORM'],
                    ],
                ],
            ]),
        ]);

        $response = $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search', [
            'type' => 'medicine',
            'q' => 'metformn',
        ]));

        $response->assertOk()
            ->assertJsonPath('suggestions.0.name', 'metformin')
            ->assertJsonPath('suggestions.0.source', 'NLM RxNorm')
            ->assertJsonPath('suggestions.0.match_type', 'approximate')
            ->assertJsonPath('online_available', true);

        Http::assertSent(fn (Request $request) =>
            str_contains($request->url(), 'rxnav.nlm.nih.gov/REST/approximateTerm.json')
            && str_contains($request->url(), 'term=metformn')
            && !str_contains($request->url(), 'citizen')
        );
    }

    public function test_rxnorm_brand_matches_are_exact_and_duplicate_terms_are_removed(): void
    {
        Http::fake([
            '*rxnav.nlm.nih.gov/REST/approximateTerm.json*' => Http::response([
                'approximateGroup' => [
                    'candidate' => [
                        ['name' => 'Glucophage', 'rxcui' => '151827', 'rank' => '1', 'source' => 'RXNORM'],
                        ['name' => 'Glucophage', 'rxcui' => '151827', 'rank' => '1', 'source' => 'RXNORM'],
                    ],
                ],
            ]),
        ]);

        $response = $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search', [
            'type' => 'medicine',
            'q' => 'Glucophage',
        ]));

        $response->assertOk()
            ->assertJsonPath('suggestions.0.name', 'Glucophage')
            ->assertJsonPath('suggestions.0.match_type', 'exact')
            ->assertJsonCount(1, 'suggestions');
    }

    public function test_vaccine_suggestions_use_active_cdc_codes_and_local_vaccine_labels(): void
    {
        Http::fake([
            '*www2a.cdc.gov/vaccines/iis/iisstandards/XML2.asp*' => Http::response(
                '<?xml version="1.0" encoding="ISO-8859-1"?><CVXCodes><CVXInfo><ShortDescription>BCG</ShortDescription><FullVaccinename>Bacillus Calmette-Guerin vaccine</FullVaccinename><CVXCode>19</CVXCode><Status>Active</Status></CVXInfo><CVXInfo><ShortDescription>BCG inactive</ShortDescription><FullVaccinename>Inactive vaccine</FullVaccinename><CVXCode>20</CVXCode><Status>Inactive</Status></CVXInfo></CVXCodes>',
                200,
                ['Content-Type' => 'text/xml']
            ),
        ]);

        $response = $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search', [
            'type' => 'vaccine',
            'q' => 'BCG',
        ]));

        $response->assertOk()
            ->assertJsonPath('suggestions.0.name', 'BCG')
            ->assertJsonPath('suggestions.0.classification_code', '19')
            ->assertJsonPath('suggestions.0.source', 'CDC CVX')
            ->assertJsonPath('suggestions.0.eligibility_status', 'age_based')
            ->assertJsonPath('online_available', true);

        $selection = app(\App\Services\MedicalTerminologySearch::class)
            ->resolveVaccineSelection($response->json('suggestions.0.selection_token'));
        $this->assertNotNull($selection);
        $this->assertSame('BCG', $selection['vaccine_code']);
    }

    public function test_unmapped_cdc_vaccine_suggestions_remain_recognized_without_assumed_age_eligibility(): void
    {
        Http::fake([
            '*www2a.cdc.gov/vaccines/iis/iisstandards/XML2.asp*' => Http::response(
                '<?xml version="1.0" encoding="ISO-8859-1"?><CVXCodes><CVXInfo><ShortDescription>Varicella</ShortDescription><FullVaccinename>Varicella vaccine, live</FullVaccinename><CVXCode>21</CVXCode><Notes>Live attenuated formulation</Notes><Status>Active</Status></CVXInfo></CVXCodes>',
                200,
                ['Content-Type' => 'text/xml']
            ),
        ]);

        $response = $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search', [
            'type' => 'vaccine',
            'q' => 'varicella',
        ]));

        $response->assertOk()
            ->assertJsonPath('suggestions.0.name', 'Varicella')
            ->assertJsonPath('suggestions.0.recognition_status', 'recognized')
            ->assertJsonPath('suggestions.0.eligibility_status', 'unknown')
            ->assertJsonPath('suggestions.0.code', null)
            ->assertJsonPath('suggestions.0.classification_code', '21')
            ->assertJsonPath('suggestions.0.official_name', 'Varicella vaccine, live')
            ->assertJsonPath('suggestions.0.formulation', 'Varicella vaccine, live')
            ->assertJsonPath('suggestions.0.formulation_notes', 'Live attenuated formulation')
            ->assertJsonStructure(['suggestions' => [['selection_token']]]);

        $codeSearch = $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search', [
            'type' => 'vaccine',
            'q' => '21',
        ]));
        $codeSearch->assertOk()
            ->assertJsonPath('suggestions.0.classification_code', '21')
            ->assertJsonPath('suggestions.0.official_name', 'Varicella vaccine, live');
    }

    public function test_who_icd_search_uses_server_credentials_and_returns_codes(): void
    {
        config([
            'services.who_icd.client_id' => 'test-client-id',
            'services.who_icd.client_secret' => 'test-client-secret',
        ]);

        Http::fake(function (Request $request) {
            if (str_contains($request->url(), 'icdaccessmanagement.who.int/connect/token')) {
                return Http::response(['access_token' => 'test-access-token', 'expires_in' => 3600]);
            }

            if (str_contains($request->url(), 'id.who.int/icd/release/11/2026-01/mms/search')) {
                return Http::response([
                    'destinationEntities' => [
                        ['title' => 'Diabetes mellitus', 'theCode' => '5A11'],
                    ],
                ]);
            }

            return Http::response([], 404);
        });

        $response = $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search', [
            'type' => 'diagnosis',
            'q' => 'diab',
        ]));

        $response->assertOk()
            ->assertJsonPath('suggestions.0.name', 'Diabetes mellitus')
            ->assertJsonPath('suggestions.0.classification_code', '5A11')
            ->assertJsonPath('suggestions.0.source', 'WHO ICD-11')
            ->assertJsonPath('online_available', true);

        Http::assertSent(fn (Request $request) =>
            str_contains($request->url(), 'id.who.int/icd/release/11/2026-01/mms/search')
            && !str_contains($request->url(), 'citizen')
        );
    }

    public function test_provider_outage_keeps_manual_medicine_entry_available(): void
    {
        Http::fake([
            '*rxnav.nlm.nih.gov/*' => Http::response([], 503),
        ]);

        $response = $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search', [
            'type' => 'medicine',
            'q' => 'metformin',
        ]));

        $response->assertOk()
            ->assertJsonPath('suggestions', [])
            ->assertJsonPath('online_available', false)
            ->assertJsonPath('availability_message', 'Online medicine suggestions are unavailable. You can enter a medicine manually.');
    }

    public function test_unrelated_provider_results_are_not_returned(): void
    {
        Http::fake([
            '*rxnav.nlm.nih.gov/REST/approximateTerm.json*' => Http::response([
                'approximateGroup' => [
                    'candidate' => [
                        ['name' => 'Pneumonia', 'rxcui' => 'test', 'rank' => '1', 'source' => 'RXNORM'],
                    ],
                ],
            ]),
        ]);

        $response = $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search', [
            'type' => 'medicine',
            'q' => 'aspirin',
        ]));

        $response->assertOk()
            ->assertJsonPath('suggestions', [])
            ->assertJsonPath('online_available', true);
    }

    public function test_who_credentials_are_optional_and_report_unavailable_status(): void
    {
        Http::fake();

        $response = $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search', [
            'type' => 'diagnosis',
            'q' => 'diabetes',
        ]));

        $response->assertOk()
            ->assertJsonPath('online_available', false)
            ->assertJsonPath('availability_message', 'WHO ICD-11 suggestions are not configured. You can enter a diagnosis manually.');

        Http::assertNothingSent();
    }

    public function test_search_requires_a_supported_type_and_two_characters(): void
    {
        $this->actingAs($this->bhw())
            ->postJson(route('bhw.medical-terminology.search', ['type' => 'symptom', 'q' => 'x']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'q']);
    }

    public function test_non_bhw_users_cannot_use_medical_terminology_search(): void
    {
        $this->actingAs(new User([
            'role' => 'superadmin',
            'must_change_password' => false,
        ]))
            ->postJson(route('bhw.medical-terminology.search', ['type' => 'medicine', 'q' => 'aspirin']))
            ->assertForbidden();
    }

    private function bhw(): User
    {
        return new User([
            'role' => 'bhw',
            'must_change_password' => false,
        ]);
    }
}
