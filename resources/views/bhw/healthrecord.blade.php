
@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/healthrecord.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
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
                <form method="GET" action="{{ route('healthrecord') }}" class="health-record-filter-form">
                    @if(request()->filled('health_filter_sex'))
                        <input type="hidden" name="health_filter_sex" value="{{ request('health_filter_sex') }}">
                    @endif
                    @foreach((array) request('health_filter_housing', []) as $value)
                        <input type="hidden" name="health_filter_housing[]" value="{{ $value }}">
                    @endforeach
                    @foreach((array) request('health_filter_health', []) as $value)
                        <input type="hidden" name="health_filter_health[]" value="{{ $value }}">
                    @endforeach
                    @foreach((array) request('health_filter_vaccine', []) as $value)
                        <input type="hidden" name="health_filter_vaccine[]" value="{{ $value }}">
                    @endforeach
                    @if(request()->boolean('filter_deceased'))
                        <input type="hidden" name="filter_deceased" value="1">
                    @endif
                    <input
                        type="text"
                        name="search"
                        placeholder="Search citizen or diagnosis"
                        value="{{ request('search') }}"
                        class="form-control health-record-search"
                    >

                    <select name="purok" class="form-select health-record-purok">
                        <option value="all" {{ request('purok', 'all') == 'all' ? 'selected' : '' }}>All Puroks</option>
                        @foreach($puroks as $purok)
                            <option value="{{ $purok->id }}" {{ (string) request('purok') === (string) $purok->id ? 'selected' : '' }}>{{ $purok->name }}</option>
                        @endforeach
                    </select>

                    <button type="button" id="openCitizenStatusFilter" class="citizen-status-filter-btn" aria-haspopup="dialog" aria-controls="citizenStatusFilterModal" data-ui-action="secondary">
                        <span>Status Filters</span>
                        <span class="status-filter-arrow" aria-hidden="true">⌄</span>
                    </button>

                    <button type="submit" class="btn-primary health-record-search-submit" data-ui-action="secondary">Search</button>
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
                                             data-ui-action="secondary">
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

                    {{ $citizens->links('partials.pagination') }}

                </div>

            </div>

        </div>



        <!-- RIGHT PANEL -->
        <div class="right-panel">


            <!-- DISEASE MONITORING LINK -->
            <div class="recent-tab">
                <h3>Disease Monitoring</h3>
                <p>View diagnosis-record trends and aggregated illness distribution by reporting period, Purok, and subgroup.</p>
                @if($canViewDiseaseAnalytics)
                    <a class="btn btn-primary" href="{{ route('bhw.analytics') }}" data-ui-action="secondary">Open disease monitoring</a>
                @else
                    <p>Reports access is required to view aggregate disease analytics.</p>
                @endif
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
            <button type="button" id="closeCitizenStatusFilter" class="citizen-status-close" aria-label="Close filter" data-ui-action="secondary">&times;</button>
        </div>
        <div class="citizen-status-body">
            <section class="citizen-status-section">
                <h3>Sex</h3>
                <div class="citizen-status-radio-group">
                    <label><input type="radio" name="health_filter_sex" value="all"><span>All</span></label>
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
                <label class="citizen-status-option"><input type="checkbox" id="healthFilterIncludeDeceased" value="1"><span>Show Deceased Residents Only</span></label>
            </section>
        </div>
        <div class="citizen-status-footer">
            <button type="button" id="clearCitizenStatusFilters" class="citizen-status-clear" data-ui-action="reset">Clear All</button>
            <button type="button" id="applyCitizenStatusFilters" class="citizen-status-apply" data-ui-action="secondary">Apply Filters</button>
        </div>
    </div>
</div>

<!-- CITIZEN MANAGEMENT-STYLE FILTER SCRIPT -->
<script src="{{ asset('js/bhw/healthrecord-inline-1.js') }}?v={{ filemtime(public_path('js/bhw/healthrecord-inline-1.js')) }}"></script>

@endsection