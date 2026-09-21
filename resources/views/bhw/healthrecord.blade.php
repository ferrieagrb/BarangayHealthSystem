@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/healthrecord.css') }}">
<!-- Load ApexCharts Library -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
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
            
            <!-- Purok Density Heatmap Widget -->
            <div class="recent-tab" style="padding: 15px; background: #fff; border-radius: 8px;">
                <h3 style="margin-bottom: 5px;">Zone Density Heatmap</h3>
                <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Concentration per Purok</p>
                <div id="purokHeatmapChart" style="width: 100%; height: 200px;"></div>
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

<!-- Render ApexCharts Heatmap using data passed from Controller -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const heatmapData = @json($heatmapSeries);

        const options = {
            series: heatmapData,
            chart: {
                type: 'heatmap',
                height: '100%',
                toolbar: { show: false }
            },
            dataLabels: {
                enabled: true,
                style: { colors: ['#fff'] }
            },
            colorScale: {
                ranges: [
                    { from: 0, to: 5, name: 'Low', color: '#93c5fd' },
                    { from: 6, to: 15, name: 'Moderate', color: '#3b82f6' },
                    { from: 16, to: 100, name: 'High', color: '#1d4ed8' }
                ]
            },
            xaxis: {
                type: 'category'
            },
            grid: { padding: { top: 0, bottom: 0 } },
            title: { text: '', align: 'left' }
        };

        const chart = new ApexCharts(document.querySelector("#purokHeatmapChart"), options);
        chart.render();
    });
</script>

@endsection