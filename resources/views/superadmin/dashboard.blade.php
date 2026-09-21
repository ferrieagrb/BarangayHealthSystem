@extends('templates.superadmin')

@section('CSSown')

@endsection
    
@section('content')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h1 class="text-2xl font-bold mb-6 text-gray-800">
    SUPERADMIN HOME
</h1>

<!-- Card container -->
<div class="w-full max-w-xl mx-auto p-6 bg-white rounded-xl shadow-md">
    <h2 class="text-lg font-bold text-gray-800 mb-1">User Growth Analytics (SuperAdmin)</h2>
    <p class="text-sm text-gray-500 mb-4">Daily User Registrations</p>
    
    <!-- Wrapper div with a fixed height -->
    <div id="userAnalyticsApexChart" class="w-full h-[360px]"></div>
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
                    width: '100%',
                    height: 300, // 👈 Explicitly smaller than the 360px container to leave room for dates at the bottom
                    toolbar: {
                        show: true
                    }
                },
                grid: {
                    padding: {
                        left: 15,
                        right: 15,
                        bottom: 0
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
                        offsetY: -2 // 👈 Pulls the date labels slightly upward inside the container
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