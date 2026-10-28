@extends('templates.layout')

@section('CSSown')
<style>
    .reports-page { padding: 24px; color: #172033; font-family: Poppins, sans-serif; }
    .reports-grid { display: grid; grid-template-columns: 1fr; gap: 18px; align-items: start; }
    .report-panel { padding: 22px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 3px 12px rgba(15,23,42,.06); }
    .report-panel h2 { margin: 0 0 6px; color: #173b82; font-size: 20px; }
    .report-panel > p { margin: 0 0 18px; color: #64748b; font-size: 13px; }
    .report-config-row { display: grid; grid-template-columns: minmax(220px, 1.2fr) repeat(3, minmax(150px, 1fr)); gap: 14px; align-items: end; }
    .report-fields { display: contents; }
    .report-field { display: grid; gap: 6px; min-width: 0; }
    .report-field[hidden] { display: none; }
    .report-field label { color: #334155; font-size: 12px; font-weight: 700; }
    .report-field input, .report-field select { width: 100%; min-height: 42px; padding: 9px 11px; border: 1px solid #cbd5e1; border-radius: 7px; box-sizing: border-box; background: #fff; color: #172033; font: inherit; }
    .report-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-top: 18px; padding-top: 16px; border-top: 1px solid #e8edf4; }
    .report-actions button { border: 0; cursor: pointer; }
    .report-actions .btn-primary, .report-actions .btn-secondary { min-height: 40px; padding: 10px 16px; border-radius: 7px; font: inherit; font-weight: 600; }
    .report-actions .btn-primary { background: #2563eb; color: #fff; }
    .report-actions .btn-secondary { background: #f1f5f9; color: #334155; }
    .report-actions .btn-secondary:hover { background: #e2e8f0; }
    .report-graphics-toggle { display: inline-flex; align-items: center; gap: 8px; margin-right: auto; color: #475569; font-size: 13px; font-weight: 500; }
    .report-graphics-toggle[hidden] { display: none; }
    .report-preview { min-width: 0; }
    .report-meta { display: flex; justify-content: space-between; gap: 12px; align-items: start; margin-bottom: 18px; }
    .report-meta h1 { margin: 0; color: #173b82; font-size: 25px; }
    .report-meta p { margin: 5px 0 0; color: #64748b; font-size: 13px; }
    .report-section { margin-top: 20px; page-break-inside: avoid; }
    .report-section h3 { margin: 0 0 10px; padding-bottom: 7px; border-bottom: 2px solid #1d4ed8; color: #173b82; font-size: 16px; }
    .report-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .report-table th, .report-table td { padding: 8px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
    .report-table th { color: #64748b; background: #f8fafc; font-size: 10px; text-transform: uppercase; }
    .report-empty { padding: 18px; color: #64748b; text-align: center; }
    .report-graphic { display: grid; gap: 8px; margin: 12px 0 4px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
    .graphic-row { display: grid; grid-template-columns: minmax(90px, .35fr) 1fr auto; gap: 8px; align-items: center; color: #475569; font-size: 11px; }
    .graphic-track { height: 9px; overflow: hidden; border-radius: 99px; background: #dbeafe; }
    .graphic-fill { height: 100%; min-width: 2px; border-radius: inherit; background: #2563eb; }
    .graphic-fill.green { background: #16a34a; }
    .graphic-fill.orange { background: #f97316; }
    .graphic-fill.red { background: #dc2626; }
    .print-only { display: none; }
    @media (max-width: 1000px) { .report-config-row { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 600px) {
        .reports-page { padding: 14px; }
        .report-panel { padding: 16px; }
        .report-config-row { grid-template-columns: 1fr; }
        .report-actions { align-items: stretch; }
        .report-actions button { width: 100%; }
        .report-meta { flex-direction: column; }
    }
    @media print {
        .sidebar, .page-top, .report-builder, .no-print { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .reports-page { padding: 0; }
        .report-panel { border: 0; box-shadow: none; padding: 0; }
        .report-preview { display: block !important; }
        .print-only { display: block; }
    }
</style>
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const purokFilter = document.getElementById('reportPurok');
    const purokValue = document.getElementById('reportPurokValue');
    const subgroupValue = document.getElementById('reportSubgroupValue');

    if (!purokFilter || !purokValue || !subgroupValue) return;

    purokFilter.addEventListener('change', function () {
        const value = this.value;

        purokValue.value = '';
        subgroupValue.value = '';

        if (value.startsWith('purok:')) {
            purokValue.value = value.slice('purok:'.length);
        } else if (value.startsWith('subgroup:')) {
            subgroupValue.value = value.slice('subgroup:'.length);
        }

        const url = new URL(window.location.href);

        url.searchParams.delete('purok');
        url.searchParams.delete('subgroup');
        url.searchParams.delete('page');

        if (purokValue.value) {
            url.searchParams.set('purok', purokValue.value);
        }

        if (subgroupValue.value) {
            url.searchParams.set('subgroup', subgroupValue.value);
        }

        window.location.href = url.toString();
    });
});
</script>
@endsection

@section('content')
<div class="reports-page">
    <div class="page-top no-print">
        <div class="page-title-group">
            <h1>Custom Reports</h1>
            <p>Select the information and filters needed for a printable BHW report.</p>
        </div>
    </div>

    <div class="reports-grid">
        @php
            $selectedReport = in_array(request('report_type'), array_keys($availableSections), true)
                ? request('report_type')
                : ($selectedSections[0] ?? array_key_first($availableSections));
            $reportGroups = [
                'General Reports' => ['citizens', 'health_records', 'supplies', 'events', 'referrals'],
                'Demographic Reports' => ['age_by_purok', 'sex_by_purok', 'pregnancy_by_purok'],
                'Health Reports' => ['immunization', 'diagnosis_summary', 'diagnosis_by_purok'],
            ];
        @endphp
        <form class="report-panel report-builder no-print" method="GET" action="{{ route('bhw.reports') }}" id="reportBuilder">
            <h2>Report Builder</h2>
            <p>Select a report and adjust only the filters that apply to it.</p>

            <div class="report-config-row">
                <div class="report-field">
                    <label for="reportType">Select Report Type</label>
                    <select id="reportType" name="report_type" required>
                        @foreach($reportGroups as $groupLabel => $sectionKeys)
                            @php $groupSections = array_intersect_key(array_flip($sectionKeys), $availableSections); @endphp
                            @if($groupSections)
                                <optgroup label="{{ $groupLabel }}">
                                    @foreach(array_keys($groupSections) as $key)
                                        <option value="{{ $key }}" {{ $selectedReport === $key ? 'selected' : '' }}>{{ $availableSections[$key] }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="report-fields">
                    <div class="report-field" data-reports="citizens,health_records,age_by_purok,sex_by_purok,pregnancy_by_purok,immunization,diagnosis_summary,diagnosis_by_purok,referrals">
                        <label for="reportPurok">Purok</label>
                        <select id="reportPurok" aria-label="Filter by Purok or Subgroup">
                            <option value="">All Puroks</option>

                            @foreach($puroks as $purok)
                                @php
                                    $subgroupCount = $purok->subgroups->count();
                                @endphp

                                <optgroup label="{{ $purok->name }}">
                                    @if($subgroupCount === 0)
                                        <option value="purok:{{ $purok->id }}"
                                            {{ (string) request('purok') === (string) $purok->id && !request()->filled('subgroup') ? 'selected' : '' }}>
                                            All of {{ $purok->name }}
                                        </option>
                                    @elseif($subgroupCount === 1)
                                        @php $subgroup = $purok->subgroups->first(); @endphp
                                        <option value="subgroup:{{ $subgroup->id }}"
                                            {{ (string) request('subgroup') === (string) $subgroup->id ? 'selected' : '' }}>
                                            {{ $subgroup->name }}
                                        </option>
                                    @else
                                        @foreach($purok->subgroups as $subgroup)
                                            <option value="subgroup:{{ $subgroup->id }}"
                                                {{ (string) request('subgroup') === (string) $subgroup->id ? 'selected' : '' }}>
                                                {{ $subgroup->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </optgroup>
                            @endforeach
                        </select>

                        <input type="hidden" name="purok" id="reportPurokValue" value="{{ request('purok') }}">
                        <input type="hidden" name="subgroup" id="reportSubgroupValue" value="{{ request('subgroup') }}">
                    </div>
                    <div class="report-field" data-reports="citizens">
                        <label for="reportSearch">Search citizens</label>
                        <input id="reportSearch" type="search" name="search" value="{{ request('search') }}" placeholder="Name or citizen ID">
                    </div>
                    <div class="report-field" data-reports="citizens">
                        <label for="citizenStatus">Citizen status</label>
                        <select id="citizenStatus" name="citizen_status">
                            <option value="">All statuses</option>
                            <option value="living" {{ request('citizen_status') === 'living' ? 'selected' : '' }}>Living</option>
                            <option value="deceased" {{ request('citizen_status') === 'deceased' ? 'selected' : '' }}>Deceased</option>
                        </select>
                    </div>
                    <div class="report-field" data-reports="citizens,age_by_purok,sex_by_purok,pregnancy_by_purok,immunization">
                        <label for="reportAgeMin">Minimum age</label>
                        <input id="reportAgeMin" type="number" name="age_min" min="0" max="120" value="{{ request('age_min') }}" placeholder="Any">
                    </div>
                    <div class="report-field" data-reports="citizens,age_by_purok,sex_by_purok,pregnancy_by_purok,immunization">
                        <label for="reportAgeMax">Maximum age</label>
                        <input id="reportAgeMax" type="number" name="age_max" min="0" max="120" value="{{ request('age_max') }}" placeholder="Any">
                    </div>
                    <div class="report-field" data-reports="citizens,sex_by_purok">
                        <label for="reportSex">Sex</label>
                        <select id="reportSex" name="sex">
                            <option value="">All</option>
                            <option value="Male" {{ request('sex') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ request('sex') === 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="report-field" data-reports="health_records,events,referrals,diagnosis_summary,diagnosis_by_purok,immunization">
                        <label for="reportStart">Start date</label>
                        <input id="reportStart" type="date" name="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div class="report-field" data-reports="health_records,events,referrals,diagnosis_summary,diagnosis_by_purok,immunization">
                        <label for="reportEnd">End date</label>
                        <input id="reportEnd" type="date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="report-field" data-reports="health_records,diagnosis_summary,diagnosis_by_purok">
                        <label for="reportDiagnosis">Diagnosis</label>
                        <select id="reportDiagnosis" name="diagnosis">
                            <option value="">All diagnoses</option>
                            @foreach($diagnoses as $diagnosis)
                                <option value="{{ $diagnosis }}" {{ request('diagnosis') === $diagnosis ? 'selected' : '' }}>{{ $diagnosis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="report-field" data-reports="supplies">
                        <label for="reportSearch">Search supplies</label>
                        <input id="reportSupplySearch" type="search" name="search" value="{{ request('search') }}" placeholder="Supply name">
                    </div>
                    <div class="report-field" data-reports="supplies">
                        <label for="reportCategory">Item category</label>
                        <select id="reportCategory" name="category">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="report-field" data-reports="supplies">
                        <label for="stockStatus">Stock status</label>
                        <select id="stockStatus" name="stock_status">
                            <option value="">All stock</option>
                            <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of stock</option>
                            <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low stock</option>
                            <option value="available" {{ request('stock_status') === 'available' ? 'selected' : '' }}>Available</option>
                        </select>
                    </div>
                    <div class="report-field" data-reports="events">
                        <label for="eventStatus">Event status</label>
                        <select id="eventStatus" name="event_status">
                            <option value="">All events</option>
                            <option value="upcoming" {{ request('event_status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="past" {{ request('event_status') === 'past' ? 'selected' : '' }}>Past</option>
                        </select>
                    </div>
                    <div class="report-field" data-reports="referrals">
                        <label for="referralStatus">Referral status</label>
                        <select id="referralStatus" name="referral_status">
                            <option value="">All statuses</option>
                            @foreach(['approved', 'released', 'returned'] as $status)
                                <option value="{{ $status }}" {{ request('referral_status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="report-field" data-reports="immunization">
                        <label for="vaccineType">Vaccine type</label>
                        <select id="vaccineType" name="vaccine_type">
                            <option value="">All vaccine types</option>
                            @foreach($vaccineTypes as $vaccineType)
                                <option value="{{ $vaccineType }}" {{ request('vaccine_type') === $vaccineType ? 'selected' : '' }}>{{ $vaccineType }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="report-actions">
                <label class="report-graphics-toggle" data-reports="age_by_purok,immunization,sex_by_purok,pregnancy_by_purok,diagnosis_summary">
                    <input type="checkbox" name="include_graphics" value="1" {{ $includeGraphics ? 'checked' : '' }}>
                    Include graphic summaries
                </label>
                <button type="submit" class="btn-primary">Generate Report</button>
                <a class="btn-secondary" href="{{ route('bhw.reports', ['report_type' => $selectedReport]) }}">Reset Filters</a>
                <button type="button" class="btn-secondary" onclick="window.print()">Export / Print Report</button>
            </div>
        </form>

        <main class="report-panel report-preview" id="printableReport">
            <div class="report-meta">
                <div>
                    <h1>Barangay Health Report</h1>
                    <p>Generated {{ now()->format('F d, Y h:i A') }}</p>
                </div>
                <span class="print-only">BHW Operations Report</span>
            </div>

            @if(in_array('citizens', $selectedSections))
                <section class="report-section">
                    <h3>Citizen Profiles ({{ $citizensReport->count() }})</h3>
                    @if($citizensReport->isEmpty()) <div class="report-empty">No citizens match the selected filters.</div>
                    @else <table class="report-table"><thead><tr><th>Name</th><th>Age</th><th>Purok</th><th>Family / Subgroup</th></tr></thead><tbody>
                        @foreach($citizensReport as $citizen)
                            <tr><td>{{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}<br><small>ID: C-{{ str_pad($citizen->id, 4, '0', STR_PAD_LEFT) }}</small></td><td>{{ $citizen->Citizen_Age }}</td><td>{{ $citizen->Citizen_Purok }}</td><td>{{ $citizen->family->family_name ?? 'No family' }} / {{ $citizen->subgroup->name ?? $citizen->family?->subgroup?->name ?? 'Not assigned' }}</td></tr>
                        @endforeach
                    </tbody></table>@endif
                </section>
            @endif

            @if(in_array('age_by_purok', $selectedSections))
                <section class="report-section"><h3>Age-Bracket Breakdown per Purok</h3><table class="report-table"><thead><tr><th>Purok</th><th>Infants</th><th>Toddlers</th><th>Children</th><th>Teenagers</th><th>Working adults</th><th>Seniors</th></tr></thead><tbody>@forelse($ageByPurok as $purok => $ages)<tr><td>{{ $purok ?: 'Unassigned' }}</td><td>{{ $ages['infants'] }}</td><td>{{ $ages['toddlers'] }}</td><td>{{ $ages['children'] }}</td><td>{{ $ages['teenagers'] }}</td><td>{{ $ages['working_adults'] }}</td><td>{{ $ages['seniors'] }}</td></tr>@empty<tr><td colspan="7" class="report-empty">No age data matches the selected filters.</td></tr>@endforelse</tbody></table>@if($includeGraphics)<div class="report-graphic">@foreach($ageByPurok as $purok => $ages) @php $total = array_sum($ages); $max = max(1, max($ageByPurok->map(fn ($row) => array_sum($row))->all())); @endphp<div class="graphic-row"><span>{{ $purok ?: 'Unassigned' }}</span><div class="graphic-track"><div class="graphic-fill" style="width:{{ ($total / $max) * 100 }}%"></div></div><strong>{{ $total }}</strong></div>@endforeach</div>@endif</section>
            @endif

            @if(in_array('immunization', $selectedSections))
                <section class="report-section"><h3>Immunization and Vaccination Coverage</h3><table class="report-table"><thead><tr><th>Children age 0-5</th><th>{{ request('vaccine_type') ? 'Received selected vaccine' : 'Fully immunized' }}</th><th>{{ request('vaccine_type') ? 'Not recorded for selected vaccine' : 'Missing milestones' }}</th></tr></thead><tbody><tr><td>{{ $immunizationSummary['total'] }}</td><td>{{ $immunizationSummary['fully_immunized'] }}</td><td>{{ $immunizationSummary['missing_milestones'] }}</td></tr></tbody></table>@if($includeGraphics)<div class="report-graphic"><div class="graphic-row"><span>Coverage</span><div class="graphic-track"><div class="graphic-fill green" style="width:{{ $immunizationSummary['total'] ? ($immunizationSummary['fully_immunized'] / $immunizationSummary['total']) * 100 : 0 }}%"></div></div><strong>{{ $immunizationSummary['total'] ? round(($immunizationSummary['fully_immunized'] / $immunizationSummary['total']) * 100) : 0 }}%</strong></div></div>@endif<p style="margin-top:8px;color:#64748b;font-size:12px;">{{ request('vaccine_type') ? 'Coverage is based on the selected vaccine and coverage period.' : 'Milestones checked: BCG, oral polio/polio, and measles/MMR records.' }}</p></section>
            @endif

            @if(in_array('sex_by_purok', $selectedSections))
                <section class="report-section"><h3>Male and Female Counts by Purok</h3><table class="report-table"><thead><tr><th>Purok</th><th>Male</th><th>Female</th><th>Other / not recorded</th></tr></thead><tbody>@forelse($sexByPurok as $purok => $counts)<tr><td>{{ $purok ?: 'Unassigned' }}</td><td>{{ $counts['male'] }}</td><td>{{ $counts['female'] }}</td><td>{{ $counts['other'] }}</td></tr>@empty<tr><td colspan="4" class="report-empty">No sex-by-Purok data matches the selected filters.</td></tr>@endforelse</tbody></table>@if($includeGraphics)<div class="report-graphic">@foreach($sexByPurok as $purok => $counts) @php $max = max(1, max($counts)); @endphp<div class="graphic-row"><span>{{ $purok ?: 'Unassigned' }} male</span><div class="graphic-track"><div class="graphic-fill" style="width:{{ ($counts['male'] / $max) * 100 }}%"></div></div><strong>{{ $counts['male'] }}</strong></div><div class="graphic-row"><span>{{ $purok ?: 'Unassigned' }} female</span><div class="graphic-track"><div class="graphic-fill orange" style="width:{{ ($counts['female'] / $max) * 100 }}%"></div></div><strong>{{ $counts['female'] }}</strong></div>@endforeach</div>@endif</section>
            @endif

            @if(in_array('pregnancy_by_purok', $selectedSections))
                <section class="report-section"><h3>Recorded Pregnancy by Purok</h3><table class="report-table"><thead><tr><th>Purok</th><th>Recorded pregnant residents</th></tr></thead><tbody>@forelse($pregnancyByPurok as $purok => $count)<tr><td>{{ $purok ?: 'Unassigned' }}</td><td>{{ $count }}</td></tr>@empty<tr><td colspan="2" class="report-empty">No pregnancy data matches the selected filters.</td></tr>@endforelse</tbody></table>@if($includeGraphics) @php $maxPregnancy = max(1, $pregnancyByPurok->max()); @endphp<div class="report-graphic">@foreach($pregnancyByPurok as $purok => $count)<div class="graphic-row"><span>{{ $purok ?: 'Unassigned' }}</span><div class="graphic-track"><div class="graphic-fill red" style="width:{{ ($count / $maxPregnancy) * 100 }}%"></div></div><strong>{{ $count }}</strong></div>@endforeach</div>@endif</section>
            @endif

            @if(in_array('diagnosis_summary', $selectedSections))
                <section class="report-section"><h3>Diagnosis Summary</h3><table class="report-table"><thead><tr><th>Diagnosis</th><th>Recorded cases</th></tr></thead><tbody>@forelse($diagnosisSummary as $diagnosis => $count)<tr><td>{{ $diagnosis }}</td><td>{{ $count }}</td></tr>@empty<tr><td colspan="2" class="report-empty">No diagnosis data matches the selected dates and citizens.</td></tr>@endforelse</tbody></table>@if($includeGraphics) @php $maxDiagnosis = max(1, $diagnosisSummary->max()); @endphp<div class="report-graphic">@foreach($diagnosisSummary as $diagnosis => $count)<div class="graphic-row"><span>{{ $diagnosis }}</span><div class="graphic-track"><div class="graphic-fill orange" style="width:{{ ($count / $maxDiagnosis) * 100 }}%"></div></div><strong>{{ $count }}</strong></div>@endforeach</div>@endif</section>
            @endif

            @if(in_array('diagnosis_by_purok', $selectedSections))
                <section class="report-section"><h3>Diagnoses by Purok</h3><table class="report-table"><thead><tr><th>Purok</th><th>Diagnosis</th><th>Cases</th></tr></thead><tbody>@forelse($diagnosisByPurok as $key => $count) @php [$purok, $diagnosis] = explode('|', $key, 2); @endphp<tr><td>{{ $purok }}</td><td>{{ $diagnosis }}</td><td>{{ $count }}</td></tr>@empty<tr><td colspan="3" class="report-empty">No diagnosis-by-Purok data matches the selected dates and citizens.</td></tr>@endforelse</tbody></table></section>
            @endif

            @if(in_array('health_records', $selectedSections))
                <section class="report-section"><h3>Health Records ({{ $healthRecords->count() }})</h3>
                    @if($healthRecords->isEmpty()) <div class="report-empty">No health records match the selected filters.</div>
                    @else <table class="report-table"><thead><tr><th>Date</th><th>Citizen</th><th>Diagnosis</th><th>Notes</th></tr></thead><tbody>@foreach($healthRecords as $record)<tr><td>{{ optional($record->created_at)->format('M d, Y') }}</td><td>{{ $record->citizen->Citizen_FName ?? '' }} {{ $record->citizen->Citizen_LName ?? '' }}</td><td>{{ $record->diagnosis }}</td><td>{{ $record->notes ?? '—' }}</td></tr>@endforeach</tbody></table>@endif
                </section>
            @endif

            @if(in_array('supplies', $selectedSections))
                <section class="report-section"><h3>Supply Inventory ({{ $supplies->count() }})</h3><table class="report-table"><thead><tr><th>Supply</th><th>Category</th><th>Quantity</th><th>Minimum</th></tr></thead><tbody>@forelse($supplies as $supply)<tr><td>{{ $supply->name }}</td><td>{{ $supply->category ?? 'Uncategorized' }}</td><td>{{ $supply->quantity }}</td><td>{{ $supply->min_stock }}</td></tr>@empty<tr><td colspan="4" class="report-empty">No supplies match the selected filters.</td></tr>@endforelse</tbody></table></section>
            @endif

            @if(in_array('events', $selectedSections))
                <section class="report-section"><h3>Events and Schedules ({{ $events->count() }})</h3><table class="report-table"><thead><tr><th>Date</th><th>Event</th><th>Description</th></tr></thead><tbody>@forelse($events as $event)<tr><td>{{ $event->start }}</td><td>{{ $event->title }}</td><td>{{ $event->description ?? '—' }}</td></tr>@empty<tr><td colspan="3" class="report-empty">No events match the selected dates.</td></tr>@endforelse</tbody></table></section>
            @endif

            @if(in_array('referrals', $selectedSections))
                <section class="report-section"><h3>Referrals ({{ $referrals->count() }})</h3><table class="report-table"><thead><tr><th>Date</th><th>Name</th><th>Request</th><th>Status</th></tr></thead><tbody>@forelse($referrals as $referral)<tr><td>{{ $referral->date_of_referral }}</td><td>{{ $referral->name }}</td><td>{{ $referral->requests_for }}</td><td>{{ ucfirst($referral->status ?? 'pending') }}</td></tr>@empty<tr><td colspan="4" class="report-empty">No referrals match the selected dates.</td></tr>@endforelse</tbody></table></section>@endif
        </main>
    </div>
</div>
<script>
    (() => {
        const reportType = document.getElementById('reportType');
        const filterFields = document.querySelectorAll('.report-field[data-reports]');
        const graphicsToggle = document.querySelector('.report-graphics-toggle');

        const updateReportFields = () => {
            filterFields.forEach((field) => {
                const isRelevant = field.dataset.reports.split(',').includes(reportType.value);
                field.hidden = !isRelevant;
                field.querySelectorAll('input, select').forEach((input) => {
                    input.disabled = !isRelevant;
                });
            });
            const graphicsRelevant = graphicsToggle.dataset.reports.split(',').includes(reportType.value);
            graphicsToggle.hidden = !graphicsRelevant;
            graphicsToggle.querySelector('input').disabled = !graphicsRelevant;
        };

        reportType.addEventListener('change', updateReportFields);
        updateReportFields();
    })();
</script>
@endsection
