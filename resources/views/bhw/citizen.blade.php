@extends('templates.layout')

@section('CSSown')
    <link rel="stylesheet" href="{{ asset('css/bhw/citizen.css') }}">
@endsection

@section('content')

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


{{-- SUMMARY CARDS --}}
<div class="summary-cards"
    style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px;">

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


{{-- DEMOGRAPHICS CHART --}}
<div class="chart-card"
    style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px;">

    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">

        <h3 style="font-size: 15px; color: #1e293b; font-weight: 600; margin: 0;">
            Demographics Breakdown
        </h3>

    </div>

    <div id="demographicsApexChart"></div>

</div>



{{-- TOOLBAR: SEARCH, FILTERS, EXPORT, AND IMPORT --}}
<div class="toolbar">

    {{-- LEFT TOOLBAR --}}
    <div class="toolbar-left"
        style="display: flex; gap: 10px; flex-wrap: wrap; flex: 1;">

        {{-- SEARCH --}}
        <div class="search-box">
            <form method="GET"
                action="{{ route('citizenlist') }}"
                id="filterForm">

                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search citizen name or ID"
                    oninput="this.form.submit()">
            </form>
        </div>


        {{-- PUROK FILTER --}}
        <div class="filter-box">
            <form method="GET"
                action="{{ route('citizenlist') }}">

                <select name="purok"
                    onchange="this.form.submit()">

                    <option value="all">
                        All Purok
                    </option>

                    <option value="Purok 1"
                        {{ request('purok') == 'Purok 1' ? 'selected' : '' }}>
                        Purok 1
                    </option>

                    <option value="Purok 2"
                        {{ request('purok') == 'Purok 2' ? 'selected' : '' }}>
                        Purok 2
                    </option>

                    <option value="Purok 3"
                        {{ request('purok') == 'Purok 3' ? 'selected' : '' }}>
                        Purok 3
                    </option>
                </select>
            </form>
        </div>


        {{-- STATUS FILTER --}}
        <div class="filter-box">
            <select>
                <option>All Status</option>
                <option>Active</option>
                <option>Under Monitoring</option>
            </select>
        </div>
    </div>


    {{-- RIGHT TOOLBAR: EXPORT AND IMPORT --}}
    <div
        style="display: flex; gap: 10px; align-items: center;">

        {{-- EXPORT BUTTON --}}
        <button type="button"
            class="btn-secondary">
            Export List
        </button>


        {{-- HIDDEN IMPORT FORM --}}
        <form action="{{ route('citizen.import') }}"
            method="POST"
            enctype="multipart/form-data"
            id="importForm"
            style="display: none;">

            @csrf

            <input type="file"
                name="file"
                id="excelFileInput"
                accept=".csv, .xlsx, .xls"
                onchange="document.getElementById('importForm').submit()">
        </form>


        {{-- IMPORT BUTTON --}}
        <button type="button"
            class="btn-secondary"
            onclick="document.getElementById('excelFileInput').click();">

            Import Excel
        </button>
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
                                onsubmit="return confirm('Delete this citizen?')">

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


{{-- APEXCHARTS CDN --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


{{-- DEMOGRAPHICS CHART SCRIPT --}}
<script>

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
        document.querySelector("#demographicsApexChart"),
        options
    );

    chart.render();

</script>

@endsection