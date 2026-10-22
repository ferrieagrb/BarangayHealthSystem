<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use App\Models\CitizenActivityLog;
use App\Models\MaintenanceRecord;
use App\Models\PurokSubgroup;
use App\Models\Supply;
use App\Models\SupplyLog;
use App\Models\UserPermission;
use App\Models\VaccinationRecord;
use App\Models\citizens;
use Illuminate\Http\Request;

class BhwAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $hasReportAccess = UserPermission::where('user_id', $user->id)
            ->where('feature', 'reports')
            ->where('access', '!=', 'none')
            ->exists();
        abort_unless($hasReportAccess, 403, 'Reports access has not been granted to this BHW.');

        $canViewAll = UserPermission::where('user_id', $user->id)
            ->where('feature', 'view_all_citizens')
            ->where('access', '!=', 'none')
            ->exists();
        $citizenQuery = citizens::query();

        if (!$canViewAll) {
            $assignedSubgroups = PurokSubgroup::where('user_id', $user->id)->pluck('id');
            $citizenQuery->where(function ($query) use ($assignedSubgroups) {
                $query->whereIn('subgroup_id', $assignedSubgroups)
                    ->orWhereHas('family', fn ($family) => $family->whereIn('subgroup_id', $assignedSubgroups));
            });
        }

        $citizenIds = (clone $citizenQuery)->pluck('id');
        $today = now()->startOfDay();
        $currentStart = $today->copy()->subDays(13);
        $previousStart = $today->copy()->subDays(27);
        $previousEnd = $today->copy()->subDays(14)->endOfDay();

        $currentDiagnosisRecords = HealthRecord::whereIn('citizen_id', $citizenIds)
            ->whereBetween('created_at', [$currentStart, now()]);
        $previousDiagnosisRecords = HealthRecord::whereIn('citizen_id', $citizenIds)
            ->whereBetween('created_at', [$previousStart, $previousEnd]);

        $currentMaintenanceRecords = MaintenanceRecord::whereIn('citizen_id', $citizenIds)
            ->whereBetween('created_at', [$currentStart, now()]);
        $previousMaintenanceRecords = MaintenanceRecord::whereIn('citizen_id', $citizenIds)
            ->whereBetween('created_at', [$previousStart, $previousEnd]);

        $currentVaccinationRecords = VaccinationRecord::whereIn('citizen_id', $citizenIds)
            ->whereBetween('created_at', [$currentStart, now()]);
        $previousVaccinationRecords = VaccinationRecord::whereIn('citizen_id', $citizenIds)
            ->whereBetween('created_at', [$previousStart, $previousEnd]);

        $currentDiagnosisCount = (clone $currentDiagnosisRecords)->count();
        $currentMaintenanceCount = (clone $currentMaintenanceRecords)->count();
        $currentVaccinationCount = (clone $currentVaccinationRecords)->count();
        $previousDiagnosisCount = (clone $previousDiagnosisRecords)->count();
        $previousMaintenanceCount = (clone $previousMaintenanceRecords)->count();
        $previousVaccinationCount = (clone $previousVaccinationRecords)->count();
        $currentCount = $currentDiagnosisCount + $currentMaintenanceCount + $currentVaccinationCount;
        $previousCount = $previousDiagnosisCount + $previousMaintenanceCount + $previousVaccinationCount;
        $change = $previousCount > 0 ? round((($currentCount - $previousCount) / $previousCount) * 100, 1) : null;

        $forecast = max(0, $currentCount + ($currentCount - $previousCount));

        $citizenActivity = CitizenActivityLog::where('module', 'citizen')
            ->whereBetween('created_at', [$currentStart, now()]);
        $citizenAdditions = (clone $citizenActivity)->whereIn('action', ['create', 'import'])->count();
        $citizenDetailEdits = (clone $citizenActivity)->where('action', 'update')->count();

        $diagnosisByPurok = (clone $currentDiagnosisRecords)
            ->with('citizen')
            ->get()
            ->groupBy(fn ($record) => $record->citizen?->Citizen_Purok ?: 'Unassigned')
            ->map(fn ($records, $purok) => [
                'purok' => $purok,
                'records' => $records->count(),
                'top_diagnosis' => $records->groupBy(fn ($record) => $record->diagnosis ?: 'Unspecified')->map->count()->sortDesc()->keys()->first() ?: 'Unspecified',
            ])
            ->sortByDesc('records')
            ->values()
            ->take(5);

        $children = (clone $citizenQuery)->where('Citizen_Age', '<=', 5)->with('vaccinations')->get();
        $immunizationRisk = $children->filter(function ($citizen) {
            $vaccines = $citizen->vaccinations->pluck('vaccine_name')->map(fn ($name) => strtolower((string) $name));
            $has = fn ($terms) => $vaccines->contains(fn ($name) => collect($terms)->contains(fn ($term) => str_contains($name, $term)));

            return !$has(['bcg']) || !$has(['polio']) || !$has(['measles', 'mmr']);
        })->count();

        $medicineUseCases = [
            'Fever, flu symptoms, or pain' => ['biogesic', 'paracetamol', 'fever', 'flu', 'pain'],
            'Colds or nasal congestion' => ['neozep', 'cold', 'colds', 'nasal', 'congestion'],
            'Cough or phlegm' => ['solmux', 'lagundi', 'cough', 'phlegm'],
            'Dehydration or diarrhea support' => ['oral rehydration', 'ors', 'loperamide', 'dehydration', 'diarrhea'],
        ];

        $supplyRecommendations = Supply::query()->get()->map(function ($supply) use ($medicineUseCases) {
            $recentWithdrawals = SupplyLog::where('supply_id', $supply->id)
                ->whereIn('action', ['release', 'withdraw'])
                ->where('created_at', '>=', now()->subDays(14))
                ->get(['quantity', 'notes']);
            $withdrawnLast14Days = $recentWithdrawals->sum('quantity');
            $dailyUsage = $withdrawnLast14Days / 14;
            $daysRemaining = $dailyUsage > 0 ? (int) floor($supply->quantity / $dailyUsage) : null;
            $medicineContext = strtolower($supply->name . ' ' . $recentWithdrawals->pluck('notes')->filter()->implode(' '));
            $purpose = collect($medicineUseCases)->search(
                fn ($keywords) => collect($keywords)->contains(fn ($keyword) => str_contains($medicineContext, $keyword))
            );
            $purpose = $purpose === false ? 'Other medicine or supply' : $purpose;

            return [
                'name' => $supply->name,
                'purpose' => $purpose,
                'quantity' => (int) $supply->quantity,
                'min_stock' => (int) $supply->min_stock,
                'daily_usage' => round($dailyUsage, 1),
                'withdrawn_quantity' => (int) $withdrawnLast14Days,
                'forecast_quantity' => (int) $withdrawnLast14Days,
                'withdrawal_count' => $recentWithdrawals->count(),
                'days_remaining' => $daysRemaining,
                'priority' => $supply->quantity <= $supply->min_stock || ($daysRemaining !== null && $daysRemaining <= 30) ? 'Review soon' : 'Stable',
            ];
        })->sortBy(function ($supply) {
            return $supply['days_remaining'] ?? 999999;
        })->take(8)->values();

        return view('bhw.analytics', compact(
            'currentCount',
            'previousCount',
            'change',
            'forecast',
            'currentDiagnosisCount',
            'currentMaintenanceCount',
            'currentVaccinationCount',
            'citizenAdditions',
            'citizenDetailEdits',
            'diagnosisByPurok',
            'immunizationRisk',
            'children',
            'supplyRecommendations'
        ));
    }
}