@extends('templates.superadmin')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">System Analytics & Infrastructure</h1>

    <!-- Section 1: Infrastructure & Storage Health -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Disk Storage (Hostinger)</h4>
            <div class="mt-2 text-2xl font-bold text-gray-800" id="disk-usage-text">Loading...</div>
            <p class="text-xs text-gray-400 mt-1" id="disk-subtext">Calculating storage...</p>
            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-3">
                <div id="disk-progress-bar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Database Size</h4>
            <div class="mt-2 text-2xl font-bold text-gray-800" id="db-size-text">Loading...</div>
            <p class="text-xs text-gray-400 mt-1">MySQL on Hostinger Server</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">PHP Environment</h4>
            <div class="mt-2 text-2xl font-bold text-gray-800" id="php-version-text">Loading...</div>
            <p class="text-xs text-green-600 mt-1">● Active & Running</p>
        </div>
    </div>

    <!-- Section 2 & 4: Errors & Browser Demographics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Background Processing & Errors</h3>
            <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-100">
                <div>
                    <span class="font-semibold text-red-700">Failed Queue Jobs</span>
                    <p class="text-xs text-red-500">Unprocessed background tasks that require attention.</p>
                </div>
                <span id="failed-jobs-badge" class="px-3 py-1 bg-red-600 text-white rounded-full text-sm font-bold">0</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-medium text-gray-800 mb-4">User Browsers & Devices</h3>
            <div id="browser-demographics-list" class="space-y-3">
                <p class="text-sm text-gray-400">Fetching browser distribution...</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", async function() {
    try {
        const response = await fetch('/superadmin/system-analytics-data');
        const data = await response.json();

        // Populate Section 1 (Disk Storage Used / Total + Subtext percentage)
        document.getElementById('disk-usage-text').textContent = `${data.server.disk_used_text} / ${data.server.disk_total_text}`;
        document.getElementById('disk-subtext').textContent = `${data.server.disk_usage_percent}% of total capacity used`;
        document.getElementById('disk-progress-bar').style.width = `${data.server.disk_usage_percent}%`;

        document.getElementById('db-size-text').textContent = `${data.server.db_size_mb} MB`;
        document.getElementById('php-version-text').textContent = `PHP ${data.server.php_version}`;

        // Populate Section 2
        document.getElementById('failed-jobs-badge').textContent = data.errors.failed_jobs;

        // Populate Section 4
        const browserContainer = document.getElementById('browser-demographics-list');
        if(data.demographics.length > 0) {
            let html = '';
            data.demographics.forEach(b => {
                html += `<div class="flex justify-between items-center text-sm"><span class="text-gray-600">${b.browser || 'Unknown'}</span><span class="font-semibold text-gray-800">${b.total} visits</span></div>`;
            });
            browserContainer.innerHTML = html;
        } else {
            browserContainer.innerHTML = `<p class="text-xs text-gray-400">No browser tracking logs found yet.</p>`;
        }
    } catch (err) {
        console.error("Failed to load system analytics:", err);
    }
});
</script>
@endsection