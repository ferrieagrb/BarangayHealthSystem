@extends('templates.admin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/logs.css') }}">
<style>
    .admin-logs-page { padding-bottom: 24px; }
    .tabs { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0; }
    .tabs button { padding: 9px 13px; border: 1px solid #dbe3ed; border-radius: 8px; background: #f8fafc; color: #334155; cursor: pointer; }
    .tabs button.active { border-color: #0e42b1; background: #0e42b1; color: #fff; }
    .tab-content { display: none; margin-top: 20px; }
    .tab-content.active { display: block; }
    .admin-log-table { width: 100%; border-collapse: collapse; background: #fff; }
    .admin-log-table th, .admin-log-table td { padding: 11px 10px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
    .admin-log-table th { color: #64748b; font-size: 11px; text-transform: uppercase; }
    .tag { display: inline-block; padding: 4px 8px; border-radius: 6px; background: #0e42b1; color: #fff; font-size: 12px; }
    .pagination-container { margin-top: 18px; }
    .pagination-container nav > div:first-child { display: none; }
    .pagination-container nav > div:last-child { display: flex; justify-content: center; }
    .pagination-container nav ul { display: flex; gap: 5px; margin: 0; padding: 0; list-style: none; }
    .pagination-container .page-link { display: block; padding: 7px 11px; border: 1px solid #dbe3ed; border-radius: 6px; color: #334155; background: #fff; text-decoration: none; }
    .pagination-container .page-item.active .page-link { border-color: #0e42b1; background: #0e42b1; color: #fff; }
    .pagination-container .page-item.disabled .page-link { color: #94a3b8; background: #f8fafc; }
    .empty-log { padding: 22px; color: #64748b; text-align: center; }
</style>
@endsection

@section('content')
<div class="admin-logs-page">
    <div class="page-top"><div class="page-title"><h1>System Audit Logs</h1><p>Review administrative actions and activity across system features.</p></div></div>

    <div class="tabs">
        <button class="tab active" data-tab="adminActivity">Admin Activity</button>
        <button class="tab" data-tab="security">Security &amp; Errors</button>
        <button class="tab" data-tab="citizens">Citizens</button>
        <button class="tab" data-tab="supplies">Supplies</button>
        <button class="tab" data-tab="health">Health</button>
        <button class="tab" data-tab="vehicle">Vehicles</button>
        <button class="tab" data-tab="announcements">Announcements</button>
        <button class="tab" data-tab="events">Calendar</button>
        <button class="tab" data-tab="referrals">Referrals</button>
        <button class="tab" data-tab="maintenance">Maintenance</button>
        <button class="tab" data-tab="releases">Batch Releases</button>
    </div>

    <div id="adminActivity" class="tab-content active">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Scope</th><th>Action</th><th>Description</th><th>IP</th></tr></thead><tbody>
            @forelse($adminActivityLogs as $log)<tr><td>{{ $log->created_at }}</td><td>{{ $log->actor_name ?? 'System' }}</td><td>{{ ucfirst($log->scope) }}</td><td><span class="tag">{{ $log->action }}</span></td><td>{{ $log->description }}</td><td>{{ $log->ip_address ?? 'N/A' }}</td></tr>@empty<tr><td colspan="6" class="empty-log">No administrative activity recorded.</td></tr>@endforelse
        </tbody></table>
        <div class="pagination-container">{{ $adminActivityLogs->appends(array_merge(request()->query(), ['tab' => 'adminActivity']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="security" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Event</th><th>Description</th><th>IP</th></tr></thead><tbody>
            @forelse($securityLogs as $log)<tr><td>{{ $log->created_at }}</td><td>{{ $log->actor_name ?? 'Unauthenticated / System' }}</td><td><span class="tag">{{ $log->action }}</span></td><td>{{ $log->description }}</td><td>{{ $log->ip_address ?? 'N/A' }}</td></tr>@empty<tr><td colspan="5" class="empty-log">No security or exception events.</td></tr>@endforelse
        </tbody></table>
        <div class="pagination-container">{{ $securityLogs->appends(array_merge(request()->query(), ['tab' => 'security']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="citizens" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Action</th><th>Citizen</th><th>Description</th></tr></thead><tbody>
            @forelse($citizenLogs as $log)<tr><td>{{ $log->created_at }}</td><td>{{ $log->user->name ?? 'Legacy / System' }}</td><td>{{ ucfirst($log->action) }}</td><td>{{ $log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A' }}</td><td>{{ $log->description }}</td></tr>@empty<tr><td colspan="5" class="empty-log">No citizen logs.</td></tr>@endforelse
        </tbody></table><div class="pagination-container">{{ $citizenLogs->appends(array_merge(request()->query(), ['tab' => 'citizens']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="supplies" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Action</th><th>Supply</th><th>Quantity</th><th>Citizen</th></tr></thead><tbody>
            @forelse($supplyLogs as $log)<tr><td>{{ $log->created_at }}</td><td>{{ $log->user->name ?? 'Legacy / System' }}</td><td>{{ ucfirst($log->action) }}</td><td>{{ $log->supply->name ?? 'Removed supply' }}</td><td>{{ $log->quantity }}</td><td>{{ $log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'General inventory' }}</td></tr>@empty<tr><td colspan="6" class="empty-log">No supply logs.</td></tr>@endforelse
        </tbody></table><div class="pagination-container">{{ $supplyLogs->appends(array_merge(request()->query(), ['tab' => 'supplies']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="health" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Action</th><th>Citizen ID</th><th>Description</th></tr></thead><tbody>
            @forelse($healthRecordLogs as $log)<tr><td>{{ $log->created_at }}</td><td>{{ $log->user->name ?? 'Legacy / System' }}</td><td>{{ ucfirst($log->action) }}</td><td>{{ $log->citizen_id ?? 'N/A' }}</td><td>{{ $log->description }}</td></tr>@empty<tr><td colspan="5" class="empty-log">No health logs.</td></tr>@endforelse
        </tbody></table><div class="pagination-container">{{ $healthRecordLogs->appends(array_merge(request()->query(), ['tab' => 'health']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="vehicle" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Vehicle</th><th>Borrower</th><th>Borrowed At</th></tr></thead><tbody>
            @forelse($vehicleLogs as $log)<tr><td>{{ $log->created_at }}</td><td>{{ $log->user->name ?? 'Legacy / System' }}</td><td>{{ $log->vehicle_name }}</td><td>{{ $log->borrower }}</td><td>{{ $log->borrowed_at }}</td></tr>@empty<tr><td colspan="5" class="empty-log">No vehicle logs.</td></tr>@endforelse
        </tbody></table><div class="pagination-container">{{ $vehicleLogs->appends(array_merge(request()->query(), ['tab' => 'vehicle']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="announcements" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Title</th><th>Category</th></tr></thead><tbody>
            @forelse($announcementLogs as $log)<tr><td>{{ $log->created_at }}</td><td>{{ $log->user->name ?? 'Legacy / System' }}</td><td>{{ $log->title }}</td><td>{{ $log->category ?? 'General' }}</td></tr>@empty<tr><td colspan="4" class="empty-log">No announcements.</td></tr>@endforelse
        </tbody></table><div class="pagination-container">{{ $announcementLogs->appends(array_merge(request()->query(), ['tab' => 'announcements']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="events" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Event</th><th>Details</th></tr></thead><tbody>
            @forelse($eventLogs as $log)<tr><td>{{ \Carbon\Carbon::parse($log->start)->format('M d, Y') }}</td><td>{{ $log->user->name ?? 'Legacy / System' }}</td><td>{{ $log->title }}</td><td>{{ $log->description ?? 'No details' }}</td></tr>@empty<tr><td colspan="4" class="empty-log">No events.</td></tr>@endforelse
        </tbody></table><div class="pagination-container">{{ $eventLogs->appends(array_merge(request()->query(), ['tab' => 'events']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="referrals" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Patient</th><th>Request</th><th>Status</th></tr></thead><tbody>
            @forelse($referralLogs as $log)<tr><td>{{ $log->date_of_referral }}</td><td>{{ $log->user->name ?? 'Legacy / System' }}</td><td>{{ $log->name }}</td><td>{{ $log->requests_for }}</td><td>{{ ucfirst($log->status ?? 'pending') }}</td></tr>@empty<tr><td colspan="5" class="empty-log">No referrals.</td></tr>@endforelse
        </tbody></table><div class="pagination-container">{{ $referralLogs->appends(array_merge(request()->query(), ['tab' => 'referrals']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="maintenance" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Date</th><th>Actor</th><th>Citizen</th><th>Medicine</th><th>Schedule</th></tr></thead><tbody>
            @forelse($maintenanceLogs as $log)<tr><td>{{ $log->created_at }}</td><td>{{ $log->user->name ?? 'Legacy / System' }}</td><td>{{ $log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A' }}</td><td>{{ $log->medicine_name }}</td><td>{{ $log->start_date->format('M d, Y') }}{{ $log->end_date ? ' - ' . $log->end_date->format('M d, Y') : ' - Ongoing' }}</td></tr>@empty<tr><td colspan="5" class="empty-log">No maintenance records.</td></tr>@endforelse
        </tbody></table><div class="pagination-container">{{ $maintenanceLogs->appends(array_merge(request()->query(), ['tab' => 'maintenance']))->links('pagination::bootstrap-5') }}</div>
    </div>

    <div id="releases" class="tab-content">
        <table class="admin-log-table"><thead><tr><th>Claim Date</th><th>Actor</th><th>Batch</th><th>Citizen</th><th>Supply</th><th>Status</th></tr></thead><tbody>
            @forelse($releaseLogs as $log)<tr><td>{{ $log->claim_date->format('M d, Y') }}</td><td>{{ $log->user->name ?? 'Legacy / System' }}</td><td>{{ $log->batch_title }}</td><td>{{ $log->citizen ? trim($log->citizen->Citizen_FName . ' ' . $log->citizen->Citizen_LName) : 'N/A' }}</td><td>{{ $log->supply->name ?? 'Unavailable' }}</td><td>{{ ucfirst($log->status) }}</td></tr>@empty<tr><td colspan="6" class="empty-log">No batch releases.</td></tr>@endforelse
        </tbody></table><div class="pagination-container">{{ $releaseLogs->appends(array_merge(request()->query(), ['tab' => 'releases']))->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const initialTab = @json(request('tab', 'adminActivity'));
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