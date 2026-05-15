<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\Event;
use App\Models\Supply;
use App\Models\Referral;
use App\Models\Announcement;
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

        // Recent referrals (latest 5)
        $recentReferrals = Referral::latest()->take(5)->get();
        $recentReferralsCount = Referral::count();

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
        ));
    }
}