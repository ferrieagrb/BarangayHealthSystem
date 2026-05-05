@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/citizen.css') }}">
@endsection

@section('content')

<div class="page-top">
        <div class="page-title-group">
            <h1>Citizen Management</h1>
            <p>Manage resident profiles, monitor status, and update citizen health-related information.</p>
        </div>
        <a href="{{ route('citizen.add') }}" class="btn-primary">+ Add Citizen</a>
    </div>

    <div class="summary-cards">
        <div class="summary-card">
            <span>Total Citizens</span>
            <strong>{{ $totalCitizens }}</strong>
        </div>
        <div class="summary-card">
            <span>Kids</span>
            <strong>{{ $kids }}</strong>
        </div>
        <div class="summary-card">
            <span>Adults</span>
            <strong>{{ $adults }}</strong>
        </div>
        <div class="summary-card">
            <span>Seniors</span>
            <strong>{{ $seniors }}</strong>
        </div>
    </div>

    <div class="toolbar">
        <div class="toolbar-left">
            <div class="search-box">
                <form method="GET" action="{{ route('citizenlist') }}" id="filterForm">

                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search citizen name or ID"
                        oninput="this.form.submit()">

                </form>
            </div>
            <div class="filter-box">
                <form method="GET" action="{{ route('citizenlist') }}">

                    <select name="purok" onchange="this.form.submit()">

                        <option value="all">All Purok</option>

                        <option value="Purok 1" {{ request('purok')=='Purok 1'?'selected':'' }}>
                            Purok 1
                        </option>

                        <option value="Purok 2" {{ request('purok')=='Purok 2'?'selected':'' }}>
                            Purok 2
                        </option>

                        <option value="Purok 3" {{ request('purok')=='Purok 3'?'selected':'' }}>
                            Purok 3
                        </option>

                    </select>

                </form>
            </div>
            <div class="filter-box">
                <select>
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Under Monitoring</option>
                </select>
            </div>
        </div>
        <button class="btn-secondary">Export List</button>
    </div>

    <div class="table-container">
        <div class="table-header">
            <div>
                <h2>Citizen Records</h2>
                <p>Showing registered barangay residents and their current profile status.</p>
            </div>
        </div>

        <table>
            @foreach($citizens as $citizen)
                <tr>
                    <td>
                        <span class="citizen-name">
                            {{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}
                        </span>
                        <span class="citizen-sub">
                            ID: C-{{ str_pad($citizen->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                    </td>

                    <td>{{ $citizen->Citizen_Age }}</td>

                    <td>{{ $citizen->Citizen_BirthDate }}</td>

                    <td>{{ $citizen->Citizen_ContactNo }}</td>

                    <td>{{ $citizen->Citizen_Purok }}</td>

                    <td>
    <div class="action-group">
        <a href="{{ route('citizendetails', $citizen->id) }}" class="btn-primary">
            View
        </a>

        <form action="{{ route('citizen.delete', $citizen->id) }}" method="POST" onsubmit="return confirm('Delete this citizen?')">
            @csrf
            @method('DELETE')

            <button type="submit" class="btn-danger">
                Delete
            </button>
        </form>
    </div>
</td>
                </tr>
                @endforeach
        </table>
        <div style="margin-top: 15px;">
                    {{ $citizens->links('pagination::bootstrap-5') }}
                </div>
    </div>

@endsection