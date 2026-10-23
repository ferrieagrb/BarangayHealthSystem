@extends('templates.superadmin')

@section('content')
<div class="w-full">
    <div class="page-top">
        <div class="page-title-group">
            <h1>System Settings</h1>
            <p>Manage global access behavior, maintenance mode, and backups.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('superadmin.system.settings.update') }}" class="space-y-5" enctype="multipart/form-data" id="systemSettingsForm">
        @csrf
        @method('PUT')

        <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-blue-900">Universal Website Logo</h2>
            <p class="mt-1 text-sm text-gray-500">This logo is used on the public landing page and login screen.</p>
            <div class="mt-5 flex flex-wrap items-center gap-5">
                <div class="flex h-28 w-28 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 p-3">
                    <img id="currentLogoPreview" src="{{ $logoUrl }}" alt="Current website logo" class="max-h-full max-w-full object-contain">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700" for="logoUpload">Upload new logo</label>
                    <input id="logoUpload" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="mt-2 block text-sm text-gray-600">
                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, or WEBP. Maximum 2 MB.</p>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-blue-900">User Experience</h2>
            <p class="mt-1 text-sm text-gray-500">Control interface preferences available to users.</p>
            <label class="mt-5 flex items-center justify-between gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <span><strong class="block text-sm text-gray-800">Allow dark mode</strong><small class="text-xs text-gray-500">Allow users to choose a dark interface theme.</small></span>
                <input type="checkbox" name="allow_dark_mode" value="1" {{ $allowDarkMode ? 'checked' : '' }} class="h-5 w-5">
            </label>
        </section>

        <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-blue-900">Maintenance Mode</h2>
            <p class="mt-1 text-sm text-gray-500">Temporarily restrict standard users while Superadmins retain access.</p>
            <label class="mt-5 flex items-center justify-between gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <span><strong class="block text-sm text-gray-800">Enable maintenance mode</strong><small class="text-xs text-gray-500">Use during upgrades or system maintenance.</small></span>
                <input type="checkbox" name="maintenance_mode" value="1" {{ $maintenanceMode ? 'checked' : '' }} class="h-5 w-5">
            </label>
        </section>

        <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-blue-900">Vaccination Scheduling</h2>
            <p class="mt-1 text-sm text-gray-500">Allow BHWs to schedule future vaccinations and show those schedules on citizen dashboards.</p>
            <label class="mt-5 flex items-center justify-between gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <span><strong class="block text-sm text-gray-800">Enable vaccination scheduling</strong><small class="text-xs text-gray-500">When disabled, scheduling forms and schedule displays are unavailable.</small></span>
                <input type="checkbox" name="vaccination_scheduling_enabled" value="1" {{ $vaccinationSchedulingEnabled ? 'checked' : '' }} class="h-5 w-5">
            </label>
        </section>

        <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-blue-900">Login Security</h2>
            <p class="mt-1 text-sm text-gray-500">Lock accounts after repeated failed password attempts.</p>
            <label class="mt-5 block text-sm font-semibold text-gray-700">Maximum failed login attempts
                <input type="number" name="max_failed_login_attempts" min="3" max="20" required value="{{ old('max_failed_login_attempts', $maxFailedLoginAttempts) }}" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </label>
        </section>

        <section class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-blue-900">Data Backup</h2>
            <p class="mt-1 text-sm text-gray-500">Choose a backup frequency or download a SQL backup immediately.</p>
            <label class="mt-5 block text-sm font-semibold text-gray-700">Automatic backup frequency
                <select name="backup_frequency" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="disabled" {{ $backupFrequency === 'disabled' ? 'selected' : '' }}>Disabled</option>
                    <option value="daily" {{ $backupFrequency === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $backupFrequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $backupFrequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </label>
            <label class="mt-5 block text-sm font-semibold text-gray-700">Backup destination
                <select name="backup_destination" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="hostinger" {{ $backupDestination === 'hostinger' ? 'selected' : '' }}>Hostinger SFTP</option>
                    <option value="local" {{ $backupDestination === 'local' ? 'selected' : '' }}>Application storage</option>
                </select>
            </label>
            <label class="mt-5 flex items-center justify-between gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <span><strong class="block text-sm text-gray-800">Encrypt backup files</strong><small class="text-xs text-gray-500">Backups are saved as encrypted .sql.enc files using the application key.</small></span>
                <input type="checkbox" name="backup_encryption" value="1" {{ $backupEncryption ? 'checked' : '' }} class="h-5 w-5">
            </label>
            <a href="{{ route('superadmin.system.settings.backup') }}" class="mt-5 inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Download Database Backup</a>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Save Settings</button>
        </div>
    </form>
</div>

<div id="logoPreviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-5" role="dialog" aria-modal="true" aria-labelledby="logoPreviewTitle">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
        <h2 id="logoPreviewTitle" class="text-lg font-semibold text-slate-800">Preview New Logo</h2>
        <p class="mt-1 text-sm text-slate-500">Confirm this image before saving it as the universal website logo.</p>
        <div class="mt-5 flex min-h-48 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-5">
            <img id="newLogoPreview" src="" alt="New logo preview" class="max-h-40 max-w-full object-contain">
        </div>
        <div class="mt-5 flex justify-end gap-3">
            <button type="button" id="cancelLogoUpload" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</button>
            <button type="button" id="confirmLogoUpload" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Confirm &amp; Save</button>
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

    function closePreview() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        input.value = '';
        preview.removeAttribute('src');
    }

    input.addEventListener('change', function () {
        const file = input.files?.[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = event => {
            preview.src = event.target.result;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            cancel.focus();
        };
        reader.readAsDataURL(file);
    });

    cancel.addEventListener('click', closePreview);
    confirm.addEventListener('click', function () {
        confirm.disabled = true;
        form.submit();
    });
    modal.addEventListener('click', event => {
        if (event.target === modal) closePreview();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) closePreview();
    });
});
</script>
@endsection
