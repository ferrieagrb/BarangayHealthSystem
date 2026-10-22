@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
<style>
    .analytics-page { width: 100%; padding: 0; color: #172033; }
    .analytics-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .analytics-card, .analytics-panel { min-width: 0; padding: 20px; border: 1px solid #dce4ee; border-radius: 12px; background: #fff; box-shadow: 0 4px 18px rgba(20,42,76,.06); }
    .analytics-card span, .analytics-panel p { color: #64748b; font-size: 13px; }
    .analytics-card strong { display: block; margin: 8px 0 4px; color: #173b82; font-size: 28px; line-height: 1.1; }
    .analytics-card small { color: #64748b; }
    .analytics-layout { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(300px, .8fr); gap: 18px; margin-bottom: 18px; }
    .analytics-panel h2 { margin: 0 0 6px; color: #173b82; font-size: 19px; }
    .analytics-demand-panel { grid-column: 1 / -1; }
    .analytics-supply-panel { grid-column: 1 / -1; }
    .analytics-panel p { margin: 0 0 16px; line-height: 1.5; }
    .analytics-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .analytics-table th, .analytics-table td { padding: 10px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
    .analytics-table th { color: #64748b; font-size: 11px; text-transform: uppercase; }
    .analytics-badge { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 700; }
    .analytics-badge.stable { background: #dcfce7; color: #166534; }
    .analytics-note { padding: 12px; border-left: 3px solid #2563eb; background: #eff6ff; color: #334155; font-size: 12px; line-height: 1.5; }
    .analytics-chart { width: 100%; min-height: 260px; }
    .analytics-type-heading { margin: 28px 0 14px; color: #173b82; font-size: 22px; }
    .analytics-type-heading:first-of-type { margin-top: 0; }
    @media (max-width: 900px) { .analytics-grid, .analytics-layout { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 620px) { .analytics-grid, .analytics-layout { grid-template-columns: 1fr; } .analytics-table { min-width: 560px; } .analytics-panel { overflow-x: auto; } }
</style>
@endsection

@section('content')
<div class="analytics-page">
    <div class="page-top">
        <div class="page-title-group">
            <h1>BHW Analytics</h1>
            <p>Predictive indicators and recommended actions based on recent barangay records.</p>
        </div>
    </div>

    <h2 class="analytics-type-heading">Predictive Analytics</h2>

    <div class="analytics-grid">
        <div class="analytics-card"><span>Next 14-day health-record forecast</span><strong>{{ number_format($forecast) }}</strong><small>Based on the latest 14 days of records</small></div>
        <div class="analytics-card"><span>New citizen registration forecast</span><strong>{{ number_format($citizenAdditions) }}</strong><small>New citizens added or imported in the previous 14 days</small></div>
        <div class="analytics-card"><span>Citizen detail-edit forecast</span><strong>{{ number_format($citizenDetailEdits) }}</strong><small>Citizen information updates in the previous 14 days</small></div>
    </div>

    <div class="analytics-layout">
        <section class="analytics-panel analytics-demand-panel">
            <h2>Predicted Health Demand</h2>
            <p>The next 14-day estimate uses all diagnosis, maintenance, and immunization/vaccine activity from the latest two 14-day periods. It projects the current change forward and cannot fall below zero.</p>
            <div id="healthDemandChart" class="analytics-chart"></div>
            <table class="analytics-table">
                <thead><tr><th>Current activity type</th><th>Records in previous 14 days</th></tr></thead>
                <tbody>
                    <tr><td>Diagnosis</td><td>{{ number_format($currentDiagnosisCount) }}</td></tr>
                    <tr><td>Maintenance</td><td>{{ number_format($currentMaintenanceCount) }}</td></tr>
                    <tr><td>Immunization / vaccine</td><td>{{ number_format($currentVaccinationCount) }}</td></tr>
                    <tr><th>Total activity</th><th>{{ number_format($currentCount) }}</th></tr>
                </tbody>
            </table>
            <div class="analytics-note">Use this signal to prepare BHW schedules, screening activities, immunization sessions, maintenance follow-ups, and commonly needed supplies. Investigate sudden increases before taking action.</div>
        </section>

        <section class="analytics-panel analytics-supply-panel">
            <h2>Supply Depletion Forecast</h2>
            <p>This forecast answers two questions: how much was withdrawn recently, and how much may be withdrawn during the next 14 days if the same usage continues.</p>
            <div class="analytics-note">The next 14-day projection matches the previous 14-day withdrawal quantity. “Days remaining” compares current stock with the recent daily withdrawal rate. Medicine use is inferred from the medicine name and withdrawal notes.</div>
            <table class="analytics-table">
                <thead><tr><th>Medicine / supply</th><th>Typical use</th><th>Withdrawn recently<br><small>Previous 14 days</small></th><th>Expected withdrawal<br><small>Next 14 days</small></th><th>Current stock</th><th>Estimated days remaining</th></tr></thead>
                <tbody>
                    @forelse($supplyRecommendations as $supply)
                        <tr>
                            <td>{{ $supply['name'] }}</td>
                            <td>{{ $supply['purpose'] }}</td>
                            <td><strong>{{ $supply['withdrawn_quantity'] }} units</strong><br><small>{{ $supply['withdrawal_count'] }} withdrawal activities</small></td>
                            <td>{{ $supply['forecast_quantity'] }} units</td>
                            <td>{{ $supply['quantity'] }} units</td>
                            <td>{{ $supply['days_remaining'] === null ? 'No recent usage' : $supply['days_remaining'] . ' days' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No supplies available for forecasting.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') return;

    const demandChart = document.querySelector('#healthDemandChart');

    if (demandChart) {
        new ApexCharts(demandChart, {
            chart: { type: 'bar', height: 260, toolbar: { show: false } },
            series: [{ name: 'All health activities', data: @json([$previousCount, $currentCount, $forecast]) }],
            xaxis: { categories: ['Previous 14 days', 'Current 14 days', 'Next 14-day estimate'] },
            colors: ['#2563eb'],
            plotOptions: { bar: { borderRadius: 5, distributed: true } },
            dataLabels: { enabled: true },
            legend: { show: false },
            grid: { borderColor: '#e2e8f0' }
        }).render();
    }

});
</script>
@endsection