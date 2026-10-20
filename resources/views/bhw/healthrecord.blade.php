
@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/healthrecord.css') }}">

<script>
    window.__healthRecordMapsReady = false;
    window.initMap = function () {
        window.__healthRecordMapsReady = true;
    };
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBBb3WrQ40r3wzE1NKWVQFYtock7GASNJs&loading=async&callback=initMap" async defer></script>
@endsection

@section('content')

<div class="content-wrapper">

    <!-- HEADER -->
    <div class="page-top">

        <div class="page-title">

            <h1>Health Records</h1>

            <p>
                Track diagnoses, treatments, and medical history of citizens.
            </p>

        </div>

            

    </div>


    <!-- SUMMARY CARDS -->
<div class="summary">

    <!-- TOTAL HEALTH RECORDS -->
    <div class="summary-card">
        <span>Total Health Records</span>

        <strong>
            {{ $citizens->sum(fn($c) => $c->healthRecords->count()) }}
        </strong>

        <p>
            Total number of recorded diagnoses, regardless of type.
        </p>
    </div>

    <!-- PATIENTS WITH RECORDS -->
    <div class="summary-card">
        <span>Patients with Records</span>

        <strong>
            {{ $citizens->filter(fn($c) => $c->healthRecords->count() > 0)->count() }}
        </strong>

        <p>
            Unique citizens who have health records.
        </p>
    </div>

    <!-- MOST COMMON DIAGNOSIS -->
    <div class="summary-card">
        <span>Most Common Diagnosis</span>

        <strong>
            @php
                $allDiagnoses = $citizens
                    ->flatMap(fn($c) => $c->healthRecords)
                    ->pluck('diagnosis')
                    ->filter();

                $mostCommon = $allDiagnoses
                    ->countBy()
                    ->sortDesc()
                    ->keys()
                    ->first();
            @endphp

            {{ $mostCommon ?? 'N/A' }}
        </strong>

        <p>
            The most frequently recorded sickness.
        </p>
    </div>

    <!-- CASES THIS MONTH -->
    <div class="summary-card">
        <span>Cases This Month</span>

        <strong>
            @php
                $thisMonthCount = $citizens
                    ->flatMap(fn($c) => $c->healthRecords)
                    ->filter(fn($r) => $r->created_at->isCurrentMonth())
                    ->count();
            @endphp

            {{ $thisMonthCount }}
        </strong>

        <p>
            Health records created this month.
        </p>
    </div>

</div>


    <!-- MAIN TWO-PANEL LAYOUT -->
    <div class="health-wrapper">


        <!-- LEFT PANEL -->
        <div class="left-panel">

            <!-- SEARCH AND FILTERS: SEARCH, PUROK, STATUS FILTER ON ONE ROW -->
            <div class="toolbar health-record-toolbar">
                <form method="GET" action="{{ route('citizenlist') }}" class="health-record-filter-form">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search citizen or diagnosis"
                        value="{{ request('search') }}"
                        class="form-control health-record-search"
                    >

                    <select name="purok" class="form-select health-record-purok">
                        <option value="all" {{ request('purok', 'all') == 'all' ? 'selected' : '' }}>All Puroks</option>
                        <option value="1" {{ request('purok') == '1' ? 'selected' : '' }}>Purok 1</option>
                        <option value="2" {{ request('purok') == '2' ? 'selected' : '' }}>Purok 2</option>
                    </select>

                    <button type="button" id="openCitizenStatusFilter" class="citizen-status-filter-btn" aria-haspopup="dialog" aria-controls="citizenStatusFilterModal">
                        <span>Status Filters</span>
                        <span class="status-filter-arrow" aria-hidden="true">⌄</span>
                    </button>

                    <button type="submit" class="btn-primary health-record-search-submit">Search</button>
                </form>
            </div>

            <!-- CITIZENS TABLE -->
            <div class="list-tab">

                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>Citizen</th>

                                <th>Latest Diagnosis</th>

                                <th>Date</th>

                                <th style="text-align: center;">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($citizens as $citizen)

                                @php
                                    $latest = $citizen->healthRecords
                                        ->sortByDesc('created_at')
                                        ->first();

                                    $rawStatus = $latest
                                        ? strtolower(trim((string) ($latest->status ?? '')))
                                        : 'no-record';

                                    if (!$latest) {
                                        $filterStatus = 'no-record';
                                    } elseif (in_array($rawStatus, ['treated', 'completed', 'resolved', 'done'])) {
                                        $filterStatus = 'treated';
                                    } elseif (in_array($rawStatus, ['pending', 'ongoing', 'in progress'])) {
                                        $filterStatus = 'pending';
                                    } elseif ($rawStatus === '') {
                                        $filterStatus = 'other';
                                    } else {
                                        $filterStatus = 'other';
                                    }
                                @endphp


                                <tr
                                    data-health-status="{{ $filterStatus }}"
                                    data-citizen-sex="{{ strtolower(trim((string) ($citizen->Citizen_Sex ?? ''))) }}"
                                    data-citizen-housing="{{ strtolower(str_replace([' ', '-'], '_', trim((string) ($citizen->Citizen_HousingStatus ?? '')))) }}"
                                    data-citizen-pregnant="{{ (int) ($citizen->Citizen_Pregnant ?? 0) }}"
                                    data-citizen-in-pills="{{ (int) ($citizen->Citizen_InPills ?? 0) }}"
                                    data-citizen-deceased="{{ (int) ($citizen->Citizen_IsDeceased ?? 0) }}"
                                >

                                    <!-- CITIZEN -->
                                    <td>

                                        <div class="record-name">

                                            {{ $citizen->Citizen_FName }}
                                            {{ $citizen->Citizen_LName }}

                                        </div>


                                        <div class="record-sub">

                                            Purok {{ $citizen->Citizen_Purok }}

                                        </div>

                                    </td>


                                    <!-- DIAGNOSIS -->
                                    <td>

                                        {{ $latest->diagnosis ?? 'No record' }}

                                    </td>


                                    <!-- DATE -->
                                    <td>

                                        {{ $latest ? $latest->created_at->format('M d, Y') : '-' }}

                                    </td>


                                    <!-- ACTION -->
                                    <td>

                                        <div class="action-group">

                                            <a
                                                href="{{ route('citizen.show', $citizen->id) }}"
                                                class="btn-primary"
                                            >
                                                View
                                            </a>

                                        </div>

                                    </td>

                                </tr>


                            @empty

                                <tr>

                                    <td
                                        colspan="4"
                                        style="text-align: center;"
                                    >
                                        No citizens found.
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                <!-- PAGINATION -->
                <div>

                    {{ $citizens->links('pagination::bootstrap-5') }}

                </div>

            </div>

        </div>



        <!-- RIGHT PANEL -->
        <div class="right-panel">


            <!-- GOOGLE MAPS HEATMAP -->
            <div class="recent-tab">

                <h3>
                    Geographic Zone Heatmap
                </h3>


                <p>
                    Patient Density Concentration (Brgy. Amuyong)
                </p>


                <div
                    id="purokGoogleMap"
                    style="width: 100%; height: 260px; border-radius: 6px;"
                ></div>

            </div>



            <!-- RECENT DIAGNOSES -->
            <div class="recent-tab">

                <h3>
                    Recent Diagnoses
                </h3>


                @php

                    $recent = $citizens

                        ->flatMap(fn($c) => $c->healthRecords->map(function ($r) use ($c) {

                            return [

                                'name' => $c->Citizen_FName . ' ' . $c->Citizen_LName,

                                'diagnosis' => $r->diagnosis,

                                'date' => $r->created_at,

                            ];

                        }))

                        ->sortByDesc('date')

                        ->take(5);

                @endphp



                <div>

                    @forelse($recent as $item)

                        <div class="recent-card">


                            <div class="recent-diagnosis">

                                {{ $item['diagnosis'] }}

                            </div>


                            <div class="recent-date">

                                {{ $item['date']->format('M d, Y') }}

                            </div>


                        </div>

                    @empty

                        <p class="empty-text">

                            No recent records.

                        </p>

                    @endforelse

                </div>

            </div>


        </div>

    </div>

</div>



<!-- CITIZEN MANAGEMENT-STYLE STATUS FILTER MODAL -->
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
                    <label><input type="radio" name="health_filter_sex" value="all" checked><span>All</span></label>
                    <label><input type="radio" name="health_filter_sex" value="male"><span>Male</span></label>
                    <label><input type="radio" name="health_filter_sex" value="female"><span>Female</span></label>
                </div>
            </section>
            <section class="citizen-status-section">
                <h3>Housing Status</h3>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_housing" value="permanent_resident"><span>Permanent Resident</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_housing" value="renter"><span>Renter</span></label>
            </section>
            <section class="citizen-status-section">
                <h3>Health &amp; Family Planning</h3>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_health" value="pregnant"><span>Pregnant</span></label>
                <label class="citizen-status-option"><input type="checkbox" name="health_filter_health" value="in_pills"><span>In-Pills</span></label>
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
                <label class="citizen-status-option"><input type="checkbox" id="healthFilterIncludeDeceased" value="1"><span>Include Deceased Residents</span></label>
            </section>
        </div>
        <div class="citizen-status-footer">
            <button type="button" id="clearCitizenStatusFilters" class="citizen-status-clear">Clear All</button>
            <button type="button" id="applyCitizenStatusFilters" class="citizen-status-apply">Apply Filters</button>
        </div>
    </div>
</div>

<!-- CITIZEN MANAGEMENT-STYLE FILTER SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('citizenStatusFilterModal');
    const openButton = document.getElementById('openCitizenStatusFilter');
    const closeButton = document.getElementById('closeCitizenStatusFilter');
    const clearButton = document.getElementById('clearCitizenStatusFilters');
    const applyButton = document.getElementById('applyCitizenStatusFilters');
    const rows = Array.from(document.querySelectorAll('.list-tab tbody tr[data-citizen-sex]'));
    if (!modal || !openButton || !closeButton || !clearButton || !applyButton) return;

    const sexInputs = Array.from(modal.querySelectorAll('input[name="health_filter_sex"]'));
    const housingInputs = Array.from(modal.querySelectorAll('input[name="health_filter_housing"]'));
    const healthInputs = Array.from(modal.querySelectorAll('input[name="health_filter_health"]'));
    const vaccineInputs = Array.from(modal.querySelectorAll('input[name="health_filter_vaccine"]'));
    const deceasedInput = document.getElementById('healthFilterIncludeDeceased');

    function openModal() {
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('citizen-status-modal-open');
        openButton.classList.add('active');
    }
    function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('citizen-status-modal-open');
        openButton.classList.remove('active');
    }
    function applyFilters() {
        const sex = (sexInputs.find(input => input.checked) || { value: 'all' }).value;
        const housing = housingInputs.filter(input => input.checked).map(input => input.value);
        const health = healthInputs.filter(input => input.checked).map(input => input.value);
        const includeDeceased = deceasedInput.checked;

        rows.forEach(row => {
            const rowSex = (row.dataset.citizenSex || '').toLowerCase();
            const rowHousing = (row.dataset.citizenHousing || '').toLowerCase().replace(/[ -]/g, '_');
            const pregnant = row.dataset.citizenPregnant === '1';
            const inPills = row.dataset.citizenInPills === '1';
            const deceased = row.dataset.citizenDeceased === '1';
            const sexMatch = sex === 'all' || rowSex === sex;
            const housingMatch = housing.length === 0 || housing.includes(rowHousing);
            const healthMatch = health.length === 0 || health.some(value =>
                (value === 'pregnant' && pregnant) || (value === 'in_pills' && inPills)
            );
            const vitalMatch = includeDeceased || !deceased;
            row.hidden = !(sexMatch && housingMatch && healthMatch && vitalMatch);
        });
    }

    openButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);
    modal.addEventListener('click', event => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && modal.classList.contains('active')) closeModal();
    });
    clearButton.addEventListener('click', function () {
        sexInputs.forEach(input => { input.checked = input.value === 'all'; });
        housingInputs.forEach(input => { input.checked = false; });
        healthInputs.forEach(input => { input.checked = false; });
        vaccineInputs.forEach(input => { input.checked = false; });
        deceasedInput.checked = false;
    });
    applyButton.addEventListener('click', function () { applyFilters(); closeModal(); });
    applyFilters();
});
</script>

<!-- GOOGLE MAPS SCRIPT -->
<script>

    function initMap() {

        const amuyongCenter = {
            lat: 14.0668,
            lng: 120.8531
        };


        const map = new google.maps.Map(

            document.getElementById("purokGoogleMap"),

            {

                zoom: 15,

                center: amuyongCenter,

                mapTypeId: "roadmap",

                disableDefaultUI: true,

                zoomControl: true

            }

        );


        const rawZoneData = @json($heatmapData ?? []);

        const infowindow = new google.maps.InfoWindow();


        rawZoneData.forEach(item => {

            const actualCount = item.records_count || 1;

            const weight = item.weight || 1;


            let fillColor = "#ffcc00";

            let strokeColor = "#ff9900";

            let baseRadius = 75;


            if (actualCount >= 10) {

                fillColor = "#b30000";

                strokeColor = "#800000";

                baseRadius = 150;

            }

            else if (actualCount >= 3) {

                fillColor = "#ff4d4d";

                strokeColor = "#cc0000";

                baseRadius = 110;

            }


            const circle = new google.maps.Circle({

                strokeColor: strokeColor,

                strokeOpacity: 0.9,

                strokeWeight: 2,

                fillColor: fillColor,

                fillOpacity: 0.5,

                map: map,

                center: new google.maps.LatLng(

                    item.location.lat,

                    item.location.lng

                ),

                radius: Math.max(

                    baseRadius,

                    weight * 20

                )

            });


            circle.addListener("click", () => {

                infowindow.setContent(

                    `<div style="color: #000; padding: 5px;">

                        <strong>Purok ${item.purok}</strong><br>

                        Registered Citizens: ${item.citizens_count}<br>

                        Health Cases: ${actualCount}

                    </div>`

                );


                infowindow.setPosition(circle.getCenter());

                infowindow.open(map);

            });

        });

    }

    window.initMap = initMap;
    if (window.__healthRecordMapsReady) {
        initMap();
    }

</script>


@endsection