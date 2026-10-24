
@extends('templates.superadmin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/superadmin/superadmin_audit_trail.css') }}">
@endsection

@section('content')
<div class="audit-trail-container">

    {{-- Page Header --}}
    <div class="audit-page-header">
        <div class="audit-page-title-group">
            <h1>Superadmin Audit Trail</h1>
            <p>
                Dedicated governance, security, and administrative action logs
                for the barangay health system.
            </p>
        </div>

        <div class="audit-header-actions">
            <a
                href="{{ route('superadmin.system.analytics.page') }}"
                class="audit-back-button"
            >
                <span aria-hidden="true">&larr;</span>
                Back to System Analytics
            </a>

            <span class="audit-mode-badge">
                Superadmin Mode
            </span>
        </div>
    </div>

    {{-- Main Audit Trail Card --}}
    <div class="audit-main-card">

        {{-- Card Header --}}
        <div class="audit-card-header">
            <div class="audit-card-heading">
                <h2>System Activity Logs</h2>
                <p>
                    Chronological history of all high-level superadmin
                    and administrative changes.
                </p>
            </div>

            <span class="audit-oversight-badge">
                Strict Oversight
            </span>
        </div>

        {{-- Audit Log Filters --}}
        <form method="GET" class="audit-filter-form">

            <div class="audit-filter-grid">

                <label class="audit-filter-field">
                    <span>Source</span>

                    <select name="source" class="audit-form-control">
                        <option value="all" {{ request('source', 'all') === 'all' ? 'selected' : '' }}>
                            All logs
                        </option>

                        <option value="authentication" {{ request('source') === 'authentication' ? 'selected' : '' }}>
                            Authentication / Security
                        </option>

                        <option value="administrative" {{ request('source') === 'administrative' ? 'selected' : '' }}>
                            Administrative
                        </option>
                    </select>
                </label>

                <label class="audit-filter-field">
                    <span>Category</span>

                    <select name="category" class="audit-form-control">
                        <option value="all" {{ request('category', 'all') === 'all' ? 'selected' : '' }}>
                            All categories
                        </option>

                        <option value="successful_logins" {{ request('category') === 'successful_logins' ? 'selected' : '' }}>
                            Successful logins
                        </option>

                        <option value="failed_logins" {{ request('category') === 'failed_logins' ? 'selected' : '' }}>
                            Failed logins
                        </option>

                        <option value="invalid_login_submissions" {{ request('category') === 'invalid_login_submissions' ? 'selected' : '' }}>
                            Invalid login submissions
                        </option>

                        <option value="rate_limited_attempts" {{ request('category') === 'rate_limited_attempts' ? 'selected' : '' }}>
                            Rate-limited attempts
                        </option>

                        <option value="locked_accounts" {{ request('category') === 'locked_accounts' ? 'selected' : '' }}>
                            Locked accounts
                        </option>

                        <option value="blocked_logins" {{ request('category') === 'blocked_logins' ? 'selected' : '' }}>
                            Blocked logins
                        </option>

                        <option value="logout_events" {{ request('category') === 'logout_events' ? 'selected' : '' }}>
                            Logout events
                        </option>

                        <option value="administrative_changes" {{ request('category') === 'administrative_changes' ? 'selected' : '' }}>
                            Administrative changes
                        </option>

                        <option value="user_management" {{ request('category') === 'user_management' ? 'selected' : '' }}>
                            User creation/deletion
                        </option>

                        <option value="account_unlocks" {{ request('category') === 'account_unlocks' ? 'selected' : '' }}>
                            Account unlocks
                        </option>

                        <option value="permission_changes" {{ request('category') === 'permission_changes' ? 'selected' : '' }}>
                            Permission changes
                        </option>

                        <option value="import_export_events" {{ request('category') === 'import_export_events' ? 'selected' : '' }}>
                            Import/export events
                        </option>
                    </select>
                </label>

                <label class="audit-filter-field">
                    <span>From</span>

                    <input
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="audit-form-control"
                    >
                </label>

                <label class="audit-filter-field">
                    <span>To</span>

                    <input
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="audit-form-control"
                    >
                </label>

            </div>

            <div class="audit-filter-actions">
                <button type="submit" class="audit-filter-button">
                    Filter Logs
                </button>

                <a
                    href="{{ route('superadmin.audit-logs') }}"
                    class="audit-clear-button"
                >
                    Clear
                </a>
            </div>
        </form>

        {{-- Audit Logs Table --}}
        <div class="audit-table-responsive">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Superadmin</th>
                        <th>Source</th>
                        <th>Action Event</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($auditLogs as $log)
                        <tr>
                            <td class="audit-timestamp">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i:s A') }}
                            </td>

                            <td class="audit-admin-cell">
                                <div class="audit-admin-name">
                                    {{ $log->admin_name ?? 'Unknown / System' }}
                                </div>

                                @if($log->admin_email)
                                    <span class="audit-admin-email">
                                        {{ $log->admin_email }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="audit-source-badge">
                                    {{ $log->source ?? 'System' }}
                                </span>
                            </td>

                            <td>
                                <span class="audit-action-badge">
                                    {{ $log->action }}
                                </span>
                            </td>

                            <td class="audit-description">
                                {{ $log->description }}
                            </td>

                            <td class="audit-ip-address">
                                {{ $log->ip_address ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="audit-empty-cell">
                                <div class="audit-empty-state">
                                    <span class="audit-empty-icon" aria-hidden="true">
                                        —
                                    </span>

                                    <p>No administrative actions logged yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($auditLogs, 'links'))
            <div class="audit-pagination">
                {{ $auditLogs->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
