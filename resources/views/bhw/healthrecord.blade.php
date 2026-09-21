@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/healthrecord.css') }}">
<!-- Load Google Maps API with async loading -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBBb3WrQ40r3wzE1NKWVQFYtock7GASNJs&loading=async&callback=initMap" async defer></script>
@endsection

@section('content')

<div class="content-wrapper">

    <!-- HEADER -->
    <div class="page-top">
        <div class="page-title">
            <h1>Health Records</h1>
            <p>Track diagnoses, treatments, and medical history of citizens.</p>
        </div>
        <button class="btn-primary">+ Add Record</button>
    </div>

    <!-- SUMMARY (3 Boxes) -->
    <div class="summary">
        <div class="summary-card">
            <span>Total Diagnoses</span>
            <strong>
                {{ $citizens->sum(fn($c) =>$c->healthRecords->count()) }}
            </strong>
        </div>

        <div class="summary-card">
            <span>Most Popular Sickness</span>
            <strong>
                @php
                    $allDiagnoses =$citizens
                        ->flatMap(fn($c) =>$c->healthRecords)
                        ->pluck('diagnosis')
                        ->filter();

                    $mostCommon =$allDiagnoses
                        ->countBy()
                        ->sortDesc()
                        ->keys()
                        ->first();
                @endphp
                {{ $mostCommon ?? 'N/A' }}
            </strong>
        </div>

        <div class="summary-card">
            <span>Cases This Month</span>
            <strong>
                @php
                    $thisMonthCount =$citizens
                        ->flatMap(fn($c) =>$c->healthRecords)
                        ->filter(fn($r) =>$r->created_at->isCurrentMonth())
                        ->count();
                @endphp
                {{ $thisMonthCount }}
            </strong>
        </div>
    </div>

    <!-- WRAPPER -->
    <div class="health-wrapper">

        <!-- LEFT -->
        <div class="left-panel">
            <div class="list-tab">

                <div class="toolbar">
                    <form method="GET" action="{{ route('citizenlist') }}" class="d-flex gap-2">
                        <input type="text" name="search" placeholder="Search citizen or diagnosis" 
                            value="{{ request('search') }}" class="form-control">

                        <select name="purok" class="form-select">
                            <option value="all" {{ request('purok') == 'all' ? 'selected' : '' }}>All Puroks</option>
                            <option value="1" {{ request('purok') == '1' ? 'selected' : '' }}>Purok 1</option>
                            <option value="2" {{ request('purok') == '2' ? 'selected' : '' }}>Purok 2</option>
                        </select>

                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Citizen</th>
                                <th>Latest Diagnosis</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($citizens as$citizen)
                                @php
                                    $latest =$citizen->healthRecords->sortByDesc('created_at')->first();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="record-name">
                                            {{ $citizen->Citizen_FName }} {{$citizen->Citizen_LName }}
                                        </div>
                                        <div class="record-sub">
                                            Purok {{ $citizen->Citizen_Purok }}
                                        </div>
                                    </td>
                                    <td>{{ $latest->diagnosis ?? 'No record' }}</td>
                                    <td>{{ $latest ? $latest->created_at->format('M d, Y') : '-' }}</td>
                                    <td>
                                        <div class="action-group">
                                            <a href="{{ route('citizen.show', $citizen->id) }}" class="btn-primary">View</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No citizens found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 15px;">
                    {{ $citizens->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

        <!-- RIGHT (Enlarged Map aligned with layout structure) -->
        <div class="right-panel" style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Google Maps Geographic Heatmap Widget (Bigger Height) -->
            <div class="recent-tab" style="padding: 15px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 5px;">Geographic Zone Heatmap</h3>
                <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Patient Density Concentration (Brgy. Amuyong)</p>
                <!-- Height increased to 450px for a much larger, clear map view -->
                <div id="purokGoogleMap" style="width: 100%; height: 450px; border-radius: 6px;"></div>
            </div>

            <!-- Recent Diagnoses Widget -->
            <div class="recent-tab">
                <h3>Recent Diagnoses</h3>
                @php
                    $recent =$citizens
                        ->flatMap(fn($c) =>$c->healthRecords->map(function ($r) use ($c) {
                            return [
                                'name' => $c->Citizen_FName . ' ' .$c->Citizen_LName,
                                'diagnosis' => $r->diagnosis,
                                'date' => $r->created_at,
                            ];
                        }))
                        ->sortByDesc('date')
                        ->take(5);
                @endphp

                @forelse($recent as$item)
                    <div class="recent-card">
                        <div class="recent-diagnosis">{{ $item['diagnosis'] }}</div>
                        <div class="recent-date">{{ $item['date']->format('M d, Y') }}</div>
                    </div>
                @empty
                    <p>No recent records.</p>
                @endforelse
            </div>

        </div>

    </div>

</div>

<!-- Google Maps Initialization & Tiered Severity Circles Script -->
<script>
    function initMap() {
        const amuyongCenter = { lat: 14.0668, lng: 120.8531 };

        const map = new google.maps.Map(document.getElementById("purokGoogleMap"), {
            zoom: 15,
            center: amuyongCenter,
            mapTypeId: "roadmap",
            disableDefaultUI: true,
            zoomControl: true
        });

        const rawZoneData = @json($heatmapData ?? []);
        const infowindow = new google.maps.InfoWindow();

        rawZoneData.forEach(item => {
            const actualCount = item.records_count || 1; 
            const weight = item.weight || 1;

            let fillColor = "#ffcc00";    // Tier 1: 1-3 cases (Yellow/Amber)
            let strokeColor = "#ff9900";
            let baseRadius = 50;

            if (actualCount >= 10) {
                // Tier 3: 10+ cases (Deep Crimson)
                fillColor = "#b30000";
                strokeColor = "#800000";
                baseRadius = 100;
            } else if (actualCount >= 3) {
                // Tier 2: 3-5 up to 9 cases (Bright Red/Orange)
                fillColor = "#ff4d4d";
                strokeColor = "#cc0000";
                baseRadius = 75;
            }

            const circle = new google.maps.Circle({
                strokeColor: strokeColor,
                strokeOpacity: 0.9,
                strokeWeight: 2,
                fillColor: fillColor,
                fillOpacity: 0.5,
                map: map,
                center: new google.maps.LatLng(item.location.lat, item.location.lng),
                radius: Math.max(baseRadius, weight * 12) 
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