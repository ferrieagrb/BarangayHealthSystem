@extends('templates.superadmin')

@section('CSSown')

@endsection
    
@section('content')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h1 class="text-2xl font-bold mb-6 text-gray-800">
    SUPERADMIN HOME
</h1>

<!-- Parent Grid Wrapper for Side-by-Side Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full max-w-7xl mx-auto">

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