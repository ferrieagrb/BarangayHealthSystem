<?php

namespace App\Http\Controllers;

use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\Event;
use App\Models\Supply;
use App\Models\Referral;
use App\Models\Announcement;
use App\Models\Purok;
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

        $citizensByPurok = citizens::select('Citizen_Purok')->get()
            ->groupBy(function ($citizen) {
                return preg_replace('/[^0-9]/', '', (string) $citizen->Citizen_Purok);
            })
            ->map->count();
        $purokHeatmapData = Purok::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($purok) use ($citizensByPurok) {
            $purokKey = preg_replace('/[^0-9]/', '', (string) $purok->name);
            $count = (int) $citizensByPurok->get($purokKey, 0);
            return [
                'lat' => (float) $purok->latitude,
                'lng' => (float) $purok->longitude,
                'weight' => max(.25, min(1, $count / 10)),
                'label' => $purok->name,
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
            , 'pendingReferrals'
            , 'purokHeatmapData'
        ));
    }
}