@extends('templates.superadmin')

@section('CSSown')

@endsection
    
@section('content')

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h1>
    SUPERADMIN HOME
</h1>

<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">User Growth Analytics (SuperAdmin)</h2>
    <div id="userAnalyticsApexChart"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async function () {
        try {
            const response = await fetch('/api/superadmin/user-analytics');
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
                    height: 350,
                    toolbar: {
                        show: true
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