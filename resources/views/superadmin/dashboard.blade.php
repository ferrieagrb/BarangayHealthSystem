@extends('templates.superadmin')

@section('CSSown')

@endsection
    
@section('content')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h1 class="text-2xl font-bold mb-6 text-gray-800">
    SUPERADMIN HOME
</h1>

<!-- Card container with overflow-hidden to contain date labels inside rounded corners -->
<div class="w-full max-w-3xl mx-auto p-6 pb-10 bg-white rounded-xl shadow-md overflow-hidden">
    <h2 class="text-lg font-bold text-gray-800 mb-1">User Growth Analytics (SuperAdmin)</h2>
    <p class="text-sm text-gray-500 mb-4">Daily User Registrations</p>
    
    <!-- Explicit height container for the chart to prevent layout blowout -->
    <div id="userAnalyticsApexChart" class="w-full h-[380px]"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async function () {
        try {
            const response = await fetch('/superadmin/user-analytics');
            const data = await response.json();

            const categories = data.map(item => item.date);
            const seriesData = data.map(item => item.count);

            const options = {
                series: [{
                    name: 'Added Users',
                    data: seriesData
                }],
                chart: {
                    type: 'area',
                    width: '100%',   // Fill the container width perfectly
                    height: '320px',  // Fill the container height perfectly
                    toolbar: {
                        show: true
                    }
                },
                grid: {
                    padding: {
                        left: 15,
                        right: 15,
                        bottom: 15
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                xaxis: {
                    categories: categories,
                    type: 'category',
                    labels: {
                        offsetY: 2 // Keeps labels safely positioned inside
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
                }
            };

            const chart = new ApexCharts(document.querySelector("#userAnalyticsApexChart"), options);
            chart.render();
        } catch (error) {
            console.error('Failed to load user analytics:', error);
        }
    });
</script>
@endsection