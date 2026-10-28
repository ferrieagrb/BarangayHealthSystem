<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use App\Models\CitizenActivityLog;
use App\Models\MaintenanceRecord;
use App\Models\Supply;
use App\Models\SupplyLog;
use App\Models\UserPermission;
use App\Models\VaccinationRecord;
use App\Models\citizens;
use App\Services\BhwDiseaseMonitoringService;
use App\Services\BhwCitizenAccess;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class BhwAnalyticsController extends Controller
{
    public function index(
        Request $request,
        BhwDiseaseMonitoringService $diseaseMonitoring,
        BhwCitizenAccess $citizenAccess
    )
    {
        $user = auth()->user();
        abort_unless($user && $user->role === 'bhw', 403);
        $hasReportAccess = UserPermission::where('user_id', $user->id)
            ->where('feature', 'reports')
            ->where('access', '!=', 'none')
            ->exists();
        abort_unless($hasReportAccess, 403, 'Reports access has not been granted to this BHW.');

        $citizenQuery = $citizenAccess->scopeCitizens(citizens::query(), $user);

        $citizenIds = (clone $citizenQuery)->select('id');
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

        $filters = $request->validate([
            'disease_period' => ['nullable', Rule::in(['today', 'last7', 'last30', 'this_month', 'previous_month', 'this_year', 'custom'])],
            'disease_start' => ['required_if:disease_period,custom', 'nullable', 'date'],
            'disease_end' => ['required_if:disease_period,custom', 'nullable', 'date', 'after_or_equal:disease_start'],
            'disease_name' => ['nullable', 'string', 'max:255'],
            'disease_purok' => ['nullable', 'integer', 'exists:puroks,id'],
            'disease_subgroup' => ['nullable', 'integer', 'exists:purok_subgroups,id'],
        ]);
        $period = $filters['disease_period'] ?? 'last30';
        $today = now()->startOfDay();
        [$periodStart, $periodEnd] = match ($period) {
            'today' => [$today->copy(), $today->copy()->endOfDay()],
            'last7' => [$today->copy()->subDays(6), now()->endOfDay()],
            'last30' => [$today->copy()->subDays(29), now()->endOfDay()],
            'this_month' => [$today->copy()->startOfMonth(), now()->endOfDay()],
            'previous_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->startOfMonth()->subSecond()],
            'this_year' => [$today->copy()->startOfYear(), now()->endOfDay()],
            'custom' => [\Illuminate\Support\Carbon::parse($filters['disease_start'])->startOfDay(), \Illuminate\Support\Carbon::parse($filters['disease_end'])->endOfDay()],
        };
        $periodLabel = match ($period) {
            'today' => 'Today',
            'last7' => 'Last 7 days',
            'last30' => 'Last 30 days',
            'this_month' => 'This month',
            'previous_month' => 'Previous month',
            'this_year' => 'This year',
            default => $periodStart->format('M j, Y') . ' – ' . $periodEnd->format('M j, Y'),
        };
        $diseaseMonitoringData = $diseaseMonitoring->build(
            $user,
            $periodStart,
            $periodEnd,
            isset($filters['disease_name']) && $filters['disease_name'] !== ''
                ? mb_strtolower(trim($filters['disease_name']))
                : null,
            isset($filters['disease_purok']) ? (int) $filters['disease_purok'] : null,
            isset($filters['disease_subgroup']) ? (int) $filters['disease_subgroup'] : null,
        );

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
            'immunizationRisk',
            'children',
            'supplyRecommendations',
            'diseaseMonitoringData',
            'period',
            'periodStart',
            'periodEnd',
            'periodLabel',
            'filters'
        ));
    }
}