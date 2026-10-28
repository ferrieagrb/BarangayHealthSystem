<?php

namespace App\Services;

use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BhwDiseaseMonitoringService
{
    public function __construct(private readonly BhwCitizenAccess $citizenAccess)
    {
    }

    public function build(
        User $user,
        Carbon $start,
        Carbon $end,
        ?string $diagnosis,
        ?int $purokId,
        ?int $subgroupId
    ): array {
        $canViewAll = $this->citizenAccess->canViewAll($user);
        $assignedSubgroupIds = $this->citizenAccess->assignedSubgroupIds($user);

        $baseQuery = fn () => $this->query($canViewAll, $assignedSubgroupIds);
        $applyFilters = function (Builder $query, bool $includeDate = true) use (
            $start,
            $end,
            $diagnosis,
            $purokId,
            $subgroupId
        ): Builder {
            if ($includeDate) {
                $query->whereBetween('hr.record_date', [$start->toDateString(), $end->toDateString()]);
            }
            if ($diagnosis !== null && $diagnosis !== '') {
                $query->whereRaw('LOWER(TRIM(hr.diagnosis)) = ?', [$diagnosis]);
            }
            if ($purokId !== null) {
                $query->where('area_purok.id', $purokId);
            }
            if ($subgroupId !== null) {
                $query->whereRaw('COALESCE(csg.id, fsg.id) = ?', [$subgroupId]);
            }

            return $query;
        };

        $recordsQuery = $applyFilters($baseQuery());
        $totalRecords = (clone $recordsQuery)->count('hr.id');
        $uniqueCitizens = (clone $recordsQuery)->distinct()->count('hr.citizen_id');

        $periodDays = $start->diffInDays($end) + 1;
        $previousEnd = $start->copy()->subDay();
        $previousStart = $previousEnd->copy()->subDays($periodDays - 1);
        $previousQuery = $applyFilters($baseQuery(), false)
            ->whereBetween('hr.record_date', [$previousStart->toDateString(), $previousEnd->toDateString()]);
        $previousRecords = $previousQuery->count('hr.id');
        $previousCitizens = (clone $previousQuery)->distinct()->count('hr.citizen_id');
        $privacySuppressed = $uniqueCitizens < 3;

        $trendGranularity = $start->diffInDays($end) <= 62 ? 'day' : 'month';
        $trendBucketExpression = $this->trendBucketExpression($trendGranularity);
        $trendRows = $privacySuppressed
            ? collect()
            : (clone $recordsQuery)
                ->selectRaw($trendBucketExpression . ' AS period_bucket, COUNT(hr.id) AS record_count, COUNT(DISTINCT hr.citizen_id) AS citizen_count')
                ->groupByRaw($trendBucketExpression)
                ->orderBy('period_bucket')
                ->get()
                ->keyBy('period_bucket');
        $trendLabels = [];
        $trendRecordCounts = [];
        $trendCitizenCounts = [];
        $trendCursor = $trendGranularity === 'day'
            ? $start->copy()->startOfDay()
            : $start->copy()->startOfMonth();
        $trendLastBucket = $trendGranularity === 'day'
            ? $end->copy()->startOfDay()
            : $end->copy()->startOfMonth();

        while ($trendCursor->lte($trendLastBucket)) {
            $bucketKey = $trendGranularity === 'day'
                ? $trendCursor->toDateString()
                : $trendCursor->format('Y-m');
            $bucket = $trendRows->get($bucketKey);
            $visibleBucket = !$bucket || (int) $bucket->citizen_count >= 3;

            $trendLabels[] = $trendCursor->format($trendGranularity === 'day' ? 'M j' : 'M Y');
            $trendRecordCounts[] = !$bucket ? 0 : ($visibleBucket ? (int) $bucket->record_count : null);
            $trendCitizenCounts[] = !$bucket ? 0 : ($visibleBucket ? (int) $bucket->citizen_count : null);
            $trendCursor->add($trendGranularity === 'day' ? '1 day' : '1 month');
        }

        $diagnosisRankings = (clone $recordsQuery)
            ->selectRaw('LOWER(TRIM(hr.diagnosis)) AS diagnosis_key, MIN(TRIM(hr.diagnosis)) AS diagnosis, COUNT(*) AS record_count, COUNT(DISTINCT hr.citizen_id) AS citizen_count')
            ->groupByRaw('LOWER(TRIM(hr.diagnosis))')
            ->havingRaw('COUNT(DISTINCT hr.citizen_id) >= 3')
            ->orderByDesc('record_count')
            ->limit(10)
            ->get();

        $mapRows = (clone $recordsQuery)
            ->selectRaw('COALESCE(csg.id, fsg.id) AS subgroup_id, area_purok.id AS purok_id, COUNT(*) AS record_count, COUNT(DISTINCT hr.citizen_id) AS citizen_count')
            ->groupByRaw('COALESCE(csg.id, fsg.id), area_purok.id')
            ->get();
        $purokIds = $mapRows->pluck('purok_id')->filter()->unique();
        $subgroupIds = $mapRows->pluck('subgroup_id')->filter()->unique();
        $puroks = Purok::query()->whereIn('id', $purokIds)->get()->keyBy('id');
        $subgroups = PurokSubgroup::query()
            ->with('purok')
            ->whereIn('id', $subgroupIds)
            ->get()
            ->keyBy('id');

        $mapAreas = [];
        foreach ($mapRows as $row) {
            $subgroup = $subgroups->get($row->subgroup_id);
            $purok = $puroks->get($row->purok_id);
            $target = $subgroup && $this->hasMapLocation($subgroup)
                ? $subgroup
                : $purok;

            if (!$target || !$this->hasMapLocation($target)) {
                continue;
            }

            $type = $target instanceof PurokSubgroup ? 'subgroup' : 'purok';
            $key = $type . '-' . $target->id;
            if (!isset($mapAreas[$key])) {
                $mapAreas[$key] = [
                    'type' => $type,
                    'id' => $target->id,
                    'purok_id' => $type === 'subgroup' ? $target->purok_id : $target->id,
                    'name' => $target->name,
                    'purok' => $type === 'subgroup' ? $target->purok?->name : $target->name,
                    'location' => $target->latitude !== null && $target->longitude !== null
                        ? ['lat' => (float) $target->latitude, 'lng' => (float) $target->longitude]
                        : null,
                    'boundary' => $target->boundary,
                    'records_count' => 0,
                    'citizens_count' => 0,
                ];
            }
            $mapAreas[$key]['records_count'] += (int) $row->record_count;
            $mapAreas[$key]['citizens_count'] += (int) $row->citizen_count;
        }

        $visibleMapAreas = array_values(array_filter(
            $mapAreas,
            fn (array $area) => $area['citizens_count'] >= 3
        ));
        $mappedRecords = array_sum(array_column($visibleMapAreas, 'records_count'));

        $visibleAreaKeys = $mapRows
            ->filter(fn ($row) => (int) $row->citizen_count >= 3)
            ->map(fn ($row) => $row->subgroup_id
                ? 'subgroup-' . $row->subgroup_id
                : 'purok-' . ($row->purok_id ?? 'unassigned'))
            ->unique()
            ->all();

        $areaRankings = (clone $recordsQuery)
            ->selectRaw('COALESCE(csg.id, fsg.id) AS subgroup_id, area_purok.id AS purok_id, MIN(area_purok.name) AS purok_name, LOWER(TRIM(hr.diagnosis)) AS diagnosis_key, MIN(TRIM(hr.diagnosis)) AS diagnosis, COUNT(*) AS record_count, COUNT(DISTINCT hr.citizen_id) AS diagnosis_citizen_count')
            ->groupByRaw('COALESCE(csg.id, fsg.id), area_purok.id, LOWER(TRIM(hr.diagnosis))')
            ->orderByDesc('record_count')
            ->get()
            ->groupBy(fn ($row) => $row->subgroup_id ? 'subgroup-' . $row->subgroup_id : 'purok-' . ($row->purok_id ?? 'unassigned'))
            ->only($visibleAreaKeys)
            ->map(function ($rows) use ($subgroups) {
                $first = $rows->first();
                $subgroup = $subgroups->get($first->subgroup_id);
                $name = $subgroup
                    ? $subgroup->name . ' - ' . ($subgroup->purok?->name ?? $first->purok_name)
                    : ($first->purok_name ?: 'Unassigned');
                $total = $rows->sum('record_count');

                return [
                    'area' => $name,
                    'area_type' => $subgroup ? 'subgroup' : ($first->purok_id ? 'purok' : null),
                    'area_id' => $subgroup?->id ?? $first->purok_id,
                    'purok_id' => $first->purok_id,
                    'records_count' => (int) $total,
                    'top_diagnosis' => $rows
                        ->filter(fn ($row) => (int) $row->diagnosis_citizen_count >= 3)
                        ->sortByDesc('record_count')
                        ->first()?->diagnosis ?? 'Suppressed (<3 citizens)',
                ];
            })
            ->sortByDesc('records_count')
            ->take(10)
            ->values();

        $missingDateRecords = $applyFilters($baseQuery(), false)
            ->whereNull('hr.record_date')
            ->count('hr.id');

        $diagnosisOptions = $baseQuery()
            ->selectRaw('LOWER(TRIM(hr.diagnosis)) AS diagnosis_key, MIN(TRIM(hr.diagnosis)) AS diagnosis')
            ->groupByRaw('LOWER(TRIM(hr.diagnosis))')
            ->havingRaw('COUNT(DISTINCT hr.citizen_id) >= 3')
            ->orderBy('diagnosis')
            ->limit(100)
            ->get();

        $availablePuroks = $canViewAll
            ? Purok::query()->orderBy('name')->get()
            : Purok::query()
                ->whereHas('subgroups', fn ($query) => $query->whereIn('id', $assignedSubgroupIds))
                ->orderBy('name')
                ->get();
        $availableSubgroups = PurokSubgroup::query()
            ->when(!$canViewAll, fn ($query) => $query->whereIn('id', $assignedSubgroupIds))
            ->when($purokId, fn ($query) => $query->where('purok_id', $purokId))
            ->with('purok:id,name')
            ->orderBy('name')
            ->get();

        $activeRecords = $totalRecords;
        $previousChange = $previousRecords > 0
            ? round((($activeRecords - $previousRecords) / $previousRecords) * 100, 1)
            : null;
        $missingLocationRecords = max(0, $totalRecords - $mappedRecords);

        return [
            'totalRecords' => $totalRecords,
            'uniqueCitizens' => $uniqueCitizens,
            'previousRecords' => $previousRecords,
            'previousChange' => $previousCitizens >= 3 ? $previousChange : null,
            'previousSuppressed' => $previousRecords > 0 && $previousCitizens < 3,
            'privacySuppressed' => $privacySuppressed,
            'missingLocationRecords' => $privacySuppressed || ($missingLocationRecords > 0 && $missingLocationRecords < 3)
                ? null
                : $missingLocationRecords,
            'missingLocationSuppressed' => !$privacySuppressed && $missingLocationRecords > 0 && $missingLocationRecords < 3,
            'missingDateRecords' => $privacySuppressed || ($missingDateRecords > 0 && $missingDateRecords < 3)
                ? null
                : $missingDateRecords,
            'missingDateSuppressed' => !$privacySuppressed && $missingDateRecords > 0 && $missingDateRecords < 3,
            'diagnosisRankings' => $privacySuppressed ? collect() : $diagnosisRankings,
            'areaRankings' => $privacySuppressed ? collect() : $areaRankings,
            'mapAreas' => $privacySuppressed ? [] : $visibleMapAreas,
            'trend' => [
                'granularity' => $trendGranularity,
                'labels' => $privacySuppressed ? [] : $trendLabels,
                'recordCounts' => $privacySuppressed ? [] : $trendRecordCounts,
                'citizenCounts' => $privacySuppressed ? [] : $trendCitizenCounts,
            ],
            'diagnosisOptions' => $diagnosisOptions,
            'availablePuroks' => $availablePuroks,
            'availableSubgroups' => $availableSubgroups,
        ];
    }

    private function trendBucketExpression(string $granularity): string
    {
        $driver = DB::connection()->getDriverName();

        if ($granularity === 'day') {
            return match ($driver) {
                'pgsql' => 'hr.record_date::text',
                default => 'DATE(hr.record_date)',
            };
        }

        return match ($driver) {
            'mysql', 'mariadb' => "DATE_FORMAT(hr.record_date, '%Y-%m')",
            'pgsql' => "TO_CHAR(hr.record_date, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', hr.record_date)",
            default => throw new \RuntimeException('Unsupported database driver for disease trend aggregation.'),
        };
    }

    private function query(bool $canViewAll, Collection $assignedSubgroupIds): Builder
    {
        $query = DB::table('health_records AS hr')
            ->join('citizens AS c', 'c.id', '=', 'hr.citizen_id')
            ->leftJoin('families AS f', 'f.id', '=', 'c.family_id')
            ->leftJoin('purok_subgroups AS csg', 'csg.id', '=', 'c.subgroup_id')
            ->leftJoin('purok_subgroups AS fsg', 'fsg.id', '=', 'f.subgroup_id')
            ->leftJoin('puroks AS text_purok', function ($join) {
                $join->on(DB::raw('LOWER(TRIM(text_purok.name))'), '=', DB::raw('LOWER(TRIM(c.Citizen_Purok))'));
            })
            ->leftJoin('puroks AS area_purok', function ($join) {
                $join->on('area_purok.id', '=', DB::raw('COALESCE(csg.purok_id, fsg.purok_id, text_purok.id)'));
            })
            ->whereNotNull('hr.diagnosis')
            ->whereRaw("TRIM(hr.diagnosis) <> ''");

        if (!$canViewAll) {
            if ($assignedSubgroupIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn(DB::raw('COALESCE(csg.id, fsg.id)'), $assignedSubgroupIds);
            }
        }

        return $query;
    }

    private function hasMapLocation(Purok|PurokSubgroup $area): bool
    {
        $validCoordinates = is_numeric($area->latitude)
            && is_numeric($area->longitude)
            && $area->latitude >= -90
            && $area->latitude <= 90
            && $area->longitude >= -180
            && $area->longitude <= 180;
        if ($validCoordinates) {
            return true;
        }

        $boundary = $area->boundary;
        if (!is_array($boundary)) {
            return false;
        }
        if (($boundary['type'] ?? null) === 'Feature') {
            $boundary = $boundary['geometry'] ?? [];
        }

        return in_array($boundary['type'] ?? null, ['Polygon', 'MultiPolygon'], true)
            && !empty($boundary['coordinates']);
    }
}
