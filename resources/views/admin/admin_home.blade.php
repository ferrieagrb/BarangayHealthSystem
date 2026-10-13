
@extends('templates.admin')

@section('CSSown')
    <link rel="stylesheet" href="{{ asset('css/admin/admin_home.css') }}">
@endsection

@section('content')

<div class="admin-dashboard">

    {{-- PAGE HEADER --}}
    <header class="admin-page-header">
        <h1>Admin Dashboard</h1>
        <p>Welcome back, {{ auth()->user()->name }}!</p>
    </header>

    {{-- METRIC CARDS --}}
    <section class="admin-metrics">

        <article class="admin-metric-card">
            <span>Active BHW Accounts</span>
            <h2 class="metric-primary">{{ $bhwCount ?? 0 }}</h2>
        </article>

        <article class="admin-metric-card">
            <span>Low Stock Items</span>
            <h2 class="metric-danger">{{ $lowStockCount ?? 0 }}</h2>
        </article>

        <article class="admin-metric-card">
            <span>System Status</span>
            <h2 class="metric-success">Online</h2>
        </article>

    </section>

    {{-- CHARTS --}}
    <section class="admin-charts">

        <article class="admin-chart-card">
            <h3>Supplies by Category</h3>
            <div id="categoryChart"></div>
        </article>

        <article class="admin-chart-card">
            <h3>BHW Permission Levels</h3>
            <div id="permissionRadialChart"></div>
        </article>

    </section>

    {{-- RECENT PERMISSION CHANGES --}}
    <section class="admin-logs-card">

        <header class="admin-logs-header">
            <h3>Recent Permission Changes</h3>
            <p>Latest recorded changes to user permissions.</p>
        </header>

        <div class="admin-table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Admin</th>
                        <th>Target User</th>
                        <th>Changes</th>
                    </tr>
                </thead>

                <tbody>
                    @if(isset($recentLogs) && $recentLogs->count() > 0)

                        @foreach($recentLogs as $log)
                            <tr>
                                <td class="log-timestamp">
                                    {{ $log->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $log->admin->name ?? 'System' }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $log->targetUser->name ?? 'Unknown' }}
                                </td>

                                <td class="log-changes">
                                    {{ $log->changes }}
                                </td>
                            </tr>
                        @endforeach

                    @else
                        <tr>
                            <td colspan="4" class="empty-logs">
                                No recent logs found.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </section>

</div>

{{-- CHART LIBRARY --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

{{-- CHART FUNCTIONALITY --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const categoryElement = document.querySelector('#categoryChart');
    const permissionElement = document.querySelector('#permissionRadialChart');

    if (typeof ApexCharts === 'undefined') {
        console.error('ApexCharts failed to load.');
        return;
    }

    if (categoryElement) {
        const categoryOptions = {
            series: [{
                name: 'Total Quantity',
                data: @json($categoryValues ?? [])
            }],

            chart: {
                type: 'bar',
                height: 280,
                width: '100%',
                toolbar: {
                    show: false
                },
                fontFamily: 'Poppins, sans-serif',
                redrawOnParentResize: true,
                redrawOnWindowResize: true
            },

            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true
                }
            },

            colors: ['#10b981'],

            xaxis: {
                categories: @json($categoryLabels ?? [])
            },

            dataLabels: {
                enabled: false
            },

            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        height: 260
                    }
                }
            }]
        };

        new ApexCharts(categoryElement, categoryOptions).render();
    }

    if (permissionElement) {
        const permissionOptions = {
            series: [
                {{ $writeCount ?? 0 }},
                {{ $readCount ?? 0 }},
                {{ $noneCount ?? 0 }}
            ],

            chart: {
                type: 'donut',
                height: 300,
                width: '100%',
                fontFamily: 'Poppins, sans-serif',
                redrawOnParentResize: true,
                redrawOnWindowResize: true
            },

            labels: [
                'Read / Write',
                'Read Only',
                'No Access'
            ],

            colors: ['#2563eb', '#f59e0b', '#ef4444'],

            legend: {
                position: 'bottom',
                fontSize: '12px'
            },

            dataLabels: {
                enabled: true
            },

            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        height: 280
                    },
                    legend: {
                        fontSize: '11px'
                    }
                }
            }]
        };

        new ApexCharts(permissionElement, permissionOptions).render();
    }

});
</script>

@endsection