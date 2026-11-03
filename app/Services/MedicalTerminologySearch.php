<?php

namespace App\Services;

use App\Models\HealthRecord;
use App\Models\MaintenanceRecord;
use App\Models\MedicationRecord;
use App\Models\User;
use App\Models\citizens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;

class MedicalTerminologySearch
{
    private const MAX_RESULTS = 8;

    public function search(string $type, string $query, User $user): array
    {
        $query = trim($query);

        return match ($type) {
            'medicine' => $this->searchMedicines($query, $user),
            'vaccine' => $this->searchVaccines($query),
            'diagnosis' => $this->searchDiagnoses($query, $user),
        };
    }

    private function searchMedicines(string $query, User $user): array
    {
        $available = false;
        $message = null;
        $suggestions = [];

        try {
            $suggestions = $this->rxNormSuggestions($query);
            $available = true;
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            $this->logProviderFailure('RxNorm', $exception);
            $message = 'Online medicine suggestions are unavailable. You can enter a medicine manually.';
        }

        $suggestions = array_merge($suggestions, $this->localMedicineSuggestions($query, $user));

        return $this->response($suggestions, $available, $message);
    }

    private function searchVaccines(string $query): array
    {
        $available = false;
        $message = null;
        $cvxRecords = [];

        try {
            $cvxRecords = $this->cvxRecords();
            $available = true;
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            $this->logProviderFailure('CDC CVX', $exception);
            $message = 'Online vaccine terminology is unavailable. Use an available vaccine option.';
        }

        $suggestions = [];
        $mappedCvxCodes = [];
        $vaccines = config('vaccination.vaccines', []);
        $aliases = config('medical_terminology.vaccine_aliases', []);

        foreach ($vaccines as $code => $vaccine) {
            $terms = array_values(array_unique(array_filter(array_map(
                'strval',
                array_merge([$code, $vaccine['label'] ?? ''], $aliases[$code] ?? [])
            ))));
            $rank = $this->bestTermRank($query, $terms);
            if ($rank > 3) {
                continue;
            }

            $cvx = $this->matchingCvxRecord([$code, $vaccine['label'] ?? ''], $cvxRecords);
            if ($cvx) {
                $mappedCvxCodes[] = $cvx['code'];
            }
            $suggestions[] = [
                'name' => $vaccine['label'],
                'code' => (string) $code,
                'classification_code' => $cvx['code'] ?? null,
                'official_name' => $cvx['official_name'] ?? null,
                'formulation' => $cvx['formulation'] ?? null,
                'formulation_notes' => $cvx['notes'] ?? null,
                'source' => $cvx ? 'CDC CVX' : 'BHEAMS vaccination options',
                'recognition_status' => 'recognized',
                'eligibility_status' => 'age_based',
                'selection_token' => $cvx ? $this->vaccineSelectionToken($cvx, (string) $code) : null,
                'match_rank' => $rank,
                'source_rank' => $cvx ? 0 : 1,
            ];
        }

        foreach ($cvxRecords as $record) {
            $code = (string) ($record['code'] ?? '');
            $name = trim((string) ($record['short'] ?? ''));
            $officialName = trim((string) ($record['full'] ?? ''));
            $formulationNotes = trim((string) ($record['notes'] ?? ''));
            if ($code === '' || in_array($code, $mappedCvxCodes, true) || ($name === '' && $officialName === '')) {
                continue;
            }

            $name = $name !== '' ? $name : $officialName;
            $rank = $this->bestTermRank($query, [$name, $officialName, $code]);
            if ($rank > 3) {
                continue;
            }

            $conditional = $this->isConditionallyEligibleVaccine($name . ' ' . $officialName);
            $selection = [
                'code' => $code,
                'short' => $name,
                'full' => $officialName !== '' ? $officialName : $name,
                'notes' => $formulationNotes,
            ];
            $suggestions[] = [
                'name' => $name,
                'code' => null,
                'classification_code' => $code,
                'official_name' => $selection['full'],
                'formulation' => $selection['full'],
                'formulation_notes' => $formulationNotes !== '' ? $formulationNotes : null,
                'source' => 'CDC CVX',
                'recognition_status' => 'recognized',
                'eligibility_status' => $conditional ? 'conditional' : 'unknown',
                'eligibility_reason' => $conditional
                    ? 'Eligibility may depend on clinical risk factors; age alone does not establish eligibility.'
                    : 'No validated BHEAMS age rule is configured for this CVX vaccine.',
                'selection_token' => $this->vaccineSelectionToken($selection, null),
                'match_rank' => $rank,
                'source_rank' => 2,
            ];
        }

        return $this->response($suggestions, $available, $message);
    }

    public function resolveVaccineSelection(string $token): ?array
    {
        [$payload, $signature] = array_pad(explode('.', $token, 2), 2, null);
        if (!is_string($signature) || $payload === '' || !hash_equals(
            hash_hmac('sha256', $payload, (string) config('app.key')),
            $signature
        )) {
            return null;
        }

        $json = base64_decode(strtr($payload, '-_', '+/'), true);
        $selection = is_string($json) ? json_decode($json, true) : null;
        if (!is_array($selection)
            || !isset($selection['cvx_code'], $selection['short_name'], $selection['official_name'], $selection['issued_at'])
            || !ctype_digit((string) $selection['cvx_code'])
            || !is_string($selection['short_name'])
            || !is_string($selection['official_name'])
            || (int) $selection['issued_at'] > now()->timestamp + 300
            || (int) $selection['issued_at'] < now()->subDays(30)->timestamp
        ) {
            return null;
        }

        $vaccineCode = $selection['vaccine_code'] ?? null;
        if ($vaccineCode !== null && !array_key_exists((string) $vaccineCode, config('vaccination.vaccines', []))) {
            return null;
        }

        return [
            'cvx_code' => (string) $selection['cvx_code'],
            'short_name' => $selection['short_name'],
            'official_name' => $selection['official_name'],
            'formulation' => is_string($selection['formulation'] ?? null) ? $selection['formulation'] : null,
            'formulation_notes' => is_string($selection['formulation_notes'] ?? null) ? $selection['formulation_notes'] : null,
            'vaccine_code' => $vaccineCode !== null ? (string) $vaccineCode : null,
            'eligibility_status' => is_string($selection['eligibility_status'] ?? null) ? $selection['eligibility_status'] : 'unknown',
            'eligibility_reason' => is_string($selection['eligibility_reason'] ?? null) ? $selection['eligibility_reason'] : null,
        ];
    }

    private function vaccineSelectionToken(array $record, ?string $vaccineCode): string
    {
        $payload = json_encode([
            'cvx_code' => trim((string) ($record['code'] ?? '')),
            'short_name' => trim((string) ($record['short'] ?? '')),
            'official_name' => trim((string) ($record['full'] ?? $record['short'] ?? '')),
            'formulation' => trim((string) ($record['full'] ?? $record['short'] ?? '')),
            'vaccine_code' => $vaccineCode,
            'eligibility_status' => $vaccineCode ? 'age_based' : ($this->isConditionallyEligibleVaccine(
                (string) ($record['short'] ?? '') . ' ' . (string) ($record['full'] ?? '')
            ) ? 'conditional' : 'unknown'),
            'eligibility_reason' => $vaccineCode
                ? null
                : ($this->isConditionallyEligibleVaccine((string) ($record['short'] ?? '') . ' ' . (string) ($record['full'] ?? ''))
                    ? 'Eligibility may depend on clinical risk factors; age alone does not establish eligibility.'
                    : 'No validated BHEAMS age rule is configured for this CVX vaccine.'),
            'issued_at' => now()->timestamp,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $encoded = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

        return $encoded . '.' . hash_hmac('sha256', $encoded, (string) config('app.key'));
    }

    private function isConditionallyEligibleVaccine(string $name): bool
    {
        $normalized = $this->normalize($name);
        foreach (config('vaccination.conditional_terms', []) as $term) {
            if (str_contains($normalized, $this->normalize((string) $term))) {
                return true;
            }
        }

        return false;
    }

    private function searchDiagnoses(string $query, User $user): array
    {
        $available = false;
        $message = null;
        $suggestions = [];
        $clientId = config('services.who_icd.client_id');
        $clientSecret = config('services.who_icd.client_secret');

        if (!is_string($clientId) || $clientId === '' || !is_string($clientSecret) || $clientSecret === '') {
            $message = 'WHO ICD-11 suggestions are not configured. You can enter a diagnosis manually.';
        } else {
            try {
                $cacheKey = 'medical-terminology:who:search:' . hash('sha256', implode('|', [
                    $clientId,
                    config('services.who_icd.release', '2026-01'),
                    mb_strtolower($query),
                ]));
                $suggestions = Cache::remember(
                    $cacheKey,
                    now()->addMinutes((int) config('medical_terminology.cache_minutes', 1440)),
                    fn () => $this->whoIcdSuggestions($query, $clientId, $clientSecret)
                );
                $available = true;
            } catch (ConnectionException|RequestException|RuntimeException $exception) {
                $this->logProviderFailure('WHO ICD-11', $exception);
                $message = 'Online diagnosis suggestions are unavailable. You can enter a diagnosis manually.';
            }
        }

        $suggestions = array_merge($suggestions, $this->localDiagnosisSuggestions($query, $user));

        return $this->response($suggestions, $available, $message);
    }

    private function rxNormSuggestions(string $query): array
    {
        $key = 'medical-terminology:rxnorm:' . hash('sha256', mb_strtolower($query));

        return Cache::remember($key, now()->addMinutes((int) config('medical_terminology.cache_minutes', 1440)), function () use ($query) {
            $response = Http::acceptJson()
                ->timeout((int) config('medical_terminology.timeout', 5))
                ->get(config('services.rxnorm.approximate_url'), [
                    'term' => $query,
                    'maxEntries' => self::MAX_RESULTS,
                    'option' => 1,
                ])
                ->throw();

            $candidates = $response->json('approximateGroup.candidate', []);
            if (!is_array($candidates)) {
                throw new RuntimeException('RxNorm returned an invalid terminology response.');
            }

            $rxNormCandidates = array_values(array_filter($candidates, fn ($candidate) =>
                is_array($candidate)
                && !empty($candidate['name'])
                && strtoupper((string) ($candidate['source'] ?? '')) === 'RXNORM'
            ));

            return array_map(fn ($candidate) => [
                'name' => trim((string) $candidate['name']),
                'code' => null,
                'classification_code' => null,
                'description' => null,
                'source' => 'NLM RxNorm',
                'match_rank' => $this->termRank($query, (string) $candidate['name']),
                'source_rank' => 1,
            ], $rxNormCandidates);
        });
    }

    private function cvxRecords(): array
    {
        $key = 'medical-terminology:cdc:cvx:active';

        return Cache::remember($key, now()->addMinutes((int) config('medical_terminology.cache_minutes', 1440)), function () {
            $response = Http::accept('application/xml')
                ->timeout((int) config('medical_terminology.timeout', 5))
                ->get(config('services.cdc_cvx.url'))
                ->throw();

            $previousErrorHandling = libxml_use_internal_errors(true);
            try {
                $xml = simplexml_load_string($response->body(), SimpleXMLElement::class, LIBXML_NONET);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previousErrorHandling);
            }

            if (!$xml || !isset($xml->CVXInfo)) {
                throw new RuntimeException('CDC returned an invalid CVX terminology response.');
            }

            $records = [];
            foreach ($xml->CVXInfo as $record) {
                if (strcasecmp(trim((string) $record->Status), 'Active') !== 0) {
                    continue;
                }

                $records[] = [
                    'short' => trim((string) $record->ShortDescription),
                    'full' => trim((string) $record->FullVaccinename),
                    'code' => trim((string) $record->CVXCode),
                    'notes' => trim((string) $record->Notes),
                ];
            }

            return $records;
        });
    }

    private function matchingCvxRecord(array $terms, array $records): ?array
    {
        $matches = [];

        foreach ($records as $record) {
            foreach ($terms as $term) {
                foreach ([$record['short'] ?? '', $record['full'] ?? ''] as $recordTerm) {
                    if ($this->normalize($term) !== '' && $this->normalize($term) === $this->normalize((string) $recordTerm)) {
                        $matches[] = [
                            'code' => $record['code'],
                            'short' => $record['short'] ?? '',
                            'full' => $record['full'] ?? '',
                            'official_name' => $record['full'] ?: $record['short'],
                            'formulation' => $record['full'] ?: $record['short'],
                            'notes' => $record['notes'] ?? '',
                            'rank' => 0,
                        ];
                        break 2;
                    }
                }
            }
        }

        usort($matches, fn ($left, $right) => $left['rank'] <=> $right['rank']);

        if ($matches === []) {
            return null;
        }

        $bestRank = $matches[0]['rank'];
        $bestMatches = array_values(array_filter($matches, fn ($match) => $match['rank'] === $bestRank));

        return count($bestMatches) === 1 ? $bestMatches[0] : null;
    }

    private function whoIcdSuggestions(string $query, string $clientId, string $clientSecret): array
    {
        $token = $this->whoAccessToken($clientId, $clientSecret);
        $release = config('services.who_icd.release', '2026-01');
        $response = Http::withToken($token)
            ->acceptJson()
            ->withHeaders([
                'API-Version' => 'v2',
                'Accept-Language' => 'en',
            ])
            ->timeout((int) config('medical_terminology.timeout', 5))
            ->get(rtrim(config('services.who_icd.api_url'), '/') . '/icd/release/11/' . rawurlencode($release) . '/mms/search', [
                'q' => $query,
                'useFlexisearch' => 'true',
                'flatResults' => 'true',
            ])
            ->throw();

        $entities = $response->json('destinationEntities', []);
        if (!is_array($entities)) {
            throw new RuntimeException('WHO ICD-11 returned an invalid terminology response.');
        }

        return array_values(array_filter(array_map(function ($entity) use ($query) {
            if (!is_array($entity) || empty($entity['title'])) {
                return null;
            }

            return [
                'name' => trim((string) $entity['title']),
                'code' => null,
                'classification_code' => isset($entity['theCode']) ? trim((string) $entity['theCode']) : null,
                'description' => null,
                'source' => 'WHO ICD-11',
                'match_rank' => $this->termRank($query, (string) $entity['title']),
                'source_rank' => 0,
            ];
        }, $entities)));
    }

    private function whoAccessToken(string $clientId, string $clientSecret): string
    {
        $key = 'medical-terminology:who:token:' . hash('sha256', $clientId);

        return Cache::remember($key, now()->addMinutes(55), function () use ($clientId, $clientSecret) {
            $response = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->acceptJson()
                ->timeout((int) config('medical_terminology.timeout', 5))
                ->post(config('services.who_icd.token_url'), [
                    'grant_type' => 'client_credentials',
                    'scope' => 'icdapi_access',
                ])
                ->throw();

            $token = $response->json('access_token');
            if (!is_string($token) || $token === '') {
                throw new RuntimeException('WHO ICD-11 did not provide an access token.');
            }

            return $token;
        });
    }

    private function localMedicineSuggestions(string $query, User $user): array
    {
        $terms = [];
        $citizenIds = null;

        foreach ([
            [MedicationRecord::class, 'medication_records'],
            [MaintenanceRecord::class, 'maintenance_records'],
        ] as [$model, $table]) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $citizenIds ??= $this->accessibleCitizenIds($user);
            $names = $model::query()
                ->whereIn('citizen_id', (clone $citizenIds))
                ->whereNotNull('medicine_name')
                ->where('medicine_name', '<>', '')
                ->distinct()
                ->orderBy('medicine_name')
                ->limit(500)
                ->pluck('medicine_name');

            foreach ($names as $name) {
                $rank = $this->termRank($query, (string) $name);
                if ($rank <= 3) {
                    $terms[] = [
                        'name' => trim((string) $name),
                        'code' => null,
                        'classification_code' => null,
                        'description' => null,
                        'source' => 'BHEAMS records',
                        'match_rank' => $rank,
                        'source_rank' => 0,
                    ];
                }
            }
        }

        if (Schema::hasTable('supplies')) {
            $inventoryNames = DB::table('supplies')
                ->where('quantity', '>', 0)
                ->where(function ($categoryQuery) {
                    foreach (['medicine', 'medication', 'drug', 'pharma'] as $keyword) {
                        $categoryQuery->orWhereRaw('LOWER(category) LIKE ?', ['%' . $keyword . '%']);
                    }
                })
                ->whereNotNull('name')
                ->where('name', '<>', '')
                ->distinct()
                ->orderBy('name')
                ->limit(500)
                ->pluck('name');

            foreach ($inventoryNames as $name) {
                $rank = $this->termRank($query, (string) $name);
                if ($rank <= 3) {
                    $terms[] = [
                        'name' => trim((string) $name),
                        'code' => null,
                        'classification_code' => null,
                        'description' => null,
                        'source' => 'BHEAMS inventory',
                        'match_rank' => $rank,
                        'source_rank' => 0,
                    ];
                }
            }
        }

        return $terms;
    }

    private function localDiagnosisSuggestions(string $query, User $user): array
    {
        if (!Schema::hasTable('health_records')) {
            return [];
        }

        $diagnoses = HealthRecord::query()
            ->whereIn('citizen_id', $this->accessibleCitizenIds($user))
            ->whereNotNull('diagnosis')
            ->where('diagnosis', '<>', '')
            ->distinct()
            ->orderBy('diagnosis')
            ->limit(500)
            ->pluck('diagnosis');

        $suggestions = [];
        foreach ($diagnoses as $diagnosis) {
            $rank = $this->termRank($query, (string) $diagnosis);
            if ($rank <= 3) {
                $suggestions[] = [
                    'name' => trim((string) $diagnosis),
                    'code' => null,
                    'classification_code' => null,
                    'description' => null,
                    'source' => 'BHEAMS records',
                    'match_rank' => $rank,
                    'source_rank' => 1,
                ];
            }
        }

        return $suggestions;
    }

    private function accessibleCitizenIds(User $user): Builder
    {
        return app(BhwCitizenAccess::class)
            ->scopeCitizens(citizens::query(), $user)
            ->select('citizens.id');
    }

    private function response(array $suggestions, bool $available, ?string $message): array
    {
        $suggestions = array_values(array_filter($suggestions, fn ($suggestion) =>
            is_array($suggestion)
            && !empty($suggestion['name'])
            && ($suggestion['match_rank'] ?? 99) <= 3
        ));

        usort($suggestions, fn ($left, $right) =>
            [$left['match_rank'], $left['source_rank'], mb_strtolower($left['name'])]
            <=> [$right['match_rank'], $right['source_rank'], mb_strtolower($right['name'])]
        );

        $unique = [];
        foreach ($suggestions as $suggestion) {
            $key = $this->normalize($suggestion['name']);
            if (($suggestion['selection_token'] ?? null) !== null && ($suggestion['code'] ?? null) === null) {
                $key .= '|cvx:' . (string) ($suggestion['classification_code'] ?? '');
            }
            if ($key === '') {
                continue;
            }

            if (isset($unique[$key])) {
                $sources = array_filter(array_unique(array_merge(
                    explode(' · ', $unique[$key]['source']),
                    explode(' · ', $suggestion['source'])
                )));
                $unique[$key]['source'] = implode(' · ', $sources);
                $unique[$key]['classification_code'] ??= $suggestion['classification_code'] ?? null;
                foreach (['official_name', 'formulation', 'formulation_notes', 'selection_token', 'eligibility_reason'] as $field) {
                    $unique[$key][$field] ??= $suggestion[$field] ?? null;
                }
                continue;
            }

            $suggestion['match_type'] = match ($suggestion['match_rank']) {
                0 => 'exact',
                3 => 'approximate',
                default => 'partial',
            };
            unset($suggestion['match_rank'], $suggestion['source_rank']);
            $unique[$key] = $suggestion;
        }

        return [
            'suggestions' => array_slice(array_values($unique), 0, self::MAX_RESULTS),
            'online_available' => $available,
            'availability_message' => $message,
        ];
    }

    private function bestTermRank(string $query, array $terms): int
    {
        $rank = 99;
        foreach ($terms as $term) {
            $rank = min($rank, $this->termRank($query, (string) $term));
        }

        return $rank;
    }

    private function termRank(string $query, string $candidate): int
    {
        $query = $this->normalize($query);
        $candidate = $this->normalize($candidate);

        if ($query === '' || $candidate === '') {
            return 99;
        }
        if ($query === $candidate) {
            return 0;
        }
        if (str_starts_with($candidate, $query)) {
            return 1;
        }
        if (str_contains($candidate, $query) || (mb_strlen($candidate) >= 4 && str_contains($query, $candidate))) {
            return 2;
        }

        if (mb_strlen($query) >= 4) {
            foreach (explode(' ', $candidate) as $word) {
                if (mb_strlen($word) < 4) {
                    continue;
                }

                similar_text($query, $word, $similarity);
                if ($similarity >= 74) {
                    return 3;
                }
            }
        }

        return 99;
    }

    private function normalize(string $value): string
    {
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', Str::lower(trim($value)));

        return trim(is_string($normalized) ? $normalized : '');
    }

    private function logProviderFailure(string $provider, \Throwable $exception): void
    {
        Log::warning('Medical terminology provider request failed.', [
            'provider' => $provider,
            'exception' => $exception::class,
        ]);
    }
}
