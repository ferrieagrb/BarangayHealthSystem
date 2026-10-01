@extends('templates.admin')

@section('CSSown')

@endsection
    
@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="page-top" style="margin-bottom: 20px;">
    <h1>Admin Dashboard</h1>
    <p>Welcome back, {{ auth()->user()->name }}!</p>
</div>

<!-- TOP METRIC CARDS -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px;">
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <span style="color: #6b7280; font-size: 0.85rem;">Active BHW Accounts</span>
        <h2 style="margin-top: 5px; color: #1f2937;">{{ $bhwCount ?? 0 }}</h2>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <span style="color: #6b7280; font-size: 0.85rem;">Low Stock Items</span>
        <h2 style="margin-top: 5px; color: #d9534f;">{{ $lowStockCount ?? 0 }}</h2>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <span style="color: #6b7280; font-size: 0.85rem;">System Status</span>
        <h2 style="margin-top: 5px; color: #10b981;">Online</h2>
    </div>
</div>

<!-- 2-COLUMN CHARTS SECTION -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
    
    <!-- Chart 2: Supplies by Category (Horizontal Bar) -->
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 15px; font-size: 16px; color: #374151;">Supplies by Category</h3>
        <div id="categoryChart"></div>
    </div>

    <!-- Chart 3: Permission Access Breakdown (Radial Bar) -->
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 15px; font-size: 16px; color: #374151;">BHW Permission Levels</h3>
        <div id="permissionRadialChart"></div>
    </div>

</div>

<!-- RECENT AUDIT LOGS WIDGET -->
<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <h3 style="margin-bottom: 15px; font-size: 16px; color: #374151;">Recent Permission Changes</h3>
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
        <thead>
            <tr style="border-bottom: 2px solid #e5e7eb; color: #374151;">
                <th style="padding: 8px;">Timestamp</th>
                <th style="padding: 8px;">Admin</th>
                <th style="padding: 8px;">Target User</th>
                <th style="padding: 8px;">Changes</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($recentLogs) && $recentLogs->count() > 0)
                @foreach($recentLogs as $log)
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 8px; color: #6b7280;">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                        <td style="padding: 8px;"><strong>{{ $log->admin->name ?? 'System' }}</strong></td>
                        <td style="padding: 8px;">{{ $log->targetUser->name ?? 'Unknown' }}</td>
                        <td style="padding: 8px; color: #4b5563;">{{ $log->changes }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="padding: 15px; text-align: center; color: #6b7280;">No recent logs found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- APEXCHARTS INITIALIZATION SCRIPT -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        
        // --- CHART 2: Supplies by Category ---
        var categoryOptions = {
            series: [{
                name: 'Total Quantity',
                data: @json($categoryValues)
            }],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: { borderRadius: 4, horizontal: true }
            },
            colors: ['#10b981'],
            xaxis: {
                categories: @json($categoryLabels)
            }
        };
        var categoryChart = new ApexCharts(document.querySelector("#categoryChart"), categoryOptions);
        categoryChart.render();

        // --- CHART 3: Permission Access Breakdown ---
        var permissionOptions = {
            series: [{{ $writeCount }}, {{ $readCount }}, {{ $noneCount }}],
            chart: {
                type: 'donut', // Changed from 'radialBar' to 'donut'
                height: 300
            },
            labels: ['Read / Write', 'Read Only', 'No Access'],
            colors: ['#2563eb', '#f59e0b', '#ef4444'],
            legend: {
                position: 'bottom'
            }
        };

        var permissionChart = new ApexCharts(document.querySelector("#permissionRadialChart"), permissionOptions);
        permissionChart.render();

    });
</script>
@endsection