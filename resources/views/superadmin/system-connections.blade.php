@extends('templates.superadmin')

@section('content')
<div class="w-full">
    <div class="page-top">
        <div class="page-title-group">
            <h1>System Connections</h1>
            <p>Monitor the services and integrations used by the health information system.</p>
        </div>
        <button type="button" id="refreshConnections" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Refresh Status</button>
    </div>

    <div class="mb-5 flex items-center justify-between gap-4 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-600 shadow-sm">
        <span>Last checked: <strong id="checkedAt">Checking...</strong></span>
        <span id="connectionSummary" class="font-semibold">Checking services...</span>
    </div>

    <div id="connectionGrid" class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4"></div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('connectionGrid');
    const checkedAt = document.getElementById('checkedAt');
    const summary = document.getElementById('connectionSummary');
    const refreshButton = document.getElementById('refreshConnections');

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[character]));
    }

    function render(data) {
        const connections = data.connections || [];
        const healthy = connections.filter(item => item.state === 'healthy').length;
        const failed = connections.filter(item => item.state === 'failed').length;
        summary.textContent = `${healthy}/${connections.length} healthy${failed ? ` · ${failed} failed` : ''}`;
        summary.className = failed ? 'font-semibold text-red-600' : 'font-semibold text-emerald-600';
        checkedAt.textContent = new Date(data.checked_at).toLocaleString();

        grid.innerHTML = connections.map(item => {
            const styles = {
                healthy: { border: 'border-emerald-200', badge: 'bg-emerald-100 text-emerald-700', icon: '✓' },
                warning: { border: 'border-amber-200', badge: 'bg-amber-100 text-amber-700', icon: '!' },
                failed: { border: 'border-red-200', badge: 'bg-red-100 text-red-700', icon: '×' }
            }[item.state] || { border: 'border-gray-200', badge: 'bg-gray-100 text-gray-700', icon: '?' };

            return `<article class="rounded-xl border ${styles.border} bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-semibold text-gray-800">${escapeHtml(item.name)}</h2>
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full ${styles.badge} font-bold">${styles.icon}</span>
                </div>
                <p class="mt-3 text-sm font-semibold text-gray-700">${escapeHtml(item.status)}</p>
                <p class="mt-1 text-sm leading-6 text-gray-500">${escapeHtml(item.detail)}</p>
            </article>`;
        }).join('');
    }

    async function refresh() {
        refreshButton.disabled = true;
        refreshButton.textContent = 'Checking...';
        try {
            const response = await fetch('{{ route('superadmin.system.connections.status') }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('Connection status request failed.');
            render(await response.json());
        } catch (error) {
            summary.textContent = error.message;
            summary.className = 'font-semibold text-red-600';
        } finally {
            refreshButton.disabled = false;
            refreshButton.textContent = 'Refresh Status';
        }
    }

    refreshButton.addEventListener('click', refresh);
    refresh();
    window.setInterval(refresh, 30000);
});
</script>
@endsection
