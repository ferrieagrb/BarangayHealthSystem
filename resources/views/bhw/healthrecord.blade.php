@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/healthrecord.css') }}">
<!-- Load Google Maps API (Visualization library is no longer needed/supported) -->
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

    <!-- SUMMARY -->
    <div class="summary">
        <div class="summary-card">
            <span>Total Diagnoses</span>
            <strong>
                {{ $citizens->sum(fn($c) => $c->healthRecords->count()) }}
            </strong>
        </div>

        <div class="summary-card">
            <span>Most Popular Sickness</span>
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
        </div>

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
                            @forelse($citizens as $citizen)
                                @php
                                    $latest = $citizen->healthRecords->sortByDesc('created_at')->first();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="record-name">
                                            {{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}
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

        <!-- RIGHT -->
        <div class="right-panel" style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Google Maps Geographic Heatmap Widget -->
            <div class="recent-tab" style="padding: 15px; background: #fff; border-radius: 8px;">
                <h3 style="margin-bottom: 5px;">Geographic Zone Heatmap</h3>
                <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Patient Density Concentration (Brgy. Amuyong)</p>
                <div id="purokGoogleMap" style="width: 100%; height: 260px; border-radius: 6px;"></div>
            </div>

            <!-- Recent Diagnoses Widget -->
            <div class="recent-tab">
                <h3>Recent Diagnoses</h3>
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

                @forelse($recent as $item)
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

<!-- Google Maps Initialization & Dynamic Zone Circles Script -->
<script>
    function initMap() {
        // Centered on Barangay Amuyong, Alfonso, Cavite
        const amuyongCenter = { lat: 14.0668, lng: 120.8531 };

        const map = new google.maps.Map(document.getElementById("purokGoogleMap"), {
            zoom: 15,
            center: amuyongCenter,
            mapTypeId: "roadmap",
            disableDefaultUI: true,
            zoomControl: true
        });

        // Pull density data passed from the controller
        const rawZoneData = @json($heatmapData ?? []);

        // Render proportional circles for each Purok zone as a modern heatmap alternative
        rawZoneData.forEach(item => {
            const caseWeight = item.weight || 1;

            new google.maps.Circle({
                strokeColor: "#ff4d4d",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#ff1a1a",
                fillOpacity: 0.4,
                map: map,
                center: new google.maps.LatLng(item.location.lat, item.location.lng),
                // Radius scales dynamically depending on how many cases are in that Purok
                radius: Math.max(35, caseWeight * 8) 
            });
        });
    }
</script>

@endsection