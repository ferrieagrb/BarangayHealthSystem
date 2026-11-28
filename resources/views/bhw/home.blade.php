@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<link rel="stylesheet" href="{{ asset('css/bhw/home.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/diagnosis-heatmap.css') }}?v={{ filemtime(public_path('css/bhw/diagnosis-heatmap.css')) }}">
<style>
    .bhw-home { padding-bottom: 28px; }
    .home-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .home-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(15, 23, 42, .05); }
    .home-card-wide { grid-column: 1 / -1; }
    .home-card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 15px; }
    .home-card h2 { color: #0f172a; font-size: 18px; }
    .home-card-header p { margin-top: 4px; color: #64748b; font-size: 13px; }
    .home-card-header a { color: #2563eb; font-size: 12px; font-weight: 600; text-decoration: none; white-space: nowrap; }
    .home-list { display: grid; gap: 9px; }
    .home-list-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px; border: 1px solid #eef2f7; border-radius: 9px; }
    .home-list-item strong { display: block; color: #1e293b; font-size: 13px; }
    .home-list-item small { display: block; margin-top: 3px; color: #64748b; }
    .home-list-item time { color: #2563eb; font-size: 12px; font-weight: 600; white-space: nowrap; }
    .home-status { padding: 4px 8px; border-radius: 999px; color: #92400e; background: #fef3c7; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .home-status.good { color: #166534; background: #dcfce7; }
    .home-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .home-table th, .home-table td { padding: 10px 8px; border-bottom: 1px solid #eef2f7; text-align: left; }
    .home-table th { color: #64748b; font-size: 11px; text-transform: uppercase; }
    .home-empty { padding: 18px; color: #64748b; text-align: center; }
    .home-map { height: 380px; overflow: hidden; border-radius: 10px; }
    @media (max-width: 800px) { .home-grid { grid-template-columns: 1fr; } .home-card-wide { grid-column: auto; } }
</style>
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/dashboard-mobile.css') }}">
@endsection

@section('content')
<div class="bhw-home">
    <div class="page-top">
        <div class="page-title-group">
            <h1>BHW Dashboard</h1>
            <p>Daily operational view for residents, health services, inventory, and follow-up work.</p>
        </div>
    </div>

    <div class="summary-cards">
        <div class="summary-card"><span>Total Citizens</span><strong>{{ $totalCitizens }}</strong><div class="dashboard-card-trend trend-{{ $dashboardTrends['citizens']['state'] }}"><b>{{ $dashboardTrends['citizens']['icon'] }} {{ $dashboardTrends['citizens']['value'] }}</b><small>vs previous 14 days</small></div><small>Registered residents</small></div>
        <div class="summary-card"><span>Inventory Units</span><strong>{{ $totalInventoryUnits }}</strong><div class="dashboard-card-trend trend-{{ $dashboardTrends['inventory']['state'] }}"><b>{{ $dashboardTrends['inventory']['icon'] }} {{ $dashboardTrends['inventory']['value'] }}</b><small>vs previous 14 days</small></div><small>{{ $totalInventory }} supply records</small></div>
        <div class="summary-card"><span>Total Referrals</span><strong>{{ $totalReferrals }}</strong><div class="dashboard-card-trend trend-{{ $dashboardTrends['referrals']['state'] }}"><b>{{ $dashboardTrends['referrals']['icon'] }} {{ $dashboardTrends['referrals']['value'] }}</b><small>vs previous 14 days</small></div><small>Recorded referral forms</small></div>
        <div class="summary-card"><span>Trending Diagnosis</span><strong>{{ $popularDisease }}</strong><div class="dashboard-card-trend trend-{{ $dashboardTrends['diagnoses']['state'] }}"><b>{{ $dashboardTrends['diagnoses']['icon'] }} {{ $dashboardTrends['diagnoses']['value'] }}</b><small>vs previous 14 days</small></div><small>Most recorded diagnosis</small></div>
    </div>

    <section class="home-card home-card-wide" style="margin-bottom: 18px;">
        <div class="home-card-header"><div><h2>Geospatial Diagnosis Heatmap</h2><p>Diagnosis hotspots in the last 7 days, by Purok and subgroup.</p></div><a href="{{ route('healthrecord') }}">Open health records</a></div>
        <div id="bhwCitizenHeatmap" class="home-map" role="region" aria-label="Diagnosis concentration heatmap" aria-describedby="bhwHeatmapStatus"></div>
        @include('bhw.partials.heatmap-controls', ['prefix' => 'bhwHeatmap'])
    </section>

    <div class="home-grid">
        <section class="home-card">
            <div class="home-card-header"><div><h2>Upcoming Events</h2><p>{{ $upcomingEventsCount }} scheduled activities</p></div><a href="{{ url('/calendar') }}">Open calendar</a></div>
            <div class="home-list">
                @forelse($upcomingEvents as $event)
                    <div class="home-list-item"><div><strong>{{ $event->title }}</strong><small>{{ $event->description ?? 'Barangay health activity' }}</small></div><time>{{ \Carbon\Carbon::parse($event->start)->format('M d, Y') }}</time></div>
                @empty <div class="home-empty">No upcoming events.</div> @endforelse
            </div>
        </section>

        <section class="home-card">
            <div class="home-card-header"><div><h2>Inventory Watch</h2><p>Supply items below their minimum level</p></div><a href="{{ route('supplies.index') }}">Manage stock</a></div>
            <div class="home-list">
                @forelse($lowStockSupplies as $supply)
                    <div class="home-list-item"><div><strong>{{ $supply->name }}</strong><small>Remaining: {{ $supply->quantity }} / Minimum: {{ $supply->min_stock }}</small></div><span class="home-status">Review</span></div>
                @empty <div class="home-empty">Inventory levels are currently stable.</div> @endforelse
            </div>
        </section>

        <section class="home-card home-card-wide">
            <div class="home-card-header"><div><h2>Recent Referrals</h2><p>Referral forms prepared at the health center</p></div><a href="{{ route('referrals.index') }}">View referrals</a></div>
            <table class="home-table" role="table" aria-label="Recent referrals"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Patient</th><th scope="col">Generated By</th></tr></thead><tbody role="rowgroup">
                @forelse($recentReferrals as $referral)
                    <tr role="row"><td role="cell" data-label="Date">{{ \Carbon\Carbon::parse($referral->date_of_referral)->format('M d, Y') }}</td><td role="cell" data-label="Patient">{{ $referral->name }}</td><td role="cell" data-label="Generated By">{{ $referral->user->name ?? 'Legacy / System' }}</td></tr>
                @empty <tr><td colspan="3" class="home-empty">No referrals recorded.</td></tr> @endforelse
            </tbody></table>
        </section>

        <section class="home-card home-card-wide">
            <div class="home-card-header"><div><h2>Announcements</h2><p>Latest community notices</p></div><a href="{{ route('announcements') }}">View all</a></div>
            <div class="home-list">
                @forelse($recentAnnouncements as $announcement)
                    <div class="home-list-item"><div><strong>{{ $announcement->title }}</strong><small>{{ $announcement->category ?? 'General notice' }}</small></div><time>{{ $announcement->created_at->format('M d') }}</time></div>
                @empty <div class="home-empty">No recent announcements.</div> @endforelse
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script id="bhwHeatmapData" type="application/json">@json($purokHeatmapData)</script>
<script src="{{ asset('js/bhw/diagnosis-heatmap.js') }}?v={{ filemtime(public_path('js/bhw/diagnosis-heatmap.js')) }}"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="{{ asset('js/bhw/analytics-heatmap.js') }}?v={{ filemtime(public_path('js/bhw/analytics-heatmap.js')) }}"></script>
<script src="{{ asset('js/bhw/home-heatmap.js') }}?v={{ filemtime(public_path('js/bhw/home-heatmap.js')) }}"></script>
<script>initBhwHomeMap();</script>
@endsection
