@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    .analytics-period { display: flex; flex-wrap: wrap; gap: 10px; justify-content: space-between; margin-top: 18px; color: #334155; font-size: 13px; font-weight: 600; }
    .analytics-period span { color: #64748b; font-weight: 400; }
    .analytics-scope { color: #64748b; font-size: 13px; line-height: 1.6; margin-bottom: 18px; }
    .analytics-activity { margin-bottom: 18px; }
    .analytics-activity-totals { display: flex; flex-wrap: wrap; justify-content: space-around; gap: 16px; color: #64748b; font-size: 13px; }
    .analytics-activity-totals strong { margin-left: 8px; color: #173b82; }
    .disease-kpi small { display: block; color: #64748b; line-height: 1.5; margin-top: 8px; }
    .analytics-table tbody tr:hover { background: #f8fafc; }
    .analytics-table td { line-height: 1.5; }
    .analytics-table a { color: #1d4ed8; text-decoration: underline; text-underline-offset: 3px; }
    .analytics-page { width: 100%; padding: 0; color: #172033; }
    .analytics-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
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
    .disease-filters { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; align-items: end; margin-bottom: 16px; }
    .disease-filters label { display: grid; gap: 5px; color: #475569; font-size: 12px; font-weight: 600; }
    .disease-filters select, .disease-filters input { width: 100%; min-height: 40px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; color: #172033; }
    .disease-filter-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .disease-filter-actions .btn { min-height: 40px; }
    .disease-date-range { display: contents; }
    .disease-kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin: 16px 0; }
    .disease-kpi { padding: 15px; border: 1px solid #dce4ee; border-radius: 10px; background: #f8fafc; }
    .disease-kpi span { display: block; color: #64748b; font-size: 12px; }
    .disease-kpi strong { display: block; margin-top: 6px; color: #173b82; font-size: 24px; }
    .disease-map { width: 100%; height: 420px; border: 1px solid #dce4ee; border-radius: 10px; background: #eef4f7; }
    .disease-map-legend { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 10px; color: #475569; font-size: 12px; }
    .disease-map-legend span { display: inline-flex; gap: 6px; align-items: center; }
    .disease-map-legend i { width: 12px; height: 12px; border: 2px solid #1d4ed8; border-radius: 50%; background: rgba(37,99,235,.25); }
    .disease-map-legend i.boundary { border-radius: 2px; background: rgba(37,99,235,.12); }
    .disease-empty { display: grid; min-height: 140px; place-items: center; padding: 20px; color: #64748b; text-align: center; }
    .disease-note { margin: 12px 0 0; padding: 11px 12px; border-left: 3px solid #2563eb; background: #eff6ff; color: #334155; font-size: 12px; line-height: 1.5; }
    .disease-suppressed { color: #64748b; font-style: italic; }
    .leaflet-popup-content { margin: 10px 12px; }
    @media (max-width: 900px) { .disease-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); } .disease-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 620px) { .disease-filters, .disease-kpi-grid { grid-template-columns: 1fr; } .disease-map { height: 330px; } }
    @media (max-width: 900px) { .analytics-grid, .analytics-layout { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 620px) { .analytics-grid, .analytics-layout { grid-template-columns: 1fr; } .analytics-table { min-width: 560px; } .analytics-panel { overflow-x: auto; } }
</style>
@endsection

@section('content')
<div class="analytics-page">
    <div class="page-top">
        <div class="page-title-group">
            <h1>BHW Analytics</h1>
            <p>Diagnosis patterns, record activity, and supply planning in one place.</p>
        </div>
    </div>

    <h2 class="analytics-type-heading">Disease monitoring</h2>
    <section class="analytics-panel" aria-labelledby="diseaseMonitoringHeading">
        <h2 id="diseaseMonitoringHeading">Diagnosis records by illness and area</h2>
        <p>Aggregates use diagnosis text and the recorded diagnosis date. Filters apply to the disease summaries, diagnosis chart, area table, and map only.</p>
        <form class="disease-filters" method="GET" action="{{ route('bhw.analytics') }}" id="diseaseFilters">
            <label>
                Reporting period
                <select name="disease_period" id="diseasePeriod">
                    <option value="today" @selected($period === 'today')>Today</option>
                    <option value="last7" @selected($period === 'last7')>Last 7 days</option>
                    <option value="last30" @selected($period === 'last30')>Last 30 days</option>
                    <option value="this_month" @selected($period === 'this_month')>This month</option>
                    <option value="previous_month" @selected($period === 'previous_month')>Previous month</option>
                    <option value="this_year" @selected($period === 'this_year')>This year</option>
                    <option value="custom" @selected($period === 'custom')>Custom range</option>
                </select>
            </label>
            <div class="disease-date-range" id="diseaseDateRange">
                <label>From <input type="date" name="disease_start" value="{{ $periodStart->toDateString() }}" id="diseaseStart"></label>
                <label>To <input type="date" name="disease_end" value="{{ $periodEnd->toDateString() }}" id="diseaseEnd"></label>
            </div>
            <label>
                Diagnosis
                <select name="disease_name">
                    <option value="">All diagnoses</option>
                    @foreach($diseaseMonitoringData['diagnosisOptions'] as $option)
                        <option value="{{ $option->diagnosis_key }}" @selected(($filters['disease_name'] ?? '') === $option->diagnosis_key)>{{ $option->diagnosis }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Purok
                <select name="disease_purok" id="diseasePurok">
                    <option value="">All accessible Puroks</option>
                    @foreach($diseaseMonitoringData['availablePuroks'] as $purok)
                        <option value="{{ $purok->id }}" @selected((string) ($filters['disease_purok'] ?? '') === (string) $purok->id)>{{ $purok->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Subgroup
                <select name="disease_subgroup">
                    <option value="">All accessible subgroups</option>
                    @foreach($diseaseMonitoringData['availableSubgroups'] as $subgroup)
                        <option value="{{ $subgroup->id }}" @selected((string) ($filters['disease_subgroup'] ?? '') === (string) $subgroup->id)>{{ $subgroup->name }} — {{ $subgroup->purok?->name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="disease-filter-actions">
                <button type="submit" class="btn btn-primary">Apply filters</button>
                <a class="btn btn-secondary" href="{{ route('bhw.analytics') }}">Reset filters</a>
            </div>
        </form>

        <div class="analytics-period">{{ $periodStart->format('M j, Y') }} – {{ $periodEnd->format('M j, Y') }} <span>Accessible citizens only</span></div>
        <div class="disease-kpi-grid">
            <div class="disease-kpi"><span>Diagnosis records · {{ $periodLabel }}</span><strong>{{ $diseaseMonitoringData['privacySuppressed'] ? 'Suppressed' : number_format($diseaseMonitoringData['totalRecords']) }}</strong></div>
            <div class="disease-kpi"><span>Distinct citizens recorded</span><strong>{{ $diseaseMonitoringData['privacySuppressed'] ? 'Fewer than 3' : number_format($diseaseMonitoringData['uniqueCitizens']) }}</strong></div>
            <div class="disease-kpi"><span>Change vs previous equal-length period</span><strong>{{ $diseaseMonitoringData['privacySuppressed'] || $diseaseMonitoringData['previousSuppressed'] ? 'Suppressed' : ($diseaseMonitoringData['previousChange'] === null ? '—' : ($diseaseMonitoringData['previousChange'] > 0 ? '+' : '') . $diseaseMonitoringData['previousChange'] . '%') }}</strong><small>{{ $diseaseMonitoringData['privacySuppressed'] || $diseaseMonitoringData['previousSuppressed'] ? 'Previous-period details withheld for privacy' : number_format($diseaseMonitoringData['previousRecords']) . ' previous-period records' }}</small></div>
            <div class="disease-kpi"><span>Records excluded from map</span><strong>{{ $diseaseMonitoringData['privacySuppressed'] ? 'Suppressed' : ($diseaseMonitoringData['missingLocationSuppressed'] ? 'Suppressed (<3)' : number_format($diseaseMonitoringData['missingLocationRecords'])) }}</strong><small>{{ $diseaseMonitoringData['privacySuppressed'] ? 'Fewer than 3 distinct citizens in this result' : 'Missing map location or privacy threshold · ' . ($diseaseMonitoringData['missingDateSuppressed'] ? 'missing-date total withheld' : number_format($diseaseMonitoringData['missingDateRecords']) . ' records have no diagnosis date') }}</small></div>
        </div>

        <p class="disease-note">Counts are diagnosis-record entries, not confirmed new cases. One citizen may have multiple visits or repeated diagnoses. The schema has no episode identifier or separate case-status field; “Resolved” is a severity value and is not treated as a case status. Small results and map areas with fewer than three distinct citizens are withheld to reduce disclosure risk. This is a review aid, not an outbreak determination.</p>
    </section>

    <div class="analytics-layout">
        <section class="analytics-panel">
            <h2>Most recorded diagnoses</h2>
            <p>Diagnosis names are grouped by case and outer-whitespace normalization only; clinically distinct terms are not automatically merged.</p>
            @if($diseaseMonitoringData['diagnosisRankings']->isNotEmpty())
                <div id="diagnosisRankingChart" class="analytics-chart" role="img" aria-label="Top diagnoses by record count; values are listed in the table below"></div>
            @endif
            <p>Up to 10 diagnoses are shown. Each must include at least three distinct citizens; displayed shares may not total 100%.</p>
            <table class="analytics-table">
                <thead><tr><th>Diagnosis</th><th>Records</th><th>Distinct citizens</th><th>Share of all filtered records</th></tr></thead>
                <tbody>
                    @forelse($diseaseMonitoringData['diagnosisRankings'] as $row)
                        <tr>
                            <td><a href="{{ route('bhw.analytics', array_merge(request()->query(), ['disease_name' => $row->diagnosis_key])) }}">{{ $row->diagnosis }}</a></td>
                            <td>{{ number_format($row->record_count) }}</td>
                            <td>{{ number_format($row->citizen_count) }}</td>
                            <td>{{ $diseaseMonitoringData['totalRecords'] ? number_format($row->record_count / $diseaseMonitoringData['totalRecords'] * 100, 1) : '0.0' }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">{{ $diseaseMonitoringData['privacySuppressed'] ? 'Details are withheld because fewer than three distinct citizens match these filters.' : 'No diagnoses meet the minimum of three distinct citizens for display.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <section class="analytics-panel">
            <h2>Geographic distribution</h2>
            <p>Only saved Purok or subgroup coordinates and boundaries are used. Map values are aggregated and do not identify citizens.</p>
            @if(count($diseaseMonitoringData['mapAreas']))
                <div id="diseaseMonitoringMap" class="disease-map" role="img" aria-label="Aggregated diagnosis records by mapped Purok and subgroup"></div>
                <div class="disease-map-legend"><span><i></i> Records by area (larger circle = more records)</span><span><i class="boundary"></i> Saved boundary</span><span>Areas with fewer than three distinct citizens are not displayed</span></div>
            @else
                <div class="disease-empty">{{ $diseaseMonitoringData['privacySuppressed'] ? 'Map details are withheld because fewer than three distinct citizens match these filters.' : 'No mapped areas match these filters. Check saved Purok/subgroup locations and the selected period.' }}</div>
            @endif
            <table class="analytics-table">
                <thead><tr><th>Area</th><th>Records</th><th>Most recorded diagnosis</th></tr></thead>
                <tbody>
                    @forelse($diseaseMonitoringData['areaRankings'] as $row)
                        <tr>
                            <td>
                                @if($row['area_id'] && $row['area_type'])
                                    <a href="{{ route('bhw.analytics', array_merge(request()->query(), [
                                        'disease_purok' => $row['purok_id'],
                                        'disease_subgroup' => $row['area_type'] === 'subgroup' ? $row['area_id'] : '',
                                    ])) }}">{{ $row['area'] }}</a>
                                @else
                                    {{ $row['area'] }}
                                @endif
                            </td>
                            <td>{{ number_format($row['records_count']) }}</td>
                            <td>{{ $row['top_diagnosis'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3">{{ $diseaseMonitoringData['privacySuppressed'] ? 'Area details are withheld because fewer than three distinct citizens match these filters.' : 'No area-level data matches these filters.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>

    <h2 class="analytics-type-heading">Activity &amp; planning</h2>
    <p class="analytics-scope">Fixed activity window: {{ $currentStart->format('M j, Y') }} – {{ now()->format('M j, Y') }}. Records are counted by creation date, not treatment date. Disease filters above do not apply here.</p>
    <section class="analytics-panel analytics-activity">
        <h2>Recent record activity</h2>
        <p>Diagnosis, maintenance, and vaccination entries created for accessible citizens. Entries are not unique visits or people.</p>
        <div id="activityMixChart" class="analytics-chart" role="img" aria-label="Record counts by activity type"></div>
        <div class="analytics-activity-totals"><span>Diagnosis <strong>{{ number_format($currentDiagnosisCount) }}</strong></span><span>Maintenance <strong>{{ number_format($currentMaintenanceCount) }}</strong></span><span>Vaccination <strong>{{ number_format($currentVaccinationCount) }}</strong></span></div>
    </section>

    <div class="analytics-panel" style="margin-bottom: 18px; background: {{ $forecastAvailable ? '#f0fdf4' : '#fff7ed' }}; border-color: {{ $forecastAvailable ? '#86efac' : '#fdba74' }};">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div>
                <strong style="display: block; color: #173b82; margin-bottom: 4px;">{{ $forecastStatusLabel }}</strong>
                <small style="color: #475569;">{{ $forecastStatusMessage }}</small>
            </div>
            @if($forecastAvailable)
                <span style="padding: 6px 10px; border-radius: 999px; background: rgba(34,197,94,0.12); color: #166534; font-size: 12px; font-weight: 700;">Ready for projection</span>
            @else
                <span style="padding: 6px 10px; border-radius: 999px; background: rgba(251,146,60,0.12); color: #9a4d00; font-size: 12px; font-weight: 700;">Need more data</span>
            @endif
        </div>
    </div>

    <div class="analytics-grid">
        <div class="analytics-card"><span>Next 14-day record-entry estimate</span><strong>{{ $forecastAvailable ? number_format($forecast) : '—' }}</strong><small>{{ $forecastAvailable ? 'Extrapolated from two 14-day windows' : 'Requires at least ' . $minimumForecastRecords . ' valid recent health records' }}</small></div>
        <div class="analytics-card"><span>Citizen creation / import log entries</span><strong>{{ number_format($citizenAdditions) }}</strong><small>Recorded creation / import actions for accessible citizens</small></div>
        <div class="analytics-card"><span>Citizen update log entries</span><strong>{{ number_format($citizenDetailEdits) }}</strong><small>Recorded update actions for accessible citizens</small></div>
    </div>

    <div class="analytics-layout">
        <section class="analytics-panel analytics-demand-panel">
            <h2>Record-entry trend estimate</h2>
            @if($forecastAvailable)
                <p>Comparison window: {{ $previousStart->format('M j, Y') }} – {{ $previousEnd->format('M j, Y') }}. The next 14-day estimate uses all diagnosis, maintenance, and immunization/vaccine activity from the latest two 14-day periods. Estimate = max(0, 2 × current entries − previous entries). This simple trend extrapolation has no confidence interval and does not predict illness or care needs.</p>
                <div id="healthDemandChart" class="analytics-chart"></div>
                <table class="analytics-table">
                    <thead><tr><th>Current activity type</th><th>Entries in current 14-day window</th></tr></thead>
                    <tbody>
                        <tr><td>Diagnosis</td><td>{{ number_format($currentDiagnosisCount) }}</td></tr>
                        <tr><td>Maintenance</td><td>{{ number_format($currentMaintenanceCount) }}</td></tr>
                        <tr><td>Immunization / vaccine</td><td>{{ number_format($currentVaccinationCount) }}</td></tr>
                        <tr><th>Total activity</th><th>{{ number_format($currentCount) }}</th></tr>
                    </tbody>
                </table>
                <div class="analytics-note">Compare with actual appointments and service schedules before planning staffing. Data entry backlogs and imports can change these counts without a change in care demand.</div>
            @else
                <p>Health forecasting is automatically disabled until there is enough valid recent activity data.</p>
                <div class="analytics-note">Add at least {{ $minimumForecastRecords }} valid records with recent dates across diagnosis, maintenance, or immunization activity to activate this forecast. The estimate remains unavailable below this data threshold.</div>
            @endif
        </section>

        <section class="analytics-panel analytics-supply-panel">
            <h2>Supply stock &amp; usage</h2>
            <p>This forecast answers two questions: how much was withdrawn recently, and how much may be withdrawn during the next 14 days if the same usage continues.</p>
            <div class="analytics-note">The next 14-day projection matches the current 14-day withdrawal quantity. “Days remaining” compares current stock with the recent daily withdrawal rate. All barangay inventory is shown, independent of citizen filters. Restocking and future changes in usage are not included. Quantities use each item’s recorded inventory unit.</div>
            <div id="supplyPlanningChart" class="analytics-chart" role="img" aria-label="Current stock and next 14-day estimated withdrawals by supply"></div>
            <table class="analytics-table">
                <thead><tr><th>Medicine / supply</th><th>Stock review</th><th>Withdrawn recently<br><small>Current 14-day window</small></th><th>Expected withdrawal<br><small>Next 14 days</small></th><th>Current stock</th><th>Estimated days remaining</th></tr></thead>
                <tbody>
                    @forelse($supplyRecommendations as $supply)
                        <tr>
                            <td>{{ $supply['name'] }}</td>
                            <td><span class="analytics-badge {{ $supply['priority'] === 'Stable' ? 'stable' : '' }}">{{ $supply['priority'] }}</span><br><small>Minimum: {{ number_format($supply['min_stock']) }}</small></td>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.getElementById('diseasePeriod');
    const dateRange = document.getElementById('diseaseDateRange');
    const startDate = document.getElementById('diseaseStart');
    const endDate = document.getElementById('diseaseEnd');
    const updateDateRange = () => {
        const custom = periodSelect.value === 'custom';
        dateRange.style.display = custom ? 'contents' : 'none';
        startDate.disabled = !custom;
        endDate.disabled = !custom;
        startDate.required = custom;
        endDate.required = custom;
    };
    periodSelect.addEventListener('change', updateDateRange);
    updateDateRange();

    if (typeof L !== 'undefined') {
        const mapElement = document.getElementById('diseaseMonitoringMap');
        const mapAreas = @json($diseaseMonitoringData['mapAreas']);
        if (mapElement && mapAreas.length) {
            const map = L.map(mapElement).setView([14.0668, 120.8531], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            const layers = [];
            mapAreas.forEach((area) => {
                const color = '#2563eb';
                const popup = document.createElement('div');
                const heading = document.createElement('strong');
                heading.textContent = area.type === 'subgroup' ? `${area.name} — ${area.purok}` : area.name;
                const details = document.createElement('div');
                details.textContent = `${area.records_count} diagnosis records · ${area.citizens_count} distinct citizens`;
                const areaLink = document.createElement('a');
                const filterParams = new URLSearchParams(new FormData(document.getElementById('diseaseFilters')));
                filterParams.set('disease_purok', area.purok_id);
                filterParams.set('disease_subgroup', area.type === 'subgroup' ? area.id : '');
                areaLink.href = `${document.getElementById('diseaseFilters').action}?${filterParams.toString()}`;
                areaLink.textContent = 'Filter to this area';
                popup.append(heading, details, areaLink);
                if (area.boundary) {
                    const boundary = L.geoJSON(area.boundary, {
                        style: { color, weight: 2, fillColor: color, fillOpacity: 0.18 }
                    }).addTo(map).bindPopup(popup);
                    layers.push(boundary);
                }
                if (area.location) {
                    const marker = L.circleMarker([area.location.lat, area.location.lng], {
                        radius: Math.min(18, 7 + Math.sqrt(area.records_count)),
                        color,
                        fillColor: color,
                        fillOpacity: 0.65,
                        weight: 2
                    }).addTo(map).bindPopup(popup);
                    layers.push(marker);
                }
            });
            if (layers.length) {
                map.fitBounds(L.featureGroup(layers).getBounds().pad(0.15), { maxZoom: 15 });
            }
        }
    }

    if (typeof ApexCharts === 'undefined') return;
    const activityCounts = @json([$currentDiagnosisCount, $currentMaintenanceCount, $currentVaccinationCount]);
    new ApexCharts(document.querySelector('#activityMixChart'), {
        chart: { type: 'bar', height: 230, toolbar: { show: false } },
        series: [{ name: 'Record entries', data: activityCounts }],
        xaxis: { categories: ['Diagnosis', 'Maintenance', 'Vaccination'] },
        yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0 },
        colors: ['#2563eb', '#0d9488', '#7c3aed'],
        plotOptions: { bar: { distributed: true, borderRadius: 5, columnWidth: '40%' } },
        legend: { show: false }, dataLabels: { enabled: true },
        noData: { text: 'No activity recorded' }
    }).render();
    const supplies = @json($supplyRecommendations);
    if (supplies.length) {
        new ApexCharts(document.querySelector('#supplyPlanningChart'), {
            chart: { type: 'bar', height: Math.max(260, supplies.length * 65), toolbar: { show: false } },
            series: [
                { name: 'Current stock', data: supplies.map(s => s.quantity) },
                { name: 'Next 14-day withdrawal estimate', data: supplies.map(s => s.forecast_quantity) }
            ],
            xaxis: { categories: supplies.map(s => s.name), decimalsInFloat: 0 },
            colors: ['#2563eb', '#f59e0b'],
            plotOptions: { bar: { horizontal: true, borderRadius: 3 } },
            dataLabels: { enabled: false }, legend: { position: 'top' },
            tooltip: { shared: true, intersect: false }
        }).render();
    }
    const demandChart = document.querySelector('#healthDemandChart');

    if (demandChart && @json($forecastAvailable)) {
        new ApexCharts(demandChart, {
            chart: { type: 'bar', height: 260, toolbar: { show: false } },
            series: [{ name: 'All health activities', data: @json([$previousCount, $currentCount, $forecast]) }],
            xaxis: { categories: ['Previous 14 days (observed)', 'Current 14 days (observed)', 'Next 14 days (estimate)'] },
            colors: ['#94a3b8', '#2563eb', '#0d9488'],
            plotOptions: { bar: { borderRadius: 5, distributed: true } },
            dataLabels: { enabled: true },
            legend: { show: false },
            grid: { borderColor: '#e2e8f0' }
        }).render();
    }

    const diagnosisChart = document.querySelector('#diagnosisRankingChart');
    const diagnosisRows = @json($diseaseMonitoringData['diagnosisRankings']);
    if (diagnosisChart && diagnosisRows.length) {
        new ApexCharts(diagnosisChart, {
            series: [{ name: 'Diagnosis records', data: diagnosisRows.map((row) => Number(row.record_count)) }],
            chart: {
                type: 'bar',
                height: Math.max(260, diagnosisRows.length * 38),
                toolbar: { show: false },
                events: {
                    dataPointSelection: (_event, _chart, options) => {
                        const selected = diagnosisRows[options.dataPointIndex];
                        if (!selected) return;
                        const params = new URLSearchParams(new FormData(document.getElementById('diseaseFilters')));
                        params.set('disease_name', selected.diagnosis_key);
                        window.location.href = `${document.getElementById('diseaseFilters').action}?${params.toString()}`;
                    }
                }
            },
            xaxis: { categories: diagnosisRows.map((row) => row.diagnosis), labels: { trim: true } },
            colors: ['#2563eb'],
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
            dataLabels: { enabled: true },
            grid: { borderColor: '#e2e8f0' }
        }).render();
    }
});
</script>
@endsection