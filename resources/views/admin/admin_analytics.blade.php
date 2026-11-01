@extends('templates.admin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/admin/admin_analytics.css') }}">
@endsection

@section('content')
<div class="reports-page">
    <div class="page-top">
        <div class="page-title-group">
            <h1>Reports Operations</h1>
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

    <section class="report-panel chart-panel workload-forecast-panel">
        <div class="workload-forecast-heading">
            <div>
                <h2>Diagnosis-record workload forecast</h2>
                <p>Monthly diagnosis entries by consultation date. Forecasts cover the next three months and are not counts of confirmed new disease cases.</p>
            </div>
            <span class="workload-forecast-status {{ $workloadForecast['available'] ? 'available' : 'insufficient' }}">
                {{ $workloadForecast['available'] ? 'Baseline forecast' : 'Insufficient history' }}
            </span>
        </div>

        @if($workloadChartLabels)
            <div id="diagnosisWorkloadForecastChart" class="chart-container" role="img" aria-label="Monthly recorded diagnosis workload and short-term forecast"></div>
        @else
            <div class="empty-report">No health records with a consultation date are available to chart.</div>
        @endif

        @if($workloadForecast['available'])
            <div class="workload-forecast-metrics">
                <span>Selected method: <strong>{{ $workloadForecast['method'] }}</strong></span>
                <span>Rolling backtest MAE: <strong>{{ number_format($workloadForecast['mae'], 2) }}</strong> records/month</span>
                <span>Last-month baseline MAE: <strong>{{ number_format($workloadForecast['baseline_mae'], 2) }}</strong></span>
                <span>Backtest WAPE: <strong>{{ $workloadForecast['wape'] === null ? 'N/A (zero actual total)' : number_format($workloadForecast['wape'], 1) . '%' }}</strong></span>
            </div>
            <p class="workload-forecast-note">The model selects between a trailing 3-month average and last-completed-month baseline using the final three one-month backtests. This short-term workload estimate has no formal prediction interval and does not establish an outbreak.</p>
        @else
            <p class="workload-forecast-note">{{ $workloadForecast['reason'] }} Only completed months with a recorded date are used for model fitting; the current month is partial and excluded. {{ $undatedHealthRecordCount ? number_format($undatedHealthRecordCount) . ' records without a consultation date are excluded.' : 'No undated health records are currently excluded.' }}</p>
        @endif
    </section>

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

    const workloadForecastElement = document.querySelector('#diagnosisWorkloadForecastChart');
    if (workloadForecastElement) {
        const workloadCategories = @json($workloadChartLabels);
        const recordedValues = @json($workloadRecordedValues);
        const forecastValues = @json($workloadForecastValues);
        new ApexCharts(workloadForecastElement, {
            chart: { type: 'line', height: 320, toolbar: { show: false } },
            series: [
                { name: 'Recorded diagnosis entries', type: 'column', data: recordedValues },
                { name: 'Three-month workload estimate', type: 'line', data: forecastValues }
            ],
            colors: ['#2563eb', '#d97706'],
            stroke: { width: [0, 3], curve: 'straight', dashArray: [0, 6] },
            plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
            xaxis: { categories: workloadCategories, labels: { rotate: -35 } },
            yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0, title: { text: 'Diagnosis records' } },
            dataLabels: { enabled: false },
            markers: { size: [0, 4], hover: { size: 6 } },
            tooltip: { shared: true, intersect: false },
            legend: { position: 'top', horizontalAlign: 'right' },
            grid: { borderColor: '#e5e7eb' },
            noData: { text: 'No dated diagnosis records are available.' }
        }).render();
    }

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