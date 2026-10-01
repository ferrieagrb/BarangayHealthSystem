@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/logs.css') }}">

<style>
.tabs button {
    padding: 10px 14px;
    border: none;
    background: #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    margin-right: 5px;
}

.tabs button.active {
    background: #0E42B1;
    color: #fff;
}

.tab-content {
    display: none;
    margin-top: 20px;
}

.tab-content.active {
    display: block;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    text-align: left;
}

.tag {
    padding: 4px 8px;
    background: #0E42B1;
    color: white;
    border-radius: 6px;
    font-size: 12px;
}

.pagination-container {
    margin-top: 20px;
}
</style>
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Audit Logs</h1>
        <p>Track citizen, supply, and health record activity</p>
    </div>
</div>

<!-- TABS -->
<div class="tabs">
    <button class="tab active" onclick="openTab(event, 'supplies')">Supplies</button>
    <button class="tab" onclick="openTab(event, 'health')">Health Records</button>
    <button class="tab" onclick="openTab(event, 'vehicle')">Vehicle Borrowing</button>
</div>

<!-- SUPPLY LOGS -->
<div id="supplies" class="tab-content active">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Action</th>
                <th>Type</th>
                <th>Notes</th>
                <th>Date / Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($supplyLogs as $log)
            <tr>
                <td>#{{ $log->id }}</td>
                <td>{{ $log->user_id ?? 'N/A' }}</td>
                <td>{{ $log->action }}</td>
                <td><span class="tag">Supply</span></td>
                <td>{{ $log->notes }}</td>
                <td>{{ $log->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination-container">
        {{ $supplyLogs->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- HEALTH LOGS -->
<div id="health" class="tab-content">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Type</th>
                <th>Description</th>
                <th>Date / Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($healthRecordLogs as $log)
            <tr>
                <td>#{{ $log->id }}</td>
                <td>{{ $log->user_id ?? 'N/A' }}</td>
                <td><span class="tag">Health</span></td>
                <td>{{ $log->description }}</td>
                <td>{{ $log->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination-container">
        {{ $healthRecordLogs->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- VEHICLE LOGS -->
<div id="vehicle" class="tab-content">
    <button onclick="toggleVehicleForm()" style="margin-bottom: 15px;">+ Add Borrow Log</button>

    <div id="vehicleForm" style="display:none; margin-top: 10px; margin-bottom: 15px;">
        <form method="POST" action="{{ route('vehicle.logs.store') }}">
            @csrf
            <input type="text" name="vehicle_name" placeholder="Vehicle Name" required>
            <input type="text" name="borrower" placeholder="Borrower Name" required>
            <input type="datetime-local" name="borrowed_at" required>
            <button type="submit">Save</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Vehicle Name</th>
                <th>Borrower</th>
                <th>Borrowed At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicleLogs as $log)
            <tr>
                <td>#{{ $log->id }}</td>
                <td>{{ $log->vehicle_name }}</td>
                <td>{{ $log->borrower }}</td>
                <td>{{ $log->borrowed_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination-container">
        {{ $vehicleLogs->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

@endsection

@section('scripts')
<script>
function openTab(evt, tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));

    document.getElementById(tab).classList.add('active');
    evt.currentTarget.classList.add('active');
}

function toggleVehicleForm() {
    let form = document.getElementById('vehicleForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
@endsection