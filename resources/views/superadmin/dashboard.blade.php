@extends('templates.superadmin')

@section('CSSown')

@endsection
    
@section('content')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h1 class="text-2xl font-bold mb-6 text-gray-800">
    SUPERADMIN HOME
</h1>

<!-- Parent Grid Wrapper for Side-by-Side Analytics Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full max-w-7xl mx-auto mb-8">

    <!-- 1. User Growth Analytics Card -->
    <div class="w-full p-6 bg-white rounded-xl shadow-md">
        <h2 class="text-lg font-bold text-gray-800 mb-1">User Growth Analytics</h2>
        <p class="text-sm text-gray-500 mb-2">Daily User Registrations</p>
        
        <div class="w-full h-[280px] overflow-hidden relative">
            <div id="userAnalyticsApexChart" class="w-full h-full"></div>
        </div>
    </div>

    <!-- 2. Website Performance Analytics Card -->
    <div class="w-full p-6 bg-white rounded-xl shadow-md">
        <h2 class="text-lg font-bold text-gray-800 mb-1">Website Traffic & Performance</h2>
        <p class="text-sm text-gray-500 mb-2">Daily Page Views / Activity</p>
        
        <div class="w-full h-[280px] overflow-hidden relative">
            <div id="websitePerformanceChart" class="w-full h-full"></div>
        </div>
    </div>

</div>

<!-- Security & Access Control Monitor Section -->
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800">Security & Access Control Monitor</h2>
        <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Live Guard Active</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Widget 1: Active Sessions & Concurrent Logins -->
        <div class="p-6 bg-white rounded-xl shadow-md flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-gray-800">Active Sessions</h3>
                    <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                        {{ $activeUserCount ?? 0 }} Online Now
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="border-b text-xs uppercase text-gray-400">
                            <tr>
                                <th class="pb-2">User</th>
                                <th class="pb-2">IP Address</th>
                                <th class="pb-2">Last Active</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($activeSessions ?? [] as $session)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3">
                                        <div class="font-medium text-gray-800">{{ $session->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $session->email }}</div>
                                    </td>
                                    <td class="py-3 font-mono text-xs text-gray-500">{{ $session->ip_address }}</td>
                                    <td class="py-3 text-xs text-gray-500">
                                        {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-400 text-xs">No active sessions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Widget 2: Failed Logins / Security Alerts -->
        <div class="p-6 bg-white rounded-xl shadow-md flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-gray-800">Security Alerts</h3>
                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-full">Real-time Monitor</span>
                </div>

                <div class="p-4 mb-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                    <div class="font-bold text-red-800 text-sm">Suspicious Login Spike Detected</div>
                    <div class="text-xs text-red-600 mt-1">
                        Recorded <strong class="font-semibold">{{ $failedLoginCount ?? 0 }}</strong> failed authentication attempts within the tracking period. No accounts currently locked out.
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="button" class="w-full py-2 px-4 border border-red-300 text-red-600 hover:bg-red-50 text-xs font-semibold rounded-lg transition">
                    Clear Failed Login Cache & Block IPs
                </button>
            </div>
        </div>

    </div>

    <!-- Widget 3: Audit Trail / Activity Logs Stream -->
    <div class="p-6 bg-white rounded-xl shadow-md">
        <h3 class="text-base font-bold text-gray-800 mb-4">System Audit Trail</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="border-b text-xs uppercase text-gray-400">
                    <tr>
                        <th class="pb-3">Admin / User</th>
                        <th class="pb-3">Action</th>
                        <th class="pb-3">Description</th>
                        <th class="pb-3">IP Address</th>
                        <th class="pb-3">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($auditLogs ?? [] as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 font-medium text-gray-800">{{ $log->admin_name ?? 'System' }}</td>
                            <td class="py-3">
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded border border-gray-200">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3 text-gray-600">{{ $log->description }}</td>
                            <td class="py-3 font-mono text-xs text-gray-500">{{ $log->ip_address ?? 'N/A' }}</td>
                            <td class="py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-400 text-xs">No audit logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async function () {
        // --- Render User Growth Chart ---
        try {
            const userResponse = await fetch('/superadmin/user-analytics');
            const userData = await userResponse.json();

            const userCategories = userData.map(item => item.date);
            const userSeriesData = userData.map(item => item.count);

            const userOptions = {
                series: [{
                    name: 'Added Users',
                    data: userSeriesData
                }],
                chart: {
                    type: 'area',
                    width: '100%',
                    height: '100%',
                    toolbar: { show: true }
                },
                grid: {
                    padding: { left: 5, right: 5, top: 0, bottom: 0 }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    categories: userCategories,
                    type: 'category',
                    labels: { style: { fontSize: '11px' } }
                },
                yaxis: { min: 0, tickAmount: 5 },
                colors: ['#3b82f6'],
                title: { text: '', align: 'left' }
            };

            const userChart = new ApexCharts(document.querySelector("#userAnalyticsApexChart"), userOptions);
            userChart.render();
        } catch (error) {
            console.error('Failed to load user analytics:', error);
        }

        // --- Render Website Performance Chart ---
        try {
            const perfResponse = await fetch('/superadmin/website-performance');
            const perfData = await perfResponse.json();

            const perfCategories = perfData.map(item => item.date);
            const perfSeriesData = perfData.map(item => item.views);

            const perfOptions = {
                series: [{
                    name: 'Page Views',
                    data: perfSeriesData
                }],
                chart: {
                    type: 'line',
                    width: '100%',
                    height: '100%',
                    toolbar: { show: true }
                },
                grid: {
                    padding: { left: 5, right: 5, top: 0, bottom: 0 }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                xaxis: {
                    categories: perfCategories,
                    type: 'category',
                    labels: { style: { fontSize: '11px' } }
                },
                yaxis: { min: 0, tickAmount: 5 },
                colors: ['#10b981'],
                title: { text: '', align: 'left' }
            };

            const perfChart = new ApexCharts(document.querySelector("#websitePerformanceChart"), perfOptions);
            perfChart.render();
        } catch (error) {
            console.error('Failed to load website performance data:', error);
        }
    });
</script>
@endsection