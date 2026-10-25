
@extends('templates.superadmin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/superadmin/superadmin_dashboard.css') }}">
@endsection

@section('content')
<div class="dashboard-container">

    {{-- Dashboard Header --}}
    <div class="page-top">
        <div class="page-title-group">
            <h1>Superadmin Dashboard</h1>
            <p>System-wide security, user, and performance overview.</p>
        </div>
    </div>

    {{-- Analytics Charts --}}
    <div class="dashboard-grid-2">

        <div class="dashboard-card">
            <h2 class="dashboard-card-title">User Growth Analytics</h2>
            <p class="dashboard-card-subtitle">Daily User Registrations</p>

<<<<<<< HEAD
<!-- Security & Access Control Monitor Section -->
<div class="w-full space-y-6">
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
                    <!-- Updated with ID for real-time count updating -->
                    <span id="active-session-count-badge" class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                        {{ $activeUserCount ?? 0 }} Online Now
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="border-b text-xs uppercase text-gray-400">
                            <tr>
                                <th class="pb-2">User</th>
                                <th class="pb-2">Login Location</th>
                                <th class="pb-2">Last Active</th>
                            </tr>
                        </thead>
                        <!-- Updated with ID for dynamic table row refreshing -->
                        <tbody id="active-sessions-table-body" class="divide-y divide-gray-100">
                            @forelse($activeSessions ?? [] as $session)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3">
                                        <div class="font-medium text-gray-800">{{ $session->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $session->email }}</div>
                                    </td>
                                    <td class="py-3 text-xs text-gray-500">{{ $session->location ?? 'Location unavailable' }}</td>
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
=======
            <div class="chart-container">
                <div id="userAnalyticsApexChart"></div>
>>>>>>> 8f05883dd366c56d631103f280dc44da05bf5816
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

    {{-- Security and Access Control --}}
    <div class="dashboard-stack">

        <div class="dashboard-header-flex">
            <h2>Security &amp; Access Control Monitor</h2>
            <span class="badge-guard">Live Guard Active</span>
        </div>

        <div class="dashboard-grid-2">

            {{-- Active Sessions --}}
            <div class="dashboard-card dashboard-card-flex">
                <div>
                    <div class="dashboard-header-flex dashboard-widget-header">
                        <h3 class="dashboard-widget-title">Active Sessions</h3>

                        <span id="active-session-count-badge" class="badge-online">
                            {{ $activeUserCount ?? 0 }} Online Now
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
                                @forelse($activeSessions ?? [] as $session)
                                    <tr>
                                        <td>
                                            <div class="table-user-name">
                                                {{ $session->name }}
                                            </div>
                                            <div class="table-user-email">
                                                {{ $session->email }}
                                            </div>
                                        </td>

                                        <td class="table-subtext">
                                            {{ $session->location ?? 'Location unavailable' }}
                                        </td>

                                        <td class="table-subtext">
                                            {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="empty-table">
                                            No active sessions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Security Alerts --}}
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
                            <strong>{{ $failedLoginCount ?? 0 }}</strong>
                            failed authentication attempts within the tracking
                            period. No accounts currently locked out.
                        </div>
                    </div>
                </div>

                <div class="security-button-wrapper">
                    <button type="button" class="btn-danger-outline">
                        Clear Failed Login Cache &amp; Block IPs
                    </button>
                </div>
            </div>

        </div>

        {{-- System Audit Trail --}}
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
                        @forelse($auditLogs ?? [] as $log)
                            <tr>
                                <td class="table-user-name">
                                    {{ $log->admin_name ?? 'System' }}
                                </td>

                                <td>
                                    <span class="badge-action">
                                        {{ $log->action }}
                                    </span>
                                </td>

                                <td>{{ $log->description }}</td>

                                <td class="table-mono">
                                    {{ $log->ip_address ?? 'N/A' }}
                                </td>

                                <td class="table-subtext">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-table">
                                    No audit logs recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', async function () {

    // User Growth Analytics
    try {
        const userResponse = await fetch('/superadmin/user-analytics');

        if (!userResponse.ok) {
            throw new Error('Unable to fetch user analytics.');
        }

        const userData = await userResponse.json();

        const userOptions = {
            series: [{
                name: 'Added Users',
                data: userData.map(item => Number(item.count) || 0)
            }],
            chart: {
                type: 'area',
                width: '100%',
                height: '100%',
                toolbar: { show: true }
            },
            grid: {
                padding: {
                    left: 5,
                    right: 5,
                    top: 0,
                    bottom: 0
                }
<<<<<<< HEAD
            };

            const perfChart = new ApexCharts(document.querySelector("#websitePerformanceChart"), perfOptions);
            perfChart.render();
        } catch (error) {
            console.error('Failed to load website performance data:', error);
        }

        // --- Real-Time Active Sessions Polling (Every 10 seconds) ---
        async function updateActiveSessions() {
            try {
                const response = await fetch('/superadmin/active-sessions-json');
                const data = await response.json();

                const badge = document.querySelector('#active-session-count-badge');
                if (badge) {
                    badge.textContent = `${data.count} Online Now`;
                }

                const tbody = document.querySelector('#active-sessions-table-body');
                if (!tbody) return;

                if (!data.sessions || data.sessions.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-400 text-xs">No active sessions found.</td>
                        </tr>
                    `;
                    return;
                }

                let rowsHtml = '';
                data.sessions.forEach(session => {
                    rowsHtml += `
                        <tr class="hover:bg-gray-50">
                            <td class="py-3">
                                <div class="font-medium text-gray-800">${session.name}</div>
                                <div class="text-xs text-gray-400">${session.email}</div>
                            </td>
                            <td class="py-3 text-xs text-gray-500">${session.location ?? 'Location unavailable'}</td>
                            <td class="py-3 text-xs text-gray-500">${session.last_activity}</td>
                        </tr>
                    `;
                });

                tbody.innerHTML = rowsHtml;
            } catch (error) {
                console.error('Failed to fetch real-time active sessions:', error);
=======
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            xaxis: {
                categories: userData.map(item => item.date),
                type: 'category',
                labels: {
                    style: { fontSize: '11px' }
                }
            },
            yaxis: {
                min: 0,
                tickAmount: 5
            },
            colors: ['#3b82f6'],
            title: {
                text: '',
                align: 'left'
            },
            noData: {
                text: 'No user analytics available'
>>>>>>> 8f05883dd366c56d631103f280dc44da05bf5816
            }
        };

        const userChart = new ApexCharts(
            document.querySelector('#userAnalyticsApexChart'),
            userOptions
        );

        userChart.render();

    } catch (error) {
        console.error('Failed to load user analytics:', error);
    }

    // Website Traffic and Performance
    try {
        const perfResponse = await fetch('/superadmin/website-performance');

        if (!perfResponse.ok) {
            throw new Error('Unable to fetch website performance.');
        }

        const perfData = await perfResponse.json();

        const perfOptions = {
            series: [
                {
                    name: 'Page Views',
                    data: perfData.map(item => Number(item.views) || 0)
                },
                {
                    name: 'Avg Load Time (ms)',
                    data: perfData.map(
                        item => Number(item.avg_load_time) || 0
                    )
                }
            ],
            chart: {
                type: 'bar',
                height: '100%',
                width: '100%',
                toolbar: { show: true }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 3
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: perfData.map(item => item.date),
                labels: {
                    style: { fontSize: '11px' }
                }
            },
            yaxis: [
                {
                    title: { text: 'Views' },
                    labels: {
                        style: { fontSize: '11px' }
                    }
                },
                {
                    opposite: true,
                    title: { text: 'Load Time (ms)' },
                    labels: {
                        style: { fontSize: '11px' }
                    }
                }
            ],
            fill: { opacity: 1 },
            colors: ['#10b981', '#f59e0b'],
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            noData: {
                text: 'No website performance data available'
            }
        };

        const perfChart = new ApexCharts(
            document.querySelector('#websitePerformanceChart'),
            perfOptions
        );

        perfChart.render();

    } catch (error) {
        console.error('Failed to load website performance data:', error);
    }

    // Refresh Active Sessions Every 10 Seconds
    async function updateActiveSessions() {
        try {
            const response = await fetch('/superadmin/active-sessions-json');

            if (!response.ok) {
                throw new Error('Unable to fetch active sessions.');
            }

            const data = await response.json();

            const badge = document.querySelector(
                '#active-session-count-badge'
            );

            if (badge) {
                badge.textContent = `${data.count ?? 0} Online Now`;
            }

            const tbody = document.querySelector(
                '#active-sessions-table-body'
            );

            if (!tbody) return;

            if (!data.sessions || data.sessions.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="empty-table">
                            No active sessions found.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.replaceChildren();

            data.sessions.forEach(session => {
                const row = document.createElement('tr');

                const userCell = document.createElement('td');

                const userName = document.createElement('div');
                userName.className = 'table-user-name';
                userName.textContent = session.name ?? 'Unknown user';

                const userEmail = document.createElement('div');
                userEmail.className = 'table-user-email';
                userEmail.textContent = session.email ?? '';

                userCell.append(userName, userEmail);

                const locationCell = document.createElement('td');
                locationCell.className = 'table-subtext';
                locationCell.textContent =
                    session.location ?? 'Location unavailable';

                const activityCell = document.createElement('td');
                activityCell.className = 'table-subtext';
                activityCell.textContent = session.last_activity ?? 'Unknown';

                row.append(userCell, locationCell, activityCell);
                tbody.appendChild(row);
            });

        } catch (error) {
            console.error(
                'Failed to fetch real-time active sessions:',
                error
            );
        }
    }

    updateActiveSessions();
    setInterval(updateActiveSessions, 10000);
});
</script>
@endsection
