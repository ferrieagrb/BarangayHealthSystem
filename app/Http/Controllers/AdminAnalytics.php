<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\HealthRecord;
use App\Models\MaintenanceRecord;
use App\Models\Referral;
use App\Models\Supply;
use App\Models\SupplyLog;
use App\Models\SupplyRelease;
use App\Models\User;
use App\Models\citizens;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAnalytics extends Controller
{
    public function index()
    {
        $totalCitizens = citizens::count();
        $totalUsers = User::count();
        $bhwUsers = User::where('role', 'bhw')->count();
        $totalHealthRecords = HealthRecord::count();
        $activeMaintenance = MaintenanceRecord::where(function ($query) {
            $query->whereNull('end_date')->orWhereDate('end_date', '>=', today());
        })->count();

        $totalInventory = Supply::sum('quantity');
        $lowStockItems = Supply::whereColumn('quantity', '<=', 'min_stock')
            ->where('quantity', '>', 0)
            ->count();
        $outOfStockItems = Supply::where('quantity', '<=', 0)->count();
        $pendingReferrals = Referral::whereIn('status', ['pending', 'approved'])->count();
        $upcomingEvents = Event::whereDate('start', '>=', today())->count();
        $scheduledReleases = SupplyRelease::where('status', 'scheduled')->count();
        $claimedReleases = SupplyRelease::where('status', 'claimed')->count();

        $monthStart = now()->subMonths(5)->startOfMonth();
        $monthLabels = collect(range(5, 0))->map(function ($monthsAgo) {
            return now()->subMonths($monthsAgo)->format('M Y');
        })->values();

        $healthRecordsByMonth = HealthRecord::where('created_at', '>=', $monthStart)
            ->get()
            ->groupBy(fn ($record) => Carbon::parse($record->created_at)->format('M Y'));
        $healthTrend = $monthLabels->map(fn ($label) => $healthRecordsByMonth->get($label, collect())->count())->values();

        $purokCounts = citizens::select('Citizen_Purok')
            ->get()
            ->groupBy(fn ($citizen) => $citizen->Citizen_Purok ?: 'Unassigned')
            ->map->count()
            ->sortDesc();

        $inventoryByCategory = Supply::select('category')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->groupBy('category')
            ->orderByDesc('total_quantity')
            ->get();

        $topInventory = $inventoryByCategory->take(6);
        $inventoryRisk = Supply::whereColumn('quantity', '<=', 'min_stock')
            ->orderBy('quantity')
            ->take(8)
            ->get(['name', 'quantity', 'min_stock']);
        $referralStatusCounts = Referral::select('status')
            ->get()
            ->groupBy(fn ($referral) => ucfirst($referral->status ?: 'unknown'))
            ->map->count();
        $recentSupplyActivity = SupplyLog::latest()->with(['supply', 'citizen'])->take(8)->get();
        $recentEvents = Event::whereDate('start', '>=', today())->orderBy('start')->take(5)->get();

        $pageViewsToday = DB::getSchemaBuilder()->hasTable('page_views')
            ? DB::table('page_views')->whereDate('created_at', today())->count()
            : 0;

        return view('admin.admin_analytics', compact(
            'totalCitizens', 'totalUsers', 'bhwUsers', 'totalHealthRecords',
            'activeMaintenance', 'totalInventory', 'lowStockItems', 'outOfStockItems',
            'pendingReferrals', 'upcomingEvents', 'scheduledReleases', 'claimedReleases',
            'monthLabels', 'healthTrend', 'purokCounts',
            'topInventory', 'inventoryRisk', 'referralStatusCounts',
            'recentSupplyActivity', 'recentEvents', 'pageViewsToday'
        ));
    }
}
