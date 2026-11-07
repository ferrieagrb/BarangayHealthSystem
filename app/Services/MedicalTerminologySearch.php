<?php

namespace App\Services;

use App\Models\User;
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

    public function refreshOfficialReferences(): array
    {
        $path = config('medical_terminology.reference_path');
        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $download = fn ($url) => Http::timeout(60)->connectTimeout(10)->get($url)->throw()->body();
        $csv = $download($data['sources']['doh-pnf']['data_url']);
        $html = $download($data['sources']['who-eml']['data_url']);
        $terms = [];
        $counts = ['doh-pnf' => 0, 'who-eml' => 0];
        $add = function ($name, $source, $type = 'medicine') use (&$terms, &$counts) {
            $name = trim(html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($name === '' || mb_strlen($name) > 255) return;
            $key = $type . '|' . mb_strtolower($name);
            $terms[$key] ??= ['name' => $name, 'type' => $type, 'sources' => []];
            if (!in_array($source, $terms[$key]['sources'], true)) {
                $terms[$key]['sources'][] = $source;
                $counts[$source]++;
            }
        };
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csv); rewind($stream);
        $headers = fgetcsv($stream);
        $column = is_array($headers) ? array_search('Molecule', $headers, true) : false;
        if ($column === false) throw new RuntimeException('DOH reference format changed; old data preserved.');
        while (($row = fgetcsv($stream)) !== false) $add($row[$column] ?? '', 'doh-pnf');
        fclose($stream);
        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors(); libxml_use_internal_errors($previous);
        $xpath = new \DOMXPath($document);
        foreach ($xpath->query('//h5[contains(@class,"medicine-name")]') as $node) {
            $add($node->getAttribute('title'), 'who-eml');
        }
        foreach ($xpath->query('//span[contains(@class,"indication-text")]') as $node) {
            $add($node->textContent, 'who-eml', 'diagnosis');
        }
        if ($counts['doh-pnf'] < 300 || $counts['who-eml'] < 500) {
            throw new RuntimeException('Official reference response was incomplete; old data preserved.');
        }
        $data['retrieved_on'] = now('Asia/Manila')->toDateString();
        $data['terms'] = array_values($terms);
        $temporary = $path . '.tmp';
        file_put_contents($temporary, json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        rename($temporary, $path);
        return $counts;
    }

    public function search(string $type, string $query, User $user, ?string $category = null): array
    {
        $query = trim($query);

        return match ($type) {
            'medicine' => $this->searchMedicines($query, $user),
            'vaccine' => $this->searchVaccines($query),
            'diagnosis' => $this->searchDiagnoses($query, $user),
            'supply' => $this->searchSupplies($query, $category ?? '', $user),
        };
    }

    public function supplyType(string $category): string
    {
        $category = $this->normalize($category);
        if (preg_match('/vaccin|immuniz|immunis|bakuna/', $category)) return 'vaccine';
        if (preg_match('/medicin|medicat|drug|pharma|gamot|vitamin|supplement/', $category)) return 'medicine';
        return 'supply';
    }

    private function searchSupplies(string $query, string $category, User $user): array
    {
        $type = $this->supplyType($category);
        if ($type !== 'supply') {
            return $this->search($type, $query, $user) + ['terminology_type' => $type];
        }

        $suggestions = [];
        if (Schema::hasTable('supplies')) {
            // Local catalog entries are only for ordinary supplies, never medical spelling.
            $names = DB::table('supplies')
                ->whereRaw('LOWER(TRIM(category)) = ?', [mb_strtolower(trim($category))])
                ->when(Schema::hasColumn('supplies', 'deleted_at'), fn ($builder) => $builder->whereNull('deleted_at'))
                ->whereNotNull('name')->distinct()->orderBy('name')->limit(500)->pluck('name');
            foreach ($names as $name) {
                $rank = $this->termRank($query, $name);
                if ($rank <= 3) $suggestions[] = [
                    'name' => $name, 'source' => 'Local supply catalog (not verified terminology)',
                    'match_rank' => $rank, 'source_rank' => 0,
                ];
            }
        }
        return $this->response($suggestions, false, null) + ['terminology_type' => 'supply'];
    }

    private function searchMedicines(string $query, User $user): array
    {
        $references = $this->officialReferenceSuggestions('medicine', $query);
        if ($references !== []) {
            return $this->response($references, false, 'Suggestions from published official medicine references; select the correct name.')
                + ['reference_available' => true];
        }
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

        return $this->response($suggestions, $available, $message);
    }

    private function officialReferenceSuggestions(string $type, string $query): array
    {
        $path = config('medical_terminology.reference_path');
        $data = is_string($path) && is_file($path)
            ? json_decode(file_get_contents($path), true)
            : ['terms' => [], 'sources' => []];
        $brandPath = config('medical_terminology.brand_reference_path');
        if ($type === 'medicine' && is_string($brandPath) && is_file($brandPath)) {
            $brands = json_decode(file_get_contents($brandPath), true);
            $data['terms'] = array_merge($data['terms'], $brands['terms'] ?? []);
            $data['sources'] = array_merge($data['sources'], $brands['sources'] ?? []);
            foreach ($brands['terms'] ?? [] as $brand) {
                $data['source_dates'][$brand['sources'][0]] = $brands['retrieved_on'];
            }
        }
        $suggestions = [];
        foreach ($data['terms'] ?? [] as $term) {
            if (($term['type'] ?? null) !== $type) continue;
            $rank = $this->bestTermRank($query, array_filter([$term['name'], $term['generic_name'] ?? null]));
            // Minor errors in a partial prefix still suggest real, sourced names.
            $normalized = $this->normalize($query);
            if ($rank > 3 && mb_strlen($normalized) >= 4) {
                $prefix = mb_substr($this->normalize($term['name']), 0, mb_strlen($normalized));
                if (levenshtein($normalized, $prefix) === 1) $rank = 3;
            }
            if ($rank > 3) continue;
            $sources = array_map(fn ($key) => $data['sources'][$key]['label'], $term['sources']);
            $suggestions[] = [
                'name' => $term['name'], 'source' => implode(' · ', $sources),
                'generic_name' => $term['generic_name'] ?? null,
                'source_url' => $data['sources'][$term['sources'][0]]['publication'],
                'reference_date' => $data['source_dates'][$term['sources'][0]] ?? $data['retrieved_on'], 'match_rank' => $rank, 'source_rank' => 0,
            ];
        }
        return $suggestions;
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

        if (!$available || $suggestions === []) {
            try {
                $suggestions = $this->nlmConditionSuggestions($query);
                $available = true;
                $message = null;
            } catch (ConnectionException|RequestException|RuntimeException $exception) {
                $this->logProviderFailure('NLM Medical Conditions', $exception);
                $message = 'Online diagnosis suggestions are unavailable. You can enter a diagnosis manually.';
            }
        }

        if ($suggestions === []) {
            try {
                $mesh = $this->meshDiagnosisSuggestions($query);
                $suggestions = $mesh;
                $available = true;
                $message = null;
            } catch (ConnectionException|RequestException|RuntimeException $exception) {
                $this->logProviderFailure('NLM MeSH', $exception);
            }
        }

        if (!$available) {
            $references = $this->officialReferenceSuggestions('diagnosis', $query);
            if ($references !== []) {
                return $this->response($references, false, 'Live lookup unavailable; showing WHO published condition terminology.')
                    + ['reference_available' => true];
            }
        }

        return $this->response($suggestions, $available, $message);
    }

    private function nlmConditionSuggestions(string $query): array
    {
        $key = 'medical-terminology:nlm:conditions:' . hash('sha256', mb_strtolower($query));
        return Cache::remember($key, now()->addMinutes((int) config('medical_terminology.cache_minutes', 1440)), function () use ($query) {
            $parameters = ['terms' => $query, 'maxList' => self::MAX_RESULTS,
                'df' => 'primary_name,icd10cm_codes'];
            $fetch = fn ($parameters) => Http::acceptJson()
                ->timeout((int) config('medical_terminology.timeout', 5))
                ->get(config('services.nlm_conditions.url'), $parameters)->throw()->json();
            $data = $fetch($parameters);
            $approximate = false;
            if (is_array($data) && ($data[0] ?? 0) === 0 && mb_strlen($query) >= 4) {
                // Construct a bounded fuzzy query; never forward user query-string operators.
                $words = preg_split('/\s+/u', preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $query), -1, PREG_SPLIT_NO_EMPTY);
                $parameters['terms'] = '';
                $parameters['q'] = implode(' AND ', array_map(fn ($word) => $word . '~1', $words));
                $data = $fetch($parameters);
                $approximate = true;
            }
            if (!is_array($data) || !is_array($data[3] ?? null)) {
                throw new RuntimeException('NLM returned invalid condition terminology.');
            }
            $items = [];
            foreach ($data[3] as $index => $row) {
                if (!is_array($row) || !is_string($row[0] ?? null) || trim($row[0]) === '') continue;
                $items[] = [
                    'name' => trim($row[0]), 'classification_code' => $row[1] ?? null,
                    'code_system' => 'ICD-10-CM', 'source' => 'NLM Medical Conditions',
                    'match_rank' => $approximate ? 3 : min(2, $this->termRank($query, $row[0])),
                    'source_rank' => 1,
                    'provider_rank' => $index,
                ];
            }
            return $items;
        });
    }

    private function meshDiagnosisSuggestions(string $query): array
    {
        $key = 'medical-terminology:nlm:mesh:' . hash('sha256', mb_strtolower($query));
        return Cache::remember($key, now()->addMinutes((int) config('medical_terminology.cache_minutes', 1440)), function () use ($query) {
            $fetch = fn ($label, $limit) => Http::acceptJson()
                ->timeout((int) config('medical_terminology.timeout', 10))
                ->get(config('services.nlm_mesh.url'), ['label' => $label, 'match' => 'contains', 'limit' => $limit])
                ->throw()->json();
            $data = $fetch($query, self::MAX_RESULTS);
            $approximate = false;
            if ($data === [] && mb_strlen($query) >= 5) {
                // Search the longest word's stem, then rank against the whole input.
                // This finds multiword spelling errors without hardcoded disease names.
                $words = preg_split('/\s+/u', $this->normalize($query), -1, PREG_SPLIT_NO_EMPTY);
                usort($words, fn ($left, $right) => mb_strlen($right) <=> mb_strlen($left));
                $stem = mb_substr($words[0] ?? '', 0, 5);
                if (mb_strlen($stem) >= 4 && $stem !== $this->normalize($query)) {
                    $data = $fetch($stem, 50);
                    $approximate = true;
                }
            }
            if (!is_array($data) || !array_is_list($data)) {
                throw new RuntimeException('NLM MeSH returned invalid terminology.');
            }
            $items = [];
            foreach ($data as $row) {
                if (!is_array($row) || !is_string($row['label'] ?? null)
                    || !is_string($row['resource'] ?? null)
                    || !preg_match('~^https?://id\.nlm\.nih\.gov/mesh/(D\d+)$~', $row['resource'] ?? '', $identifier)) continue;
                $queryWords = explode(' ', $this->normalize($query));
                $candidateWords = explode(' ', $this->normalize($row['label']));
                foreach ($queryWords as $word) {
                    $matches = array_filter($candidateWords, fn ($candidate) =>
                        str_starts_with($candidate, $word)
                        || (mb_strlen($word) >= 5 && levenshtein($word, $candidate) <= 2)
                    );
                    if ($matches === []) continue 2;
                }
                $rank = $this->termRank($query, $row['label']);
                if ($approximate) $rank = 3;
                if ($rank > 3) continue;
                $items[] = ['name' => $row['label'], 'source' => 'NLM MeSH',
                    'source_url' => 'https://id.nlm.nih.gov/mesh/' . $identifier[1],
                    'classification_code' => $identifier[1], 'code_system' => 'MeSH',
                    'match_rank' => $approximate ? 3 : $rank, 'source_rank' => 2];
            }
            return $items;
        });
    }

    private function rxNormSuggestions(string $query): array
    {
        $key = 'medical-terminology:rxnorm:v2:' . hash('sha256', mb_strtolower($query));

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
                && is_string($candidate['name'] ?? null) && trim($candidate['name']) !== ''
                && is_string($candidate['source'] ?? null)
                && in_array(strtoupper((string) ($candidate['source'] ?? '')), ['RXNORM', 'ATC', 'USP', 'INN', 'BAN'], true)
            ));

            $items = array_map(fn ($candidate) => [
                'name' => trim((string) $candidate['name']),
                'code' => null,
                'classification_code' => null,
                'description' => null,
                'source' => 'NLM RxNorm' . (strtoupper($candidate['source']) === 'RXNORM' ? '' : ' / ' . strtoupper($candidate['source'])),
                'match_rank' => $this->termRank($query, (string) $candidate['name']),
                'source_rank' => 1,
            ], $rxNormCandidates);

            if (array_filter($items, fn ($item) => $item['match_rank'] <= 3) === [] && mb_strlen($query) >= 4) {
                $spelling = Http::acceptJson()->timeout((int) config('medical_terminology.timeout', 5))
                    ->get(config('services.rxnorm.spelling_url'), ['name' => $query])->throw()
                    ->json('suggestionGroup.suggestionList.suggestion', []);
                foreach (is_array($spelling) ? $spelling : [] as $name) {
                    if (!is_string($name) || trim($name) === '') continue;
                    $items[] = ['name' => trim($name), 'source' => 'NLM RxNorm',
                        'match_rank' => 3, 'source_rank' => 1];
                }
            }
            return $items;
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
                'name' => trim(html_entity_decode(strip_tags((string) $entity['title']), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
                'code' => null,
                'classification_code' => isset($entity['theCode']) ? trim((string) $entity['theCode']) : null,
                'description' => null,
                'source' => 'WHO ICD-11',
                'code_system' => 'ICD-11',
                'match_rank' => min(3, $this->termRank($query, strip_tags((string) $entity['title']))),
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

    private function response(array $suggestions, bool $available, ?string $message): array
    {
        $suggestions = array_values(array_filter($suggestions, fn ($suggestion) =>
            is_array($suggestion)
            && !empty($suggestion['name'])
            && ($suggestion['match_rank'] ?? 99) <= 3
        ));

        usort($suggestions, fn ($left, $right) =>
            [$left['match_rank'], $left['source_rank'], $left['provider_rank'] ?? 0, mb_strtolower($left['name'])]
            <=> [$right['match_rank'], $right['source_rank'], $right['provider_rank'] ?? 0, mb_strtolower($right['name'])]
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
            unset($suggestion['match_rank'], $suggestion['source_rank'], $suggestion['provider_rank']);
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
