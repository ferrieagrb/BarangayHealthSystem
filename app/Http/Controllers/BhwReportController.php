<?php

namespace App\Http\Controllers;

use App\Exports\CustomReportExport;
use App\Models\citizens;
use App\Models\Event;
use App\Models\HealthRecord;
use App\Models\MaintenanceRecord;
use App\Models\MedicationRecord;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Models\Referral;
use App\Models\Supply;
use App\Models\UserPermission;
use App\Models\Vaccination;
use App\Models\VaccinationRecord;
use App\Models\VaccinationSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BhwReportController extends Controller
{
    public function index(Request $request)
    {
        $availableSections = [
            'citizens' => 'Citizen Profiles',
            'health_records' => 'Health Records',
            'supplies' => 'Supply Inventory',
            'events' => 'Events and Schedules',
            'referrals' => 'Referrals',
            'age_by_purok' => 'Age-Bracket Breakdown per Purok',
            'immunization' => 'Immunization and Vaccination Coverage',
            'sex_by_purok' => 'Male and Female Counts by Purok',
            'pregnancy_by_purok' => 'Recorded Pregnancy by Purok',
            'diagnosis_summary' => 'Diagnosis Summary',
            'diagnosis_by_purok' => 'Diagnoses by Purok',
        ];

        $user = auth()->user();
        $hasReportAccess = UserPermission::where('user_id', $user->id)
            ->where('feature', 'reports')
            ->where('access', '!=', 'none')
            ->exists();
        abort_unless($hasReportAccess, 403, 'Reports access has not been granted to this BHW.');

        $granularReportAccess = UserPermission::where('user_id', $user->id)
            ->whereIn('feature', ['reports_demographics', 'reports_health', 'reports_operations'])
            ->where('access', '!=', 'none')
            ->pluck('feature');

        if ($granularReportAccess->isNotEmpty()) {
            $sectionGroups = [
                'reports_demographics' => ['citizens', 'age_by_purok', 'sex_by_purok', 'pregnancy_by_purok'],
                'reports_health' => ['health_records', 'immunization', 'diagnosis_summary', 'diagnosis_by_purok'],
                'reports_operations' => ['supplies', 'events', 'referrals'],
            ];
            $allowedSections = $granularReportAccess->flatMap(fn ($feature) => $sectionGroups[$feature] ?? [])->unique()->all();
            $availableSections = array_intersect_key($availableSections, array_flip($allowedSections));
        }

        $request->validate([
            'report_type' => ['nullable', 'string', Rule::in(array_keys($availableSections))],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['string', Rule::in(array_keys($availableSections))],
        ]);

        $requestedReport = $request->input('report_type');
        $selectedSections = $requestedReport && array_key_exists($requestedReport, $availableSections)
            ? [$requestedReport]
            : array_values(array_intersect(
                (array) $request->input('sections', ['citizens']),
                array_keys($availableSections)
            ));
        if (! $selectedSections) {
            $selectedSections = [array_key_first($availableSections) ?: 'citizens'];
        }
        $includeGraphics = $request->boolean('include_graphics');

        $citizenFilterSections = ['citizens', 'age_by_purok', 'sex_by_purok', 'pregnancy_by_purok', 'immunization'];
        $locationFilterSections = [
            'citizens', 'health_records', 'age_by_purok', 'sex_by_purok', 'pregnancy_by_purok',
            'immunization', 'diagnosis_summary', 'diagnosis_by_purok', 'referrals',
        ];
        $dateFilterSections = ['health_records', 'events', 'referrals', 'immunization', 'diagnosis_summary', 'diagnosis_by_purok'];
        $citizenSearchSections = ['citizens', 'health_records', 'immunization', 'diagnosis_summary', 'diagnosis_by_purok'];
        $filterRules = [];

        if (array_intersect($selectedSections, $locationFilterSections)) {
            $filterRules['purok'] = $request->input('purok') === 'all'
                ? ['nullable', Rule::in(['all'])]
                : ['nullable', 'integer', 'exists:puroks,id'];
            $filterRules['subgroup'] = $request->input('subgroup') === 'all'
                ? ['nullable', Rule::in(['all'])]
                : ['nullable', 'integer', 'exists:purok_subgroups,id'];
        }
        if (array_intersect($selectedSections, $citizenFilterSections)) {
            $filterRules['age_min'] = ['nullable', 'integer', 'between:0,120'];
            $filterRules['age_max'] = ['nullable', 'integer', 'between:0,120', 'gte:age_min'];
        }
        if (array_intersect($selectedSections, $citizenSearchSections) || in_array('supplies', $selectedSections, true)) {
            $filterRules['search'] = ['nullable', 'string', 'max:100'];
        }
        if (in_array('citizens', $selectedSections, true)) {
            $filterRules['citizen_status'] = ['nullable', Rule::in(['living', 'deceased'])];
            $filterRules['sex'] = ['nullable', Rule::in(['Male', 'Female'])];
        }
        if (array_intersect($selectedSections, $dateFilterSections)) {
            $filterRules['start_date'] = ['nullable', 'date_format:Y-m-d'];
            $filterRules['end_date'] = ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'];
        }
        if (array_intersect($selectedSections, ['health_records', 'diagnosis_summary', 'diagnosis_by_purok'])) {
            $filterRules['diagnosis'] = ['nullable', 'string', 'max:255'];
        }
        if (in_array('supplies', $selectedSections, true)) {
            $filterRules['category'] = ['nullable', 'string', 'max:255'];
            $filterRules['stock_status'] = ['nullable', Rule::in(['out', 'low', 'available'])];
        }
        if (in_array('events', $selectedSections, true)) {
            $filterRules['event_status'] = ['nullable', Rule::in(['upcoming', 'past'])];
        }
        if (in_array('referrals', $selectedSections, true)) {
            $filterRules['referral_status'] = ['nullable', Rule::in(['approved', 'released', 'returned'])];
        }
        if (in_array('immunization', $selectedSections, true)) {
            $filterRules['vaccine_type'] = ['nullable', 'string', 'max:255'];
        }
        if ($filterRules) {
            $request->validate($filterRules);
        }

        $citizenQuery = citizens::query();
        $canViewAll = UserPermission::where('user_id', $user->id)
            ->where('feature', 'view_all_citizens')
            ->where('access', '!=', 'none')
            ->exists();

        if (! $canViewAll) {
            $assignedSubgroups = PurokSubgroup::where('user_id', $user->id)->pluck('id');
            $citizenQuery->where(function ($query) use ($assignedSubgroups) {
                $query->whereIn('subgroup_id', $assignedSubgroups)
                    ->orWhereHas('family', fn ($family) => $family->whereIn('subgroup_id', $assignedSubgroups));
            });
        }

        $purokRecord = null;
        if (array_intersect($selectedSections, $locationFilterSections) && $request->filled('purok') && $request->input('purok') !== 'all') {
            $purokRecord = Purok::findOrFail($request->input('purok'));
            $citizenQuery->where(function ($query) use ($purokRecord) {
                $query->whereHas('subgroup', fn ($subgroup) => $subgroup->where('purok_id', $purokRecord->id))
                    ->orWhereHas('family', fn ($family) => $family->where('purok_id', $purokRecord->id))
                    ->orWhereHas('family.subgroup', fn ($subgroup) => $subgroup->where('purok_id', $purokRecord->id))
                    ->orWhere(function ($legacy) use ($purokRecord) {
                        $legacy->whereDoesntHave('subgroup')
                            ->whereDoesntHave('family')
                            ->whereIn('Citizen_Purok', [$purokRecord->name, (string) $purokRecord->id]);
                    });
            });
        }

        if (array_intersect($selectedSections, $locationFilterSections) && $request->filled('subgroup') && $request->input('subgroup') !== 'all') {
            $subgroup = PurokSubgroup::findOrFail($request->input('subgroup'));
            if ($purokRecord && (int) $subgroup->purok_id !== (int) $purokRecord->id) {
                throw ValidationException::withMessages([
                    'subgroup' => 'The selected subgroup does not belong to the selected Purok.',
                ]);
            }

            $citizenQuery->where(function ($query) use ($subgroup) {
                $query->where('subgroup_id', $subgroup->id)
                    ->orWhereHas('family', fn ($family) => $family->where('subgroup_id', $subgroup->id));
            });
        }

        if (array_intersect($selectedSections, $citizenSearchSections) && $request->filled('search')) {
            $search = trim($request->input('search'));
            $citizenId = preg_match('/^C-?0*(\d+)$/i', $search, $matches)
                ? (int) $matches[1]
                : (ctype_digit($search) ? (int) $search : null);

            $citizenQuery->where(function ($query) use ($search, $citizenId) {
                $query->where('Citizen_FName', 'like', "%{$search}%")
                    ->orWhere('Citizen_MName', 'like', "%{$search}%")
                    ->orWhere('Citizen_LName', 'like', "%{$search}%");
                if ($citizenId !== null) {
                    $query->orWhere('id', $citizenId);
                }
            });
        }

        $applyAgeFilters = (bool) array_intersect($selectedSections, $citizenFilterSections);
        $citizenQuery
            ->when($applyAgeFilters && $request->filled('age_min'), fn ($query) => $query->where('Citizen_Age', '>=', $request->integer('age_min')))
            ->when($applyAgeFilters && $request->filled('age_max'), fn ($query) => $query->where('Citizen_Age', '<=', $request->integer('age_max')))
            ->when(in_array('citizens', $selectedSections, true) && $request->input('citizen_status') === 'living', fn ($query) => $query->where('Citizen_IsDeceased', false))
            ->when(in_array('citizens', $selectedSections, true) && $request->input('citizen_status') === 'deceased', fn ($query) => $query->where('Citizen_IsDeceased', true))
            ->when(in_array('citizens', $selectedSections, true) && $request->filled('sex'), fn ($query) => $query->where('Citizen_Sex', $request->input('sex')));

        $includeImmunization = in_array('immunization', $selectedSections, true);
        $needsDiagnosisSummary = in_array('diagnosis_summary', $selectedSections, true)
            || in_array('diagnosis_by_purok', $selectedSections, true);
        $includeDiagnosisHistory = in_array('health_records', $selectedSections, true) || $needsDiagnosisSummary;
        $citizenRelations = ['family.purok', 'family.subgroup.purok', 'subgroup.purok'];
        $citizensReport = (clone $citizenQuery)->with($citizenRelations)->get();
        $citizenIds = $citizensReport->pluck('id');

        $ageByPurok = $citizensReport->groupBy(fn ($citizen) => $this->purokNameForCitizen($citizen))->map(function ($group) {
            $validAge = fn ($age) => is_numeric($age) && (int) $age >= 0 && (int) $age <= 120;

            return [
                'infants' => $group->whereBetween('Citizen_Age', [0, 1])->count(),
                'toddlers' => $group->whereBetween('Citizen_Age', [2, 4])->count(),
                'children' => $group->whereBetween('Citizen_Age', [5, 12])->count(),
                'teenagers' => $group->whereBetween('Citizen_Age', [13, 17])->count(),
                'working_adults' => $group->whereBetween('Citizen_Age', [18, 59])->count(),
                'seniors' => $group->whereBetween('Citizen_Age', [60, 120])->count(),
                'unknown_age' => $group->filter(fn ($citizen) => ! $validAge($citizen->Citizen_Age))->count(),
            ];
        });
        $ageTotals = collect($ageByPurok->first() ? array_fill_keys(array_keys($ageByPurok->first()), 0) : [
            'infants' => 0, 'toddlers' => 0, 'children' => 0, 'teenagers' => 0,
            'working_adults' => 0, 'seniors' => 0, 'unknown_age' => 0,
        ]);
        foreach ($ageByPurok as $ages) {
            foreach ($ages as $category => $count) {
                $ageTotals[$category] += $count;
            }
        }

        $vaccinationRecords = collect();
        $legacyVaccinations = collect();
        $vaccinationSchedules = collect();
        $immunizationHistory = collect();
        $vaccinationDoseSummary = collect();
        if ($includeImmunization && $citizenIds->isNotEmpty()) {
            $vaccinationRecords = VaccinationRecord::with([
                'citizen.subgroup.purok',
                'citizen.family.purok',
                'citizen.family.subgroup.purok',
            ])->whereIn('citizen_id', $citizenIds)
                ->when($request->filled('start_date'), fn ($query) => $query->whereDate('date_administered', '>=', $request->input('start_date')))
                ->when($request->filled('end_date'), fn ($query) => $query->whereDate('date_administered', '<=', $request->input('end_date')))
                ->orderBy('date_administered')->orderBy('id')->get()
                ->filter(fn ($record) => ! $request->filled('vaccine_type') || $this->sameVaccineType($record->vaccine_name, $request->input('vaccine_type')))
                ->values();

            $legacyVaccinations = Vaccination::with([
                'citizen.subgroup.purok',
                'citizen.family.purok',
                'citizen.family.subgroup.purok',
            ])->whereIn('citizen_id', $citizenIds)
                ->when($request->filled('start_date'), fn ($query) => $query->whereDate('date_administered', '>=', $request->input('start_date')))
                ->when($request->filled('end_date'), fn ($query) => $query->whereDate('date_administered', '<=', $request->input('end_date')))
                ->orderBy('date_administered')->orderBy('id')->get()
                ->filter(fn ($record) => ! $request->filled('vaccine_type') || $this->sameVaccineType($record->vaccine_name, $request->input('vaccine_type')))
                ->values();

            $knownAdministeredDoseKeys = VaccinationRecord::whereIn('citizen_id', $citizenIds)
                ->get(['citizen_id', 'vaccine_name', 'dose_number'])
                ->filter(fn ($record) => ! $request->filled('vaccine_type') || $this->sameVaccineType($record->vaccine_name, $request->input('vaccine_type')))
                ->mapWithKeys(fn ($record) => [
                    implode('|', [
                        $record->citizen_id,
                        $this->canonicalVaccineName($record->vaccine_name ?? ''),
                        $this->normalizeDoseLabel($record->dose_number ?? ''),
                    ]) => true,
                ])->all();

            $vaccinationSchedules = VaccinationSchedule::with([
                'citizen.subgroup.purok',
                'citizen.family.purok',
                'citizen.family.subgroup.purok',
            ])->whereIn('citizen_id', $citizenIds)
                ->orderBy('scheduled_date')->orderBy('id')->get()
                ->filter(fn ($schedule) => ! $request->filled('vaccine_type') || $this->sameVaccineType($schedule->vaccine_name, $request->input('vaccine_type')))
                ->values();

            $mapCitizen = fn ($citizen) => [
                'citizen_id' => $citizen->id,
                'citizen_id_label' => 'C-'.str_pad($citizen->id, 4, '0', STR_PAD_LEFT),
                'citizen_name' => trim(implode(' ', array_filter([
                    $citizen->Citizen_FName,
                    $citizen->Citizen_MName,
                    $citizen->Citizen_LName,
                ]))),
                'age' => is_numeric($citizen->Citizen_Age) ? (int) $citizen->Citizen_Age : 'Not recorded',
                'sex' => $citizen->Citizen_Sex ?: 'Not recorded',
                'purok' => $this->purokNameForCitizen($citizen),
                'subgroup' => $citizen->subgroup?->name ?? $citizen->family?->subgroup?->name ?? 'Not assigned',
            ];
            $administeredEvents = collect();
            $seenAdministeredDoses = [];
            foreach ($vaccinationRecords as $record) {
                $vaccineKey = $this->canonicalVaccineName($record->vaccine_name ?? '');
                $doseKey = $this->normalizeDoseLabel($record->dose_number ?? '');
                $date = Carbon::parse($record->date_administered)->format('Y-m-d');
                $identity = implode('|', [$record->citizen_id, $vaccineKey, $doseKey, $date]);
                if (isset($seenAdministeredDoses[$identity])) {
                    continue;
                }

                $seenAdministeredDoses[$identity] = true;
                $administeredEvents->push(array_merge($mapCitizen($record->citizen), [
                    'vaccine_key' => $vaccineKey,
                    'vaccine_name' => $record->vaccine_name,
                    'dose_key' => $doseKey,
                    'dose_number' => $record->dose_number ?: 'Not recorded',
                    'date' => $date,
                    'status' => 'Administered',
                ]));
            }
            foreach ($legacyVaccinations as $record) {
                $vaccineKey = $this->canonicalVaccineName($record->vaccine_name ?? '');
                $date = Carbon::parse($record->date_administered)->format('Y-m-d');
                $duplicatesDoseRecord = $administeredEvents->contains(fn ($event) => (int) $event['citizen_id'] === (int) $record->citizen_id
                    && $event['vaccine_key'] === $vaccineKey
                    && $event['date'] === $date);
                $identity = implode('|', [$record->citizen_id, $vaccineKey, 'legacy', $date]);
                if ($duplicatesDoseRecord || isset($seenAdministeredDoses[$identity])) {
                    continue;
                }

                $seenAdministeredDoses[$identity] = true;
                $administeredEvents->push(array_merge($mapCitizen($record->citizen), [
                    'vaccine_key' => $vaccineKey,
                    'vaccine_name' => $record->vaccine_name,
                    'dose_key' => '',
                    'dose_number' => 'Not recorded',
                    'date' => $date,
                    'status' => 'Administered',
                ]));
            }

            $isWithinDateRange = fn ($date) => (! $request->filled('start_date') || $date >= $request->input('start_date'))
                && (! $request->filled('end_date') || $date <= $request->input('end_date'));
            $immunizationHistory = $administeredEvents
                ->filter(fn ($event) => $isWithinDateRange($event['date']))
                ->values();
            $schedulesInRange = $vaccinationSchedules->filter(fn ($schedule) => $isWithinDateRange($schedule->scheduled_date->format('Y-m-d')));
            $administeredByVaccine = $immunizationHistory->groupBy(fn ($event) => $event['citizen_id'].'|'.$event['vaccine_key']);
            $schedulesInRangeByVaccine = $schedulesInRange->groupBy(fn ($schedule) => $schedule->citizen_id.'|'.$this->canonicalVaccineName($schedule->vaccine_name ?? ''));
            $allPendingSchedulesByVaccine = $vaccinationSchedules
                ->where('status', 'scheduled')
                ->reject(fn ($schedule) => $schedule->dose_number
                    && isset($knownAdministeredDoseKeys[implode('|', [
                        $schedule->citizen_id,
                        $this->canonicalVaccineName($schedule->vaccine_name ?? ''),
                        $this->normalizeDoseLabel($schedule->dose_number),
                    ])]))
                ->groupBy(fn ($schedule) => $schedule->citizen_id.'|'.$this->canonicalVaccineName($schedule->vaccine_name ?? ''));
            $vaccineGroupKeys = $administeredByVaccine->keys()->merge($schedulesInRangeByVaccine->keys())->unique();

            $vaccinationDoseSummary = $vaccineGroupKeys->map(function ($groupKey) use ($administeredByVaccine, $schedulesInRangeByVaccine, $allPendingSchedulesByVaccine, $knownAdministeredDoseKeys) {
                $administered = $administeredByVaccine->get($groupKey, collect());
                $schedules = $schedulesInRangeByVaccine->get($groupKey, collect());
                $first = $administered->first();
                $scheduleForCitizen = $schedules->first();
                $citizenInfo = $first ?? ($scheduleForCitizen ? [
                    'citizen_id' => $scheduleForCitizen->citizen_id,
                    'citizen_id_label' => 'C-'.str_pad($scheduleForCitizen->citizen_id, 4, '0', STR_PAD_LEFT),
                    'citizen_name' => trim(implode(' ', array_filter([
                        $scheduleForCitizen->citizen?->Citizen_FName,
                        $scheduleForCitizen->citizen?->Citizen_MName,
                        $scheduleForCitizen->citizen?->Citizen_LName,
                    ]))),
                    'age' => is_numeric($scheduleForCitizen->citizen?->Citizen_Age) ? (int) $scheduleForCitizen->citizen->Citizen_Age : 'Not recorded',
                    'sex' => $scheduleForCitizen->citizen?->Citizen_Sex ?: 'Not recorded',
                    'purok' => $this->purokNameForCitizen($scheduleForCitizen->citizen),
                    'subgroup' => $scheduleForCitizen->citizen?->subgroup?->name ?? $scheduleForCitizen->citizen?->family?->subgroup?->name ?? 'Not assigned',
                ] : []);
                $nextSchedule = $allPendingSchedulesByVaccine->get($groupKey, collect())
                    ->sortBy('scheduled_date')
                    ->first();
                $listedSchedules = $schedules
                    ->reject(fn ($schedule) => $schedule->dose_number
                        && isset($knownAdministeredDoseKeys[implode('|', [
                            $schedule->citizen_id,
                            $this->canonicalVaccineName($schedule->vaccine_name ?? ''),
                            $this->normalizeDoseLabel($schedule->dose_number),
                        ])]))
                    ->when($nextSchedule && ! $schedules->contains('id', $nextSchedule->id), fn ($records) => $records->push($nextSchedule));
                $nextDueDate = $nextSchedule?->scheduled_date?->format('Y-m-d');
                $latestDate = $administered->max('date');
                $doseDetails = $administered->map(fn ($event) => [
                    'label' => $event['dose_number'],
                    'status' => 'Administered',
                ])->concat($listedSchedules->map(fn ($schedule) => [
                    'label' => $schedule->dose_number ?: 'Not recorded',
                    'status' => $schedule->status ? ucfirst($schedule->status) : 'Not recorded',
                ]))->groupBy(fn ($dose) => $this->normalizeDoseLabel($dose['label']))
                    ->map(fn ($doses) => [
                        'label' => $doses->first()['label'],
                        'status' => $doses->pluck('status')->unique()->implode(', '),
                    ])->values();
                $statusSummary = $doseDetails->pluck('status')->unique()->implode('; ');

                return [
                    'citizen_id_label' => $citizenInfo['citizen_id_label'],
                    'citizen_name' => $citizenInfo['citizen_name'],
                    'age' => $citizenInfo['age'],
                    'sex' => $citizenInfo['sex'],
                    'purok' => $citizenInfo['purok'],
                    'subgroup' => $citizenInfo['subgroup'],
                    'vaccine_name' => $first['vaccine_name'] ?? $scheduleForCitizen->vaccine_name,
                    'dose_numbers' => $doseDetails->map(fn ($dose) => $dose['label'].' ('.$dose['status'].')')->implode('; '),
                    'administered_doses' => $administered->count(),
                    'latest_vaccination_date' => $latestDate,
                    'vaccination_status' => $statusSummary,
                    'next_dose_due_date' => $nextDueDate,
                ];
            })->sortBy(fn ($record) => $record['citizen_name'].'|'.$record['vaccine_name'])->values();
        }

        $immunization = collect();
        $immunizationSummary = ['total' => 0, 'vaccinated' => 0, 'missing' => 0];
        if ($includeImmunization) {
            $immunization = $citizensReport
                ->filter(fn ($citizen) => is_numeric($citizen->Citizen_Age) && (int) $citizen->Citizen_Age >= 0 && (int) $citizen->Citizen_Age <= 5)
                ->keyBy('id');
            $administeredHistory = $immunizationHistory->where('status', 'Administered')
                ->filter(fn ($record) => $immunization->has($record['citizen_id']));
            $immunizationSummary['total'] = $immunization->count();
            if ($request->filled('vaccine_type')) {
                $immunizationSummary['vaccinated'] = $administeredHistory
                    ->filter(fn ($record) => $this->sameVaccineType($record['vaccine_name'], $request->input('vaccine_type')))
                    ->pluck('citizen_id')->unique()->count();
            } else {
                $vaccineNamesByCitizen = $administeredHistory->groupBy('citizen_id')
                    ->map(fn ($records) => $records->pluck('vaccine_name'));
                $immunizationSummary['vaccinated'] = $immunization->keys()->filter(function ($citizenId) use ($vaccineNamesByCitizen) {
                    $vaccineNames = $vaccineNamesByCitizen->get($citizenId, collect());

                    return $vaccineNames->contains(fn ($name) => $this->matchesVaccineAlias($name, ['bcg']))
                        && $vaccineNames->contains(fn ($name) => $this->matchesVaccineAlias($name, ['polio', 'poliomyelitis', 'opv', 'ipv']))
                        && $vaccineNames->contains(fn ($name) => $this->matchesVaccineAlias($name, ['measles', 'mmr']));
                })->count();
            }
            $immunizationSummary['missing'] = $immunizationSummary['total'] - $immunizationSummary['vaccinated'];
        }

        $sexByPurok = $citizensReport->groupBy(fn ($citizen) => $this->purokNameForCitizen($citizen))->map(function ($group) {
            return [
                'male' => $group->where('Citizen_Sex', 'Male')->count(),
                'female' => $group->where('Citizen_Sex', 'Female')->count(),
                'other' => $group->whereNotIn('Citizen_Sex', ['Male', 'Female'])->count(),
            ];
        });
        $sexTotals = [
            'male' => $sexByPurok->sum('male'),
            'female' => $sexByPurok->sum('female'),
            'other' => $sexByPurok->sum('other'),
        ];

        $pregnancyByPurok = $citizensReport->groupBy(fn ($citizen) => $this->purokNameForCitizen($citizen))->map(fn ($group) => $group->where('Citizen_Pregnant', true)->count());
        $pregnancyTotal = $pregnancyByPurok->sum();

        $healthRecordRelations = [
            'citizen.subgroup.purok',
            'citizen.family.purok',
            'citizen.family.subgroup.purok',
        ];
        $healthRecordQuery = HealthRecord::with($healthRecordRelations)
            ->whereIn('citizen_id', $citizenIds)
            ->when($request->filled('start_date'), fn ($query) => $query->whereDate('record_date', '>=', $request->input('start_date')))
            ->when($request->filled('end_date'), fn ($query) => $query->whereDate('record_date', '<=', $request->input('end_date')))
            ->when($request->filled('diagnosis'), fn ($query) => $query->where('diagnosis', $request->input('diagnosis')));
        $healthRecords = $includeDiagnosisHistory
            ? (clone $healthRecordQuery)->orderByDesc('record_date')->orderByDesc('created_at')->get()
            : collect();
        $healthRecordDetails = $healthRecords->map(function ($record) {
            $citizen = $record->citizen;

            return [
                'citizen_name' => $citizen
                    ? trim(implode(' ', array_filter([$citizen->Citizen_FName, $citizen->Citizen_MName, $citizen->Citizen_LName])))
                    : 'Citizen unavailable',
                'age' => is_numeric($citizen?->Citizen_Age) ? (int) $citizen->Citizen_Age : 'Not recorded',
                'sex' => $citizen?->Citizen_Sex ?: 'Not recorded',
                'purok' => $this->purokNameForCitizen($citizen),
                'date' => $record->record_date ? Carbon::parse($record->record_date)->format('Y-m-d') : 'Not recorded',
                'diagnosis' => $record->diagnosis ?: 'Not recorded',
                'blood_pressure' => $record->blood_pressure ?: 'Not recorded',
                'weight' => is_numeric($record->weight) ? (float) $record->weight : 'Not recorded',
                'height' => is_numeric($record->height) ? (float) $record->height : 'Not recorded',
                'bmi' => is_numeric($record->bmi) ? (float) $record->bmi : 'Not recorded',
                'temperature' => is_numeric($record->temperature) ? (float) $record->temperature : 'Not recorded',
            ];
        });
        $diagnosisSummary = collect();
        $diagnosisByPurok = collect();
        if ($needsDiagnosisSummary) {
            $diagnosisRecords = $healthRecords;
            $diagnosisSummary = $diagnosisRecords->groupBy(fn ($record) => $record->diagnosis ?: 'Unspecified')->map->count()->sortDesc();
            $diagnosisByPurok = $diagnosisRecords->groupBy(function ($record) {
                return $this->purokNameForCitizen($record->citizen).'|'.($record->diagnosis ?: 'Unspecified');
            })->map->count()->sortDesc();
        }

        $medicationRecords = collect();
        $maintenanceRecords = collect();
        if (in_array('health_records', $selectedSections, true) && $citizenIds->isNotEmpty()) {
            $medicationRecords = MedicationRecord::with('citizen')->whereIn('citizen_id', $citizenIds)
                ->when($request->filled('start_date'), fn ($query) => $query->whereDate('date_dispensed', '>=', $request->input('start_date')))
                ->when($request->filled('end_date'), fn ($query) => $query->whereDate('date_dispensed', '<=', $request->input('end_date')))
                ->orderByDesc('date_dispensed')->orderByDesc('id')->get();
            $maintenanceRecords = MaintenanceRecord::with(['citizen', 'user'])->whereIn('citizen_id', $citizenIds)
                ->when($request->filled('start_date'), fn ($query) => $query->whereDate('start_date', '>=', $request->input('start_date')))
                ->when($request->filled('end_date'), fn ($query) => $query->whereDate('start_date', '<=', $request->input('end_date')))
                ->orderByDesc('start_date')->orderByDesc('id')->get();
        }
        $medicationHistory = $medicationRecords->map(fn ($record) => [
            'citizen_id' => $record->citizen_id,
            'citizen_name' => trim(implode(' ', array_filter([
                $record->citizen?->Citizen_FName,
                $record->citizen?->Citizen_MName,
                $record->citizen?->Citizen_LName,
            ]))),
            'date' => $record->date_dispensed ? Carbon::parse($record->date_dispensed)->format('Y-m-d') : 'Not recorded',
            'medicine' => $record->medicine_name,
            'dosage' => $record->dosage ?: 'Not recorded',
            'quantity' => $record->quantity_dispensed ?? 'Not recorded',
            'frequency' => 'Not recorded',
            'end_date' => 'Not recorded',
            'recorded_by' => 'Not recorded',
            'source' => 'Medication dispensing',
        ])->concat($maintenanceRecords->map(fn ($record) => [
            'citizen_id' => $record->citizen_id,
            'citizen_name' => trim(implode(' ', array_filter([
                $record->citizen?->Citizen_FName,
                $record->citizen?->Citizen_MName,
                $record->citizen?->Citizen_LName,
            ]))),
            'date' => $record->start_date ? Carbon::parse($record->start_date)->format('Y-m-d') : 'Not recorded',
            'medicine' => $record->medicine_name,
            'dosage' => $record->dosage ?: 'Not recorded',
            'quantity' => $record->quantity_dispensed ?? 'Not recorded',
            'frequency' => $record->frequency ?: 'Not recorded',
            'end_date' => $record->end_date ? Carbon::parse($record->end_date)->format('Y-m-d') : 'Ongoing / not recorded',
            'recorded_by' => $record->user?->name ?: 'Not recorded',
            'source' => 'Maintenance medicine',
        ]))->sortByDesc('date')->values();

        $supplies = collect();
        if (in_array('supplies', $selectedSections)) {
            $supplies = Supply::when($request->filled('category'), fn ($query) => $query->where('category', $request->input('category')))
                ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->input('search').'%'))
                ->when($request->input('stock_status') === 'out', fn ($query) => $query->where('quantity', '<=', 0))
                ->when($request->input('stock_status') === 'low', fn ($query) => $query->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'min_stock'))
                ->when($request->input('stock_status') === 'available', fn ($query) => $query->whereColumn('quantity', '>', 'min_stock'))
                ->orderBy('name')->get();
        }

        $events = collect();
        if (in_array('events', $selectedSections)) {
            $events = Event::when($request->filled('start_date'), fn ($query) => $query->whereDate('start', '>=', $request->input('start_date')))
                ->when($request->filled('end_date'), fn ($query) => $query->whereDate('start', '<=', $request->input('end_date')))
                ->when($request->input('event_status') === 'upcoming', fn ($query) => $query->whereDate('start', '>=', today()))
                ->when($request->input('event_status') === 'past', fn ($query) => $query->whereDate('start', '<', today()))
                ->orderBy('start')->get();
        }

        $referrals = collect();
        if (in_array('referrals', $selectedSections)) {
            $referrals = Referral::where('user_id', $user->id)
                ->when($request->filled('start_date'), fn ($query) => $query->whereDate('date_of_referral', '>=', $request->input('start_date')))
                ->when($request->filled('end_date'), fn ($query) => $query->whereDate('date_of_referral', '<=', $request->input('end_date')))
                ->when($request->filled('referral_status'), fn ($query) => $query->where('status', $request->input('referral_status')))
                ->latest('date_of_referral')->get()
                ->when($request->filled('subgroup'), function ($records) use ($request) {
                    $subgroup = PurokSubgroup::findOrFail($request->input('subgroup'));

                    return $records->filter(fn ($referral) => $this->addressContainsPlace($referral->address, $subgroup->name));
                })
                ->when($purokRecord, fn ($records) => $records
                    ->filter(fn ($referral) => $this->addressContainsPlace($referral->address, $purokRecord->name)))
                ->values();
        }

        $puroks = Purok::with('subgroups')->orderBy('name')->get();
        $categories = Supply::query()->select('category')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        $diagnoses = HealthRecord::query()
            ->whereIn('citizen_id', $citizenIds)
            ->select('diagnosis')
            ->whereNotNull('diagnosis')
            ->distinct()
            ->orderBy('diagnosis')
            ->pluck('diagnosis');
        $vaccineTypes = collect();
        if ($citizenIds->isNotEmpty()) {
            $vaccineTypes = VaccinationRecord::query()
                ->whereIn('citizen_id', $citizenIds)
                ->whereNotNull('vaccine_name')
                ->pluck('vaccine_name')
                ->concat(Vaccination::query()->whereIn('citizen_id', $citizenIds)->whereNotNull('vaccine_name')->pluck('vaccine_name'))
                ->concat(VaccinationSchedule::query()->whereIn('citizen_id', $citizenIds)->whereNotNull('vaccine_name')->pluck('vaccine_name'))
                ->unique()
                ->sort()
                ->values();
        }
        $reportFilterSummary = collect([
            'Purok' => $request->input('purok')
                ? ($purokRecord?->name ?? ($request->input('purok') === 'all' ? 'All Puroks' : null))
                : null,
            'Subgroup' => $request->input('subgroup')
                ? ($request->input('subgroup') === 'all' ? 'All subgroups' : PurokSubgroup::find($request->input('subgroup'))?->name)
                : null,
            'Date range' => $request->filled('start_date') || $request->filled('end_date')
                ? ($request->input('start_date') ?: 'Any').' to '.($request->input('end_date') ?: 'Any')
                : null,
            'Citizen search' => $request->input('search'),
            'Age' => $request->filled('age_min') || $request->filled('age_max')
                ? ($request->input('age_min') ?: 'Any').' to '.($request->input('age_max') ?: 'Any')
                : null,
            'Sex' => $request->input('sex'),
            'Citizen status' => $request->input('citizen_status'),
            'Diagnosis' => $request->input('diagnosis'),
            'Vaccine' => $request->input('vaccine_type'),
            'Supply category' => $request->input('category'),
            'Stock status' => $request->input('stock_status'),
            'Event status' => $request->input('event_status'),
            'Referral status' => $request->input('referral_status'),
        ])->filter()->map(fn ($value, $label) => $label.': '.$value)->implode(' | ');

        if ($request->input('export_format') === 'xlsx') {
            $reportTitle = collect($selectedSections)
                ->map(fn ($section) => $availableSections[$section])
                ->implode(' + ');

            return Excel::download(
                new CustomReportExport(
                    $reportTitle,
                    now()->format('F d, Y h:i A'),
                    $reportFilterSummary,
                    $this->buildExportSections(
                        $request,
                        $selectedSections,
                        $citizensReport,
                        $ageByPurok,
                        $ageTotals,
                        $immunizationSummary,
                        $sexByPurok,
                        $sexTotals,
                        $pregnancyByPurok,
                        $pregnancyTotal,
                        $diagnosisSummary,
                        $diagnosisByPurok,
                        $healthRecords,
                        $healthRecordDetails,
                        $vaccinationDoseSummary,
                        $medicationHistory,
                        $supplies,
                        $events,
                        $referrals
                    )
                ),
                Str::slug($reportTitle).'-'.now()->format('Y-m-d').'.xlsx'
            );
        }

        return view('bhw.reports', compact(
            'availableSections', 'selectedSections', 'citizensReport', 'healthRecords', 'healthRecordDetails',
            'diagnoses', 'vaccineTypes', 'vaccinationDoseSummary', 'medicationHistory',
            'supplies', 'events', 'referrals', 'puroks', 'categories', 'ageByPurok',
            'ageTotals', 'immunization', 'immunizationSummary', 'sexByPurok', 'sexTotals', 'pregnancyByPurok',
            'pregnancyTotal',
            'includeGraphics',
            'diagnosisSummary', 'diagnosisByPurok', 'reportFilterSummary'
        ));
    }

    private function matchesVaccineAlias(?string $vaccineName, array $aliases): bool
    {
        $normalized = $this->normalizeVaccineName($vaccineName ?? '');

        foreach ($aliases as $alias) {
            $normalizedAlias = $this->normalizeVaccineName($alias);
            if ($normalizedAlias !== '' && str_contains(' '.$normalized.' ', ' '.$normalizedAlias.' ')) {
                return true;
            }
        }

        return false;
    }

    private function purokNameForCitizen(?citizens $citizen): string
    {
        return $citizen?->subgroup?->purok?->name
            ?? $citizen?->family?->subgroup?->purok?->name
            ?? $citizen?->family?->purok?->name
            ?? ($citizen?->Citizen_Purok ?: 'Unassigned');
    }

    private function sameVaccineType(?string $first, ?string $second): bool
    {
        return $this->canonicalVaccineName($first ?? '') === $this->canonicalVaccineName($second ?? '');
    }

    private function canonicalVaccineName(string $name): string
    {
        $aliases = [
            'bcg' => ['bcg'],
            'hepatitis b' => ['hepatitis b', 'hepb'],
            'pentavalent' => ['pentavalent', 'penta'],
            'oral polio' => ['oral polio', 'opv'],
            'inactivated polio' => ['inactivated polio', 'ipv'],
            'pneumococcal' => ['pneumococcal', 'pcv'],
            'mmr' => ['measles mumps rubella', 'mmr'],
            'mr' => ['measles rubella', 'mr'],
            'tetanus' => ['tetanus', 'td'],
            'hpv' => ['human papillomavirus', 'hpv'],
        ];

        foreach ($aliases as $canonical => $terms) {
            if ($this->matchesVaccineAlias($name, $terms)) {
                return $canonical;
            }
        }

        return $this->normalizeVaccineName($name);
    }

    private function normalizeDoseLabel(string $label): string
    {
        $normalized = strtolower(preg_replace('/[^a-z0-9]+/i', '', trim($label)) ?? '');
        $normalized = preg_replace('/^dose/', '', $normalized) ?? $normalized;
        if ($normalized === '') {
            return '';
        }

        return ltrim($normalized, '0') ?: '0';
    }

    private function normalizeVaccineName(string $name): string
    {
        $normalized = strtolower(trim(preg_replace('/[^a-z0-9]+/i', ' ', $name) ?? ''));
        $abbreviations = [
            'b c g' => 'bcg',
            'h e p b' => 'hepb',
            'o p v' => 'opv',
            'i p v' => 'ipv',
            'p c v' => 'pcv',
            'm m r' => 'mmr',
            'm r' => 'mr',
            't d' => 'td',
            'h p v' => 'hpv',
        ];

        return str_replace(array_keys($abbreviations), array_values($abbreviations), $normalized);
    }

    private function addressContainsPlace(?string $address, string $place): bool
    {
        $normalize = fn ($value) => trim(preg_replace('/[^a-z0-9]+/i', ' ', strtolower($value)) ?? '');
        $normalizedAddress = ' '.$normalize($address ?? '').' ';
        $normalizedPlace = $normalize($place);

        return $normalizedPlace !== '' && str_contains($normalizedAddress, ' '.$normalizedPlace.' ');
    }

    private function buildExportSections(
        Request $request,
        array $selectedSections,
        $citizensReport,
        $ageByPurok,
        $ageTotals,
        array $immunizationSummary,
        $sexByPurok,
        array $sexTotals,
        $pregnancyByPurok,
        int $pregnancyTotal,
        $diagnosisSummary,
        $diagnosisByPurok,
        $healthRecords,
        $healthRecordDetails,
        $vaccinationDoseSummary,
        $medicationHistory,
        $supplies,
        $events,
        $referrals
    ): array {
        $sections = [];

        if (in_array('citizens', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Citizen Profiles',
                'headings' => ['Name', 'Age', 'Sex', 'Purok', 'Family / Subgroup'],
                'rows' => $citizensReport->map(fn ($citizen) => [
                    $citizen->Citizen_FName.' '.$citizen->Citizen_LName.' (ID: C-'.str_pad($citizen->id, 4, '0', STR_PAD_LEFT).')',
                    (int) $citizen->Citizen_Age,
                    $citizen->Citizen_Sex ?: 'Not recorded',
                    $this->purokNameForCitizen($citizen),
                    ($citizen->family->family_name ?? 'No family').' / '.($citizen->subgroup->name ?? $citizen->family?->subgroup?->name ?? 'Not assigned'),
                ])->all(),
                'column_formats' => ['B' => '0'],
            ];
        }

        if (in_array('age_by_purok', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Age-Bracket Breakdown per Purok',
                'headings' => ['Purok', 'Infants', 'Toddlers', 'Children', 'Teenagers', 'Working adults', 'Seniors', 'Unknown age'],
                'rows' => $ageByPurok->map(fn ($ages, $purok) => [
                    $purok ?: 'Unassigned',
                    $ages['infants'],
                    $ages['toddlers'],
                    $ages['children'],
                    $ages['teenagers'],
                    $ages['working_adults'],
                    $ages['seniors'],
                    $ages['unknown_age'],
                ])->values()->push(array_merge(['Total'], array_values($ageTotals->all())))->all(),
                'column_formats' => array_fill_keys(['B', 'C', 'D', 'E', 'F', 'G', 'H'], '0'),
            ];
        }

        if (in_array('immunization', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Immunization and Vaccination Coverage',
                'headings' => [
                    'Eligible children age 0-5',
                    $request->filled('vaccine_type') ? 'Children with selected vaccine recorded' : 'Children with recorded core vaccine groups',
                    $request->filled('vaccine_type') ? 'No selected vaccine record' : 'Missing one or more core vaccine groups',
                ],
                'rows' => [[
                    $immunizationSummary['total'],
                    $immunizationSummary['vaccinated'],
                    $immunizationSummary['missing'],
                ]],
                'column_formats' => ['A' => '0', 'B' => '0', 'C' => '0'],
            ];

            $sections[] = [
                'title' => 'Vaccination Summary',
                'headings' => [
                    'Citizen ID', 'Citizen name', 'Age', 'Sex', 'Purok', 'Subgroup', 'Vaccine',
                    'Dose number(s)', 'Total administered doses', 'Latest vaccination date',
                    'Vaccination status', 'Next dose due date',
                ],
                'rows' => $vaccinationDoseSummary->map(fn ($record) => [
                    $record['citizen_id_label'],
                    $record['citizen_name'],
                    $record['age'],
                    $record['sex'],
                    $record['purok'],
                    $record['subgroup'],
                    $record['vaccine_name'],
                    $record['dose_numbers'] ?: 'Not recorded',
                    $record['administered_doses'],
                    $record['latest_vaccination_date'] ? Date::dateTimeToExcel(Carbon::parse($record['latest_vaccination_date'])) : null,
                    $record['vaccination_status'],
                    $record['next_dose_due_date'] ? Date::dateTimeToExcel(Carbon::parse($record['next_dose_due_date'])) : null,
                ])->all(),
                'column_formats' => ['C' => '0', 'I' => '0', 'J' => 'mmm d, yyyy', 'L' => 'mmm d, yyyy'],
            ];
        }

        if (in_array('sex_by_purok', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Male and Female Counts by Purok',
                'headings' => ['Purok', 'Male', 'Female', 'Other / not recorded'],
                'rows' => $sexByPurok->map(fn ($counts, $purok) => [
                    $purok ?: 'Unassigned',
                    $counts['male'],
                    $counts['female'],
                    $counts['other'],
                ])->values()->push(['Total', $sexTotals['male'], $sexTotals['female'], $sexTotals['other']])->all(),
                'column_formats' => ['B' => '0', 'C' => '0', 'D' => '0'],
            ];
        }

        if (in_array('pregnancy_by_purok', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Recorded Pregnancy by Purok',
                'headings' => ['Purok', 'Recorded pregnant residents'],
                'rows' => $pregnancyByPurok->map(fn ($count, $purok) => [$purok ?: 'Unassigned', $count])
                    ->values()->push(['Total', $pregnancyTotal])->all(),
                'column_formats' => ['B' => '0'],
            ];
        }

        if (in_array('diagnosis_summary', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Diagnosis Summary',
                'headings' => ['Diagnosis', 'Recorded cases'],
                'rows' => $diagnosisSummary->map(fn ($count, $diagnosis) => [$diagnosis, $count])->all(),
                'column_formats' => ['B' => '0'],
            ];
        }

        if (in_array('diagnosis_by_purok', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Diagnoses by Purok',
                'headings' => ['Purok', 'Diagnosis', 'Cases'],
                'rows' => $diagnosisByPurok->map(function ($count, $key) {
                    [$purok, $diagnosis] = explode('|', $key, 2);

                    return [$purok, $diagnosis, $count];
                })->values()->all(),
                'column_formats' => ['C' => '0'],
            ];
        }

        if (in_array('health_records', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Health Records',
                'headings' => [
                    'Citizen name', 'Age', 'Sex', 'Purok', 'Diagnosis', 'Consultation date',
                    'Blood pressure', 'Weight kg', 'Height cm', 'BMI', 'Temperature °C',
                ],
                'rows' => $healthRecordDetails->map(fn ($record) => [
                    $record['citizen_name'],
                    $record['age'],
                    $record['sex'],
                    $record['purok'],
                    $record['diagnosis'],
                    $record['date'] !== 'Not recorded' ? Date::dateTimeToExcel(Carbon::parse($record['date'])) : null,
                    $record['blood_pressure'],
                    $record['weight'],
                    $record['height'],
                    $record['bmi'],
                    $record['temperature'],
                ])->all(),
                'column_formats' => [
                    'B' => '0',
                    'F' => 'mmm d, yyyy',
                    'H' => '0.00',
                    'I' => '0.00',
                    'J' => '0.0',
                    'K' => '0.0',
                ],
            ];
        }

        if ((
            in_array('diagnosis_summary', $selectedSections, true)
            || in_array('diagnosis_by_purok', $selectedSections, true)
        ) && ! in_array('health_records', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Diagnosis History',
                'headings' => [
                    'Citizen name', 'Age', 'Sex', 'Purok', 'Diagnosis', 'Consultation date',
                    'Blood pressure', 'Weight kg', 'Height cm', 'BMI', 'Temperature °C',
                ],
                'rows' => $healthRecordDetails->map(fn ($record) => [
                    $record['citizen_name'],
                    $record['age'],
                    $record['sex'],
                    $record['purok'],
                    $record['diagnosis'],
                    $record['date'] !== 'Not recorded' ? Date::dateTimeToExcel(Carbon::parse($record['date'])) : null,
                    $record['blood_pressure'],
                    $record['weight'],
                    $record['height'],
                    $record['bmi'],
                    $record['temperature'],
                ])->all(),
                'column_formats' => ['B' => '0', 'F' => 'mmm d, yyyy', 'H' => '0.00', 'I' => '0.00', 'J' => '0.0', 'K' => '0.0'],
            ];
        }

        if (in_array('health_records', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Medication History',
                'headings' => ['Citizen ID', 'Citizen name', 'Date', 'Medicine', 'Dosage', 'Quantity', 'Frequency', 'End date', 'Recorded by', 'Source'],
                'rows' => $medicationHistory->map(fn ($record) => [
                    'C-'.str_pad($record['citizen_id'], 4, '0', STR_PAD_LEFT),
                    $record['citizen_name'],
                    $record['date'] !== 'Not recorded' ? Date::dateTimeToExcel(Carbon::parse($record['date'])) : null,
                    $record['medicine'],
                    $record['dosage'],
                    $record['quantity'],
                    $record['frequency'],
                    in_array($record['end_date'], ['Not recorded', 'Ongoing / not recorded'], true) ? $record['end_date'] : Date::dateTimeToExcel(Carbon::parse($record['end_date'])),
                    $record['recorded_by'],
                    $record['source'],
                ])->all(),
                'column_formats' => ['C' => 'mmm d, yyyy', 'F' => '0', 'H' => 'mmm d, yyyy'],
            ];
        }

        if (in_array('supplies', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Supply Inventory',
                'headings' => ['Supply', 'Category', 'Quantity', 'Minimum'],
                'rows' => $supplies->map(fn ($supply) => [
                    $supply->name,
                    $supply->category ?? 'Uncategorized',
                    (int) $supply->quantity,
                    (int) $supply->min_stock,
                ])->all(),
                'column_formats' => ['C' => '0', 'D' => '0'],
            ];
        }

        if (in_array('events', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Events and Schedules',
                'headings' => ['Date', 'Event', 'Description'],
                'rows' => $events->map(fn ($event) => [
                    $event->start ? Date::dateTimeToExcel(Carbon::parse($event->start)) : null,
                    $event->title,
                    $event->description ?? '—',
                ])->all(),
                'column_formats' => ['A' => 'mmm d, yyyy'],
            ];
        }

        if (in_array('referrals', $selectedSections, true)) {
            $sections[] = [
                'title' => 'Referrals',
                'headings' => ['Date', 'Name', 'Request', 'Status'],
                'rows' => $referrals->map(fn ($referral) => [
                    $referral->date_of_referral ? Date::dateTimeToExcel(Carbon::parse($referral->date_of_referral)) : null,
                    $referral->name,
                    $referral->requests_for,
                    ucfirst($referral->status ?? 'pending'),
                ])->all(),
                'column_formats' => ['A' => 'mmm d, yyyy'],
            ];
        }

        return $sections;
    }
}
