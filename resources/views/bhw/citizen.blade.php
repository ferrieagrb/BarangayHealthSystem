@extends('templates.layout')

@section('CSSown')

    <link rel="stylesheet" href="{{ asset('css/bhw/citizen.css') }}">

<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
<<<<<<< HEAD
=======
    <style>
        .citizen-password-modal { display: none; position: fixed; inset: 0; z-index: 10000; align-items: center; justify-content: center; padding: 20px; background: rgba(15, 23, 42, .55); }
        .citizen-password-modal.is-open { display: flex; }
        .citizen-password-dialog { width: min(100%, 420px); padding: 24px; border-radius: 12px; background: #fff; box-shadow: 0 18px 50px rgba(15, 23, 42, .24); }
        .citizen-password-dialog h2 { margin: 0 0 8px; color: #173b82; font-size: 20px; }
        .citizen-password-dialog p { margin: 0 0 16px; color: #64748b; font-size: 13px; line-height: 1.5; }
        .citizen-password-dialog label { display: block; margin: 12px 0 6px; color: #334155; font-size: 12px; font-weight: 700; }
        .citizen-password-dialog input { width: 100%; min-height: 42px; padding: 9px 11px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        .citizen-password-dialog-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; }
        .citizen-export-modal { display: none; position: fixed; inset: 0; z-index: 10000; align-items: center; justify-content: center; padding: 20px; background: rgba(15, 23, 42, .55); }
        .citizen-export-modal.is-open { display: flex; }
        .citizen-export-dialog { width: min(100%, 420px); padding: 24px; border-radius: 12px; background: #fff; box-shadow: 0 18px 50px rgba(15, 23, 42, .24); }
        .citizen-export-dialog h2 { margin: 0 0 8px; color: #173b82; font-size: 20px; }
        .citizen-export-dialog p { margin: 0 0 18px; color: #64748b; font-size: 13px; line-height: 1.5; }
        .citizen-export-options { display: grid; gap: 10px; }
        .citizen-export-option { display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; padding: 13px 14px; border: 1px solid #dbe3ed; border-radius: 8px; background: #f8fafc; color: #172033; text-align: left; cursor: pointer; text-decoration: none; box-sizing: border-box; }
        .citizen-export-option:hover { border-color: #2563eb; background: #eff6ff; }
        .citizen-export-option strong { display: block; color: #173b82; }
        .citizen-export-option small { display: block; margin-top: 3px; color: #64748b; }
        .citizen-export-close { display: flex; justify-content: flex-end; margin-top: 18px; }
        .citizen-export-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 16px; max-height: 300px; overflow-y: auto; padding: 4px 2px; }
        .citizen-export-field { display: flex; align-items: center; gap: 8px; color: #334155; font-size: 13px; }
        .citizen-export-field input { width: 16px; height: 16px; }
        .citizen-export-format { display: flex; justify-content: center; gap: 0; margin-top: 20px; }
        .citizen-export-format button { padding: 10px 18px; border: 1px solid #cbd5e1; background: #f8fafc; color: #475569; cursor: pointer; }
        .citizen-export-format button:first-child { border-radius: 7px 0 0 7px; }
        .citizen-export-format button:last-child { border-radius: 0 7px 7px 0; }
        .citizen-export-format button.active { border-color: #1d4ed8; background: #1d4ed8; color: #fff; }
        .citizen-export-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; }
    </style>
>>>>>>> 8f05883dd366c56d631103f280dc44da05bf5816
@endsection

@section('content')

{{-- SUCCESS POPUP: CITIZEN ACCOUNT CREATED --}}

@if (session('success'))

    <div id="citizen-success-popup"

        style="position:fixed; inset:0; background:rgba(0,0,0,0.5);

                display:flex; align-items:center; justify-content:center;

                z-index:99999; padding:20px;">

        <div style="background:white; width:100%; max-width:400px;

                padding:30px; border-radius:14px; text-align:center;

                box-shadow:0 10px 30px rgba(0,0,0,0.2);">

            <div style="width:58px; height:58px; margin:0 auto 15px;

                border-radius:50%; background:#16a34a; color:white;

                display:flex; align-items:center; justify-content:center;

                font-size:32px;">

                ✓

            </div>

            <h2 style="color:#166534; font-size:22px; font-weight:bold;

                margin-bottom:10px;">

                Success!

            </h2>

            <p style="color:#4b5563; margin-bottom:22px;">

                {{ session('success') }}

            </p>

            <button type="button"

                onclick="closeCitizenSuccessPopup()"

                style="background:#1646bd; color:white; border:none;

                border-radius:7px; padding:10px 30px;

                cursor:pointer; font-weight:bold;">

                OK

            </button>

        </div>

    </div>

@endif

{{-- PAGE TOP HEADER --}}

<div class="page-top">

    <div class="page-title-group">

        <h1>Citizen Management</h1>

        <p>Manage resident profiles, monitor status, and update citizen health-related information.</p>

    </div>

    <div class="page-header-actions" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">

        <a href="{{ route('citizen.add') }}" class="btn-primary">

            + Add Citizen

        </a>

        <button type="button"
                class="btn-secondary"
                onclick="document.getElementById('excelFileInput').click();">

            Import List

        </button>

    </div>

</div>

{{-- CITIZEN OVERVIEW --}}

<div class="citizen-overview">

    {{-- LEFT: SUMMARY CARDS --}}

    <div class="summary-cards">

        {{-- TOTAL CITIZENS --}}

        <div class="summary-card">

            <span>Total Citizens</span>

            <strong>{{ $totalCitizens }}</strong>

            <div class="summary-card-trend trend-{{ $citizenTrends['total']['state'] }}"><span>{{ $citizenTrends['total']['icon'] }} {{ $citizenTrends['total']['value'] }}</span><small>vs previous 14 days</small></div>

        </div>

        {{-- KIDS --}}

        <div class="summary-card">

            <span>Kids</span>

            <strong>{{ $kids }}</strong>

            <div class="summary-card-trend trend-{{ $citizenTrends['kids']['state'] }}"><span>{{ $citizenTrends['kids']['icon'] }} {{ $citizenTrends['kids']['value'] }}</span><small>vs previous 14 days</small></div>

        </div>

        {{-- ADULTS --}}

        <div class="summary-card">

            <span>Adults</span>

            <strong>{{ $adults }}</strong>

            <div class="summary-card-trend trend-{{ $citizenTrends['adults']['state'] }}"><span>{{ $citizenTrends['adults']['icon'] }} {{ $citizenTrends['adults']['value'] }}</span><small>vs previous 14 days</small></div>

        </div>

        {{-- SENIORS --}}

        <div class="summary-card">

            <span>Seniors</span>

            <strong>{{ $seniors }}</strong>

            <div class="summary-card-trend trend-{{ $citizenTrends['seniors']['state'] }}"><span>{{ $citizenTrends['seniors']['icon'] }} {{ $citizenTrends['seniors']['value'] }}</span><small>vs previous 14 days</small></div>

        </div>

    </div>

    {{-- RIGHT: DEMOGRAPHICS --}}

    <div class="chart-card">

        <div class="chart-header">

            <h3>Demographics Breakdown</h3>

        </div>

        <div id="demographicsApexChart"></div>

    </div>

</div>

{{-- TOOLBAR --}}

<div class="toolbar">

    {{-- ALL SEARCH + FILTER CONTROLS --}}

    <div class="toolbar-left">

        {{-- SEARCH --}}

        <form method="GET"

            action="{{ route('citizenlist') }}"

            class="citizen-filter-form">

            <div class="search-box">

                <input type="search"

                name="search"

                value="{{ request('search') }}"

                placeholder="Search citizen name or ID">

            </div>

            {{-- PUROK --}}

            <div class="filter-box">

                <select name="purok"

                onchange="this.form.submit()">

                <option value="">

                All Purok

                </option>

                @foreach($puroks ?? [] as $purok)

                <option value="{{ $purok->id }}"

                {{ request('purok') == $purok->id ? 'selected' : '' }}>

                {{ $purok->name }}

                </option>

                @endforeach

                </select>

            </div>

            {{-- AGE RANGE --}}

            <div class="filter-box">

                <input type="text"

                name="age"

                value="{{ request('age') }}"

                placeholder="Age or range (e.g. 8 or 4-10)"

                onchange="this.form.submit()">

            </div>

                {{-- STATUS FILTER BUTTON --}}
                <div class="filter-box">
                <button type="button"
                id="openCitizenStatusFilter"
                class="citizen-status-filter-btn">
                <span>Status Filters</span>
                <span class="status-filter-arrow" aria-hidden="true">⌄</span>
                </button>
                </div>

        </form>

    </div>

{{-- EXPORT / IMPORT --}}

<div class="toolbar-actions">

    <button type="button" id="openCitizenExportModal" class="btn-secondary">Export List</button>

    {{-- HIDDEN EXCEL IMPORT FORM --}}

    <form action="{{ route('citizen.import') }}"

        method="POST"

        enctype="multipart/form-data"

        id="importForm"

        style="display:none;">

        @csrf

        <input type="file"

            name="file"

            id="excelFileInput"

            accept=".csv,.txt,.xlsx,.xls"

            onchange="document.getElementById('importForm').requestSubmit();">

    </form>

</div>

<div id="citizenExportModal" class="citizen-export-modal" role="dialog" aria-modal="true" aria-labelledby="citizenExportTitle">
    <div class="citizen-export-dialog">
        <h2 id="citizenExportTitle">Export Citizen Records</h2>
        <p>Check the citizen details to include in the file. Only checked fields will be exported.</p>
        <form id="citizenExportForm" data-no-loader="true">
            @php
                $defaultExportFields = [
                    'Citizen_FName',
                    'Citizen_MName',
                    'Citizen_LName',
                    'Citizen_BirthDate',
                    'Citizen_ContactNo',
                    'Citizen_Purok',
                    'Citizen_Sex',
                    'Citizen_Age',
                    'Citizen_HousingStatus',
                ];
            @endphp
            <div class="citizen-export-fields">
                @foreach($exportFieldDefinitions as $field => $label)
                    <label class="citizen-export-field"><input type="checkbox" name="fields[]" value="{{ $field }}" {{ in_array($field, $defaultExportFields, true) ? 'checked' : '' }}> <span>{{ $label }}</span></label>
                @endforeach
            </div>
            <div class="citizen-export-format" role="group" aria-label="Export format">
                <button type="button" class="active" data-export-format="xlsx">Excel (.xlsx)</button>
                <button type="button" data-export-format="pdf">PDF (.pdf)</button>
            </div>
            <input type="hidden" name="format" id="citizenExportFormat" value="xlsx">
            <div class="citizen-export-actions">
                <button type="button" id="closeCitizenExportModal" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Download</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const exportModal = document.getElementById('citizenExportModal');
    const openExportButton = document.getElementById('openCitizenExportModal');
    const closeExportButton = document.getElementById('closeCitizenExportModal');
    const exportForm = document.getElementById('citizenExportForm');
    const exportFormat = document.getElementById('citizenExportFormat');
    const exportFormatButtons = exportModal ? exportModal.querySelectorAll('[data-export-format]') : [];

    if (!exportModal || !openExportButton) return;

    openExportButton.addEventListener('click', function () {
        exportModal.classList.add('is-open');
    });

    closeExportButton.addEventListener('click', function () {
        exportModal.classList.remove('is-open');
    });

    exportModal.addEventListener('click', function (event) {
        if (event.target === exportModal) exportModal.classList.remove('is-open');
    });

    exportFormatButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            exportFormat.value = button.dataset.exportFormat;
            exportFormatButtons.forEach(item => item.classList.toggle('active', item === button));
        });
    });

    exportForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const currentFilters = new URLSearchParams(window.location.search);
        const selectedFields = new FormData(exportForm);
        const fields = selectedFields.getAll('fields[]');
        if (!fields.length) {
            window.alert('Select at least one citizen detail to export.');
            return;
        }
        fields.forEach(field => currentFilters.append('fields[]', field));
        const exportPath = exportFormat.value === 'pdf' ? '{{ route('citizen.export.pdf') }}' : '{{ route('citizen.export') }}';
        window.location.href = exportPath + '?' + currentFilters.toString();
        exportModal.classList.remove('is-open');
    });
});
</script>

{{-- CITIZEN STATUS FILTER MODAL --}}
<div id="citizenStatusFilterModal" class="citizen-status-overlay" aria-hidden="true">
    <div class="citizen-status-modal" role="dialog" aria-modal="true" aria-labelledby="citizenStatusFilterTitle">
        <div class="citizen-status-header">
            <h2 id="citizenStatusFilterTitle">Filter Citizen Records</h2>
            <button type="button" id="closeCitizenStatusFilter" class="citizen-status-close" aria-label="Close filter">&times;</button>
        </div>

        <div class="citizen-status-body">
            <section class="citizen-status-section">
                <h3>Sex</h3>
                <div class="citizen-status-radio-group">
                <label><input type="radio" name="filter_sex" value="all" checked><span>All</span></label>
                <label><input type="radio" name="filter_sex" value="Male"><span>Male</span></label>
                <label><input type="radio" name="filter_sex" value="Female"><span>Female</span></label>
                </div>
            </section>

            <section class="citizen-status-section">
                <h3>Housing Status</h3>
                <label class="citizen-status-option"><input type="checkbox" name="filter_housing[]" value="Permanent Resident"><span>Permanent Resident</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="filter_housing[]" value="Renter"><span>Renter</span></label>
            </section>

            <section class="citizen-status-section">
                <h3>Health &amp; Family Planning</h3>
                <label class="citizen-status-option"><input type="checkbox" name="filter_health[]" value="pregnant"><span>Pregnant</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="filter_health[]" value="in_pills"><span>In-Pills</span></label>
            </section>
            <section class="citizen-status-section">
                <h3>Immunization Vaccines</h3>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="bcg"><span>BCG</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="hepb"><span>Hepatitis B (HepB)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="penta"><span>Pentavalent (Penta)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="opv"><span>Oral Polio Vaccine (OPV)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="ipv"><span>Inactivated Polio Vaccine (IPV)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="pcv"><span>Pneumococcal Conjugate Vaccine (PCV)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="mmr"><span>Measles, Mumps, Rubella (MMR)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="mr"><span>Measles-Rubella (MR)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="td"><span>Tetanus-Diphtheria (Td)</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_vaccine" value="hpv"><span>Human Papillomavirus (HPV)</span></label>
            </section>

            <section class="citizen-status-section">
                <h3>Vital Status</h3>
                <label class="citizen-status-option"><input type="checkbox" id="filterIncludeDeceased" name="filter_deceased" value="1"><span>Include Deceased Residents</span></label>
            </section>
        </div>

        <div class="citizen-status-footer">
            <button type="button" id="clearCitizenStatusFilters" class="citizen-status-clear">Clear All</button>
            <button type="button" id="applyCitizenStatusFilters" class="citizen-status-apply">Apply Filters</button>
        </div>
    </div>
</div>

{{-- TABLE CONTAINER --}}

<div class="table-container">

    {{-- TABLE HEADER --}}

    <div class="card-header"

        style="margin-bottom: 20px;">

        <h2>Citizen Records</h2>

        <p>

            Showing registered barangay residents and their current profile status.

        </p>



    </div>

    <div id="citizen-results">

        @php
            $activeFilters = [];

            if (request()->filled('search')) {
                $activeFilters[] = 'Search: ' . request('search');
            }
            if (request()->filled('purok')) {
                $purokObj = \App\Models\Purok::find(request('purok'));
                $activeFilters[] = 'Purok: ' . ($purokObj ? $purokObj->name : request('purok'));
            }
            if (request()->filled('age')) {
                $activeFilters[] = 'Age: ' . request('age');
            }
            if (request()->filled('filter_sex') && request('filter_sex') !== 'all') {
                $activeFilters[] = 'Sex: ' . request('filter_sex');
            }
            if (request()->has('filter_housing')) {
                $activeFilters[] = 'Housing: ' . implode(', ', (array) request('filter_housing'));
            }
            if (request()->has('filter_health')) {
                $activeFilters[] = 'Health: ' . implode(', ', (array) request('filter_health'));
            }
            if (request()->boolean('filter_deceased')) {
                $activeFilters[] = 'Including Deceased';
            }
        @endphp

        <div id="citizen-results-summary"
             class="citizen-results-highlight"
             style="display:inline-flex; align-items:center; flex-wrap:wrap; gap:6px; margin:0 0 12px; padding:8px 12px; border:1px solid #bbf7d0; border-radius:8px; background:#f0fdf4; color:#166534; font-size:13px; font-weight:500; line-height:1.5;">
            <strong style="font-weight:700;">Showing {{ $citizens->total() }} results</strong>
            @if(count($activeFilters) > 0)
                <span aria-hidden="true" style="color:#86a98e; font-weight:700;">·</span>
                <span id="active-citizen-filters">{{ implode(', ', $activeFilters) }}</span>
            @endif
        </div>

        {{-- CITIZEN TABLE --}}

    <table>

        <thead>

            <tr>

                <th>Name / ID</th>

                <th>Age</th>

                <th>Birth Date</th>

                <th>Contact</th>

                <th>Purok</th>

                <th>Subgroup</th>

                <th>Action</th>

            </tr>

        </thead>

        <tbody>

            @forelse($citizens as $citizen)

                <tr>

                {{-- NAME AND ID --}}

                <td>

                <span class="record-name">

                {{ $citizen->Citizen_FName }}

                {{ $citizen->Citizen_LName }}

                </span>

                <span class="record-sub">

                ID:

                {{ $citizenDisplayIds[$citizen->id] ?? 'Unassigned' }}

                </span>

                </td>

                {{-- AGE --}}

                <td>

                {{ $citizen->Citizen_Age }}

                </td>

                {{-- BIRTH DATE --}}

                <td>

                {{ $citizen->Citizen_BirthDate }}

                </td>

                {{-- CONTACT --}}

                <td>

                {{ $citizen->Citizen_ContactNo }}

                </td>

                {{-- PUROK --}}

                <td>

                {{ $citizen->Citizen_Purok }}

                </td>

                {{-- SUBGROUP --}}

                <td>

                {{ $citizen->subgroup->name ?? $citizen->family?->subgroup?->name ?? 'Unassigned' }}

                </td>

                {{-- ACTIONS --}}

                <td>

                @php

                $user = auth()->user();

                // Use fully qualified model namespace in Blade

                $isAssigned = $user->role !== 'bhw' || \App\Models\PurokSubgroup::where('user_id', $user->id)
                    ->where(function ($query) use ($citizen) {
                        $query->where('id', $citizen->subgroup_id)
                            ->orWhere('id', optional($citizen->family)->subgroup_id);
                    })->exists();

                @endphp

                <div class="action-group">

                {{-- VIEW BUTTON --}}

                <a href="{{ route('citizendetails', $citizen->id) }}"

                class="btn-primary">

                View

                </a>

                @if($isAssigned)

                {{-- MANAGE E-CARD BUTTON --}}

                <a href="{{ route('bhw.citizen.ecard', $citizen->id) }}"

                class="btn-primary">

                E-Card

                </a>

                @if(auth()->user()->hasWriteAccess('citizen_password_reset'))
                <button type="button"

                class="btn-secondary resetCitizenPasswordBtn"

                data-citizen-id="{{ $citizen->id }}"

                data-citizen-name="{{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}">

                Reset Password

                </button>
                @endif

                {{-- DELETE BUTTON --}}

                <form action="{{ route('citizen.delete', $citizen->id) }}"

                method="POST"

                class="deleteCitizenForm"

                data-citizen-name="{{ $citizen->Citizen_FName }} {{$citizen->Citizen_LName }}">

                @csrf

                @method('DELETE')

                <button type="submit"

                class="btn-danger">

                Delete

                </button>

                </form>

                @else

                <span class="text-muted" style="font-size: 11px;">View Only (Unassigned)</span>

                @endif

                </div>

                </td>

                </tr>

            @empty

                <tr>

                <td colspan="7"

                class="empty-text"

                style="text-align: center; padding: 20px;">

                No citizen records found.

                </td>

                </tr>

            @endforelse

        </tbody>

    </table>

    {{-- PAGINATION --}}

    <div style="margin-top: 15px;">

        {{ $citizens->links('pagination::bootstrap-5') }}

    </div>

</div>

{{-- ================================================================

    DELETE CONFIRMATION MODAL

    ================================================================ --}}

<div id="citizenDeleteConfirmModal"

    class="citizen-delete-confirm-overlay">

    <div class="citizen-delete-confirm-modal">

        <h2>

            Delete Citizen?

        </h2>

        <p>

            Are you sure you want to delete

            <strong id="confirmCitizenName"></strong>?

            <br>

            This action cannot be undone.

        </p>

        <div class="citizen-delete-confirm-buttons">

            <button type="button"

                id="cancelCitizenDelete"

                class="citizen-delete-cancel">

                Cancel

            </button>

            <button type="button"

                id="confirmCitizenDelete"

                class="citizen-delete-save">

                Yes, Delete

            </button>

        </div>

    </div>

</div>

<div id="citizenPasswordResetModal" class="citizen-password-modal" role="dialog" aria-modal="true" aria-labelledby="citizenPasswordResetTitle">
    <div class="citizen-password-dialog">
        <h2 id="citizenPasswordResetTitle">Reset Citizen Password</h2>
        <p>Set a temporary password for <strong id="resetCitizenName"></strong>. The citizen will be required to change it at the next login.</p>
        <form method="POST" id="citizenPasswordResetForm">
            @csrf
            <label for="resetCitizenPassword">Temporary password</label>
            <input type="password" id="resetCitizenPassword" name="password" minlength="8" required autocomplete="new-password">
            <label for="resetCitizenPasswordConfirmation">Confirm temporary password</label>
            <input type="password" id="resetCitizenPasswordConfirmation" name="password_confirmation" minlength="8" required autocomplete="new-password">
            <div class="citizen-password-dialog-actions">
                <button type="button" class="btn-secondary" id="cancelCitizenPasswordReset">Cancel</button>
                <button type="submit" class="btn-primary">Reset Password</button>
            </div>
        </form>
    </div>
</div>

{{-- APEXCHARTS CDN --}}

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

{{-- DEMOGRAPHICS CHART & FILTER SCRIPT --}}

<script>

    // ============================================================

    // SUCCESS POPUP SCRIPT

    // ============================================================

    function closeCitizenSuccessPopup() {

        const popup = document.getElementById('citizen-success-popup');

        if (popup) {

            popup.remove();

        }

    }


            const citizenPasswordResetModal = document.getElementById('citizenPasswordResetModal');
            const citizenPasswordResetForm = document.getElementById('citizenPasswordResetForm');
            const resetCitizenName = document.getElementById('resetCitizenName');

            document.querySelectorAll('.resetCitizenPasswordBtn').forEach(function (button) {
                button.addEventListener('click', function () {
                    citizenPasswordResetForm.action = '{{ url('/citizen') }}/' + button.dataset.citizenId + '/password-reset';
                    resetCitizenName.textContent = button.dataset.citizenName;
                    citizenPasswordResetForm.reset();
                    citizenPasswordResetModal.classList.add('is-open');
                    document.getElementById('resetCitizenPassword').focus();
                });
            });

            document.getElementById('cancelCitizenPasswordReset').addEventListener('click', function () {
                citizenPasswordResetModal.classList.remove('is-open');
                citizenPasswordResetForm.reset();
            });

            citizenPasswordResetModal.addEventListener('click', function (event) {
                if (event.target === citizenPasswordResetModal) {
                    citizenPasswordResetModal.classList.remove('is-open');
                    citizenPasswordResetForm.reset();
                }
            });

    // ============================================================

    // ORIGINAL PUROK / SUBGROUP CODE

    // ============================================================

    // Pass Purok and Subgroups structure from backend to JS for dynamic cascading dropdowns

    const puroksData = {!! json_encode($puroks ?? []) !!};

    const selectedPurokId = "{{ request('purok') }}";

    const selectedSubgroupId = "{{ request('subgroup') }}";

    function populateSubgroups(purokId) {

        const subgroupSelect =

            document.getElementById('subgroupFilter');

        subgroupSelect.innerHTML =

            '<option value="">All Subgroups</option>';

        if (!purokId) {

            subgroupSelect.disabled = false;

            return;

        }

        const foundPurok =

            puroksData.find(

                p => p.id == purokId

            );

        if (

            foundPurok &&

            foundPurok.subgroups

        ) {

            foundPurok.subgroups.forEach(sub => {

                const opt =

                document.createElement('option');

                opt.value = sub.id;

                opt.textContent = sub.name;

                if (

                sub.id == selectedSubgroupId

                ) {

                opt.selected = true;

                }

                subgroupSelect.appendChild(opt);

            });

        }

    }

    function onPurokChange(selectElement) {

        // Reset subgroup selection when purok changes, then submit form

        document.getElementById('subgroupFilter').value = '';

        selectElement.form.submit();

    }

    // ============================================================

    // ORIGINAL PAGE LOAD

    // ============================================================

    document.addEventListener(

        'DOMContentLoaded',

        function() {

            if (selectedPurokId) {

                populateSubgroups(

                selectedPurokId

                );

            }

            // ====================================================

            // DELETE CONFIRMATION

            // ====================================================

            const deleteConfirmModal =

                document.getElementById(

                'citizenDeleteConfirmModal'

                );

            const confirmCitizenName =

                document.getElementById(

                'confirmCitizenName'

                );

            const cancelCitizenDelete =

                document.getElementById(

                'cancelCitizenDelete'

                );

            const confirmCitizenDelete =

                document.getElementById(

                'confirmCitizenDelete'

                );

            let selectedDeleteForm = null;

            // ----------------------------------------------------

            // Open confirmation modal

            // ----------------------------------------------------

            document

                .querySelectorAll('.deleteCitizenForm')

                .forEach(function(form) {

                form.addEventListener(

                'submit',

                function(event) {

                event.preventDefault();

                selectedDeleteForm = form;

                const citizenName =

                form.dataset.citizenName;

                confirmCitizenName.textContent =

                citizenName;

                deleteConfirmModal.classList.add(

                'active'

                );

                }

                );

                });

            // ----------------------------------------------------

            // Cancel deletion

            // ----------------------------------------------------

            cancelCitizenDelete.addEventListener(

                'click',

                function() {

                selectedDeleteForm = null;

                deleteConfirmModal.classList.remove(

                'active'

                );

                }

            );

            // ----------------------------------------------------

            // Confirm deletion

            // ----------------------------------------------------

            confirmCitizenDelete.addEventListener(

                'click',

                function() {

                if (!selectedDeleteForm) {

                return;

                }

                deleteConfirmModal.classList.remove(

                'active'

                );

                /*

                * Use native form.submit() so the submit

                * event does not trigger the confirmation

                * again.

                */

                selectedDeleteForm.submit();

                }

            );

            // ----------------------------------------------------

            // Click outside confirmation modal

            // ----------------------------------------------------

            deleteConfirmModal.addEventListener(

                'click',

                function(event) {

                if (

                event.target ===

                deleteConfirmModal

                ) {

                selectedDeleteForm = null;

                deleteConfirmModal.classList.remove(

                'active'

                );

                }

                }

            );

            // ----------------------------------------------------

            // ESC key closes confirmation

            // ----------------------------------------------------

            document.addEventListener(

                'keydown',

                function(event) {

                if (

                event.key === 'Escape' &&

                deleteConfirmModal.classList.contains('active')

                ) {

                selectedDeleteForm = null;

                deleteConfirmModal.classList.remove(

                'active'

                );

                }

                }

            );

        }

    );

    // ============================================================

    // ORIGINAL APEXCHARTS SETUP

    // ============================================================

    var options = {

        series: [

            {

                name: 'Count',

                data: [

                {{ $kids }},

                {{ $adults }},

                {{ $seniors }}

                ]

            }

        ],

        chart: {

            type: 'bar',

              height: '100%',

            toolbar: {

                show: false

            }

        },

        plotOptions: {

            bar: {

                borderRadius: 4,

                distributed: true,

                columnWidth: '45%'

            }

        },

        colors: [

            '#3b82f6',

            '#14b8a6',

            '#f97316'

        ],

        dataLabels: {

            enabled: true,

            formatter: function (value) {
                return Math.floor(value);
            },

            style: {
                fontSize: '15px',
                fontWeight: 600,
                colors: ['#0f172a']
            },

            offsetY: -4

        },

        legend: {

            show: false

        },

        xaxis: {

            categories: [

                'Kids',

                'Adults',

                'Seniors'

            ],

            axisBorder: {

                show: false

            },

            axisTicks: {

                show: false

            },

            labels: {
                style: {
                    fontSize: '13px',
                    colors: ['#334155', '#334155', '#334155']
                }
            }

        },

        yaxis: {

            tickAmount: 3,

            labels: {

                style: {
                    fontSize: '12px',
                    colors: ['#475569']
                },

                formatter: function (val) {

                return Math.floor(val);

                }

            }

        },

        grid: {

            strokeDashArray: 4,

            xaxis: {

                lines: {

                show: false

                }

            }

        }

    };

    var chart = new ApexCharts(

        document.querySelector(

            "#demographicsApexChart"

        ),

        options

    );

    chart.render();

</script>

{{-- CITIZEN STATUS FILTER MODAL SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('citizenStatusFilterModal');
    const openButton = document.getElementById('openCitizenStatusFilter');
    const closeButton = document.getElementById('closeCitizenStatusFilter');
    const clearButton = document.getElementById('clearCitizenStatusFilters');
    const applyButton = document.getElementById('applyCitizenStatusFilters');

    if (!modal || !openButton || !closeButton || !clearButton || !applyButton) return;

    function openModal() {
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        openButton.classList.add('active');
        document.body.style.overflow = 'hidden';
        closeButton.focus();
    }

    function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        openButton.classList.remove('active');
        document.body.style.overflow = '';
        openButton.focus();
    }

    openButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('active')) closeModal();
    });

    // Populate modal inputs from current URL parameters on load
    const params = new URLSearchParams(window.location.search);
    const savedSex = params.get('filter_sex') || 'all';
    const savedSexInput = modal.querySelector('input[name="filter_sex"][value="' + savedSex + '"]');
    if (savedSexInput) savedSexInput.checked = true;

    params.getAll('filter_housing[]').forEach(function (value) {
        const input = modal.querySelector('input[name="filter_housing[]"][value="' + value + '"]');
        if (input) input.checked = true;
    });
    params.getAll('filter_health[]').forEach(function (value) {
        const input = modal.querySelector('input[name="filter_health[]"][value="' + value + '"]');
        if (input) input.checked = true;
    });
    params.getAll('health_filter_vaccine[]').forEach(function (value) {
        const input = modal.querySelector('input[name="health_filter_vaccine"][value="' + value + '"]');
        if (input) input.checked = true;
    });

    const deceased = document.getElementById('filterIncludeDeceased');
    if (deceased) deceased.checked = params.get('filter_deceased') === '1';

    // Clear filters button handler
    clearButton.addEventListener('click', function () {
        const allSex = modal.querySelector('input[name="filter_sex"][value="all"]');
        if (allSex) allSex.checked = true;
        modal.querySelectorAll('input[type="checkbox"]').forEach(function (input) { input.checked = false; });
    });

    // Apply filters button handler
    applyButton.addEventListener('click', function () {
        // Start with the current URL and its existing query parameters (like search, purok, age)
        const targetUrl = new URL(window.location.href);
        const query = targetUrl.searchParams;

        // Clear out old status filter parameters and pagination page before setting fresh ones
        ['filter_sex', 'filter_housing[]', 'filter_health[]', 'health_filter_vaccine[]', 'filter_deceased', 'page'].forEach(function (key) {
            query.delete(key);
        });

        // Add Sex filter if not 'all'
        const selectedSex = modal.querySelector('input[name="filter_sex"]:checked');
        if (selectedSex && selectedSex.value !== 'all') {
            query.set('filter_sex', selectedSex.value);
        }

        // Add Housing Status checkboxes
        modal.querySelectorAll('input[name="filter_housing[]"]:checked').forEach(function (input) {
            query.append('filter_housing[]', input.value);
        });

        // Add Health checkboxes
        modal.querySelectorAll('input[name="filter_health[]"]:checked').forEach(function (input) {
            query.append('filter_health[]', input.value);
        });

        modal.querySelectorAll('input[name="health_filter_vaccine"]:checked').forEach(function (input) {
            query.append('health_filter_vaccine[]', input.value);
        });

        // Add Deceased flag if checked
        if (deceased && deceased.checked) {
            query.set('filter_deceased', '1');
        }

        // Navigate directly to the fully constructed URL query string
        window.location.href = targetUrl.toString();
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.querySelector('.citizen-filter-form');
    const searchInput = searchForm
        ? searchForm.querySelector('input[name="search"]')
        : null;

    let searchTimer = null;
    let requestController = null;

    if (!searchForm || !searchInput) return;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(async function () {
            if (requestController) {
                requestController.abort();
            }

            requestController = new AbortController();

            // Preserve existing modal/status filters in the URL.
            // Update only search, Purok, and age from the toolbar.
            const params = new URLSearchParams(window.location.search);

            ['search', 'purok', 'age', 'page'].forEach(function (key) {
                params.delete(key);
            });

            const formData = new FormData(searchForm);

            ['search', 'purok', 'age'].forEach(function (key) {
                const value = formData.get(key);

                if (value !== null && String(value).trim() !== '') {
                    params.set(key, String(value).trim());
                }
            });

            const results = document.getElementById('citizen-results');
            if (!results) return;

            results.setAttribute('aria-busy', 'true');

            try {
                const queryString = params.toString();
                const requestUrl = queryString
                    ? searchForm.action + '?' + queryString
                    : searchForm.action;

                const response = await fetch(requestUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    signal: requestController.signal
                });

                if (!response.ok) {
                    throw new Error('Search request failed');
                }

                const html = await response.text();
                const parsed = new DOMParser().parseFromString(html, 'text/html');
                const updatedResults = parsed.getElementById('citizen-results');

                if (updatedResults) {
                    results.replaceWith(updatedResults);
                    window.history.replaceState({}, '', requestUrl);
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error(error);
                }
            } finally {
                const currentResults = document.getElementById('citizen-results');

                if (currentResults) {
                    currentResults.removeAttribute('aria-busy');
                }
            }
        }, 350);
    });
});
</script>

@endsection