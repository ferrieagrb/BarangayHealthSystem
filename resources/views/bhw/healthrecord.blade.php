
@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/healthrecord.css') }}">

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

            <a href="#" class="btn-primary add-record-btn">
                + Add Record
            </a>

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

            <!-- SEARCH AND FILTERS -->
            <div class="toolbar">

                <form
                    method="GET"
                    action="{{ route('citizenlist') }}"
                    class="d-flex gap-2"
                >

                    <input
                        type="text"
                        name="search"
                        placeholder="Search citizen or diagnosis"
                        value="{{ request('search') }}"
                        class="form-control"
                    >


                    <select
                        name="purok"
                        class="form-select"
                    >

                        <option
                            value="all"
                            {{ request('purok') == 'all' ? 'selected' : '' }}
                        >
                            All Puroks
                        </option>

                        <option
                            value="1"
                            {{ request('purok') == '1' ? 'selected' : '' }}
                        >
                            Purok 1
                        </option>

                        <option
                            value="2"
                            {{ request('purok') == '2' ? 'selected' : '' }}
                        >
                            Purok 2
                        </option>

                    </select>


                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        Search
                    </button>

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
                                @endphp


                                <tr>

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

            let baseRadius = 45;


            if (actualCount >= 10) {

                fillColor = "#b30000";

                strokeColor = "#800000";

                baseRadius = 90;

            }

            else if (actualCount >= 3) {

                fillColor = "#ff4d4d";

                strokeColor = "#cc0000";

                baseRadius = 65;

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

                    weight * 10

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

</script>


@endsection