<?php $__env->startSection('CSSown'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/superadmin/superadmin_dashboard.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-container">

    
    <div class="page-top">
        <div class="page-title-group">
            <h1>Superadmin Dashboard</h1>
            <p>System-wide security, user, and performance overview.</p>
        </div>
    </div>

    
    <div class="dashboard-grid-2">

        <div class="dashboard-card">
            <h2 class="dashboard-card-title">User Growth Analytics</h2>
            <p class="dashboard-card-subtitle">Daily User Registrations</p>

            <div class="chart-container">
                <div id="userAnalyticsApexChart"></div>
            </div>
        </div>

        <div class="dashboard-card">
            <h2 class="dashboard-card-title">Website Traffic &amp; Performance</h2>
            <p class="dashboard-card-subtitle">Daily Page Views / Activity</p>

            <div class="chart-container">
                <div id="websitePerformanceChart"></div>
            </div>
        </div>

    </div>

    
    <div class="dashboard-stack">

        <div class="dashboard-header-flex">
            <h2>Security &amp; Access Control Monitor</h2>
            <span class="badge-guard">Live Guard Active</span>
        </div>

        <div class="dashboard-grid-2">

            
            <div class="dashboard-card dashboard-card-flex">
                <div>
                    <div class="dashboard-header-flex dashboard-widget-header">
                        <h3 class="dashboard-widget-title">Active Sessions</h3>

                        <span id="active-session-count-badge" class="badge-online">
                            <?php echo e($activeUserCount ?? 0); ?> Online Now
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Login Location</th>
                                    <th>Last Active</th>
                                </tr>
                            </thead>

                            <tbody id="active-sessions-table-body">
                                <?php $__empty_1 = true; $__currentLoopData = $activeSessions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <div class="table-user-name">
                                                <?php echo e($session->name); ?>

                                            </div>
                                            <div class="table-user-email">
                                                <?php echo e($session->email); ?>

                                            </div>
                                        </td>

                                        <td class="table-subtext">
                                            <?php echo e($session->location ?? 'Location unavailable'); ?>

                                        </td>

                                        <td class="table-subtext">
                                            <?php echo e(\Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans()); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="empty-table">
                                            No active sessions found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            
            <div class="dashboard-card dashboard-card-flex">
                <div>
                    <div class="dashboard-header-flex dashboard-widget-header">
                        <h3 class="dashboard-widget-title">Security Alerts</h3>
                        <span class="badge-monitor">Real-time Monitor</span>
                    </div>

                    <div class="security-alert-box">
                        <div class="security-alert-title">
                            Suspicious Login Spike Detected
                        </div>

                        <div class="security-alert-desc">
                            Recorded
                            <strong><?php echo e($failedLoginCount ?? 0); ?></strong>
                            failed authentication attempts within the tracking
                            period. No accounts currently locked out.
                        </div>
                    </div>
                </div>

                <div class="security-button-wrapper">
                    <button type="button" class="btn-danger-outline" data-ui-action="reset">
                        Clear Failed Login Cache &amp; Block IPs
                    </button>
                </div>
            </div>

        </div>

        
        <div class="dashboard-card">
            <h3 class="dashboard-widget-title audit-trail-title">
                System Audit Trail
            </h3>

            <div class="table-responsive">
                <table class="dashboard-table audit-table">
                    <thead>
                        <tr>
                            <th>Admin / User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $auditLogs ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="table-user-name">
                                    <?php echo e($log->admin_name ?? 'System'); ?>

                                </td>

                                <td>
                                    <span class="badge-action">
                                        <?php echo e($log->action); ?>

                                    </span>
                                </td>

                                <td><?php echo e($log->description); ?></td>

                                <td class="table-mono">
                                    <?php echo e($log->ip_address ?? 'N/A'); ?>

                                </td>

                                <td class="table-subtext">
                                    <?php echo e(\Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="empty-table">
                                    No audit logs recorded yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script src="<?php echo e(asset('js/superadmin/dashboard-inline-1.js')); ?>?v=<?php echo e(filemtime(public_path('js/superadmin/dashboard-inline-1.js'))); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/superadmin/dashboard.blade.php ENDPATH**/ ?>