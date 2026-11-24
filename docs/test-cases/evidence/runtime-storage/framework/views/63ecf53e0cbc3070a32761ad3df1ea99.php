<?php $__env->startSection('CSSown'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/superadmin/superadmin_settings.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="superadmin-settings">

    <div class="settings-page-header">
        <div class="settings-page-title-group">
            <h1>System Settings</h1>
            <p>Manage global access behavior, maintenance mode, and backups.</p>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="settings-alert settings-alert-success" role="alert">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="settings-alert settings-alert-error" role="alert">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <form
        method="POST"
        action="<?php echo e(route('superadmin.system.settings.update')); ?>"
        class="settings-form"
        enctype="multipart/form-data"
        id="systemSettingsForm"
    >
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        
        <section class="settings-card">
            <div class="settings-card-header">
                <h2>Universal Website Logo</h2>
                <p>This logo is used on the public landing page and login screen.</p>
            </div>

            <div class="settings-logo-content">
                <div class="settings-logo-preview-box">
                    <img
                        id="currentLogoPreview"
                        src="<?php echo e($logoUrl); ?>"
                        alt="Current website logo"
                        class="settings-current-logo"
                    >
                </div>

                <div class="settings-logo-upload">
                    <label for="logoUpload" class="settings-field-label">
                        Upload new logo
                    </label>

                    <input
                        id="logoUpload"
                        name="logo"
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        class="settings-file-input"
                    >

                    <p class="settings-field-help">
                        PNG, JPG, or WEBP. Maximum 2 MB.
                    </p>
                </div>
            </div>
        </section>

        
        <section class="settings-card">
            <div class="settings-card-header">
                <h2>User Experience</h2>
                <p>Control interface preferences available to users.</p>
            </div>

            <label class="settings-toggle-row">
                <span class="settings-toggle-description">
                    <strong>Allow dark mode</strong>
                    <small>Allow users to choose a dark interface theme.</small>
                </span>

                <input
                    type="checkbox"
                    name="allow_dark_mode"
                    value="1"
                    <?php echo e($allowDarkMode ? 'checked' : ''); ?>

                    class="settings-toggle-input"
                >
            </label>
        </section>

        
        <section class="settings-card">
            <div class="settings-card-header">
                <h2>Maintenance Mode</h2>
                <p>Temporarily restrict standard users while Superadmins retain access.</p>
            </div>

            <label class="settings-toggle-row">
                <span class="settings-toggle-description">
                    <strong>Enable maintenance mode</strong>
                    <small>Use during upgrades or system maintenance.</small>
                </span>

                <input
                    type="checkbox"
                    name="maintenance_mode"
                    value="1"
                    <?php echo e($maintenanceMode ? 'checked' : ''); ?>

                    class="settings-toggle-input"
                >
            </label>
        </section>

        
        <section class="settings-card">
            <div class="settings-card-header">
                <h2>Vaccination Scheduling</h2>
                <p>Allow BHWs to schedule future vaccinations and show those schedules on citizen dashboards.</p>
            </div>

            <label class="settings-toggle-row">
                <span class="settings-toggle-description">
                    <strong>Enable vaccination scheduling</strong>
                    <small>When disabled, scheduling forms and schedule displays are unavailable.</small>
                </span>

                <input
                    type="checkbox"
                    name="vaccination_scheduling_enabled"
                    value="1"
                    <?php echo e($vaccinationSchedulingEnabled ? 'checked' : ''); ?>

                    class="settings-toggle-input"
                >
            </label>
        </section>

        
        <section class="settings-card">
            <div class="settings-card-header">
                <h2>Password Re-entry</h2>
                <p>Ask users to re-enter their own password before sensitive actions.</p>
            </div>

            <label class="settings-toggle-row">
                <span class="settings-toggle-description">
                    <strong>Require password confirmation</strong>
                    <small>Applies to exports, backups, role changes and password resets. Turning this off weakens protection of citizen data.</small>
                </span>

                <input type="hidden" name="password_reentry_required" value="0">
                <input
                    type="checkbox"
                    name="password_reentry_required"
                    value="1"
                    <?php echo e($passwordReentryRequired ? 'checked' : ''); ?>

                    class="settings-toggle-input"
                >
            </label>
        </section>

        
        <section class="settings-card">
            <div class="settings-card-header">
                <h2>Login Security</h2>
                <p>Lock accounts after repeated failed password attempts.</p>
            </div>

            <div class="settings-field">
                <label for="maxFailedLoginAttempts" class="settings-field-label">
                    Maximum failed login attempts
                </label>

                <input
                    type="number"
                    id="maxFailedLoginAttempts"
                    name="max_failed_login_attempts"
                    min="3"
                    max="20"
                    required
                    value="<?php echo e(old('max_failed_login_attempts', $maxFailedLoginAttempts)); ?>"
                    class="settings-form-control"
                >

                <?php $__errorArgs = ['max_failed_login_attempts'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="settings-field-error"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </section>

        
        <section class="settings-card">
            <div class="settings-card-header">
                <h2>Data Backup</h2>
                <p>Choose a backup frequency or download a SQL backup immediately.</p>
            </div>

            <div class="settings-field">
                <label for="backupFrequency" class="settings-field-label">
                    Automatic backup frequency
                </label>

                <select
                    id="backupFrequency"
                    name="backup_frequency"
                    class="settings-form-control"
                >
                    <option value="disabled" <?php echo e($backupFrequency === 'disabled' ? 'selected' : ''); ?>>
                        Disabled
                    </option>
                    <option value="daily" <?php echo e($backupFrequency === 'daily' ? 'selected' : ''); ?>>
                        Daily
                    </option>
                    <option value="weekly" <?php echo e($backupFrequency === 'weekly' ? 'selected' : ''); ?>>
                        Weekly
                    </option>
                    <option value="monthly" <?php echo e($backupFrequency === 'monthly' ? 'selected' : ''); ?>>
                        Monthly
                    </option>
                </select>
            </div>

            <div class="settings-field">
                <label for="backupDestination" class="settings-field-label">
                    Backup destination
                </label>

                <select
                    id="backupDestination"
                    name="backup_destination"
                    class="settings-form-control"
                >
                    <option value="hostinger" <?php echo e($backupDestination === 'hostinger' ? 'selected' : ''); ?>>
                        Hostinger SFTP
                    </option>
                    <option value="local" <?php echo e($backupDestination === 'local' ? 'selected' : ''); ?>>
                        Application storage
                    </option>
                </select>
            </div>

            <label class="settings-toggle-row settings-backup-toggle">
                <span class="settings-toggle-description">
                    <strong>Encrypt backup files</strong>
                    <small>Backups are saved as encrypted .sql.enc files using the application key.</small>
                </span>

                <input
                    type="checkbox"
                    name="backup_encryption"
                    value="1"
                    <?php echo e($backupEncryption ? 'checked' : ''); ?>

                    class="settings-toggle-input"
                >
            </label>

            <div class="settings-backup-actions">
                <a
                    href="<?php echo e(route('superadmin.system.settings.backup')); ?>"
                    class="settings-button settings-button-secondary"
                    data-no-loader
                 data-ui-action="secondary">
                    Download Database Backup
                </a>
            </div>
        </section>

        
        <div class="settings-form-footer">
            <button type="submit" class="settings-button settings-button-primary" data-ui-action="primary">
                Save Settings
            </button>
        </div>

    </form>
</div>


<div
    id="logoPreviewModal"
    class="settings-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="logoPreviewTitle"
    aria-hidden="true"
>
    <div class="settings-modal-content">
        <div class="settings-modal-header">
            <h2 id="logoPreviewTitle">Preview New Logo</h2>
            <p>Confirm this image before saving it as the universal website logo.</p>
        </div>

        <div class="settings-modal-preview">
            <img
                id="newLogoPreview"
                src=""
                alt="New logo preview"
                class="settings-new-logo"
            >
        </div>

        <div class="settings-modal-actions">
            <button
                type="button"
                id="cancelLogoUpload"
                class="settings-button settings-button-secondary"
             data-ui-action="secondary">
                Cancel
            </button>

            <button
                type="button"
                id="confirmLogoUpload"
                class="settings-button settings-button-primary"
             data-ui-action="primary">
                Confirm &amp; Save
            </button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/superadmin/settings-inline-1.js')); ?>?v=<?php echo e(filemtime(public_path('js/superadmin/settings-inline-1.js'))); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/superadmin/settings.blade.php ENDPATH**/ ?>