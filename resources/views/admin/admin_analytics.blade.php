@extends('templates.admin')

@section('CSSown')
<style>
    .reports-page { padding: 0; }
    .reports-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin: 20px 0; }
    .report-card, .report-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 2px 8px rgba(15, 23, 42, .05); }
    .report-card { padding: 18px; }
    .report-card span { color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .report-card strong { display: block; margin-top: 8px; color: #0f172a; font-size: 28px; }
    .report-card small { color: #64748b; }
    .report-counter { opacity: 0; transition: opacity .2s ease; }
    .report-counter.counter-ready { opacity: 1; }
    .report-layout { display: grid; grid-template-columns: 1.2fr .8fr; gap: 18px; margin-bottom: 18px; }
    .report-panel { padding: 20px; }
    .chart-panel { min-height: 360px; }
    .chart-container { min-height: 280px; }
    .report-panel h2 { margin: 0 0 4px; color: #0f172a; font-size: 18px; }
    .report-panel > p { margin: 0 0 16px; color: #64748b; font-size: 13px; }
    .bar-list { display: grid; gap: 12px; }
    .bar-label { display: flex; justify-content: space-between; color: #334155; font-size: 13px; margin-bottom: 5px; }
    .bar-track { height: 9px; overflow: hidden; border-radius: 99px; background: #e2e8f0; }
    .bar-fill { height: 100%; border-radius: inherit; background: #2563eb; }
    .report-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .report-table th, .report-table td { padding: 10px 8px; border-bottom: 1px solid #eef2f7; text-align: left; }
    .report-table th { color: #64748b; font-size: 11px; text-transform: uppercase; }
    .status-warning { color: #b45309; font-weight: 700; }
    .status-good { color: #15803d; font-weight: 700; }
    .empty-report { padding: 22px; color: #64748b; text-align: center; }
    @media (max-width: 1000px) { .reports-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .report-layout { grid-template-columns: 1fr; } }
    @media (max-width: 600px) { .reports-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="reports-page">
    <div class="page-top">
        <div class="page-title-group">
            <h1>Reports &amp; Operations</h1>
            <p>Operational overview of residents, health services, inventory, and scheduled distributions.</p>
        </div>
    </div>

    <div class="reports-grid">
        <div class="report-card"><span>Registered Citizens</span><strong class="report-counter" data-count="{{ $totalCitizens }}">0</strong><small>Resident profiles</small></div>
        <div class="report-card"><span>Health Records</span><strong class="report-counter" data-count="{{ $totalHealthRecords }}">0</strong><small><span class="report-counter" data-count="{{ $activeMaintenance }}">0</span> active maintenance</small></div>
        <div class="report-card"><span>Inventory Units</span><strong class="report-counter" data-count="{{ $totalInventory }}">0</strong><small><span class="report-counter" data-count="{{ $lowStockItems }}">0</span> low stock, <span class="report-counter" data-count="{{ $outOfStockItems }}">0</span> empty</small></div>
        <div class="report-card"><span>Today</span><strong class="report-counter" data-count="{{ $pageViewsToday }}">0</strong><small>Page views</small></div>
    </div>

    <div class="reports-grid">
        <div class="report-card"><span>System Users</span><strong class="report-counter" data-count="{{ $totalUsers }}">0</strong><small><span class="report-counter" data-count="{{ $bhwUsers }}">0</span> BHW accounts</small></div>
        <div class="report-card"><span>Referral Workload</span><strong class="report-counter" data-count="{{ $pendingReferrals }}">0</strong><small>Pending or approved referrals</small></div>
        <div class="report-card"><span>Upcoming Events</span><strong class="report-counter" data-count="{{ $upcomingEvents }}">0</strong><small>From today onward</small></div>
        <div class="report-card"><span>Supply Releases</span><strong class="report-counter" data-count="{{ $scheduledReleases }}">0</strong><small><span class="report-counter" data-count="{{ $claimedReleases }}">0</span> claimed</small></div>
    </div>

    <div class="report-layout">
        <section class="report-panel chart-panel">
            <h2>Health Records Trend</h2>
            <p>New health records created during the last six months.</p>
            <div id="healthTrendChart" class="chart-container"></div>
        </section>

        <section class="report-panel chart-panel">
            <h2>Citizens by Purok</h2>
            <p>Resident distribution across the barangay.</p>
            <div id="purokChart" class="chart-container"></div>
        </section>
    </div>

    <div class="report-layout">
        <section class="report-panel chart-panel">
            <h2>Inventory Depletion Risk</h2>
            <p>Current stock compared with each item’s minimum threshold. Items at or below the threshold need review.</p>
            <div id="inventoryRiskChart" class="chart-container"></div>
        </section>

        <section class="report-panel chart-panel">
            <h2>Referral Workload</h2>
            <p>Current referral status distribution to help prioritize follow-up.</p>
            <div id="referralWorkloadChart" class="chart-container"></div>
        </section>
    </div>

    <section class="report-panel chart-panel" style="margin-bottom: 18px;">
        <h2>Inventory by Category</h2>
        <p>Total available units grouped by supply category.</p>
        <div id="inventoryCategoryChart" class="chart-container"></div>
    </section>

    <div class="report-layout">
        <section class="report-panel">
            <h2>Inventory by Category</h2>
            <p>Current available units grouped by supply category.</p>
            <table class="report-table">
                <thead><tr><th>Category</th><th>Total Units</th><th>Risk</th></tr></thead>
                <tbody>
                    @forelse($topInventory as $inventory)
                        <tr>
                            <td>{{ $inventory->category ?: 'Uncategorized' }}</td>
                            <td>{{ number_format($inventory->total_quantity) }}</td>
                            <td class="{{ $inventory->total_quantity <= 5 ? 'status-warning' : 'status-good' }}">{{ $inventory->total_quantity <= 5 ? 'Review' : 'Stable' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-report">No inventory data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="report-panel">
            <h2>Upcoming Schedule</h2>
            <p>Next community and distribution events.</p>
            <table class="report-table">
                <thead><tr><th>Date</th><th>Event</th></tr></thead>
                <tbody>
                    @forelse($recentEvents as $event)
                        <tr><td>{{ \Carbon\Carbon::parse($event->start)->format('M d, Y') }}</td><td>{{ $event->title }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="empty-report">No upcoming events.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>

    <section class="report-panel">
        <h2>Recent Supply Activity</h2>
        <p>Latest inventory movements for administrative review.</p>
        <table class="report-table">
            <thead><tr><th>Date</th><th>Action</th><th>Supply</th><th>Quantity</th><th>Citizen</th></tr></thead>
            <tbody>
                @forelse($recentSupplyActivity as $log)
                    <tr>
                        <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                        <td>{{ ucfirst($log->action) }}</td>
                        <td>{{ $log->supply->name ?? 'Removed supply' }}</td>
                        <td>{{ $log->quantity }}</td>
                        <td>{{ $log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'General inventory' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-report">No supply activity recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.report-counter').forEach(function (counter) {
        const target = Number(counter.dataset.count || 0);
        const duration = 1800;
        const started = performance.now();

        function tick(now) {
            const progress = Math.min((now - started) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            counter.textContent = Math.round(target * eased).toLocaleString();
            if (progress < 1) requestAnimationFrame(tick);
        }

        counter.classList.add('counter-ready');
        requestAnimationFrame(tick);
    });

    if (typeof ApexCharts === 'undefined') return;

    new ApexCharts(document.querySelector('#healthTrendChart'), {
        chart: { type: 'area', height: 280, toolbar: { show: false } },
        series: [{ name: 'Health records', data: @json($healthTrend->values()) }],
        xaxis: { categories: @json($monthLabels->values()) },
        colors: ['#2563eb'],
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        grid: { borderColor: '#e5e7eb' },
        yaxis: { min: 0, forceNiceScale: true }
    }).render();

    new ApexCharts(document.querySelector('#purokChart'), {
        chart: { type: 'donut', height: 280 },
        series: @json($purokCounts->values()),
        labels: @json($purokCounts->keys()->values()),
        colors: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: true }
    }).render();

    new ApexCharts(document.querySelector('#inventoryRiskChart'), {
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        series: [
            { name: 'Current quantity', data: @json($inventoryRisk->pluck('quantity')->values()) },
            { name: 'Minimum threshold', data: @json($inventoryRisk->pluck('min_stock')->values()) }
        ],
        xaxis: { categories: @json($inventoryRisk->pluck('name')->values()) },
        colors: ['#ef4444', '#94a3b8'],
        plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        dataLabels: { enabled: false },
        legend: { position: 'bottom' },
        grid: { borderColor: '#e5e7eb' }
    }).render();

    new ApexCharts(document.querySelector('#referralWorkloadChart'), {
        chart: { type: 'donut', height: 280 },
        series: @json($referralStatusCounts->values()),
        labels: @json($referralStatusCounts->keys()->values()),
        colors: ['#f59e0b', '#2563eb', '#10b981', '#ef4444', '#64748b'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: true }
    }).render();

    new ApexCharts(document.querySelector('#inventoryCategoryChart'), {
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        series: [{ name: 'Units', data: @json($topInventory->pluck('total_quantity')->values()) }],
        colors: ['#0e42b1'],
        plotOptions: { bar: { borderRadius: 5, horizontal: true } },
        dataLabels: { enabled: false },
        grid: { borderColor: '#e5e7eb' },
        xaxis: { categories: @json($topInventory->pluck('category')->values()), min: 0 }
    }).render();
});
</script>
@endsection