<?php $__env->startSection('CSSown'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/bhw/logs.css')); ?>?v=<?php echo e(filemtime(public_path('css/bhw/logs.css'))); ?>">
<style>
    .logs-page { padding-bottom: 24px; }
    .tabs { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0; }
    .tabs button { padding: 9px 13px; border: 1px solid #dbe3ed; border-radius: 8px; background: #f8fafc; color: #334155; cursor: pointer; }
    .tabs button.active { border-color: #0e42b1; background: #0e42b1; color: #fff; }
    .tab-content { display: none; margin-top: 20px; }
    .tab-content.active { display: block; }
    .log-table { width: 100%; border-collapse: collapse; background: #fff; }
    .log-table th, .log-table td { padding: 11px 10px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
    .log-table th { color: #64748b; font-size: 11px; text-transform: uppercase; }
    .tag { display: inline-block; padding: 4px 8px; border-radius: 6px; background: #0e42b1; color: #fff; font-size: 12px; }
    .status-tag { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 12px; font-weight: 600; }
    .status-tag.claimed, .status-tag.approved, .status-tag.fulfilled { background: #dcfce7; color: #166534; }
    .pagination-container { margin-top: 18px; }
    .pagination-container nav > div:first-child { display: none; }
    .pagination-container nav > div:last-child { display: flex; justify-content: center; }
    .pagination-container nav ul { display: flex; gap: 5px; margin: 0; padding: 0; list-style: none; }
    .pagination-container .page-link { display: block; padding: 7px 11px; border: 1px solid #dbe3ed; border-radius: 6px; color: #334155; background: #fff; text-decoration: none; }
    .pagination-container .page-item.active .page-link { border-color: #0e42b1; background: #0e42b1; color: #fff; }
    .pagination-container .page-item.disabled .page-link { color: #94a3b8; background: #f8fafc; }
    .empty-log { padding: 22px; color: #64748b; text-align: center; }
</style>
<link rel="stylesheet" href="<?php echo e(asset('css/bhw/shared-ui.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="logs-page" data-audit-tabs data-initial-tab="<?php echo e(request('tab', 'citizens')); ?>">
    <div class="page-top">
        <div class="page-title">
            <h1>Audit Logs</h1>
            <p>Review activity across citizens, health, inventory, schedules, referrals, and releases.</p>
        </div>
    </div>

    <div class="tabs" role="tablist" aria-label="Audit log sections">
        <button class="tab active" data-tab="citizens">Citizens</button>
        <button class="tab" data-tab="supplies">Supplies</button>
        <button class="tab" data-tab="health">Health</button>
        <button class="tab" data-tab="vehicle">Vehicles</button>
        <button class="tab" data-tab="announcements">Announcements</button>
        <button class="tab" data-tab="events">Calendar</button>
        <button class="tab" data-tab="referrals">Referrals</button>
        <button class="tab" data-tab="maintenance">Maintenance</button>
        <button class="tab" data-tab="releases">Batch Releases</button>
    </div>

    <div id="citizens" class="tab-content active">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Action</th><th scope="col">Citizen</th><th scope="col">Description</th></tr></thead><tbody role="rowgroup">
            <?php $__empty_1 = true; $__currentLoopData = $citizenLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr role="row"><td role="cell" data-label="Date"><?php echo e($log->created_at); ?></td><td role="cell" data-label="Actor"><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td role="cell" data-label="Action"><span class="tag"><?php echo e(ucfirst($log->action)); ?></span></td><td role="cell" data-label="Citizen"><?php echo e($log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A'); ?></td><td role="cell" data-label="Description"><?php echo e($log->description); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr role="row"><td role="cell" colspan="5" class="empty-log">No citizen logs.</td></tr><?php endif; ?>
        </tbody></table>
        <div class="pagination-container"><?php echo e($citizenLogs->appends(array_merge(request()->query(), ['tab' => 'citizens']))->links('partials.pagination')); ?></div>
    </div>

    <div id="supplies" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Action</th><th scope="col">Supply</th><th scope="col">Quantity</th><th scope="col">Citizen</th><th scope="col">Notes</th></tr></thead><tbody role="rowgroup">
            <?php $__empty_1 = true; $__currentLoopData = $supplyLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr role="row"><td role="cell" data-label="Date"><?php echo e($log->created_at); ?></td><td role="cell" data-label="Actor"><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td role="cell" data-label="Action"><span class="tag"><?php echo e(ucfirst($log->action)); ?></span></td><td role="cell" data-label="Supply"><?php echo e($log->supply->name ?? 'Removed supply'); ?></td><td role="cell" data-label="Quantity"><?php echo e($log->quantity); ?></td><td role="cell" data-label="Citizen"><?php echo e($log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'General inventory'); ?></td><td role="cell" data-label="Notes"><?php echo e($log->notes ?? 'N/A'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr role="row"><td role="cell" colspan="7" class="empty-log">No supply logs.</td></tr><?php endif; ?>
        </tbody></table>
        <div class="pagination-container"><?php echo e($supplyLogs->appends(array_merge(request()->query(), ['tab' => 'supplies']))->links('partials.pagination')); ?></div>
    </div>

    <div id="health" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Action</th><th scope="col">Citizen ID</th><th scope="col">Description</th></tr></thead><tbody role="rowgroup">
            <?php $__empty_1 = true; $__currentLoopData = $healthRecordLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr role="row"><td role="cell" data-label="Date"><?php echo e($log->created_at); ?></td><td role="cell" data-label="Actor"><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td role="cell" data-label="Action"><span class="tag"><?php echo e(ucfirst($log->action)); ?></span></td><td role="cell" data-label="Citizen ID"><?php echo e($log->citizen_id ?? 'N/A'); ?></td><td role="cell" data-label="Description"><?php echo e($log->description); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr role="row"><td role="cell" colspan="5" class="empty-log">No health-record logs.</td></tr><?php endif; ?>
        </tbody></table>
        <div class="pagination-container"><?php echo e($healthRecordLogs->appends(array_merge(request()->query(), ['tab' => 'health']))->links('partials.pagination')); ?></div>
    </div>

    <div id="vehicle" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Vehicle</th><th scope="col">Borrower</th><th scope="col">Borrowed At</th></tr></thead><tbody role="rowgroup">
            <?php $__empty_1 = true; $__currentLoopData = $vehicleLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr role="row"><td role="cell" data-label="Date"><?php echo e($log->created_at); ?></td><td role="cell" data-label="Actor"><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td role="cell" data-label="Vehicle"><?php echo e($log->vehicle_name); ?></td><td role="cell" data-label="Borrower"><?php echo e($log->borrower); ?></td><td role="cell" data-label="Borrowed At"><?php echo e($log->borrowed_at); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr role="row"><td role="cell" colspan="5" class="empty-log">No vehicle logs.</td></tr><?php endif; ?>
        </tbody></table>
        <div class="pagination-container"><?php echo e($vehicleLogs->appends(array_merge(request()->query(), ['tab' => 'vehicle']))->links('partials.pagination')); ?></div>
    </div>

    <div id="announcements" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Title</th><th scope="col">Category</th><th scope="col">Description</th></tr></thead><tbody role="rowgroup">
            <?php $__empty_1 = true; $__currentLoopData = $announcementLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr role="row"><td role="cell" data-label="Date"><?php echo e($log->created_at); ?></td><td role="cell" data-label="Actor"><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td role="cell" data-label="Title"><?php echo e($log->title); ?></td><td role="cell" data-label="Category"><?php echo e($log->category ?? 'General'); ?></td><td role="cell" data-label="Description"><?php echo e($log->description); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr role="row"><td role="cell" colspan="5" class="empty-log">No announcements.</td></tr><?php endif; ?>
        </tbody></table>
        <div class="pagination-container"><?php echo e($announcementLogs->appends(array_merge(request()->query(), ['tab' => 'announcements']))->links('partials.pagination')); ?></div>
    </div>

    <div id="events" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Event</th><th scope="col">Details</th></tr></thead><tbody role="rowgroup">
            <?php $__empty_1 = true; $__currentLoopData = $eventLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr role="row"><td role="cell" data-label="Date"><?php echo e(\Carbon\Carbon::parse($log->start)->format('M d, Y')); ?></td><td role="cell" data-label="Actor"><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td role="cell" data-label="Event"><?php echo e($log->title); ?></td><td role="cell" data-label="Details"><?php echo e($log->description ?? 'No details'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr role="row"><td role="cell" colspan="4" class="empty-log">No calendar events.</td></tr><?php endif; ?>
        </tbody></table>
        <div class="pagination-container"><?php echo e($eventLogs->appends(array_merge(request()->query(), ['tab' => 'events']))->links('partials.pagination')); ?></div>
    </div>

    <div id="referrals" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Patient</th><th scope="col">Request</th><th scope="col">Status</th></tr></thead><tbody role="rowgroup">
            <?php $__empty_1 = true; $__currentLoopData = $referralLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr role="row"><td role="cell" data-label="Date"><?php echo e($log->date_of_referral); ?></td><td role="cell" data-label="Actor"><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td role="cell" data-label="Patient"><?php echo e($log->name); ?></td><td role="cell" data-label="Request"><?php echo e($log->requests_for); ?></td><td role="cell" data-label="Status"><span class="status-tag <?php echo e($log->status); ?>"><?php echo e(ucfirst($log->status ?? 'pending')); ?></span></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr role="row"><td role="cell" colspan="5" class="empty-log">No referrals.</td></tr><?php endif; ?>
        </tbody></table>
        <div class="pagination-container"><?php echo e($referralLogs->appends(array_merge(request()->query(), ['tab' => 'referrals']))->links('partials.pagination')); ?></div>
    </div>

    <div id="maintenance" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date Added</th><th scope="col">Actor</th><th scope="col">Citizen</th><th scope="col">Medicine</th><th scope="col">Dosage</th><th scope="col">Frequency</th><th scope="col">Schedule</th></tr></thead><tbody role="rowgroup">
            <?php $__empty_1 = true; $__currentLoopData = $maintenanceLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr role="row"><td role="cell" data-label="Date Added"><?php echo e($log->created_at); ?></td><td role="cell" data-label="Actor"><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td role="cell" data-label="Citizen"><?php echo e($log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A'); ?></td><td role="cell" data-label="Medicine"><?php echo e($log->medicine_name); ?></td><td role="cell" data-label="Dosage"><?php echo e($log->dosage); ?></td><td role="cell" data-label="Frequency"><?php echo e($log->frequency); ?></td><td role="cell" data-label="Schedule"><?php echo e($log->start_date->format('M d, Y')); ?><?php echo e($log->end_date ? ' - ' . $log->end_date->format('M d, Y') : ' - Ongoing'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr role="row"><td role="cell" colspan="7" class="empty-log">No maintenance records.</td></tr><?php endif; ?>
        </tbody></table>
        <div class="pagination-container"><?php echo e($maintenanceLogs->appends(array_merge(request()->query(), ['tab' => 'maintenance']))->links('partials.pagination')); ?></div>
    </div>

    <div id="releases" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Claim Date</th><th scope="col">Actor</th><th scope="col">Batch</th><th scope="col">Citizen</th><th scope="col">Supply</th><th scope="col">Quantity</th><th scope="col">Status</th></tr></thead><tbody role="rowgroup">
            <?php $__empty_1 = true; $__currentLoopData = $releaseLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr role="row"><td role="cell" data-label="Claim Date"><?php echo e($log->claim_date->format('M d, Y')); ?></td><td role="cell" data-label="Actor"><?php echo e($log->user->name ?? 'Legacy / System'); ?></td><td role="cell" data-label="Batch"><?php echo e($log->batch_title); ?></td><td role="cell" data-label="Citizen"><?php echo e($log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A'); ?></td><td role="cell" data-label="Supply"><?php echo e($log->supply->name ?? 'Unavailable'); ?></td><td role="cell" data-label="Quantity"><?php echo e($log->quantity); ?></td><td role="cell" data-label="Status"><span class="status-tag <?php echo e($log->status); ?>"><?php echo e(ucfirst($log->status)); ?></span></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr role="row"><td role="cell" colspan="7" class="empty-log">No batch releases.</td></tr><?php endif; ?>
        </tbody></table>
        <div class="pagination-container"><?php echo e($releaseLogs->appends(array_merge(request()->query(), ['tab' => 'releases']))->links('partials.pagination')); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/audit-log-tabs.js')); ?>?v=<?php echo e(filemtime(public_path('js/audit-log-tabs.js'))); ?>" defer></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/bhw/logs.blade.php ENDPATH**/ ?>