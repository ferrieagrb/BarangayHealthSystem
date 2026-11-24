<?php $__env->startSection('CSSown'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/admin/admin_analytics.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="reports-page">
    <div class="page-top">
        <div class="page-title-group">
            <h1>Reports Operations</h1>
            <p>Operational overview of residents, health services, inventory, and scheduled distributions.</p>
        </div>
    </div>

    <div class="reports-grid">
        <div class="report-card"><span>Registered Citizens</span><strong class="report-counter" data-count="<?php echo e($totalCitizens); ?>">0</strong><small>Resident profiles</small></div>
        <div class="report-card"><span>Health Records</span><strong class="report-counter" data-count="<?php echo e($totalHealthRecords); ?>">0</strong><small><span class="report-counter" data-count="<?php echo e($activeMaintenance); ?>">0</span> active maintenance</small></div>
        <div class="report-card"><span>Inventory Units</span><strong class="report-counter" data-count="<?php echo e($totalInventory); ?>">0</strong><small><span class="report-counter" data-count="<?php echo e($lowStockItems); ?>">0</span> low stock, <span class="report-counter" data-count="<?php echo e($outOfStockItems); ?>">0</span> empty</small></div>
        <div class="report-card"><span>Today</span><strong class="report-counter" data-count="<?php echo e($pageViewsToday); ?>">0</strong><small>Page views</small></div>
    </div>

    <div class="reports-grid">
        <div class="report-card"><span>System Users</span><strong class="report-counter" data-count="<?php echo e($totalUsers); ?>">0</strong><small><span class="report-counter" data-count="<?php echo e($bhwUsers); ?>">0</span> BHW accounts</small></div>
        <div class="report-card"><span>Referral Workload</span><strong class="report-counter" data-count="<?php echo e($pendingReferrals); ?>">0</strong><small>Pending or approved referrals</small></div>
        <div class="report-card"><span>Upcoming Events</span><strong class="report-counter" data-count="<?php echo e($upcomingEvents); ?>">0</strong><small>From today onward</small></div>
        <div class="report-card"><span>Supply Releases</span><strong class="report-counter" data-count="<?php echo e($scheduledReleases); ?>">0</strong><small><span class="report-counter" data-count="<?php echo e($claimedReleases); ?>">0</span> claimed</small></div>
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
            <span class="workload-forecast-status <?php echo e($workloadForecast['available'] ? 'available' : 'insufficient'); ?>">
                <?php echo e($workloadForecast['available'] ? 'Baseline forecast' : 'Insufficient history'); ?>

            </span>
        </div>

        <?php if($workloadChartLabels): ?>
            <div id="diagnosisWorkloadForecastChart" class="chart-container" role="img" aria-label="Monthly recorded diagnosis workload and short-term forecast"></div>
        <?php else: ?>
            <div class="empty-report">No health records with a consultation date are available to chart.</div>
        <?php endif; ?>

        <?php if($workloadForecast['available']): ?>
            <div class="workload-forecast-metrics">
                <span>Selected method: <strong><?php echo e($workloadForecast['method']); ?></strong></span>
                <span>Rolling backtest MAE: <strong><?php echo e(number_format($workloadForecast['mae'], 2)); ?></strong> records/month</span>
                <span>Last-month baseline MAE: <strong><?php echo e(number_format($workloadForecast['baseline_mae'], 2)); ?></strong></span>
                <span>Backtest WAPE: <strong><?php echo e($workloadForecast['wape'] === null ? 'N/A (zero actual total)' : number_format($workloadForecast['wape'], 1) . '%'); ?></strong></span>
            </div>
            <p class="workload-forecast-note">The model selects between a trailing 3-month average and last-completed-month baseline using the final three one-month backtests. This short-term workload estimate has no formal prediction interval and does not establish an outbreak.</p>
        <?php else: ?>
            <p class="workload-forecast-note"><?php echo e($workloadForecast['reason']); ?> Only completed months with a recorded date are used for model fitting; the current month is partial and excluded. <?php echo e($undatedHealthRecordCount ? number_format($undatedHealthRecordCount) . ' records without a consultation date are excluded.' : 'No undated health records are currently excluded.'); ?></p>
        <?php endif; ?>
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
                    <?php $__empty_1 = true; $__currentLoopData = $topInventory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inventory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($inventory->category ?: 'Uncategorized'); ?></td>
                            <td><?php echo e(number_format($inventory->total_quantity)); ?></td>
                            <td class="<?php echo e($inventory->total_quantity <= 5 ? 'status-warning' : 'status-good'); ?>"><?php echo e($inventory->total_quantity <= 5 ? 'Review' : 'Stable'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="empty-report">No inventory data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="report-panel">
            <h2>Upcoming Schedule</h2>
            <p>Next community and distribution events.</p>
            <table class="report-table">
                <thead><tr><th>Date</th><th>Event</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr><td><?php echo e(\Carbon\Carbon::parse($event->start)->format('M d, Y')); ?></td><td><?php echo e($event->title); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="2" class="empty-report">No upcoming events.</td></tr>
                    <?php endif; ?>
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
                <?php $__empty_1 = true; $__currentLoopData = $recentSupplyActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($log->created_at->format('M d, Y h:i A')); ?></td>
                        <td><?php echo e(ucfirst($log->action)); ?></td>
                        <td><?php echo e($log->supply->name ?? 'Removed supply'); ?></td>
                        <td><?php echo e($log->quantity); ?></td>
                        <td><?php echo e($log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'General inventory'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="empty-report">No supply activity recorded.</td></tr>
                <?php endif; ?>
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
        series: [{ name: 'Health records', data: <?php echo json_encode($healthTrend->values(), 15, 512) ?> }],
        xaxis: { categories: <?php echo json_encode($monthLabels->values(), 15, 512) ?> },
        colors: ['#2563eb'],
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        grid: { borderColor: '#e5e7eb' },
        yaxis: { min: 0, forceNiceScale: true }
    }).render();

    const workloadForecastElement = document.querySelector('#diagnosisWorkloadForecastChart');
    if (workloadForecastElement) {
        const workloadCategories = <?php echo json_encode($workloadChartLabels, 15, 512) ?>;
        const recordedValues = <?php echo json_encode($workloadRecordedValues, 15, 512) ?>;
        const forecastValues = <?php echo json_encode($workloadForecastValues, 15, 512) ?>;
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
        series: <?php echo json_encode($purokCounts->values(), 15, 512) ?>,
        labels: <?php echo json_encode($purokCounts->keys()->values(), 15, 512) ?>,
        colors: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: true }
    }).render();

    new ApexCharts(document.querySelector('#inventoryRiskChart'), {
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        series: [
            { name: 'Current quantity', data: <?php echo json_encode($inventoryRisk->pluck('quantity')->values(), 15, 512) ?> },
            { name: 'Minimum threshold', data: <?php echo json_encode($inventoryRisk->pluck('min_stock')->values(), 15, 512) ?> }
        ],
        xaxis: { categories: <?php echo json_encode($inventoryRisk->pluck('name')->values(), 15, 512) ?> },
        colors: ['#ef4444', '#94a3b8'],
        plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        dataLabels: { enabled: false },
        legend: { position: 'bottom' },
        grid: { borderColor: '#e5e7eb' }
    }).render();

    new ApexCharts(document.querySelector('#referralWorkloadChart'), {
        chart: { type: 'donut', height: 280 },
        series: <?php echo json_encode($referralStatusCounts->values(), 15, 512) ?>,
        labels: <?php echo json_encode($referralStatusCounts->keys()->values(), 15, 512) ?>,
        colors: ['#f59e0b', '#2563eb', '#10b981', '#ef4444', '#64748b'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: true }
    }).render();

    new ApexCharts(document.querySelector('#inventoryCategoryChart'), {
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        series: [{ name: 'Units', data: <?php echo json_encode($topInventory->pluck('total_quantity')->values(), 15, 512) ?> }],
        colors: ['#0e42b1'],
        plotOptions: { bar: { borderRadius: 5, horizontal: true } },
        dataLabels: { enabled: false },
        grid: { borderColor: '#e5e7eb' },
        xaxis: { categories: <?php echo json_encode($topInventory->pluck('category')->values(), 15, 512) ?>, min: 0 }
    }).render();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('templates.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/admin/admin_analytics.blade.php ENDPATH**/ ?>