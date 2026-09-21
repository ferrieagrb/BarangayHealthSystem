@extends('templates.superadmin')

@section('CSSown')

@endsection
    
@section('content')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h1>
    SUPERADMIN HOME
</h1>

<!-- Example using Tailwind classes for max-width and fixed height -->
<div class="w-full max-w-xl h-[500px] mx-auto p-4 bg-white rounded-lg shadow flex flex-col">
    <h2 class="text-lg font-bold mb-2">User Growth Analytics (SuperAdmin)</h2>
    
    <!-- Wrapper container for the chart taking remaining space -->
    <div id="userAnalyticsApexChart" class="flex-grow"></div>
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
                    type: 'area', // You can change to 'line', 'bar', etc.
                    width: '100%',   // Matches the parent div's width
                    height: '100%',  // Matches the parent div's height
                    toolbar: {
                        show: true
                    }
                },
                grid: {
                    padding: {
                        left: 20,  // Adds space on the left
                        right: 20  // Adds space on the right
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
                    type: 'category'
                },
                yaxis: {
                    min: 0,
                    tickAmount: 5
                },
                colors: ['#3b82f6'],
                title: {
                    text: 'Daily User Registrations',
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