@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/home.css') }}">
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&loading=async&libraries=geometry&callback=initBhwHomeMap" async defer></script>
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
    .heatmap-legend { display: flex; justify-content: space-between; margin-top: 8px; color: #64748b; font-size: 12px; }
    @media (max-width: 800px) { .home-grid { grid-template-columns: 1fr; } .home-card-wide { grid-column: auto; } }
</style>
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
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
        <div class="summary-card"><span>Pending Referrals</span><strong>{{ $pendingReferrals }}</strong><div class="dashboard-card-trend trend-{{ $dashboardTrends['referrals']['state'] }}"><b>{{ $dashboardTrends['referrals']['icon'] }} {{ $dashboardTrends['referrals']['value'] }}</b><small>vs previous 14 days</small></div><small>Awaiting follow-up</small></div>
        <div class="summary-card"><span>Trending Diagnosis</span><strong>{{ $popularDisease }}</strong><div class="dashboard-card-trend trend-{{ $dashboardTrends['diagnoses']['state'] }}"><b>{{ $dashboardTrends['diagnoses']['icon'] }} {{ $dashboardTrends['diagnoses']['value'] }}</b><small>vs previous 14 days</small></div><small>Most recorded diagnosis</small></div>
    </div>

    <section class="home-card home-card-wide" style="margin-bottom: 18px;">
        <div class="home-card-header"><div><h2>Diagnosis Heatmap</h2><p>Diagnoses recorded in the last 7 days, by Purok and subgroup boundary set in Demographics.</p></div><a href="{{ route('healthrecord') }}">Open health records</a></div>
        <div id="bhwCitizenHeatmap" class="home-map"></div>
        <div class="heatmap-legend"><span>Fewer diagnoses</span><span>More diagnoses</span></div>
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
            <div class="home-card-header"><div><h2>Referral Follow-up</h2><p>Recent patients requiring coordination</p></div><a href="{{ route('referrals.index') }}">View referrals</a></div>
            <table class="home-table"><thead><tr><th>Date</th><th>Patient</th><th>Request</th><th>Status</th></tr></thead><tbody>
                @forelse($recentReferrals as $referral)
                    <tr><td>{{ \Carbon\Carbon::parse($referral->date_of_referral)->format('M d, Y') }}</td><td>{{ $referral->name }}</td><td>{{ $referral->requests_for }}</td><td><span class="home-status {{ in_array($referral->status, ['released', 'returned']) ? 'good' : '' }}">{{ ucfirst($referral->status ?? 'pending') }}</span></td></tr>
                @empty <tr><td colspan="4" class="home-empty">No referrals recorded.</td></tr> @endforelse
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
<script>
function initBhwHomeMap() {
    const mapElement = document.getElementById('bhwCitizenHeatmap');
    if (!mapElement || typeof google === 'undefined') return;

    const map = new google.maps.Map(mapElement, {
        zoom: 15,
        center: { lat: 14.0668, lng: 120.8531 },
        mapTypeId: 'roadmap',
        disableDefaultUI: true,
        zoomControl: true
    });

    const areas = @json($purokHeatmapData);
    const infoWindow = new google.maps.InfoWindow();
    const bounds = new google.maps.LatLngBounds();

    function showInfo(area, position) {
        infoWindow.setContent(`<div style="color:#000;padding:5px;"><strong>${area.label}</strong><br>Diagnoses (last 7 days): ${area.count}</div>`);
        infoWindow.setPosition(position);
        infoWindow.open(map);
    }

    // Build heat points (centre + radius in metres) and thin clickable outlines.
    const heatPoints = [];
    areas.sort((a, b) => (a.type === b.type ? 0 : a.type === 'purok' ? -1 : 1));

    areas.forEach(function (area) {
        const ring = area.boundary && area.boundary.coordinates ? area.boundary.coordinates[0] : null;
        let center = null;
        let radius = 150;

        if (ring && ring.length >= 3) {
            const path = ring.map(function (p) { return { lat: p[1], lng: p[0] }; });
            const ab = new google.maps.LatLngBounds();
            path.forEach(function (p) { ab.extend(p); bounds.extend(p); });
            center = ab.getCenter().toJSON();
            const span = google.maps.geometry && google.maps.geometry.spherical
                ? google.maps.geometry.spherical.computeDistanceBetween(ab.getSouthWest(), ab.getNorthEast())
                : 300;
            radius = Math.max(80, span * 0.6);
            const polygon = new google.maps.Polygon({
                paths: path,
                map,
                strokeColor: area.type === 'purok' ? '#1e3a8a' : '#334155',
                strokeOpacity: .8,
                strokeWeight: area.type === 'purok' ? 2 : 1.5,
                fillColor: '#000',
                fillOpacity: 0.001,
                zIndex: area.type === 'purok' ? 1 : 2
            });
            polygon.addListener('click', function (event) { showInfo(area, event.latLng); });
        } else if (area.lat !== null && area.lng !== null) {
            center = { lat: Number(area.lat), lng: Number(area.lng) };
            bounds.extend(center);
        }

        if (center && area.weight > 0) {
            heatPoints.push({ lat: center.lat, lng: center.lng, weight: area.weight, radius: radius });
        }
    });

    const maxWeight = Math.max(5, ...heatPoints.map(function (p) { return p.weight; }));

    // Colour ramp: transparent -> blue -> green -> yellow -> red.
    const ramp = (function () {
        const c = document.createElement('canvas');
        c.width = 1; c.height = 256;
        const g = c.getContext('2d');
        const grad = g.createLinearGradient(0, 0, 0, 256);
        grad.addColorStop(0.0, 'rgba(0,0,255,0)');
        grad.addColorStop(0.2, 'rgba(0,0,255,0.6)');
        grad.addColorStop(0.4, 'rgba(0,200,255,0.8)');
        grad.addColorStop(0.55, 'rgba(0,220,60,0.85)');
        grad.addColorStop(0.75, 'rgba(255,230,0,0.9)');
        grad.addColorStop(1.0, 'rgba(255,0,0,0.95)');
        g.fillStyle = grad;
        g.fillRect(0, 0, 1, 256);
        return g.getImageData(0, 0, 1, 256).data;
    })();

    class HeatOverlay extends google.maps.OverlayView {
        constructor() {
            super();
            this.canvas = document.createElement('canvas');
            this.canvas.style.position = 'absolute';
            this.canvas.style.pointerEvents = 'none';
        }
        onAdd() { this.getPanes().overlayLayer.appendChild(this.canvas); }
        onRemove() { this.canvas.remove(); }
        draw() {
            const projection = this.getProjection();
            const mapBounds = map.getBounds();
            if (!projection || !mapBounds) return;
            const sw = projection.fromLatLngToDivPixel(mapBounds.getSouthWest());
            const ne = projection.fromLatLngToDivPixel(mapBounds.getNorthEast());
            const width = Math.round(ne.x - sw.x);
            const height = Math.round(sw.y - ne.y);
            if (width <= 0 || height <= 0) return;

            this.canvas.style.left = sw.x + 'px';
            this.canvas.style.top = ne.y + 'px';
            this.canvas.width = width;
            this.canvas.height = height;

            const shade = document.createElement('canvas');
            shade.width = width; shade.height = height;
            const sg = shade.getContext('2d');
            const mpp = 156543.03392 * Math.cos(map.getCenter().lat() * Math.PI / 180) / Math.pow(2, map.getZoom());

            heatPoints.forEach(function (p) {
                const pt = projection.fromLatLngToDivPixel(new google.maps.LatLng(p.lat, p.lng));
                const x = pt.x - sw.x;
                const y = pt.y - ne.y;
                const r = Math.max(30, p.radius / mpp);
                const alpha = Math.min(1, p.weight / maxWeight);
                const rg = sg.createRadialGradient(x, y, 0, x, y, r);
                rg.addColorStop(0, 'rgba(0,0,0,' + alpha + ')');
                rg.addColorStop(1, 'rgba(0,0,0,0)');
                sg.fillStyle = rg;
                sg.fillRect(x - r, y - r, r * 2, r * 2);
            });

            const img = sg.getImageData(0, 0, width, height);
            const d = img.data;
            for (let i = 0; i < d.length; i += 4) {
                const a = d[i + 3];
                if (!a) continue;
                const o = a * 4;
                d[i] = ramp[o]; d[i + 1] = ramp[o + 1]; d[i + 2] = ramp[o + 2];
                d[i + 3] = Math.min(255, ramp[o + 3] * 0.85);
            }
            this.canvas.getContext('2d').putImageData(img, 0, 0);
        }
    }

    const overlay = new HeatOverlay();
    overlay.setMap(map);

    if (!bounds.isEmpty()) map.fitBounds(bounds, 40);
}
</script>
@endsection