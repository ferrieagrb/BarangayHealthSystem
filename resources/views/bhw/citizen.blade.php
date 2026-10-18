@extends('templates.layout')

@section('CSSown')

    <link rel="stylesheet" href="{{ asset('css/bhw/citizen.css') }}">

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

    <a href="{{ route('citizen.add') }}" class="btn-primary">

        + Add Citizen

    </a>

</div>

{{-- CITIZEN OVERVIEW --}}

<div class="citizen-overview">

    {{-- LEFT: SUMMARY CARDS --}}

    <div class="summary-cards">

        {{-- TOTAL CITIZENS --}}

        <div class="summary-card">

            <span>Total Citizens</span>

            <strong>{{ $totalCitizens }}</strong>

        </div>

        {{-- KIDS --}}

        <div class="summary-card">

            <span>Kids</span>

            <strong>{{ $kids }}</strong>

        </div>

        {{-- ADULTS --}}

        <div class="summary-card">

            <span>Adults</span>

            <strong>{{ $adults }}</strong>

        </div>

        {{-- SENIORS --}}

        <div class="summary-card">

            <span>Seniors</span>

            <strong>{{ $seniors }}</strong>

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

    {{-- EXPORT CITIZENS TO EXCEL --}} 

    <a href="{{ route('citizen.export') }}"

    class="btn-secondary"

    style="text-decoration: none;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            box-sizing: border-box;

            vertical-align: middle;">

        Export List

    </a>

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

            accept=".csv,.xlsx,.xls"

            onchange="document.getElementById('importForm').submit()">

    </form>

    {{-- IMPORT BUTTON --}}

    <button type="button"

            class="btn-secondary"

            onclick="document.getElementById('excelFileInput').click();">

        Import Excel

    </button>

</div>

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

        @if(count($activeFilters) > 0)
            <p id="active-citizen-filters" style="color: #9ca3af; font-size: 13px; margin-top: 4px;">
                (Showing {{ implode(', ', $activeFilters) }})
            </p>
        @endif

    </div>

    <div id="citizen-results">

        {{-- CITIZEN TABLE --}}

    <table>

        <thead>

            <tr>

                <th>Name / ID</th>

                <th>Age</th>

                <th>Birth Date</th>

                <th>Contact</th>

                <th>Purok</th>

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

                C-{{ str_pad($citizen->id, 4, '0', STR_PAD_LEFT) }}

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

                {{-- ACTIONS --}}

                <td>

                @php

                $user = auth()->user();

                // Use fully qualified model namespace in Blade

                $isAssigned =$user->role !== 'bhw' || \App\Models\PurokSubgroup::where('user_id', $user->id)->where('id', optional($citizen->family)->subgroup_id)->exists();

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

                Manage E-Card

                </a>

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

                <td colspan="6"

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

            height: 160,

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

            enabled: false

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

            }

        },

        yaxis: {

            tickAmount: 3,

            labels: {

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
        ['filter_sex', 'filter_housing[]', 'filter_health[]', 'filter_deceased', 'page'].forEach(function (key) {
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
    const searchInput = searchForm ? searchForm.querySelector('input[name="search"]') : null;
    const results = document.getElementById('citizen-results');
    let searchTimer;
    let requestController;

    if (!searchForm || !searchInput || !results) return;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(async function () {
            if (requestController) requestController.abort();
            requestController = new AbortController();
            const params = new URLSearchParams(new FormData(searchForm));
            params.delete('page');
            if (!searchInput.value.trim()) {
                params.delete('search');
                const activeFilters = document.getElementById('active-citizen-filters');
                if (activeFilters) activeFilters.remove();
            }
            results.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(`${searchForm.action}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    signal: requestController.signal
                });
                if (!response.ok) throw new Error('Search request failed');
                const html = await response.text();
                const parsed = new DOMParser().parseFromString(html, 'text/html');
                const updatedResults = parsed.getElementById('citizen-results');
                if (updatedResults) results.replaceWith(updatedResults);
            } catch (error) {
                if (error.name !== 'AbortError') console.error(error);
            } finally {
                const currentResults = document.getElementById('citizen-results');
                if (currentResults) currentResults.removeAttribute('aria-busy');
            }
        }, 350);
    });
});
</script>

@endsection