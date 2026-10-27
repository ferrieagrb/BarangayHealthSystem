<<<<<<< HEAD
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
=======

@extends('templates.superadmin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/superadmin/system_connections.css') }}">
@endsection

@section('content')
<div class="system-connections-container">

    {{-- Page Header --}}
    <div class="page-top">
        <div class="page-title-group">
            <h1>System Connections</h1>
            <p>
                Monitor the services and integrations used by the health
                information system.
            </p>
        </div>

        <button
            type="button"
            id="refreshConnections"
            class="connections-refresh-button"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 4v6h6M20 20v-6h-6M5.6 9a7 7 0 0112-2L20 10M4 14l2.4 3a7 7 0 0012-2">
                </path>
            </svg>
            <span>Refresh Status</span>
        </button>
    </div>

    {{-- Connection Summary --}}
    <div class="connections-summary">
        <div class="connections-last-checked">
            <span>Last checked:</span>
            <strong id="checkedAt">Checking...</strong>
        </div>

        <span
            id="connectionSummary"
            class="connections-summary-status is-loading"
            aria-live="polite"
        >
            Checking services...
        </span>
    </div>

    {{-- Connection Status Cards --}}
    <div
        id="connectionGrid"
        class="connections-grid"
        aria-live="polite"
    >
        <div class="connections-loading-card">
            <span class="connections-loading-spinner"></span>
            <p>Checking service connections...</p>
        </div>
    </div>

>>>>>>> 8f05883dd366c56d631103f280dc44da05bf5816
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('connectionGrid');
    const checkedAt = document.getElementById('checkedAt');
    const summary = document.getElementById('connectionSummary');
    const refreshButton = document.getElementById('refreshConnections');

<<<<<<< HEAD
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
=======
    let isRefreshing = false;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, character => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
>>>>>>> 8f05883dd366c56d631103f280dc44da05bf5816
        }[character]));
    }

    function render(data) {
<<<<<<< HEAD
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
=======
        const connections = Array.isArray(data.connections)
            ? data.connections
            : [];

        const healthy = connections.filter(
            item => item.state === 'healthy'
        ).length;

        const failed = connections.filter(
            item => item.state === 'failed'
        ).length;

        const warnings = connections.filter(
            item => item.state === 'warning'
        ).length;

        summary.textContent =
            `${healthy}/${connections.length} healthy` +
            (warnings ? ` · ${warnings} warning` : '') +
            (failed ? ` · ${failed} failed` : '');

        summary.className = 'connections-summary-status ' +
            (failed > 0
                ? 'is-failed'
                : warnings > 0
                    ? 'is-warning'
                    : connections.length > 0
                        ? 'is-healthy'
                        : 'is-neutral');

        const checkedDate = new Date(data.checked_at);

        checkedAt.textContent = data.checked_at &&
            !Number.isNaN(checkedDate.getTime())
                ? checkedDate.toLocaleString()
                : 'Unavailable';

        if (connections.length === 0) {
            grid.innerHTML = `
                <div class="connections-empty-state">
                    <div class="connections-empty-icon">—</div>
                    <h2>No connections found</h2>
                    <p>No service connection information is currently available.</p>
                </div>
            `;
            return;
        }

        const stateStyles = {
            healthy: {
                card: 'connection-healthy',
                badge: 'connection-badge-healthy',
                icon: '✓',
                label: 'Healthy'
            },
            warning: {
                card: 'connection-warning',
                badge: 'connection-badge-warning',
                icon: '!',
                label: 'Warning'
            },
            failed: {
                card: 'connection-failed',
                badge: 'connection-badge-failed',
                icon: '×',
                label: 'Failed'
            }
        };

        grid.innerHTML = connections.map(item => {
            const state = stateStyles[item.state] || {
                card: 'connection-unknown',
                badge: 'connection-badge-unknown',
                icon: '?',
                label: 'Unknown'
            };

            return `
                <article class="connection-card ${state.card}">
                    <div class="connection-card-header">
                        <h2 class="connection-card-title">
                            ${escapeHtml(item.name)}
                        </h2>

                        <span
                            class="connection-status-icon ${state.badge}"
                            title="${state.label}"
                            aria-label="${state.label}"
                        >
                            ${state.icon}
                        </span>
                    </div>

                    <div class="connection-card-status">
                        <span class="connection-status-indicator"></span>
                        ${escapeHtml(item.status)}
                    </div>

                    <p class="connection-card-detail">
                        ${escapeHtml(item.detail)}
                    </p>

                    <div class="connection-card-footer">
                        <span class="connection-state-label">
                            ${state.label}
                        </span>
                    </div>
                </article>
            `;
>>>>>>> 8f05883dd366c56d631103f280dc44da05bf5816
        }).join('');
    }

    async function refresh() {
<<<<<<< HEAD
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
=======
        if (isRefreshing) return;

        isRefreshing = true;
        refreshButton.disabled = true;
        refreshButton.classList.add('is-loading');

        const buttonText = refreshButton.querySelector('span');

        if (buttonText) {
            buttonText.textContent = 'Checking...';
        }

        summary.textContent = 'Checking services...';
        summary.className = 'connections-summary-status is-loading';

        try {
            const response = await fetch(
                '{{ route('superadmin.system.connections.status') }}',
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }
            );

            if (!response.ok) {
                throw new Error(
                    `Connection status request failed (${response.status}).`
                );
            }

            const data = await response.json();
            render(data);

        } catch (error) {
            console.error('Failed to refresh system connections:', error);

            summary.textContent = 'Unable to check services';
            summary.className =
                'connections-summary-status is-failed';

            grid.innerHTML = `
                <div class="connections-error-state">
                    <div class="connections-error-icon">!</div>
                    <h2>Connection check failed</h2>
                    <p>${escapeHtml(error.message)}</p>
                    <span>Please try refreshing the status.</span>
                </div>
            `;

        } finally {
            isRefreshing = false;
            refreshButton.disabled = false;
            refreshButton.classList.remove('is-loading');

            if (buttonText) {
                buttonText.textContent = 'Refresh Status';
            }
>>>>>>> 8f05883dd366c56d631103f280dc44da05bf5816
        }
    }

    refreshButton.addEventListener('click', refresh);
<<<<<<< HEAD
    refresh();
=======

    refresh();

>>>>>>> 8f05883dd366c56d631103f280dc44da05bf5816
    window.setInterval(refresh, 30000);
});
</script>
@endsection
