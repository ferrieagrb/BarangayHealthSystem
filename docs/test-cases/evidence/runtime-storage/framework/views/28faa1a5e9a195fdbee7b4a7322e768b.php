<?php $__env->startSection('CSSown'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/superadmin/superadmin_audit_trail.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="audit-trail-container">

    
    <div class="audit-page-header">
        <div class="audit-page-title-group">
            <h1>Superadmin Audit Trail</h1>
            <p>
                Dedicated governance, security, and administrative action logs
                for the barangay health system.
            </p>
        </div>

        <div class="audit-header-actions">
            <a
                href="<?php echo e(route('superadmin.system.analytics.page')); ?>"
                class="audit-back-button"
             data-ui-action="secondary">
                <span aria-hidden="true">&larr;</span>
                Back to System Analytics
            </a>

            <span class="audit-mode-badge">
                Superadmin Mode
            </span>
        </div>
    </div>

    
    <div class="audit-main-card">

        
        <div class="audit-card-header">
            <div class="audit-card-heading">
                <h2>System Activity Logs</h2>
                <p>
                    Chronological history of all high-level superadmin
                    and administrative changes.
                </p>
            </div>

            <span class="audit-oversight-badge">
                Strict Oversight
            </span>
        </div>

        
        <form method="GET" class="audit-filter-form">

            <div class="audit-filter-grid">

                <label class="audit-filter-field">
                    <span>Source</span>

                    <select name="source" class="audit-form-control">
                        <option value="all" <?php echo e(request('source', 'all') === 'all' ? 'selected' : ''); ?>>
                            All logs
                        </option>

                        <option value="authentication" <?php echo e(request('source') === 'authentication' ? 'selected' : ''); ?>>
                            Authentication / Security
                        </option>

                        <option value="administrative" <?php echo e(request('source') === 'administrative' ? 'selected' : ''); ?>>
                            Administrative
                        </option>
                    </select>
                </label>

                <label class="audit-filter-field">
                    <span>Category</span>

                    <select name="category" class="audit-form-control">
                        <option value="all" <?php echo e(request('category', 'all') === 'all' ? 'selected' : ''); ?>>
                            All categories
                        </option>

                        <option value="successful_logins" <?php echo e(request('category') === 'successful_logins' ? 'selected' : ''); ?>>
                            Successful logins
                        </option>

                        <option value="failed_logins" <?php echo e(request('category') === 'failed_logins' ? 'selected' : ''); ?>>
                            Failed logins
                        </option>

                        <option value="invalid_login_submissions" <?php echo e(request('category') === 'invalid_login_submissions' ? 'selected' : ''); ?>>
                            Invalid login submissions
                        </option>

                        <option value="rate_limited_attempts" <?php echo e(request('category') === 'rate_limited_attempts' ? 'selected' : ''); ?>>
                            Rate-limited attempts
                        </option>

                        <option value="locked_accounts" <?php echo e(request('category') === 'locked_accounts' ? 'selected' : ''); ?>>
                            Locked accounts
                        </option>

                        <option value="blocked_logins" <?php echo e(request('category') === 'blocked_logins' ? 'selected' : ''); ?>>
                            Blocked logins
                        </option>

                        <option value="logout_events" <?php echo e(request('category') === 'logout_events' ? 'selected' : ''); ?>>
                            Logout events
                        </option>

                        <option value="administrative_changes" <?php echo e(request('category') === 'administrative_changes' ? 'selected' : ''); ?>>
                            Administrative changes
                        </option>

                        <option value="user_management" <?php echo e(request('category') === 'user_management' ? 'selected' : ''); ?>>
                            User creation/deletion
                        </option>

                        <option value="account_unlocks" <?php echo e(request('category') === 'account_unlocks' ? 'selected' : ''); ?>>
                            Account unlocks
                        </option>

                        <option value="permission_changes" <?php echo e(request('category') === 'permission_changes' ? 'selected' : ''); ?>>
                            Permission changes
                        </option>

                        <option value="import_export_events" <?php echo e(request('category') === 'import_export_events' ? 'selected' : ''); ?>>
                            Import/export events
                        </option>
                    </select>
                </label>

                <label class="audit-filter-field">
                    <span>From</span>

                    <input
                        type="date"
                        name="date_from"
                        value="<?php echo e(request('date_from')); ?>"
                        class="audit-form-control"
                    >
                </label>

                <label class="audit-filter-field">
                    <span>To</span>

                    <input
                        type="date"
                        name="date_to"
                        value="<?php echo e(request('date_to')); ?>"
                        class="audit-form-control"
                    >
                </label>

            </div>

            <div class="audit-filter-actions">
                <button type="submit" class="audit-filter-button" data-ui-action="secondary">
                    Filter Logs
                </button>

                <a
                    href="<?php echo e(route('superadmin.audit-logs')); ?>"
                    class="audit-clear-button"
                 data-ui-action="reset">
                    Clear
                </a>
            </div>
        </form>

        
        <div class="audit-table-responsive">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Superadmin</th>
                        <th>Source</th>
                        <th>Action Event</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="audit-timestamp">
                                <?php echo e(\Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i:s A')); ?>

                            </td>

                            <td class="audit-admin-cell">
                                <div class="audit-admin-name">
                                    <?php echo e($log->admin_name ?? 'Unknown / System'); ?>

                                </div>

                                <?php if($log->admin_email): ?>
                                    <span class="audit-admin-email">
                                        <?php echo e($log->admin_email); ?>

                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="audit-source-badge">
                                    <?php echo e($log->source ?? 'System'); ?>

                                </span>
                            </td>

                            <td>
                                <span class="audit-action-badge">
                                    <?php echo e($log->action); ?>

                                </span>
                            </td>

                            <td class="audit-description">
                                <?php echo e($log->description); ?>

                            </td>

                            <td class="audit-ip-address">
                                <?php echo e($log->ip_address ?? 'N/A'); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="audit-empty-cell">
                                <div class="audit-empty-state">
                                    <span class="audit-empty-icon" aria-hidden="true">
                                        —
                                    </span>

                                    <p>No administrative actions logged yet.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if(method_exists($auditLogs, 'links')): ?>
            <div class="audit-pagination">
                <?php echo e($auditLogs->links()); ?>

            </div>
        <?php endif; ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/superadmin/audit-logs.blade.php ENDPATH**/ ?>