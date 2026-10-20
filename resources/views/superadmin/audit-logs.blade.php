@extends('templates.superadmin')

@section('content')
<div class="w-full">
    <div class="page-top">
        <div class="page-title-group">
            <h1>Superadmin Audit Trail</h1>
            <p>Dedicated governance, security, and administrative action logs for the barangay health system.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('superadmin.system.analytics.page') }}" class="px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition">
                &larr; Back to System Analytics
            </a>
            <span class="px-3 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-full border border-red-200">
                Superadmin Mode
            </span>
        </div>
    </div>

    <!-- Main Card -->
    <div class="w-full bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-800">System Activity Logs</h3>
                <p class="text-xs text-gray-400">Chronological history of all high-level superadmin and administrative changes.</p>
            </div>
            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-md font-semibold text-xs border border-amber-200">
                Strict Oversight
            </span>
        </div>

        <form method="GET" class="p-5 border-b border-gray-100 bg-gray-50/50 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <label class="text-xs font-semibold text-gray-600">Source
                <select name="source" class="mt-1 w-full border-gray-300 rounded-md text-sm">
                    <option value="all" {{ request('source', 'all') === 'all' ? 'selected' : '' }}>All logs</option>
                    <option value="authentication" {{ request('source') === 'authentication' ? 'selected' : '' }}>Authentication / Security</option>
                    <option value="administrative" {{ request('source') === 'administrative' ? 'selected' : '' }}>Administrative</option>
                </select>
            </label>
            <label class="text-xs font-semibold text-gray-600">Category
                <select name="category" class="mt-1 w-full border-gray-300 rounded-md text-sm">
                    <option value="all" {{ request('category', 'all') === 'all' ? 'selected' : '' }}>All categories</option>
                    <option value="successful_logins" {{ request('category') === 'successful_logins' ? 'selected' : '' }}>Successful logins</option>
                    <option value="failed_logins" {{ request('category') === 'failed_logins' ? 'selected' : '' }}>Failed logins</option>
                    <option value="invalid_login_submissions" {{ request('category') === 'invalid_login_submissions' ? 'selected' : '' }}>Invalid login submissions</option>
                    <option value="rate_limited_attempts" {{ request('category') === 'rate_limited_attempts' ? 'selected' : '' }}>Rate-limited attempts</option>
                    <option value="locked_accounts" {{ request('category') === 'locked_accounts' ? 'selected' : '' }}>Locked accounts</option>
                    <option value="blocked_logins" {{ request('category') === 'blocked_logins' ? 'selected' : '' }}>Blocked logins</option>
                    <option value="logout_events" {{ request('category') === 'logout_events' ? 'selected' : '' }}>Logout events</option>
                    <option value="administrative_changes" {{ request('category') === 'administrative_changes' ? 'selected' : '' }}>Administrative changes</option>
                    <option value="user_management" {{ request('category') === 'user_management' ? 'selected' : '' }}>User creation/deletion</option>
                    <option value="account_unlocks" {{ request('category') === 'account_unlocks' ? 'selected' : '' }}>Account unlocks</option>
                    <option value="permission_changes" {{ request('category') === 'permission_changes' ? 'selected' : '' }}>Permission changes</option>
                    <option value="import_export_events" {{ request('category') === 'import_export_events' ? 'selected' : '' }}>Import/export events</option>
                </select>
            </label>
            <label class="text-xs font-semibold text-gray-600">From
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 w-full border-gray-300 rounded-md text-sm">
            </label>
            <label class="text-xs font-semibold text-gray-600">To
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 w-full border-gray-300 rounded-md text-sm">
            </label>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-xs font-semibold">Filter Logs</button>
                <a href="{{ route('superadmin.audit-logs') }}" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-md text-xs font-semibold">Clear</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Timestamp</th>
                        <th class="py-3.5 px-6">Superadmin</th>
                        <th class="py-3.5 px-6">Source</th>
                        <th class="py-3.5 px-6">Action Event</th>
                        <th class="py-3.5 px-6">Description</th>
                        <th class="py-3.5 px-6">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-4 px-6 text-xs font-mono text-gray-500 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i:s A') }}
                            </td>
                            <td class="py-4 px-6 font-medium text-gray-900">
                                {{ $log->admin_name ?? 'Unknown / System' }}
                                @if($log->admin_email)<span class="block text-xs text-gray-400 font-normal">{{ $log->admin_email }}</span>@endif
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded font-mono text-xs border border-blue-200">{{ $log->source ?? 'System' }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-800 rounded font-mono text-xs border border-gray-200">{{ $log->action }}</span>
                            </td>
                            <td class="py-4 px-6 text-gray-600">{{ $log->description }}</td>
                            <td class="py-4 px-6 text-xs font-mono text-gray-400">{{ $log->ip_address ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400 text-sm">
                                <p>No administrative actions logged yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Section -->
        @if(method_exists($auditLogs, 'links'))
            <div class="p-6 border-t border-gray-100 bg-gray-50/30">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection