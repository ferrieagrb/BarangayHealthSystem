@extends('templates.superadmin')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">System Analytics & Infrastructure</h1>

    <!-- Section 1: Core System Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Registered Users</p>
                <div class="mt-1 text-2xl font-bold text-gray-800" id="total-users-text">Loading...</div>
            </div>
            <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Active Users (Last 15m)</p>
                <div class="mt-1 text-2xl font-bold text-green-600" id="active-users-text">Loading...</div>
            </div>
            <div class="p-3 bg-green-50 text-green-600 rounded-lg">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Recorded Page Views</p>
                <div class="mt-1 text-2xl font-bold text-gray-800" id="total-views-text">Loading...</div>
            </div>
            <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Section 2: Infrastructure & Storage Health -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Disk Storage (Hostinger)</h4>
            <div class="mt-2 text-2xl font-bold text-gray-800" id="disk-usage-text">Loading...</div>
            <p class="text-xs text-gray-400 mt-1" id="disk-subtext">Calculating storage...</p>
            <div class="w-full bg-gray-200 rounded-full h-2.5 mt-3">
                <div id="disk-progress-bar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: 0%"></div>
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

    <!-- Section 3: Analytics Lists & Demographics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Visited Pages -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
            <h3 class="text-lg font-medium text-gray-800 mb-4">Top Visited Pages</h3>
            <div id="top-pages-list" class="space-y-3">
                <p class="text-sm text-gray-400">Loading top visited pages...</p>
            </div>
        </div>

        <!-- User Browsers & Devices -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-medium text-gray-800 mb-4">User Browsers</h3>
            <div id="browser-demographics-list" class="space-y-3">
                <p class="text-sm text-gray-400">Fetching browser distribution...</p>
            </div>
        </div>
    </div>

    <!-- Section 4: Errors & Queue -->
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
</div>

<script>
document.addEventListener("DOMContentLoaded", async function() {
    try {
        const response = await fetch('/superadmin/system-analytics-data');
        const data = await response.json();

        // Populate Section 1: Core Record Counts
        document.getElementById('total-users-text').textContent = data.records.total_users.toLocaleString();
        document.getElementById('active-users-text').textContent = `${data.records.active_sessions} Online`;
        document.getElementById('total-views-text').textContent = data.records.total_page_views.toLocaleString();

        // Populate Section 2: Storage & PHP
        document.getElementById('disk-usage-text').textContent = `${data.server.disk_used_text} / ${data.server.disk_total_text}`;
        document.getElementById('disk-subtext').textContent = `${data.server.disk_usage_percent}% of total capacity used`;
        document.getElementById('disk-progress-bar').style.width = `${data.server.disk_usage_percent}%`;

        document.getElementById('db-size-text').textContent = `${data.server.db_size_mb} MB`;
        document.getElementById('php-version-text').textContent = `PHP ${data.server.php_version}`;

        // Populate Section 3: Top Pages
        const topPagesContainer = document.getElementById('top-pages-list');
        if (data.top_pages && data.top_pages.length > 0) {
            let html = '<div class="divide-y divide-gray-100">';
            data.top_pages.forEach((p, idx) => {
                // Shorten displayed URL for clean visual presentation
                const parsedUrl = new URL(p.url, window.location.origin);
                const path = parsedUrl.pathname;

                html += `
                    <div class="py-2.5 flex items-center justify-between text-sm">
                        <div class="flex items-center space-x-3 overflow-hidden pr-2">
                            <span class="font-bold text-gray-400 w-5 text-right">${idx + 1}.</span>
                            <span class="text-gray-700 font-medium truncate" title="${p.url}">${path}</span>
                        </div>
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-md font-semibold text-xs whitespace-nowrap">
                            ${p.total_views.toLocaleString()} views
                        </span>
                    </div>
                `;
            });
            html += '</div>';
            topPagesContainer.innerHTML = html;
        } else {
            topPagesContainer.innerHTML = `<p class="text-xs text-gray-400">No page view records logged yet.</p>`;
        }

        // Populate Section 3: Browsers
        const browserContainer = document.getElementById('browser-demographics-list');
        if (data.demographics && data.demographics.length > 0) {
            let html = '';
            data.demographics.forEach(b => {
                html += `
                    <div class="flex justify-between items-center text-sm py-1 border-b border-gray-50 last:border-0">
                        <span class="text-gray-600 font-medium">${b.browser || 'Unknown'}</span>
                        <span class="font-semibold text-gray-800">${b.total.toLocaleString()} visits</span>
                    </div>`;
            });
            browserContainer.innerHTML = html;
        } else {
            browserContainer.innerHTML = `<p class="text-xs text-gray-400">No browser tracking logs found yet.</p>`;
        }

        // Populate Section 4: Failed Jobs
        document.getElementById('failed-jobs-badge').textContent = data.errors.failed_jobs;

    } catch (err) {
        console.error("Failed to load system analytics:", err);
    }
});
</script>
@endsection