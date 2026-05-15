@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/healthrecord.css') }}">
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
                            <!-- Add other puroks here -->
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
                                            {{ $citizen->Citizen_Purok }}
                                        </div>
                                    </td>

                                    <td>{{ $latest->diagnosis ?? 'No record' }}</td>

                                    <td>
                                        {{ $latest ? $latest->created_at->format('M d, Y') : '-' }}
                                    </td>

                                    <td>
                                        <div class="action-group">
                                            <a href="{{ route('citizen.show', $citizen->id) }}" class="btn-primary">
                                                View
                                            </a>
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
        <div class="right-panel">
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

@endsection