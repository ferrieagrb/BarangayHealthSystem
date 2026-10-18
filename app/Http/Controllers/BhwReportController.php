<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\HealthRecord;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Models\Referral;
use App\Models\Supply;
use App\Models\UserPermission;
use App\Models\citizens;
use Illuminate\Http\Request;

class BhwReportController extends Controller
{
    public function index(Request $request)
    {
        $availableSections = [
            'citizens' => 'Citizen profiles',
            'health_records' => 'Health records',
            'supplies' => 'Supply inventory',
            'events' => 'Events and schedules',
            'referrals' => 'Referrals',
            'age_by_purok' => 'Age-bracket breakdown per Purok',
            'immunization' => 'Immunization and vaccination coverage',
            'sex_by_purok' => 'Male and female counts by Purok',
            'pregnancy_by_purok' => 'Recorded pregnancy by Purok',
            'diagnosis_summary' => 'Diagnosis summary',
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

        $selectedSections = array_values(array_intersect(
            (array) $request->input('sections', ['citizens']),
            array_keys($availableSections)
        ));
        if (!$selectedSections) {
            $selectedSections = [array_key_first($availableSections) ?: 'citizens'];
        }
        $includeGraphics = $request->boolean('include_graphics');

        $citizenQuery = citizens::query();
        $canViewAll = UserPermission::where('user_id', $user->id)
            ->where('feature', 'view_all_citizens')
            ->where('access', '!=', 'none')
            ->exists();

        if (!$canViewAll) {
            $assignedSubgroups = PurokSubgroup::where('user_id', $user->id)->pluck('id');
            $citizenQuery->where(function ($query) use ($assignedSubgroups) {
                $query->whereIn('subgroup_id', $assignedSubgroups)
                    ->orWhereHas('family', fn ($family) => $family->whereIn('subgroup_id', $assignedSubgroups));
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $citizenQuery->where(function ($query) use ($search) {
                $query->where('Citizen_FName', 'like', "%{$search}%")
                    ->orWhere('Citizen_LName', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        if ($request->filled('purok')) {
            $citizenQuery->where('Citizen_Purok', $request->input('purok'));
        }

        $citizensReport = (clone $citizenQuery)->with(['family.subgroup', 'subgroup', 'vaccinations'])->get();
        $citizenIds = $citizensReport->pluck('id');

        $ageByPurok = $citizensReport->groupBy('Citizen_Purok')->map(function ($group) {
            return [
                'infants' => $group->whereBetween('Citizen_Age', [0, 1])->count(),
                'toddlers' => $group->whereBetween('Citizen_Age', [2, 4])->count(),
                'children' => $group->whereBetween('Citizen_Age', [5, 12])->count(),
                'teenagers' => $group->whereBetween('Citizen_Age', [13, 17])->count(),
                'working_adults' => $group->whereBetween('Citizen_Age', [18, 59])->count(),
                'seniors' => $group->where('Citizen_Age', '>=', 60)->count(),
            ];
        });

        $immunization = $citizensReport->filter(fn ($citizen) => (int) $citizen->Citizen_Age <= 5)->map(function ($citizen) {
            $vaccines = $citizen->vaccinations->pluck('vaccine_name')->map(fn ($name) => strtolower((string) $name));
            $has = fn ($terms) => $vaccines->contains(fn ($name) => collect($terms)->contains(fn ($term) => str_contains($name, $term)));
            return [
                'citizen' => $citizen,
                'bcg' => $has(['bcg']),
                'polio' => $has(['polio']),
                'measles' => $has(['measles', 'mmr']),
            ];
        });
        $immunizationSummary = [
            'total' => $immunization->count(),
            'fully_immunized' => $immunization->filter(fn ($row) => $row['bcg'] && $row['polio'] && $row['measles'])->count(),
            'missing_milestones' => $immunization->filter(fn ($row) => !$row['bcg'] || !$row['polio'] || !$row['measles'])->count(),
        ];

        $sexByPurok = $citizensReport->groupBy('Citizen_Purok')->map(function ($group) {
            return [
                'male' => $group->where('Citizen_Sex', 'Male')->count(),
                'female' => $group->where('Citizen_Sex', 'Female')->count(),
                'other' => $group->whereNotIn('Citizen_Sex', ['Male', 'Female'])->count(),
            ];
        });

        $pregnancyByPurok = $citizensReport->groupBy('Citizen_Purok')->map(fn ($group) => $group->where('Citizen_Pregnant', true)->count());

        $healthRecords = collect();
        if (in_array('health_records', $selectedSections)) {
            $healthRecords = HealthRecord::with('citizen')
                ->whereIn('citizen_id', $citizenIds)
                ->when($request->filled('start_date'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('start_date')))
                ->when($request->filled('end_date'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('end_date')))
                ->latest()
                ->get();
        }

        $diagnosisSummary = collect();
        $diagnosisByPurok = collect();
        if (in_array('diagnosis_summary', $selectedSections) || in_array('diagnosis_by_purok', $selectedSections)) {
            $diagnosisRecords = HealthRecord::with('citizen')
                ->whereIn('citizen_id', $citizenIds)
                ->when($request->filled('start_date'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('start_date')))
                ->when($request->filled('end_date'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('end_date')))
                ->get();
            $diagnosisSummary = $diagnosisRecords->groupBy(fn ($record) => $record->diagnosis ?: 'Unspecified')->map->count()->sortDesc();
            $diagnosisByPurok = $diagnosisRecords->groupBy(function ($record) {
                return ($record->citizen->Citizen_Purok ?? 'Unassigned') . '|' . ($record->diagnosis ?: 'Unspecified');
            })->map->count()->sortDesc();
        }

        $supplies = collect();
        if (in_array('supplies', $selectedSections)) {
            $supplies = Supply::when($request->filled('category'), fn ($query) => $query->where('category', $request->input('category')))
                ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->input('search') . '%'))
                ->orderBy('name')->get();
        }

        $events = collect();
        if (in_array('events', $selectedSections)) {
            $events = Event::when($request->filled('start_date'), fn ($query) => $query->whereDate('start', '>=', $request->input('start_date')))
                ->when($request->filled('end_date'), fn ($query) => $query->whereDate('start', '<=', $request->input('end_date')))
                ->orderBy('start')->get();
        }

        $referrals = collect();
        if (in_array('referrals', $selectedSections)) {
            $referrals = Referral::when($request->filled('start_date'), fn ($query) => $query->whereDate('date_of_referral', '>=', $request->input('start_date')))
                ->when($request->filled('end_date'), fn ($query) => $query->whereDate('date_of_referral', '<=', $request->input('end_date')))
                ->latest('date_of_referral')->get();
        }

        $puroks = Purok::orderBy('name')->get();
        $categories = Supply::query()->select('category')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        return view('bhw.reports', compact(
            'availableSections', 'selectedSections', 'citizensReport', 'healthRecords',
            'supplies', 'events', 'referrals', 'puroks', 'categories', 'ageByPurok',
            'immunization', 'immunizationSummary', 'sexByPurok', 'pregnancyByPurok',
            'includeGraphics',
            'diagnosisSummary', 'diagnosisByPurok'
        ));
    }
}
