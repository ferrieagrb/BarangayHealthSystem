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
        Http::preventStrayRequests();
        config([
            'medical_terminology.reference_path' => null,
            'medical_terminology.brand_reference_path' => null,
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
            '*spellingsuggestions.json*' => Http::response(['suggestionGroup' => ['suggestionList' => ['suggestion' => []]]]),
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

    public function test_diagnosis_uses_nlm_when_who_credentials_are_not_configured(): void
    {
        Http::fake(['*clinicaltables.nlm.nih.gov/*' => Http::response([
            1, ['1'], null, [['Diabetes mellitus', 'E11.9']],
        ])]);

        $response = $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search', [
            'type' => 'diagnosis',
            'q' => 'diabetes',
        ]));

        $response->assertOk()
            ->assertJsonPath('online_available', true)
            ->assertJsonPath('suggestions.0.source', 'NLM Medical Conditions')
            ->assertJsonPath('suggestions.0.code_system', 'ICD-10-CM')
            ->assertJsonPath('suggestions.0.classification_code', 'E11.9');

        Http::assertSentCount(1);
    }

    public function test_search_requires_a_supported_type_and_two_characters(): void
    {
        $this->actingAs($this->bhw())
            ->postJson(route('bhw.medical-terminology.search', ['type' => 'symptom', 'q' => 'x']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'q']);
    }

    public function test_superadmin_can_use_shared_terminology_without_accessing_patient_data(): void
    {
        Http::fake(['*rxnav.nlm.nih.gov/*' => Http::response([
            'approximateGroup' => ['candidate' => [['name' => 'aspirin', 'source' => 'RXNORM']]],
        ])]);
        $this->actingAs(new User([
            'role' => 'superadmin',
            'must_change_password' => false,
        ]))
            ->postJson(route('bhw.medical-terminology.search', ['type' => 'medicine', 'q' => 'aspirin']))
            ->assertOk()->assertJsonPath('suggestions.0.name', 'aspirin');
    }

    public function test_admin_inventory_can_search_medicine_spelling_suggestions(): void
    {
        Http::fake([
            '*rxnav.nlm.nih.gov/*' => Http::response([
                'approximateGroup' => ['candidate' => [
                    ['name' => 'metformin', 'source' => 'RXNORM'],
                ]],
            ]),
        ]);

        $this->actingAs(new User(['role' => 'admin', 'must_change_password' => false]))
            ->postJson(route('bhw.medical-terminology.search'), [
                'type' => 'medicine', 'q' => 'metformn',
            ])
            ->assertOk()
            ->assertJsonPath('suggestions.0.name', 'metformin')
            ->assertJsonPath('suggestions.0.match_type', 'approximate');
    }

    public function test_anonymous_users_cannot_search_medical_terminology(): void
    {
        Http::fake();
        $this->postJson(route('bhw.medical-terminology.search'), [
                'type' => 'diagnosis', 'q' => 'diabetes',
            ])->assertUnauthorized();
        Http::assertNothingSent();
    }

    public function test_unknown_account_roles_cannot_search_terminology(): void
    {
        Http::fake();
        $this->actingAs(new User(['role' => 'visitor', 'must_change_password' => false]))
            ->postJson(route('bhw.medical-terminology.search'), [
                'type' => 'medicine', 'q' => 'metformin',
            ])->assertForbidden();
        Http::assertNothingSent();
    }


    public function test_medicine_spelling_never_reads_inventory_or_patient_records(): void
    {
        Http::fake(['*rxnav.nlm.nih.gov/*' => Http::response([
            'approximateGroup' => ['candidate' => [['name' => 'amoxicillin', 'source' => 'RXNORM']]],
        ])]);
        $queries = [];
        \Illuminate\Support\Facades\DB::listen(function ($event) use (&$queries) { $queries[] = $event->sql; });
        $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search'), [
            'type' => 'medicine', 'q' => 'Amoxcillin',
        ])->assertOk()->assertJsonPath('suggestions.0.name', 'amoxicillin');
        $this->assertSame([], $queries, 'Medical spelling must not query local inventory or patient records.');
    }

    public function test_paracetamol_from_nlm_atc_vocabulary_is_supported(): void
    {
        Http::fake(['*rxnav.nlm.nih.gov/*' => Http::response([
            'approximateGroup' => ['candidate' => [['name' => 'paracetamol', 'source' => 'ATC']]],
        ])]);
        $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search'), [
            'type' => 'medicine', 'q' => 'Paracetmol',
        ])->assertOk()->assertJsonPath('suggestions.0.name', 'paracetamol')
            ->assertJsonPath('suggestions.0.source', 'NLM RxNorm / ATC')
            ->assertJsonPath('suggestions.0.match_type', 'approximate');
    }

    public function test_rxnorm_spelling_endpoint_is_used_when_approximate_search_has_no_results(): void
    {
        Http::fake([
            '*approximateTerm.json*' => Http::response(['approximateGroup' => ['candidate' => []]]),
            '*spellingsuggestions.json*' => Http::response([
                'suggestionGroup' => ['suggestionList' => ['suggestion' => ['amoxicillin']]],
            ]),
        ]);
        $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search'), [
            'type' => 'medicine', 'q' => 'Amoxcillin',
        ])->assertOk()->assertJsonPath('suggestions.0.name', 'amoxicillin')
            ->assertJsonPath('suggestions.0.match_type', 'approximate');
        Http::assertSentCount(2);
    }

    public function test_nlm_fuzzy_diagnosis_search_returns_standardized_codes(): void
    {
        Http::fake(function (Request $request) {
            return $request['terms'] === ''
                ? Http::response([1, ['368'], null, [['Pneumonia', 'J18.9']]])
                : Http::response([0, [], null, []]);
        });
        $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search'), [
            'type' => 'diagnosis', 'q' => 'Pnuemonia',
        ])->assertOk()->assertJsonPath('suggestions.0.name', 'Pneumonia')
            ->assertJsonPath('suggestions.0.classification_code', 'J18.9')
            ->assertJsonPath('suggestions.0.match_type', 'approximate');
        Http::assertSent(fn (Request $request) => ($request->data()['q'] ?? null) === 'Pnuemonia~1');
    }

    public function test_ordinary_supply_search_is_category_scoped_and_never_calls_medicine_api(): void
    {
        Http::fake();
        \Illuminate\Support\Facades\Schema::create('supplies', function ($table) {
            $table->string('name'); $table->string('category');
        });
        \Illuminate\Support\Facades\DB::table('supplies')->insert([
            ['name' => 'Gauze pads', 'category' => 'Dressings'],
            ['name' => 'Gauze brand', 'category' => 'Medicine'],
        ]);
        $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search'), [
            'type' => 'supply', 'category' => 'Dressings', 'q' => 'Gauze',
        ])->assertOk()->assertJsonCount(1, 'suggestions')
            ->assertJsonPath('suggestions.0.name', 'Gauze pads')
            ->assertJsonPath('terminology_type', 'supply');
        Http::assertNothingSent();
    }

    public function test_medicine_supply_search_uses_verified_online_terminology_only(): void
    {
        Http::fake(['*rxnav.nlm.nih.gov/*' => Http::response([
            'approximateGroup' => ['candidate' => [['name' => 'amoxicillin', 'source' => 'RXNORM']]],
        ])]);
        $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search'), [
            'type' => 'supply', 'category' => 'Medicines', 'q' => 'amox',
        ])->assertOk()->assertJsonPath('suggestions.0.name', 'amoxicillin')
            ->assertJsonPath('terminology_type', 'medicine');
    }

    public function test_shared_search_supports_citizens_without_exposing_patient_data(): void
    {
        Http::fake(['*rxnav.nlm.nih.gov/*' => Http::response([
            'approximateGroup' => ['candidate' => [['name' => 'aspirin', 'source' => 'RXNORM']]],
        ])]);
        $this->actingAs(new User(['role' => 'citizen', 'must_change_password' => false]))
            ->postJson(route('bhw.medical-terminology.search'), ['type' => 'medicine', 'q' => 'aspirin'])
            ->assertOk()->assertJsonPath('suggestions.0.name', 'aspirin');
    }

    public function test_supply_search_requires_category_and_diagnosis_outage_allows_manual_entry(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search'), [
            'type' => 'supply', 'q' => 'amox',
        ])->assertUnprocessable()->assertJsonValidationErrors('category');
        $this->postJson(route('bhw.medical-terminology.search'), ['type' => 'diagnosis', 'q' => 'diabetes'])
            ->assertOk()->assertJsonPath('online_available', false)->assertJsonPath('suggestions', []);
    }

    public function test_duplicate_catalog_spelling_variants_are_rejected_without_changing_stock(): void
    {
        \Illuminate\Support\Facades\Schema::create('supplies', function ($table) {
            $table->id(); $table->string('name'); $table->string('category');
            $table->integer('quantity'); $table->softDeletes();
        });
        \Illuminate\Support\Facades\DB::table('supplies')->insert([
            'name' => 'Vitamin-C', 'category' => 'Medicines', 'quantity' => 12,
        ]);
        $user = new class extends User {
            public function hasWriteAccess(string $feature): bool { return true; }
        };
        $user->role = 'bhw'; $user->must_change_password = false;
        $this->actingAs($user)->postJson(route('supplies.store'), [
            'name' => ' vitamin c ', 'category' => 'Medicines', 'min_stock' => 5,
        ])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->assertDatabaseCount('supplies', 1);
        $this->assertDatabaseHas('supplies', ['name' => 'Vitamin-C', 'quantity' => 12]);
    }

    public function test_repeated_asset_includes_emit_one_autocomplete_component(): void
    {
        $this->actingAs($this->bhw());
        $html = \Illuminate\Support\Facades\Blade::render(
            '@include("partials.medical-autocomplete-assets") @include("partials.medical-autocomplete-assets")'
        );
        $this->assertSame(1, substr_count($html, 'js/medical-autocomplete.js'));
        $this->assertSame(1, substr_count($html, 'name="medical-terminology-search-url"'));
    }

    public function test_published_who_doh_references_work_without_inventory_or_network(): void
    {
        config(['medical_terminology.reference_path' => resource_path('data/medical-terminology/official-references.json')]);
        Http::fake();
        foreach (['Amoxcillin' => 'Amoxicillin', 'Paracetmol' => 'Paracetamol'] as $query => $name) {
            $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search'), [
                'type' => 'medicine', 'q' => $query,
            ])->assertOk()->assertJsonPath('suggestions.0.name', $name)
                ->assertJsonPath('reference_available', true);
        }
        $this->postJson(route('bhw.medical-terminology.search'), ['type' => 'medicine', 'q' => 'flui'])
            ->assertOk()->assertJsonPath('reference_available', true)
            ->assertJsonCount(8, 'suggestions');
        Http::assertNothingSent();
    }

    public function test_who_condition_reference_survives_online_diagnosis_outage(): void
    {
        config(['medical_terminology.reference_path' => resource_path('data/medical-terminology/official-references.json')]);
        Http::fake(['*' => Http::response([], 503)]);
        $response = $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search'), [
            'type' => 'diagnosis', 'q' => 'Hypertenion',
        ])->assertOk()->assertJsonPath('reference_available', true);
        $this->assertStringContainsString('WHO', $response->json('suggestions.0.source'));
        $this->assertNull($response->json('suggestions.0.classification_code'), 'Do not fabricate ICD codes for reference names.');
    }

    public function test_verified_brand_generic_lookup_supports_fluimucil_exact_partial_and_typo(): void
    {
        config(['medical_terminology.brand_reference_path' => resource_path('data/medical-terminology/verified-brands.json')]);
        Http::fake();
        foreach (['fluimucil' => 'exact', 'flui' => 'partial', 'Flumucil' => 'approximate'] as $query => $match) {
            $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search'), [
                'type' => 'medicine', 'q' => $query,
            ])->assertOk()->assertJsonPath('suggestions.0.name', 'Fluimucil')
                ->assertJsonPath('suggestions.0.generic_name', 'Acetylcysteine')
                ->assertJsonPath('suggestions.0.match_type', $match)
                ->assertJsonPath('suggestions.0.source', 'Philippine FDA published drug record');
        }
        $this->postJson(route('bhw.medical-terminology.search'), [
            'type' => 'supply', 'category' => 'Medicine', 'q' => 'fluimucil',
        ])->assertOk()->assertJsonPath('suggestions.0.name', 'Fluimucil');
        $this->postJson(route('bhw.medical-terminology.search'), [
            'type' => 'medicine', 'q' => 'acetylcysteine',
        ])->assertOk()->assertJsonPath('suggestions.0.name', 'Fluimucil');
        Http::assertNothingSent();
    }

    public function test_mesh_fallback_matches_iga_vasculitis_and_multiword_typo_without_unrelated_conditions(): void
    {
        Http::fake([
            '*clinicaltables.nlm.nih.gov/*' => Http::response([0, [], null, []]),
            '*id.nlm.nih.gov/mesh/lookup/descriptor*' => function (Request $request) {
                if (($request->data()['label'] ?? '') === 'Iga Vascullitis') return Http::response([]);
                return Http::response([
                    ['resource' => 'http://id.nlm.nih.gov/mesh/D011695', 'label' => 'IgA Vasculitis'],
                    ['resource' => 'http://id.nlm.nih.gov/mesh/D000001', 'label' => 'Retinal Vasculitis'],
                ]);
            },
        ]);
        foreach (['IgA vasculitis' => 'exact', 'IgA vasc' => 'partial', 'Iga Vascullitis' => 'approximate'] as $query => $match) {
            $this->actingAs($this->bhw())->postJson(route('bhw.medical-terminology.search'), [
                'type' => 'diagnosis', 'q' => $query,
            ])->assertOk()->assertJsonCount(1, 'suggestions')
                ->assertJsonPath('suggestions.0.name', 'IgA Vasculitis')
                ->assertJsonPath('suggestions.0.code_system', 'MeSH')
                ->assertJsonPath('suggestions.0.classification_code', 'D011695')
                ->assertJsonPath('suggestions.0.match_type', $match);
        }
    }

    private function bhw(): User
    {
        return new User([
            'role' => 'bhw',
            'must_change_password' => false,
        ]);
    }
}
