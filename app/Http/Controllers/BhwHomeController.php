<?php

namespace App\Http\Controllers;

use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\Event;
use App\Models\Supply;
use App\Models\Referral;
use App\Models\Announcement;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use Illuminate\Support\Facades\DB;

class BhwHomeController extends Controller
{
    public function index()
    {
        // Total citizens
        $totalCitizens = citizens::count();

        // Most common disease
        $popularDisease = HealthRecord::select('diagnosis', DB::raw('COUNT(*) as total'))
            ->groupBy('diagnosis')
            ->orderByDesc('total')
            ->first();
        $popularDisease = $popularDisease ? $popularDisease->diagnosis : 'N/A';

        // Upcoming events (next 5)
        $upcomingEvents = Event::whereDate('start', '>=', now())
            ->orderBy('start', 'asc')
            ->take(5)
            ->get();
        $upcomingEventsCount = $upcomingEvents->count();

        // Recent announcements (latest 5)
        $recentAnnouncements = Announcement::latest()
            ->take(5)
            ->get();

        // Inventory watch list for the dashboard panel
        $lowStockSupplies = Supply::whereColumn('quantity', '<=', 'min_stock')
            ->orderBy('quantity', 'asc')
            ->take(5)
            ->get();
        $totalInventory = Supply::count();
        $totalInventoryUnits = Supply::sum('quantity');

        // Recent referrals (latest 5)
        $recentReferrals = Referral::latest()->take(5)->get();
        $recentReferralsCount = Referral::count();
        $pendingReferrals = Referral::whereIn('status', ['pending', 'approved'])->count();

        $latestPeriodStart = now()->subDays(13)->startOfDay();
        $previousPeriodStart = now()->subDays(27)->startOfDay();
        $previousPeriodEnd = now()->subDays(14)->endOfDay();
        $rate = function (int|float $current, int|float $previous): array {
            if ($previous == 0) {
                return [
                    'value' => $current > 0 ? '+100%' : 'No change',
                    'state' => $current > 0 ? 'up' : 'flat',
                    'icon' => $current > 0 ? '▲' : '•',
                ];
            }

            $change = round((($current - $previous) / $previous) * 100, 1);

            return [
                'value' => $change > 0 ? '+' . abs($change) . '%' : ($change < 0 ? '-' . abs($change) . '%' : 'No change'),
                'state' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat'),
                'icon' => $change > 0 ? '▲' : ($change < 0 ? '▼' : '•'),
            ];
        };

        $dashboardTrends = [
            'citizens' => $rate(
                citizens::whereBetween('created_at', [$latestPeriodStart, now()])->count(),
                citizens::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count()
            ),
            'inventory' => $rate(
                Supply::whereBetween('created_at', [$latestPeriodStart, now()])->sum('quantity'),
                Supply::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->sum('quantity')
            ),
            'referrals' => $rate(
                Referral::whereIn('status', ['pending', 'approved'])->whereBetween('created_at', [$latestPeriodStart, now()])->count(),
                Referral::whereIn('status', ['pending', 'approved'])->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count()
            ),
            'diagnoses' => $rate(
                HealthRecord::whereBetween('created_at', [$latestPeriodStart, now()])->count(),
                HealthRecord::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count()
            ),
        ];

        // Heatmap: diagnoses recorded in the last 7 days, grouped by the
        // citizen's subgroup (falling back to their family's subgroup / Purok).
        $recentRecords = HealthRecord::with('citizen.family')
            ->where('created_at', '>=', now()->subDays(7)->startOfDay())
            ->get();
        $subgroupCounts = [];
        $purokCounts = [];
        $purokIdByName = Purok::pluck('id', 'name');
        $subgroupPurokIds = PurokSubgroup::pluck('purok_id', 'id');

        foreach ($recentRecords as $record) {
            $citizen = $record->citizen;
            if (!$citizen) {
                continue;
            }

            $subgroupId = $citizen->subgroup_id ?? $citizen->family?->subgroup_id;
            $purokId = $subgroupId
                ? ($subgroupPurokIds[$subgroupId] ?? null)
                : ($citizen->family?->purok_id ?? $purokIdByName[$citizen->Citizen_Purok] ?? null);

            if ($subgroupId) {
                $subgroupCounts[$subgroupId] = ($subgroupCounts[$subgroupId] ?? 0) + 1;
            }
            if ($purokId) {
                $purokCounts[$purokId] = ($purokCounts[$purokId] ?? 0) + 1;
            }
        }

        $purokHeatmapData = Purok::all()->map(fn ($purok) => [
            'type' => 'purok',
            'label' => $purok->name,
            'lat' => $purok->latitude,
            'lng' => $purok->longitude,
            'boundary' => $purok->boundary,
            'count' => (int) ($purokCounts[$purok->id] ?? 0),
        ])->concat(
            PurokSubgroup::with('purok')->get()->map(fn ($subgroup) => [
                'type' => 'subgroup',
                'label' => trim(($subgroup->purok->name ?? '') . ' / ' . $subgroup->name, ' /'),
                'lat' => $subgroup->latitude,
                'lng' => $subgroup->longitude,
                'boundary' => $subgroup->boundary,
                'count' => (int) ($subgroupCounts[$subgroup->id] ?? 0),
            ])
        )->filter(fn ($area) => $area['boundary'] || ($area['lat'] !== null && $area['lng'] !== null))
            ->values();

        return view('bhw.home', compact(
            'totalCitizens',
            'popularDisease',
            'upcomingEvents',
            'upcomingEventsCount',
            'recentAnnouncements',
            'lowStockSupplies',
            'totalInventory',
            'recentReferrals',
            'recentReferralsCount'
            , 'totalInventoryUnits'
            , 'pendingReferrals'
            , 'dashboardTrends'
            , 'purokHeatmapData'
        ));
    }
}