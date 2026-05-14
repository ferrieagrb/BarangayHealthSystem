@extends('templates.admin')

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

td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.tag {
    padding: 4px 8px;
    background: #0E42B1;
    color: white;
    border-radius: 6px;
    font-size: 12px;
}
</style>
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>System Logs</h1>
        <p>Track citizen, supply, and health record activity</p>
    </div>
</div>

<!-- TABS -->
<div class="tabs">
    <button class="tab active" onclick="openTab(event, 'citizens')">Citizens</button>
    <button class="tab" onclick="openTab(event, 'supplies')">Supplies</button>
    <button class="tab" onclick="openTab(event, 'health')">Health Records</button>
    <button class="tab" onclick="openTab(event, 'vehicle')">Vehicle Borrowing</button>
    <!--
    <button class="tab" onclick="openTab(event, 'Immunization')">Immunization Recording</button>
    <button class="tab" onclick="openTab(event, 'HomeVisit')">Home Visit Recording</button>
    -->
</div>
<div class="tabs">
    <button class="tab" onclick="openTab(event, 'Schedule')">Schedule Logs</button>
    <button class="tab" onclick="openTab(event, 'Calendar')">Calendar Logs</button>
    <button class="tab" onclick="openTab(event, 'Referral')">Referral Logs</button>
    <button class="tab" onclick="openTab(event, 'User')">User Logs</button>
    <button class="tab" onclick="openTab(event, 'Schedule')">Schedule Logs</button>
    <button class="tab" onclick="openTab(event, 'Announcement')">Announcement Logs</button>
    <button class="tab" onclick="openTab(event, 'Schedule')">Schedule Logs</button>
    <button class="tab" onclick="openTab(event, 'EnvironmentOcular')">Environment Ocular Logs</button>
</div>

<!-- CITIZEN LOGS -->
<div id="citizens" class="tab-content active">
    <table>
        @foreach($citizenLogs as $log)
        <tr>
            <td>#{{ $log->id }}</td>
            <td><span class="tag">Citizen</span></td>
            <td>{{ $log->description }}</td>
            <td>{{ $log->created_at }}</td>
        </tr>
        @endforeach
    </table>
</div>

<!-- SUPPLY LOGS -->
<div id="supplies" class="tab-content">
    <table>
        @foreach($supplyLogs as $log)
        <tr>
            <td>#{{ $log->id }}</td>
            <td><span class="tag">Supply</span></td>
            <td>{{ $log->description }}</td>
            <td>{{ $log->created_at }}</td>
        </tr>
        @endforeach
    </table>
</div>

<!-- HEALTH LOGS -->
<div id="health" class="tab-content">
    <table>
        @foreach($healthRecordLogs as $log)
        <tr>
            <td>#{{ $log->id }}</td>
            <td><span class="tag">Health</span></td>
            <td>{{ $log->description }}</td>
            <td>{{ $log->created_at }}</td>
        </tr>
        @endforeach
    </table>
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
</script>
@endsection