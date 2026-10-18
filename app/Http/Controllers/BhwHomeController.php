<?php

namespace App\Http\Controllers;

use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\Event;
use App\Models\Supply;
use App\Models\Referral;
use App\Models\Announcement;
use App\Models\MaintenanceRecord;
use App\Models\SupplyRelease;
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

        // Low stock supplies (next 5)
        $lowStockSupplies = Supply::whereColumn('quantity', '<=', 'min_stock')
            ->orderBy('quantity', 'asc')
            ->take(5)
            ->get();
        $totalInventory = Supply::count();
        $totalInventoryUnits = Supply::sum('quantity');
        $lowStockCount = Supply::whereColumn('quantity', '<=', 'min_stock')
            ->where('quantity', '>', 0)
            ->count();

        // Recent referrals (latest 5)
        $recentReferrals = Referral::latest()->take(5)->get();
        $recentReferralsCount = Referral::count();
        $pendingReferrals = Referral::whereIn('status', ['pending', 'approved'])->count();
        $activeMaintenance = MaintenanceRecord::where(function ($query) {
            $query->whereNull('end_date')->orWhereDate('end_date', '>=', today());
        })->count();
        $scheduledReleases = SupplyRelease::where('status', 'scheduled')->count();

        $purokCoordinates = [
            '1' => [14.0680, 120.8515],
            '2' => [14.0655, 120.8540],
            '3' => [14.0630, 120.8500],
            '4' => [14.0690, 120.8550],
            '5' => [14.0640, 120.8525],
        ];
        $citizensByPurok = citizens::select('Citizen_Purok')->get()
            ->groupBy(function ($citizen) {
                return preg_replace('/[^0-9]/', '', (string) $citizen->Citizen_Purok);
            })
            ->map->count();
        $purokHeatmapData = collect($purokCoordinates)->map(function ($coordinates, $purok) use ($citizensByPurok) {
            $count = (int) $citizensByPurok->get($purok, 0);
            return [
                'lat' => $coordinates[0],
                'lng' => $coordinates[1],
                'weight' => max(.25, min(1, $count / 10)),
                'label' => 'Purok ' . $purok,
                'count' => $count,
            ];
        })->values();

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
            , 'lowStockCount'
            , 'pendingReferrals'
            , 'activeMaintenance'
            , 'scheduledReleases'
            , 'purokHeatmapData'
        ));
    }
}