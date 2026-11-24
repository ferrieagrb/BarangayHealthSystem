<?php $__env->startSection('CSSown'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/admin/record-pages.css')); ?>?v=<?php echo e(filemtime(public_path('css/admin/record-pages.css'))); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-record-page admin-logs-page" data-audit-tabs data-initial-tab="<?php echo e(request('tab', 'adminActivity')); ?>">
    <div class="page-top"><div class="page-title-group"><h1>System Audit Logs</h1><p>Review administrative actions and activity across system features.</p></div></div>

    <div class="tabs" role="tablist" aria-label="Audit log sections">
        <button class="tab active" data-tab="adminActivity">Admin Activity</button>
        <button class="tab" data-tab="security">Security &amp; Errors</button>
        <button class="tab" data-tab="citizens">Citizens</button>
        <button class="tab" data-tab="supplies">Supplies</button>
        <button class="tab" data-tab="health">Health</button>
        <button class="tab" data-tab="vehicle">Vehicles</button>
        <button class="tab" data-tab="announcements">Announcements</button>
        <button class="tab" data-tab="events">Calendar</button>
        <button class="tab" data-tab="referrals">Referrals</button>
        <button class="tab" data-tab="maintenance">Maintenance</button>
        <button class="tab" data-tab="releases">Batch Releases</button>
    </div>

    <div id="adminActivity" class="tab-content active">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Scope</th><th>Action</th><th>Description</th><th>IP</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $adminActivityLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($log->created_at); ?></td><td><?php echo e($log->actor_name ?? 'System'); ?></td><td><?php echo e(ucfirst($log->scope)); ?></td><td><span class="tag"><?php echo e($log->action); ?></span></td><td><?php echo e($log->description); ?></td><td><?php echo e($log->ip_address ?? 'N/A'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="empty-log">No administrative activity recorded.</td></tr><?php endif; ?>
        </tbody></table>
        <div class="pagination-container"><?php echo e($adminActivityLogs->appends(array_merge(request()->query(), ['tab' => 'adminActivity']))->links('partials.pagination')); ?></div>
    </div>

    <div id="security" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Event</th><th>Description</th><th>IP</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $securityLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($log->created_at); ?></td><td><?php echo e($log->actor_name ?? 'Unauthenticated / System'); ?></td><td><span class="tag"><?php echo e($log->action); ?></span></td><td><?php echo e($log->description); ?></td><td><?php echo e($log->ip_address ?? 'N/A'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="empty-log">No security or exception events.</td></tr><?php endif; ?>
        </tbody></table>
        <div class="pagination-container"><?php echo e($securityLogs->appends(array_merge(request()->query(), ['tab' => 'security']))->links('partials.pagination')); ?></div>
    </div>

    <div id="citizens" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Action</th><th>Citizen</th><th>Description</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $citizenLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($log->created_at); ?></td><td><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td><?php echo e(ucfirst($log->action)); ?></td><td><?php echo e($log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A'); ?></td><td><?php echo e($log->description); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="empty-log">No citizen logs.</td></tr><?php endif; ?>
        </tbody></table><div class="pagination-container"><?php echo e($citizenLogs->appends(array_merge(request()->query(), ['tab' => 'citizens']))->links('partials.pagination')); ?></div>
    </div>

    <div id="supplies" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Action</th><th>Supply</th><th>Quantity</th><th>Citizen</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $supplyLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($log->created_at); ?></td><td><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td><?php echo e(ucfirst($log->action)); ?></td><td><?php echo e($log->supply->name ?? 'Removed supply'); ?></td><td><?php echo e($log->quantity); ?></td><td><?php echo e($log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'General inventory'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="empty-log">No supply logs.</td></tr><?php endif; ?>
        </tbody></table><div class="pagination-container"><?php echo e($supplyLogs->appends(array_merge(request()->query(), ['tab' => 'supplies']))->links('partials.pagination')); ?></div>
    </div>

    <div id="health" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Action</th><th>Citizen ID</th><th>Description</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $healthRecordLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($log->created_at); ?></td><td><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td><?php echo e(ucfirst($log->action)); ?></td><td><?php echo e($log->citizen_id ?? 'N/A'); ?></td><td><?php echo e($log->description); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="empty-log">No health logs.</td></tr><?php endif; ?>
        </tbody></table><div class="pagination-container"><?php echo e($healthRecordLogs->appends(array_merge(request()->query(), ['tab' => 'health']))->links('partials.pagination')); ?></div>
    </div>

    <div id="vehicle" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Vehicle</th><th>Borrower</th><th>Borrowed At</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $vehicleLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($log->created_at); ?></td><td><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td><?php echo e($log->vehicle_name); ?></td><td><?php echo e($log->borrower); ?></td><td><?php echo e($log->borrowed_at); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="empty-log">No vehicle logs.</td></tr><?php endif; ?>
        </tbody></table><div class="pagination-container"><?php echo e($vehicleLogs->appends(array_merge(request()->query(), ['tab' => 'vehicle']))->links('partials.pagination')); ?></div>
    </div>

    <div id="announcements" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Title</th><th>Category</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $announcementLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($log->created_at); ?></td><td><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td><?php echo e($log->title); ?></td><td><?php echo e($log->category ?? 'General'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="4" class="empty-log">No announcements.</td></tr><?php endif; ?>
        </tbody></table><div class="pagination-container"><?php echo e($announcementLogs->appends(array_merge(request()->query(), ['tab' => 'announcements']))->links('partials.pagination')); ?></div>
    </div>

    <div id="events" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Event</th><th>Details</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $eventLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e(\Carbon\Carbon::parse($log->start)->format('M d, Y')); ?></td><td><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td><?php echo e($log->title); ?></td><td><?php echo e($log->description ?? 'No details'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="4" class="empty-log">No events.</td></tr><?php endif; ?>
        </tbody></table><div class="pagination-container"><?php echo e($eventLogs->appends(array_merge(request()->query(), ['tab' => 'events']))->links('partials.pagination')); ?></div>
    </div>

    <div id="referrals" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Patient</th><th>Request</th><th>Status</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $referralLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($log->date_of_referral); ?></td><td><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td><?php echo e($log->name); ?></td><td><?php echo e($log->requests_for); ?></td><td><?php echo e(ucfirst($log->status ?? 'pending')); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="empty-log">No referrals.</td></tr><?php endif; ?>
        </tbody></table><div class="pagination-container"><?php echo e($referralLogs->appends(array_merge(request()->query(), ['tab' => 'referrals']))->links('partials.pagination')); ?></div>
    </div>

    <div id="maintenance" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Citizen</th><th>Medicine</th><th>Schedule</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $maintenanceLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($log->created_at); ?></td><td><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td><?php echo e($log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A'); ?></td><td><?php echo e($log->medicine_name); ?></td><td><?php echo e($log->start_date->format('M d, Y')); ?><?php echo e($log->end_date ? ' - ' . $log->end_date->format('M d, Y') : ' - Ongoing'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="empty-log">No maintenance records.</td></tr><?php endif; ?>
        </tbody></table><div class="pagination-container"><?php echo e($maintenanceLogs->appends(array_merge(request()->query(), ['tab' => 'maintenance']))->links('partials.pagination')); ?></div>
    </div>

    <div id="releases" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Claim Date</th><th>Actor</th><th>Batch</th><th>Citizen</th><th>Supply</th><th>Status</th></tr></thead><tbody>
            <?php $__empty_1 = true; $__currentLoopData = $releaseLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($log->claim_date->format('M d, Y')); ?></td><td><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td><?php echo e($log->batch_title); ?></td><td><?php echo e($log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A'); ?></td><td><?php echo e($log->supply->name ?? 'Unavailable'); ?></td><td><?php echo e(ucfirst($log->status)); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="empty-log">No batch releases.</td></tr><?php endif; ?>
        </tbody></table><div class="pagination-container"><?php echo e($releaseLogs->appends(array_merge(request()->query(), ['tab' => 'releases']))->links('partials.pagination')); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/audit-log-tabs.js')); ?>?v=<?php echo e(filemtime(public_path('js/audit-log-tabs.js'))); ?>" defer></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('templates.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/admin/admin_logs.blade.php ENDPATH**/ ?>