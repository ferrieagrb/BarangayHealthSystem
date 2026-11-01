@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/logs.css') }}">
<style>
    .logs-page { padding-bottom: 24px; }
    .tabs { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0; }
    .tabs button { padding: 9px 13px; border: 1px solid #dbe3ed; border-radius: 8px; background: #f8fafc; color: #334155; cursor: pointer; }
    .tabs button.active { border-color: #0e42b1; background: #0e42b1; color: #fff; }
    .tab-content { display: none; margin-top: 20px; }
    .tab-content.active { display: block; }
    .log-table { width: 100%; border-collapse: collapse; background: #fff; }
    .log-table th, .log-table td { padding: 11px 10px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
    .log-table th { color: #64748b; font-size: 11px; text-transform: uppercase; }
    .tag { display: inline-block; padding: 4px 8px; border-radius: 6px; background: #0e42b1; color: #fff; font-size: 12px; }
    .status-tag { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 12px; font-weight: 600; }
    .status-tag.claimed, .status-tag.approved, .status-tag.fulfilled { background: #dcfce7; color: #166534; }
    .pagination-container { margin-top: 18px; }
    .pagination-container nav > div:first-child { display: none; }
    .pagination-container nav > div:last-child { display: flex; justify-content: center; }
    .pagination-container nav ul { display: flex; gap: 5px; margin: 0; padding: 0; list-style: none; }
    .pagination-container .page-link { display: block; padding: 7px 11px; border: 1px solid #dbe3ed; border-radius: 6px; color: #334155; background: #fff; text-decoration: none; }
    .pagination-container .page-item.active .page-link { border-color: #0e42b1; background: #0e42b1; color: #fff; }
    .pagination-container .page-item.disabled .page-link { color: #94a3b8; background: #f8fafc; }
    .empty-log { padding: 22px; color: #64748b; text-align: center; }
</style>
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
@endsection

@section('content')
<div class="logs-page">
    <div class="page-top">
        <div class="page-title">
            <h1>Audit Logs</h1>
            <p>Review activity across citizens, health, inventory, schedules, referrals, and releases.</p>
        </div>
    </div>

    <div class="tabs">
        <button class="tab active" data-tab="citizens">Citizens</button>
        <button class="tab" data-tab="supplies">Supplies</button>
        <button class="tab" data-tab="health">Health</button>
        <button class="tab" data-tab="vehicle">Vehicles</button>
        <button class="tab" data-tab="announcements">Announcements</button>
        <button class="tab" data-tab="events">Calendar</button>
        <button class="tab" data-tab="referrals">Referrals</button>
        <button class="tab" data-tab="maintenance">Maintenance</button>
        <button class="tab" data-tab="releases">Batch Releases</button>
    </div>

    <div id="citizens" class="tab-content active">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Action</th><th scope="col">Citizen</th><th scope="col">Description</th></tr></thead><tbody role="rowgroup">
            @forelse($citizenLogs as $log)<tr role="row"><td role="cell" data-label="Date">{{ $log->created_at }}</td><td role="cell" data-label="Actor">{{ $log->user->name ?? 'Legacy / System' }}</td><td role="cell" data-label="Action"><span class="tag">{{ ucfirst($log->action) }}</span></td><td role="cell" data-label="Citizen">{{ $log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A' }}</td><td role="cell" data-label="Description">{{ $log->description }}</td></tr>@empty<tr role="row"><td role="cell" colspan="5" class="empty-log">No citizen logs.</td></tr>@endforelse
        </tbody></table>
        <div class="pagination-container">{{ $citizenLogs->appends(array_merge(request()->query(), ['tab' => 'citizens']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="supplies" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Action</th><th scope="col">Supply</th><th scope="col">Quantity</th><th scope="col">Citizen</th><th scope="col">Notes</th></tr></thead><tbody role="rowgroup">
            @forelse($supplyLogs as $log)<tr role="row"><td role="cell" data-label="Date">{{ $log->created_at }}</td><td role="cell" data-label="Actor">{{ $log->user->name ?? 'Legacy / System' }}</td><td role="cell" data-label="Action"><span class="tag">{{ ucfirst($log->action) }}</span></td><td role="cell" data-label="Supply">{{ $log->supply->name ?? 'Removed supply' }}</td><td role="cell" data-label="Quantity">{{ $log->quantity }}</td><td role="cell" data-label="Citizen">{{ $log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'General inventory' }}</td><td role="cell" data-label="Notes">{{ $log->notes ?? 'N/A' }}</td></tr>@empty<tr role="row"><td role="cell" colspan="7" class="empty-log">No supply logs.</td></tr>@endforelse
        </tbody></table>
        <div class="pagination-container">{{ $supplyLogs->appends(array_merge(request()->query(), ['tab' => 'supplies']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="health" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Action</th><th scope="col">Citizen ID</th><th scope="col">Description</th></tr></thead><tbody role="rowgroup">
            @forelse($healthRecordLogs as $log)<tr role="row"><td role="cell" data-label="Date">{{ $log->created_at }}</td><td role="cell" data-label="Actor">{{ $log->user->name ?? 'Legacy / System' }}</td><td role="cell" data-label="Action"><span class="tag">{{ ucfirst($log->action) }}</span></td><td role="cell" data-label="Citizen ID">{{ $log->citizen_id ?? 'N/A' }}</td><td role="cell" data-label="Description">{{ $log->description }}</td></tr>@empty<tr role="row"><td role="cell" colspan="5" class="empty-log">No health-record logs.</td></tr>@endforelse
        </tbody></table>
        <div class="pagination-container">{{ $healthRecordLogs->appends(array_merge(request()->query(), ['tab' => 'health']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="vehicle" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Vehicle</th><th scope="col">Borrower</th><th scope="col">Borrowed At</th></tr></thead><tbody role="rowgroup">
            @forelse($vehicleLogs as $log)<tr role="row"><td role="cell" data-label="Date">{{ $log->created_at }}</td><td role="cell" data-label="Actor">{{ $log->user->name ?? 'Legacy / System' }}</td><td role="cell" data-label="Vehicle">{{ $log->vehicle_name }}</td><td role="cell" data-label="Borrower">{{ $log->borrower }}</td><td role="cell" data-label="Borrowed At">{{ $log->borrowed_at }}</td></tr>@empty<tr role="row"><td role="cell" colspan="5" class="empty-log">No vehicle logs.</td></tr>@endforelse
        </tbody></table>
        <div class="pagination-container">{{ $vehicleLogs->appends(array_merge(request()->query(), ['tab' => 'vehicle']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="announcements" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Title</th><th scope="col">Category</th><th scope="col">Description</th></tr></thead><tbody role="rowgroup">
            @forelse($announcementLogs as $log)<tr role="row"><td role="cell" data-label="Date">{{ $log->created_at }}</td><td role="cell" data-label="Actor">{{ $log->user->name ?? 'Legacy / System' }}</td><td role="cell" data-label="Title">{{ $log->title }}</td><td role="cell" data-label="Category">{{ $log->category ?? 'General' }}</td><td role="cell" data-label="Description">{{ $log->description }}</td></tr>@empty<tr role="row"><td role="cell" colspan="5" class="empty-log">No announcements.</td></tr>@endforelse
        </tbody></table>
        <div class="pagination-container">{{ $announcementLogs->appends(array_merge(request()->query(), ['tab' => 'announcements']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="events" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Event</th><th scope="col">Details</th></tr></thead><tbody role="rowgroup">
            @forelse($eventLogs as $log)<tr role="row"><td role="cell" data-label="Date">{{ \Carbon\Carbon::parse($log->start)->format('M d, Y') }}</td><td role="cell" data-label="Actor">{{ $log->user->name ?? 'Legacy / System' }}</td><td role="cell" data-label="Event">{{ $log->title }}</td><td role="cell" data-label="Details">{{ $log->description ?? 'No details' }}</td></tr>@empty<tr role="row"><td role="cell" colspan="4" class="empty-log">No calendar events.</td></tr>@endforelse
        </tbody></table>
        <div class="pagination-container">{{ $eventLogs->appends(array_merge(request()->query(), ['tab' => 'events']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="referrals" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date</th><th scope="col">Actor</th><th scope="col">Patient</th><th scope="col">Request</th><th scope="col">Status</th></tr></thead><tbody role="rowgroup">
            @forelse($referralLogs as $log)<tr role="row"><td role="cell" data-label="Date">{{ $log->date_of_referral }}</td><td role="cell" data-label="Actor">{{ $log->user->name ?? 'Legacy / System' }}</td><td role="cell" data-label="Patient">{{ $log->name }}</td><td role="cell" data-label="Request">{{ $log->requests_for }}</td><td role="cell" data-label="Status"><span class="status-tag {{ $log->status }}">{{ ucfirst($log->status ?? 'pending') }}</span></td></tr>@empty<tr role="row"><td role="cell" colspan="5" class="empty-log">No referrals.</td></tr>@endforelse
        </tbody></table>
        <div class="pagination-container">{{ $referralLogs->appends(array_merge(request()->query(), ['tab' => 'referrals']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="maintenance" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Date Added</th><th scope="col">Actor</th><th scope="col">Citizen</th><th scope="col">Medicine</th><th scope="col">Dosage</th><th scope="col">Frequency</th><th scope="col">Schedule</th></tr></thead><tbody role="rowgroup">
            @forelse($maintenanceLogs as $log)<tr role="row"><td role="cell" data-label="Date Added">{{ $log->created_at }}</td><td role="cell" data-label="Actor">{{ $log->user->name ?? 'Legacy / System' }}</td><td role="cell" data-label="Citizen">{{ $log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A' }}</td><td role="cell" data-label="Medicine">{{ $log->medicine_name }}</td><td role="cell" data-label="Dosage">{{ $log->dosage }}</td><td role="cell" data-label="Frequency">{{ $log->frequency }}</td><td role="cell" data-label="Schedule">{{ $log->start_date->format('M d, Y') }}{{ $log->end_date ? ' - ' . $log->end_date->format('M d, Y') : ' - Ongoing' }}</td></tr>@empty<tr role="row"><td role="cell" colspan="7" class="empty-log">No maintenance records.</td></tr>@endforelse
        </tbody></table>
        <div class="pagination-container">{{ $maintenanceLogs->appends(array_merge(request()->query(), ['tab' => 'maintenance']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="releases" class="tab-content">
        <table class="log-table" role="table"><thead role="rowgroup"><tr role="row"><th scope="col">Claim Date</th><th scope="col">Actor</th><th scope="col">Batch</th><th scope="col">Citizen</th><th scope="col">Supply</th><th scope="col">Quantity</th><th scope="col">Status</th></tr></thead><tbody role="rowgroup">
            @forelse($releaseLogs as $log)<tr role="row"><td role="cell" data-label="Claim Date">{{ $log->claim_date->format('M d, Y') }}</td><td role="cell" data-label="Actor">{{ $log->user->name ?? 'Legacy / System' }}</td><td role="cell" data-label="Batch">{{ $log->batch_title }}</td><td role="cell" data-label="Citizen">{{ $log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A' }}</td><td role="cell" data-label="Supply">{{ $log->supply->name ?? 'Unavailable' }}</td><td role="cell" data-label="Quantity">{{ $log->quantity }}</td><td role="cell" data-label="Status"><span class="status-tag {{ $log->status }}">{{ ucfirst($log->status) }}</span></td></tr>@empty<tr role="row"><td role="cell" colspan="7" class="empty-log">No batch releases.</td></tr>@endforelse
        </tbody></table>
        <div class="pagination-container">{{ $releaseLogs->appends(array_merge(request()->query(), ['tab' => 'releases']))->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const initialTab = @json(request('tab', 'citizens'));
    document.querySelectorAll('.tab').forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(panel => panel.classList.remove('active'));
            button.classList.add('active');
            document.getElementById(button.dataset.tab).classList.add('active');
        });
    });
    const initialButton = document.querySelector(`.tab[data-tab="${initialTab}"]`);
    if (initialButton) initialButton.click();
});
</script>
@endsection