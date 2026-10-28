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
    .report-validation-errors { margin-bottom: 14px; padding: 12px 14px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #991b1b; }
    .report-export-feedback { flex-basis: 100%; margin: 0; color: #991b1b; font-size: 13px; }
    .report-export-feedback[hidden] { display: none; }
    .report-graphic { display: grid; gap: 8px; margin: 12px 0 4px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
    .report-table-scroll { width: 100%; overflow-x: auto; }
    .report-details-table { min-width: 1500px; }
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
        @page { size: landscape; margin: 14mm; }
        html, body, .dashboard, .main-content { height: auto !important; min-height: 0 !important; overflow: visible !important; }
        .dashboard { display: block !important; }
        .sidebar, .page-top, .report-builder, .no-print { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; max-height: none !important; }
        .reports-page, .reports-grid, #printableReport, .report-section { height: auto !important; max-height: none !important; overflow: visible !important; }
        .reports-page { padding: 0; }
        .reports-grid { display: block !important; }
        .report-panel { border: 0; box-shadow: none; padding: 0; }
        .report-preview { display: block !important; }
        .report-table { width: 100%; page-break-inside: auto; }
        .report-table-scroll { width: 100%; overflow: visible !important; }
        .report-details-table { min-width: 0; font-size: 8px; }
        .report-table thead { display: table-header-group; }
        .report-table tr { break-inside: avoid; page-break-inside: avoid; }
        .report-section { break-inside: auto; page-break-inside: auto; }
        .report-graphic { break-inside: avoid; page-break-inside: avoid; }
        .print-only { display: block; }
    }
</style>
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reportForm = document.getElementById('reportBuilder');
    const excelButton = reportForm?.querySelector('button[name="export_format"][value="xlsx"]');
    const exportFeedback = document.getElementById('reportExportFeedback');
    let exportInProgress = false;

    reportForm?.addEventListener('submit', async function (event) {
        if (event.submitter !== excelButton || !excelButton || exportInProgress) return;

        event.preventDefault();
        exportInProgress = true;
        exportFeedback.hidden = true;
        exportFeedback.textContent = '';
        excelButton.disabled = true;
        excelButton.setAttribute('aria-busy', 'true');
        const originalLabel = excelButton.textContent;
        excelButton.textContent = 'Preparing Excel...';

        try {
            const requestUrl = new URL(reportForm.action, window.location.href);
            const query = new URLSearchParams(new FormData(reportForm));
            query.set('export_format', 'xlsx');
            requestUrl.search = query.toString();

            const response = await fetch(requestUrl, {
                credentials: 'same-origin',
                headers: { Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }
            });
            const contentType = response.headers.get('Content-Type') || '';
            if (!response.ok || !contentType.includes('spreadsheetml')) {
                const responseText = await response.text();
                const errorDocument = new DOMParser().parseFromString(responseText, 'text/html');
                const validationMessage = errorDocument.querySelector('.report-validation-errors, .alert-danger, .errors')?.textContent.trim();
                throw new Error(response.redirected
                    ? 'Your session may have expired. Reload the page and sign in before exporting.'
                    : validationMessage || 'The server did not return an Excel workbook. Check the report filters and try again.');
            }

            const filenameMatch = response.headers.get('Content-Disposition')?.match(/filename\*=UTF-8''([^;]+)|filename="?([^";]+)"?/i);
            const filename = filenameMatch
                ? decodeURIComponent(filenameMatch[1] || filenameMatch[2])
                : 'custom-report.xlsx';
            const fileUrl = URL.createObjectURL(await response.blob());
            const downloadLink = document.createElement('a');
            downloadLink.href = fileUrl;
            downloadLink.download = filename;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            downloadLink.remove();
            window.setTimeout(() => URL.revokeObjectURL(fileUrl), 60000);
        } catch (error) {
            exportFeedback.textContent = error instanceof Error ? error.message : 'Excel export failed. Please try again.';
            exportFeedback.hidden = false;
        } finally {
            exportInProgress = false;
            excelButton.disabled = false;
            excelButton.removeAttribute('aria-busy');
            excelButton.textContent = originalLabel;
        }
    });

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
            @if($errors->any())
                <div class="report-validation-errors" role="alert">
                    <strong>Please correct the report filters:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
                                <optgroup label="{{ $purok->name }}">
                                    <option value="purok:{{ $purok->id }}"
                                        {{ (string) request('purok') === (string) $purok->id && !request()->filled('subgroup') ? 'selected' : '' }}>
                                        All of {{ $purok->name }}
                                    </option>
                                    @foreach($purok->subgroups as $subgroup)
                                        <option value="subgroup:{{ $subgroup->id }}"
                                            {{ (string) request('subgroup') === (string) $subgroup->id ? 'selected' : '' }}>
                                            {{ $subgroup->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        <input type="hidden" name="purok" id="reportPurokValue" value="{{ request('purok') }}">
                        <input type="hidden" name="subgroup" id="reportSubgroupValue" value="{{ request('subgroup') }}">
                    </div>
                    <div class="report-field" data-reports="citizens,health_records,immunization,diagnosis_summary,diagnosis_by_purok">
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
                    <div class="report-field" data-reports="citizens">
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
                        <label for="reportSupplySearch">Search supplies</label>
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
                <button type="submit" class="btn-secondary" name="export_format" value="xlsx" data-no-loader="true">Export to Excel</button>
                <button type="button" class="btn-secondary" onclick="window.print()">Export / Print PDF</button>
                <p id="reportExportFeedback" class="report-export-feedback" role="alert" hidden></p>
            </div>
        </form>

        <main class="report-panel report-preview" id="printableReport" data-no-motion>
            <div class="report-meta">
                <div>
                    <h1>Barangay Health Report</h1>
                    <p>Generated {{ now()->format('F d, Y h:i A') }}</p>
                    <p>Filters: {{ $reportFilterSummary ?: 'None' }}</p>
                </div>
                <span class="print-only">BHW Operations Report</span>
            </div>

            @if(in_array('citizens', $selectedSections))
                <section class="report-section">
                    <h3>Citizen Profiles ({{ $citizensReport->count() }})</h3>
                    @if($citizensReport->isEmpty()) <div class="report-empty">No citizens match the selected filters.</div>
                    @else <table class="report-table"><thead><tr><th>Name</th><th>Age</th><th>Sex</th><th>Purok</th><th>Family / Subgroup</th></tr></thead><tbody>
                        @foreach($citizensReport as $citizen)
                            <tr><td>{{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}<br><small>ID: C-{{ str_pad($citizen->id, 4, '0', STR_PAD_LEFT) }}</small></td><td>{{ $citizen->Citizen_Age }}</td><td>{{ $citizen->Citizen_Sex ?: 'Not recorded' }}</td><td>{{ $citizen->subgroup?->purok?->name ?? $citizen->family?->subgroup?->purok?->name ?? $citizen->family?->purok?->name ?? ($citizen->Citizen_Purok ?: 'Unassigned') }}</td><td>{{ $citizen->family->family_name ?? 'No family' }} / {{ $citizen->subgroup->name ?? $citizen->family?->subgroup?->name ?? 'Not assigned' }}</td></tr>
                        @endforeach
                    </tbody></table>@endif
                </section>
            @endif

            @if(in_array('age_by_purok', $selectedSections))
                <section class="report-section">
                    <h3>Age-Bracket Breakdown per Purok</h3>
                    <table class="report-table">
                        <thead><tr><th>Purok</th><th>Infants</th><th>Toddlers</th><th>Children</th><th>Teenagers</th><th>Working adults</th><th>Seniors</th><th>Unknown age</th></tr></thead>
                        <tbody>
                            @forelse($ageByPurok as $purok => $ages)
                                <tr><td>{{ $purok }}</td><td>{{ $ages['infants'] }}</td><td>{{ $ages['toddlers'] }}</td><td>{{ $ages['children'] }}</td><td>{{ $ages['teenagers'] }}</td><td>{{ $ages['working_adults'] }}</td><td>{{ $ages['seniors'] }}</td><td>{{ $ages['unknown_age'] }}</td></tr>
                            @empty
                                <tr><td colspan="8" class="report-empty">No age data matches the selected filters.</td></tr>
                            @endforelse
                            <tr><th>Total</th><th>{{ $ageTotals['infants'] }}</th><th>{{ $ageTotals['toddlers'] }}</th><th>{{ $ageTotals['children'] }}</th><th>{{ $ageTotals['teenagers'] }}</th><th>{{ $ageTotals['working_adults'] }}</th><th>{{ $ageTotals['seniors'] }}</th><th>{{ $ageTotals['unknown_age'] }}</th></tr>
                        </tbody>
                    </table>
                    @if($includeGraphics && $ageByPurok->isNotEmpty())
                        <div class="report-graphic">
                            @php $maxAgeTotal = max(1, $ageByPurok->map(fn ($row) => array_sum($row))->max()); @endphp
                            @foreach($ageByPurok as $purok => $ages)
                                @php $total = array_sum($ages); @endphp
                                <div class="graphic-row"><span>{{ $purok }}</span><div class="graphic-track"><div class="graphic-fill" style="width:{{ ($total / $maxAgeTotal) * 100 }}%"></div></div><strong>{{ $total }}</strong></div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if(in_array('immunization', $selectedSections))
                <section class="report-section">
                    <h3>Immunization and Vaccination Coverage</h3>
                    <table class="report-table">
                        <thead><tr><th>Eligible children age 0-5</th><th>{{ request('vaccine_type') ? 'Children with selected vaccine recorded' : 'Children with BCG, polio, and measles groups recorded' }}</th><th>{{ request('vaccine_type') ? 'No selected vaccine record' : 'Missing one or more core vaccine groups' }}</th></tr></thead>
                        <tbody><tr><td>{{ $immunizationSummary['total'] }}</td><td>{{ $immunizationSummary['vaccinated'] }}</td><td>{{ $immunizationSummary['missing'] }}</td></tr></tbody>
                    </table>
                    @if($includeGraphics)
                        <div class="report-graphic"><div class="graphic-row"><span>Coverage</span><div class="graphic-track"><div class="graphic-fill green" style="width:{{ $immunizationSummary['total'] ? ($immunizationSummary['vaccinated'] / $immunizationSummary['total']) * 100 : 0 }}%"></div></div><strong>{{ $immunizationSummary['total'] ? round(($immunizationSummary['vaccinated'] / $immunizationSummary['total']) * 100) : 0 }}%</strong></div></div>
                    @endif
                    <p style="margin-top:8px;color:#64748b;font-size:12px;">{{ request('vaccine_type') ? 'Counts eligible children with a matching administered record within the selected date range. Vaccination details below include all ages in the selected citizen scope.' : 'This is a count of the three named vaccine groups recorded in the selected date range; it does not determine completion of a vaccine-specific dose schedule.' }}</p>

                    <h3>Vaccination details ({{ $vaccinationDoseSummary->count() }} citizen/vaccine records)</h3>
                    @if($vaccinationDoseSummary->isEmpty())
                        <div class="report-empty">No vaccination records or scheduled doses match the selected filters.</div>
                    @else
                        <div class="report-table-scroll">
                            <table class="report-table report-details-table">
                            <thead><tr><th>Citizen ID</th><th>Citizen name</th><th>Age</th><th>Sex</th><th>Purok</th><th>Subgroup</th><th>Vaccine</th><th>Dose number(s)</th><th>Total administered doses</th><th>Latest vaccination date</th><th>Vaccination status</th><th>Next dose due date</th></tr></thead>
                            <tbody>
                                @foreach($vaccinationDoseSummary as $record)
                                    <tr>
                                        <td>{{ $record['citizen_id_label'] }}</td>
                                        <td>{{ $record['citizen_name'] }}</td>
                                        <td>{{ $record['age'] }}</td>
                                        <td>{{ $record['sex'] }}</td>
                                        <td>{{ $record['purok'] }}</td>
                                        <td>{{ $record['subgroup'] }}</td>
                                        <td>{{ $record['vaccine_name'] }}</td>
                                        <td>{{ $record['dose_numbers'] ?: 'Not recorded' }}</td>
                                        <td>{{ $record['administered_doses'] }}</td>
                                        <td>{{ $record['latest_vaccination_date'] ? \Illuminate\Support\Carbon::parse($record['latest_vaccination_date'])->format('M d, Y') : 'Not recorded' }}</td>
                                        <td>{{ $record['vaccination_status'] }}</td>
                                        <td>{{ $record['next_dose_due_date'] ? \Illuminate\Support\Carbon::parse($record['next_dose_due_date'])->format('M d, Y') : 'Not recorded' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                        <p style="margin-top:8px;color:#64748b;font-size:12px;">Status reflects recorded vaccination entries and active schedules. No active scheduled dose does not establish completion of a vaccine series.</p>
                    @endif
                </section>
            @endif

            @if(in_array('sex_by_purok', $selectedSections))
                <section class="report-section">
                    <h3>Male and Female Counts by Purok</h3>
                    <table class="report-table">
                        <thead><tr><th>Purok</th><th>Male</th><th>Female</th><th>Other / not recorded</th></tr></thead>
                        <tbody>
                            @forelse($sexByPurok as $purok => $counts)
                                <tr><td>{{ $purok }}</td><td>{{ $counts['male'] }}</td><td>{{ $counts['female'] }}</td><td>{{ $counts['other'] }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="report-empty">No sex-by-Purok data matches the selected filters.</td></tr>
                            @endforelse
                            <tr><th>Total</th><th>{{ $sexTotals['male'] }}</th><th>{{ $sexTotals['female'] }}</th><th>{{ $sexTotals['other'] }}</th></tr>
                        </tbody>
                    </table>
                    @if($includeGraphics && $sexByPurok->isNotEmpty())
                        <div class="report-graphic">
                            @foreach($sexByPurok as $purok => $counts)
                                @php $max = max(1, max($counts)); @endphp
                                <div class="graphic-row"><span>{{ $purok }} male</span><div class="graphic-track"><div class="graphic-fill" style="width:{{ ($counts['male'] / $max) * 100 }}%"></div></div><strong>{{ $counts['male'] }}</strong></div>
                                <div class="graphic-row"><span>{{ $purok }} female</span><div class="graphic-track"><div class="graphic-fill orange" style="width:{{ ($counts['female'] / $max) * 100 }}%"></div></div><strong>{{ $counts['female'] }}</strong></div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if(in_array('pregnancy_by_purok', $selectedSections))
                <section class="report-section">
                    <h3>Recorded Pregnancy by Purok</h3>
                    <table class="report-table">
                        <thead><tr><th>Purok</th><th>Recorded pregnant residents</th></tr></thead>
                        <tbody>
                            @forelse($pregnancyByPurok as $purok => $count)
                                <tr><td>{{ $purok }}</td><td>{{ $count }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="report-empty">No pregnancy data matches the selected filters.</td></tr>
                            @endforelse
                            <tr><th>Total</th><th>{{ $pregnancyTotal }}</th></tr>
                        </tbody>
                    </table>
                    @if($includeGraphics && $pregnancyByPurok->isNotEmpty())
                        @php $maxPregnancy = max(1, $pregnancyByPurok->max()); @endphp
                        <div class="report-graphic">
                            @foreach($pregnancyByPurok as $purok => $count)
                                <div class="graphic-row"><span>{{ $purok }}</span><div class="graphic-track"><div class="graphic-fill red" style="width:{{ ($count / $maxPregnancy) * 100 }}%"></div></div><strong>{{ $count }}</strong></div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if(in_array('diagnosis_summary', $selectedSections))
                <section class="report-section"><h3>Diagnosis Summary</h3><table class="report-table"><thead><tr><th>Diagnosis</th><th>Recorded cases</th></tr></thead><tbody>@forelse($diagnosisSummary as $diagnosis => $count)<tr><td>{{ $diagnosis }}</td><td>{{ $count }}</td></tr>@empty<tr><td colspan="2" class="report-empty">No diagnosis data matches the selected dates and citizens.</td></tr>@endforelse</tbody></table>@if($includeGraphics) @php $maxDiagnosis = max(1, $diagnosisSummary->max()); @endphp<div class="report-graphic">@foreach($diagnosisSummary as $diagnosis => $count)<div class="graphic-row"><span>{{ $diagnosis }}</span><div class="graphic-track"><div class="graphic-fill orange" style="width:{{ ($count / $maxDiagnosis) * 100 }}%"></div></div><strong>{{ $count }}</strong></div>@endforeach</div>@endif</section>
            @endif

            @if(in_array('diagnosis_by_purok', $selectedSections))
                <section class="report-section"><h3>Diagnoses by Purok</h3><table class="report-table"><thead><tr><th>Purok</th><th>Diagnosis</th><th>Cases</th></tr></thead><tbody>@forelse($diagnosisByPurok as $key => $count) @php [$purok, $diagnosis] = explode('|', $key, 2); @endphp<tr><td>{{ $purok }}</td><td>{{ $diagnosis }}</td><td>{{ $count }}</td></tr>@empty<tr><td colspan="3" class="report-empty">No diagnosis-by-Purok data matches the selected dates and citizens.</td></tr>@endforelse</tbody></table></section>
            @endif

            @if(in_array('health_records', $selectedSections) || in_array('diagnosis_summary', $selectedSections) || in_array('diagnosis_by_purok', $selectedSections))
                <section class="report-section">
                        <h3>{{ in_array('health_records', $selectedSections) ? 'Health Record Details' : 'Diagnosis History Details' }} ({{ $healthRecordDetails->count() }} records)</h3>
                        @if($healthRecordDetails->isEmpty())
                            <div class="report-empty">No health records match the selected citizens, diagnosis, and date range.</div>
                        @else
                            <div class="report-table-scroll">
                                <table class="report-table report-details-table">
                                    <thead><tr><th>Citizen name</th><th>Age</th><th>Sex</th><th>Purok</th><th>Diagnosis</th><th>Consultation date</th><th>Blood pressure</th><th>Weight kg</th><th>Height cm</th><th>BMI</th><th>Temperature °C</th></tr></thead>
                                    <tbody>
                                        @foreach($healthRecordDetails as $record)
                                            <tr>
                                                <td>{{ $record['citizen_name'] }}</td>
                                                <td>{{ $record['age'] }}</td>
                                                <td>{{ $record['sex'] }}</td>
                                                <td>{{ $record['purok'] }}</td>
                                                <td>{{ $record['diagnosis'] }}</td>
                                                <td>{{ $record['date'] !== 'Not recorded' ? \Illuminate\Support\Carbon::parse($record['date'])->format('M d, Y') : $record['date'] }}</td>
                                                <td>{{ $record['blood_pressure'] }}</td>
                                                <td>{{ $record['weight'] }}</td>
                                                <td>{{ $record['height'] }}</td>
                                                <td>{{ $record['bmi'] }}</td>
                                                <td>{{ $record['temperature'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                </section>
            @endif

            @if(in_array('health_records', $selectedSections))
                <section class="report-section">
                    <h3>Medication and Maintenance History ({{ $medicationHistory->count() }} records)</h3>
                    @if($medicationHistory->isEmpty())
                        <div class="report-empty">No medication or maintenance medicine records match the selected citizens and date range.</div>
                    @else
                        <div class="report-table-scroll">
                            <table class="report-table report-details-table">
                                <thead><tr><th>Citizen ID</th><th>Citizen</th><th>Date</th><th>Medicine</th><th>Dosage</th><th>Quantity</th><th>Frequency</th><th>End date</th><th>Recorded by</th><th>Record type</th></tr></thead>
                                <tbody>
                                    @foreach($medicationHistory as $record)
                                        <tr><td>C-{{ str_pad($record['citizen_id'], 4, '0', STR_PAD_LEFT) }}</td><td>{{ $record['citizen_name'] }}</td><td>{{ $record['date'] }}</td><td>{{ $record['medicine'] }}</td><td>{{ $record['dosage'] }}</td><td>{{ $record['quantity'] }}</td><td>{{ $record['frequency'] }}</td><td>{{ $record['end_date'] }}</td><td>{{ $record['recorded_by'] }}</td><td>{{ $record['source'] }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
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

        reportType.addEventListener('change', () => {
            updateReportFields();
            document.querySelectorAll('.report-field[data-reports][hidden] input, .report-field[data-reports][hidden] select')
                .forEach((input) => {
                    if (input.type === 'checkbox') input.checked = false;
                    else input.value = '';
                });
            document.querySelectorAll('input[name="search"]').forEach((input) => {
                input.value = '';
            });
        });
        updateReportFields();
    })();
</script>
@endsection
