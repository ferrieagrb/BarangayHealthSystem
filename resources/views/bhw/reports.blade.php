@extends('templates.layout')

@section('CSSown')
<style>
    .reports-page { padding: 24px; color: #172033; }
    .reports-grid { display: grid; grid-template-columns: minmax(280px, .8fr) minmax(0, 2fr); gap: 18px; align-items: start; }
    .report-panel { padding: 22px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; box-shadow: 0 3px 12px rgba(15,23,42,.06); }
    .report-panel h2 { margin: 0 0 6px; color: #173b82; font-size: 20px; }
    .report-panel > p { margin: 0 0 18px; color: #64748b; font-size: 13px; }
    .report-options { display: grid; gap: 9px; margin-bottom: 20px; }
    .report-option { display: flex; align-items: center; gap: 9px; padding: 10px; border: 1px solid #dbe3ef; border-radius: 7px; cursor: pointer; }
    .report-option:has(input:checked) { border-color: #2563eb; background: #eff6ff; }
    .report-field { display: grid; gap: 6px; margin-bottom: 13px; }
    .report-field label { color: #334155; font-size: 12px; font-weight: 700; }
    .report-field input, .report-field select { width: 100%; min-height: 40px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
    .report-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 18px; }
    .report-actions button { border: 0; cursor: pointer; }
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
    @media (max-width: 900px) { .reports-grid { grid-template-columns: 1fr; } }
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

@section('content')
<div class="reports-page">
    <div class="page-top no-print">
        <div class="page-title-group">
            <h1>Custom Reports</h1>
            <p>Select the information and filters needed for a printable BHW report.</p>
        </div>
    </div>

    <div class="reports-grid">
        <form class="report-panel report-builder no-print" method="GET" action="{{ route('bhw.reports') }}">
            <h2>Report Builder</h2>
            <p>Choose one or more sections, then refine the report.</p>

            <div class="report-options">
                @foreach($availableSections as $key => $label)
                    <label class="report-option">
                        <input type="checkbox" name="sections[]" value="{{ $key }}" {{ in_array($key, $selectedSections) ? 'checked' : '' }}>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <label class="report-option">
                <input type="checkbox" name="include_graphics" value="1" {{ $includeGraphics ? 'checked' : '' }}>
                <span>Include graphic summaries</span>
            </label>

            <div class="report-field">
                <label for="reportSearch">Citizen or supply search</label>
                <input id="reportSearch" type="search" name="search" value="{{ request('search') }}" placeholder="Name, ID, or supply name">
            </div>
            <div class="report-field">
                <label for="reportPurok">Purok</label>
                <select id="reportPurok" name="purok">
                    <option value="">All Puroks</option>
                    @foreach($puroks as $purok)
                        <option value="{{ $purok->name }}" {{ request('purok') === $purok->name ? 'selected' : '' }}>{{ $purok->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="report-field">
                <label for="reportCategory">Supply category</label>
                <select id="reportCategory" name="category">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="report-field">
                <label for="reportStart">Start date</label>
                <input id="reportStart" type="date" name="start_date" value="{{ request('start_date') }}">
            </div>
            <div class="report-field">
                <label for="reportEnd">End date</label>
                <input id="reportEnd" type="date" name="end_date" value="{{ request('end_date') }}">
            </div>
            <div class="report-actions">
                <button type="submit" class="btn-primary">Generate Preview</button>
                <button type="button" class="btn-secondary" onclick="window.print()">Print Report</button>
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
                <section class="report-section"><h3>Immunization and Vaccination Coverage</h3><table class="report-table"><thead><tr><th>Children age 0-5</th><th>Fully immunized</th><th>Missing milestones</th></tr></thead><tbody><tr><td>{{ $immunizationSummary['total'] }}</td><td>{{ $immunizationSummary['fully_immunized'] }}</td><td>{{ $immunizationSummary['missing_milestones'] }}</td></tr></tbody></table>@if($includeGraphics)<div class="report-graphic"><div class="graphic-row"><span>Coverage</span><div class="graphic-track"><div class="graphic-fill green" style="width:{{ $immunizationSummary['total'] ? ($immunizationSummary['fully_immunized'] / $immunizationSummary['total']) * 100 : 0 }}%"></div></div><strong>{{ $immunizationSummary['total'] ? round(($immunizationSummary['fully_immunized'] / $immunizationSummary['total']) * 100) : 0 }}%</strong></div></div>@endif<p style="margin-top:8px;color:#64748b;font-size:12px;">Milestones checked: BCG, oral polio/polio, and measles/MMR records.</p></section>
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
@endsection
