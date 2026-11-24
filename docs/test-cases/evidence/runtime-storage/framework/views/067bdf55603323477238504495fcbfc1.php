<?php $__env->startSection('CSSown'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/admin/admin_home.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="admin-dashboard">

    
    <div class="page-top admin-page-header">
        <div class="page-title-group">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <?php echo e(auth()->user()->name); ?>!</p>
        </div>
    </div>

    
    <section class="admin-metrics">

        <article class="admin-metric-card">
            <span>Active BHW Accounts</span>
            <h2 class="metric-primary"><?php echo e($bhwCount ?? 0); ?></h2>
        </article>

        <article class="admin-metric-card">
            <span>Low Stock Items</span>
            <h2 class="metric-danger"><?php echo e($lowStockCount ?? 0); ?></h2>
        </article>

        <article class="admin-metric-card">
            <span>System Status</span>
            <h2 class="metric-success">Online</h2>
        </article>

    </section>

    
    <section class="admin-charts">

        <article class="admin-chart-card">
            <h3>Supplies by Category</h3>
            <div id="categoryChart"></div>
        </article>

        <article class="admin-chart-card">
            <h3>BHW Permission Levels</h3>
            <div id="permissionRadialChart"></div>
        </article>

    </section>

    
    <section class="admin-logs-card">

        <header class="admin-logs-header">
            <h3>Recent Permission Changes</h3>
            <p>Latest recorded changes to user permissions.</p>
        </header>

        <div class="admin-table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Admin</th>
                        <th>Target User</th>
                        <th>Changes</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(isset($recentLogs) && $recentLogs->count() > 0): ?>

                        <?php $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="log-timestamp">
                                    <?php echo e($log->created_at->format('M d, Y h:i A')); ?>

                                </td>

                                <td>
                                    <strong>
                                        <?php echo e($log->admin->name ?? 'System'); ?>

                                    </strong>
                                </td>

                                <td>
                                    <?php echo e($log->targetUser->name ?? 'Unknown'); ?>

                                </td>

                                <td class="log-changes">
                                    <?php echo e($log->changes); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-logs">
                                No recent logs found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </section>

</div>


<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const categoryElement = document.querySelector('#categoryChart');
    const permissionElement = document.querySelector('#permissionRadialChart');

    if (typeof ApexCharts === 'undefined') {
        console.error('ApexCharts failed to load.');
        return;
    }

    if (categoryElement) {
        const categoryOptions = {
            series: [{
                name: 'Total Quantity',
                data: <?php echo json_encode($categoryValues ?? [], 15, 512) ?>
            }],

            chart: {
                type: 'bar',
                height: 280,
                width: '100%',
                toolbar: {
                    show: false
                },
                fontFamily: 'Poppins, sans-serif',
                redrawOnParentResize: true,
                redrawOnWindowResize: true
            },

            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true
                }
            },

            colors: ['#10b981'],

            xaxis: {
                categories: <?php echo json_encode($categoryLabels ?? [], 15, 512) ?>
            },

            dataLabels: {
                enabled: false
            },

            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        height: 260
                    }
                }
            }]
        };

        new ApexCharts(categoryElement, categoryOptions).render();
    }

    if (permissionElement) {
        const permissionOptions = {
            series: [
                <?php echo e($writeCount ?? 0); ?>,
                <?php echo e($readCount ?? 0); ?>,
                <?php echo e($noneCount ?? 0); ?>

            ],

            chart: {
                type: 'donut',
                height: 300,
                width: '100%',
                fontFamily: 'Poppins, sans-serif',
                redrawOnParentResize: true,
                redrawOnWindowResize: true
            },

            labels: [
                'Read / Write',
                'Read Only',
                'No Access'
            ],

            colors: ['#2563eb', '#f59e0b', '#ef4444'],

            legend: {
                position: 'bottom',
                fontSize: '12px'
            },

            dataLabels: {
                enabled: true
            },

            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        height: 280
                    },
                    legend: {
                        fontSize: '11px'
                    }
                }
            }]
        };

        new ApexCharts(permissionElement, permissionOptions).render();
    }

});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('templates.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/admin/admin_home.blade.php ENDPATH**/ ?>