
@extends('templates.superadmin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/superadmin/superadmin_settings.css') }}">

<div class="superadmin-settings">

    <div class="settings-page-header">
        <div class="settings-page-title-group">
            <h1>System Settings</h1>
            <p>Manage global access behavior, maintenance mode, and backups.</p>
        </div>
    </div>

    @include('partials.appearance-settings', ['appearanceFeedbackHandled' => true])

    @if(session('success'))
        <div class="settings-alert settings-alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="settings-alert settings-alert-error" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('superadmin.system.settings.update') }}"
        class="settings-form"
        enctype="multipart/form-data"
        id="systemSettingsForm"
    >
        @csrf
        @method('PUT')

        {{-- Universal Website Logo --}}
        <section class="settings-card">
            <div class="settings-card-header">
                <h2>Universal Website Logo</h2>
                <p>This logo is used on the public landing page and login screen.</p>
            </div>

            <div class="settings-logo-content">
                <div class="settings-logo-preview-box">
                    <img
                        id="currentLogoPreview"
                        src="{{ $logoUrl }}"
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

        {{-- User Experience --}}
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
                    {{ $allowDarkMode ? 'checked' : '' }}
                    class="settings-toggle-input"
                >
            </label>
        </section>

        {{-- Maintenance Mode --}}
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
                    {{ $maintenanceMode ? 'checked' : '' }}
                    class="settings-toggle-input"
                >
            </label>
        </section>

        {{-- Vaccination Scheduling --}}
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
                    {{ $vaccinationSchedulingEnabled ? 'checked' : '' }}
                    class="settings-toggle-input"
                >
            </label>
        </section>

        {{-- Login Security --}}
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
                    value="{{ old('max_failed_login_attempts', $maxFailedLoginAttempts) }}"
                    class="settings-form-control"
                >

                @error('max_failed_login_attempts')
                    <span class="settings-field-error">{{ $message }}</span>
                @enderror
            </div>
        </section>

        {{-- Data Backup --}}
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
                    <option value="disabled" {{ $backupFrequency === 'disabled' ? 'selected' : '' }}>
                        Disabled
                    </option>
                    <option value="daily" {{ $backupFrequency === 'daily' ? 'selected' : '' }}>
                        Daily
                    </option>
                    <option value="weekly" {{ $backupFrequency === 'weekly' ? 'selected' : '' }}>
                        Weekly
                    </option>
                    <option value="monthly" {{ $backupFrequency === 'monthly' ? 'selected' : '' }}>
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
                    <option value="hostinger" {{ $backupDestination === 'hostinger' ? 'selected' : '' }}>
                        Hostinger SFTP
                    </option>
                    <option value="local" {{ $backupDestination === 'local' ? 'selected' : '' }}>
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
                    {{ $backupEncryption ? 'checked' : '' }}
                    class="settings-toggle-input"
                >
            </label>

            <div class="settings-backup-actions">
                <a
                    href="{{ route('superadmin.system.settings.backup') }}"
                    class="settings-button settings-button-secondary"
                >
                    Download Database Backup
                </a>
            </div>
        </section>

        {{-- Save Settings --}}
        <div class="settings-form-footer">
            <button type="submit" class="settings-button settings-button-primary">
                Save Settings
            </button>
        </div>

    </form>
</div>

{{-- Logo Preview Modal --}}
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
            >
                Cancel
            </button>

            <button
                type="button"
                id="confirmLogoUpload"
                class="settings-button settings-button-primary"
            >
                Confirm &amp; Save
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('systemSettingsForm');
    const input = document.getElementById('logoUpload');
    const modal = document.getElementById('logoPreviewModal');
    const preview = document.getElementById('newLogoPreview');
    const cancel = document.getElementById('cancelLogoUpload');
    const confirm = document.getElementById('confirmLogoUpload');

    if (!form || !input || !modal || !preview || !cancel || !confirm) {
        return;
    }

    let previewObjectUrl = null;

    function closePreview() {
        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
        input.value = '';

        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }

        preview.removeAttribute('src');
        input.focus();
    }

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];

        if (!file) {
            return;
        }

        const allowedTypes = [
            'image/png',
            'image/jpeg',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {
            alert('Please select a PNG, JPG, or WEBP image.');
            input.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            alert('The selected image must not exceed 2 MB.');
            input.value = '';
            return;
        }

        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
        }

        previewObjectUrl = URL.createObjectURL(file);
        preview.src = previewObjectUrl;

        modal.classList.add('is-visible');
        modal.setAttribute('aria-hidden', 'false');
        cancel.focus();
    });

    cancel.addEventListener('click', closePreview);

    confirm.addEventListener('click', function () {
        if (!input.files || !input.files[0]) {
            closePreview();
            return;
        }

        confirm.disabled = true;
        confirm.textContent = 'Saving...';

        form.requestSubmit();
    });

    form.addEventListener('submit', function () {
        confirm.disabled = true;
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closePreview();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-visible')) {
            closePreview();
        }
    });
});
</script>
@endsection
