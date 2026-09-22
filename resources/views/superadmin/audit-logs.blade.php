@extends('templates.superadmin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Superadmin Audit Trail</h1>
            <p class="text-xs text-gray-500 mt-1">Dedicated governance, security, and administrative action logs for the barangay health system.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('superadmin.analytics') }}" class="px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition">
                &larr; Back to System Analytics
            </a>
            <span class="px-3 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-full border border-red-200">
                Superadmin Mode
            </span>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-800">System Activity Logs</h3>
                <p class="text-xs text-gray-400">Chronological history of all high-level superadmin and administrative changes.</p>
            </div>
            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-md font-semibold text-xs border border-amber-200">
                Strict Oversight
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Timestamp</th>
                        <th class="py-3.5 px-6">Superadmin</th>
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
                                {{ $log->admin_name }}
                                <span class="block text-xs text-gray-400 font-normal">{{ $log->admin_email }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-800 rounded font-mono text-xs border border-gray-200">{{ $log->action }}</span>
                            </td>
                            <td class="py-4 px-6 text-gray-600">{{ $log->description }}</td>
                            <td class="py-4 px-6 text-xs font-mono text-gray-400">{{ $log->ip_address ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400 text-sm">
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