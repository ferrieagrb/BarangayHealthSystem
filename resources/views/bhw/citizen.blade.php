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
                <input type="text"
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

            

            {{-- STATUS --}}
            <div class="filter-box">
                <select>
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Under Monitoring</option>
                </select>
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

                        <div class="action-group">

                            {{-- VIEW BUTTON --}}
                            <a href="{{ route('citizendetails', $citizen->id) }}"
                                class="btn-primary">

                                View
                            </a>


                            {{-- MANAGE E-CARD BUTTON --}}
                            <a href="{{ route('bhw.citizen.ecard', $citizen->id) }}"
                                class="btn-primary">

                                Manage E-Card
                            </a>


                            {{-- DELETE BUTTON --}}
                            <form action="{{ route('citizen.delete', $citizen->id) }}"
                                method="POST"
                                class="deleteCitizenForm"
                                data-citizen-name="{{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}">

                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="btn-danger">

                                    Delete
                                </button>
                            </form>

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

@endsection