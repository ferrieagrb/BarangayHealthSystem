
@extends('templates.superadmin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/superadmin/system_analytics.css') }}">
@endsection

@section('content')
<div class="system-analytics-container">

    {{-- Page Header --}}
    <div class="page-top">
        <div class="page-title-group">
            <h1>System Analytics &amp; Infrastructure</h1>
            <p>Monitor platform usage, active sessions, and infrastructure health.</p>
        </div>
    </div>

    {{-- Section 1: Core System Overview --}}
    <section class="analytics-section">
        <div class="analytics-overview-grid">

            <div class="analytics-card overview-card">
                <div class="overview-card-content">
                    <p class="analytics-label">Total Registered Users</p>
                    <div class="analytics-stat-value" id="total-users-text">
                        Loading...
                    </div>
                </div>

                <div class="analytics-icon icon-blue" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                </div>
            </div>

            <div class="analytics-card overview-card">
                <div class="overview-card-content">
                    <p class="analytics-label">Active Users (Last 15m)</p>
                    <div class="analytics-stat-value stat-green" id="active-users-text">
                        Loading...
                    </div>
                </div>

                <div class="analytics-icon icon-green" aria-hidden="true">
                    <span class="status-pulse">
                        <span class="status-pulse-ring"></span>
                        <span class="status-pulse-dot"></span>
                    </span>
                </div>
            </div>

            <div class="analytics-card overview-card">
                <div class="overview-card-content">
                    <p class="analytics-label">Total Recorded Page Views</p>
                    <div class="analytics-stat-value" id="total-views-text">
                        Loading...
                    </div>
                </div>

                <div class="analytics-icon icon-purple" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </div>
            </div>

        </div>
    </section>

    {{-- Section 2: Infrastructure and Storage --}}
    <section class="analytics-section">
        <div class="infrastructure-grid">

            <div class="analytics-card infrastructure-card">
                <h3 class="analytics-label">Disk Storage (Hostinger)</h3>

                <div class="analytics-stat-value" id="disk-usage-text">
                    Loading...
                </div>

                <p class="analytics-muted-text" id="disk-subtext">
                    Calculating storage...
                </p>

                <div class="progress-track"
                    role="progressbar"
                    aria-label="Disk storage usage"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="0"
                    id="disk-progress-track">

                    <div class="progress-bar" id="disk-progress-bar"></div>
                </div>
            </div>

            <div class="analytics-card infrastructure-card">
                <h3 class="analytics-label">Database Size</h3>

                <div class="analytics-stat-value" id="db-size-text">
                    Loading...
                </div>

                <p class="analytics-muted-text">
                    MySQL on Hostinger Server
                </p>
            </div>

            <div class="analytics-card infrastructure-card">
                <h3 class="analytics-label">PHP Environment</h3>

                <div class="analytics-stat-value" id="php-version-text">
                    Loading...
                </div>

                <p class="environment-status">
                    <span class="environment-status-dot"></span>
                    Active &amp; Running
                </p>
            </div>

        </div>
    </section>

    {{-- Section 3: Analytics Lists and Demographics --}}
    <section class="analytics-section">
        <div class="analytics-details-grid">

            {{-- Top Visited Pages --}}
            <div class="analytics-card top-pages-card">
                <h3 class="analytics-card-title">Top Visited Pages</h3>

                <div class="top-pages-list" id="top-pages-list">
                    <p class="analytics-empty-message">
                        Loading top visited pages...
                    </p>
                </div>
            </div>

            {{-- Browser Demographics --}}
            <div class="analytics-card browser-card">
                <h3 class="analytics-card-title">User Browsers</h3>

                <div class="browser-demographics-list"
                    id="browser-demographics-list">

                    <p class="analytics-empty-message">
                        Fetching browser distribution...
                    </p>
                </div>
            </div>

        </div>
    </section>

    {{-- Section 4: Background Processing and Errors --}}
    <section class="analytics-section">
        <div class="analytics-card errors-card">
            <h3 class="analytics-card-title">
                Background Processing &amp; Errors
            </h3>

            <div class="failed-jobs-panel">
                <div class="failed-jobs-content">
                    <span class="failed-jobs-title">Failed Queue Jobs</span>

                    <p class="failed-jobs-description">
                        Unprocessed background tasks that require attention.
                    </p>
                </div>

                <span class="failed-jobs-badge" id="failed-jobs-badge">0</span>
            </div>
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    const numberFormat = new Intl.NumberFormat();

    function setText(id, value) {
        const element = document.getElementById(id);

        if (element) {
            element.textContent = value ?? 'N/A';
        }
    }

    function createElement(tag, className, textContent = '') {
        const element = document.createElement(tag);

        if (className) {
            element.className = className;
        }

        element.textContent = textContent;

        return element;
    }

    try {
        const response = await fetch('/superadmin/system-analytics-data');

        if (!response.ok) {
            throw new Error('Unable to fetch system analytics data.');
        }

        const data = await response.json();

        // Section 1: Core System Overview
        const records = data.records ?? {};

        setText(
            'total-users-text',
            numberFormat.format(Number(records.total_users) || 0)
        );

        setText(
            'active-users-text',
            `${numberFormat.format(Number(records.active_sessions) || 0)} Online`
        );

        setText(
            'total-views-text',
            numberFormat.format(Number(records.total_page_views) || 0)
        );

        // Section 2: Infrastructure and Storage
        const server = data.server ?? {};

        setText(
            'disk-usage-text',
            `${server.disk_used_text ?? 'N/A'} / ${server.disk_total_text ?? 'N/A'}`
        );

        const usagePercent = Math.min(
            100,
            Math.max(0, Number(server.disk_usage_percent) || 0)
        );

        setText(
            'disk-subtext',
            `${usagePercent}% of total capacity used`
        );

        const progressBar = document.getElementById('disk-progress-bar');
        const progressTrack = document.getElementById('disk-progress-track');

        if (progressBar) {
            progressBar.style.width = `${usagePercent}%`;
        }

        if (progressTrack) {
            progressTrack.setAttribute('aria-valuenow', usagePercent);
        }

        setText(
            'db-size-text',
            `${server.db_size_mb ?? 'N/A'} MB`
        );

        setText(
            'php-version-text',
            `PHP ${server.php_version ?? 'N/A'}`
        );

        // Section 3: Top Visited Pages
        const topPagesContainer = document.getElementById('top-pages-list');
        const topPages = Array.isArray(data.top_pages)
            ? data.top_pages
            : [];

        if (topPagesContainer) {
            topPagesContainer.replaceChildren();

            if (topPages.length > 0) {
                const list = createElement('div', 'top-pages-items');

                topPages.forEach((page, index) => {
                    let path = String(page.url ?? '/');

                    try {
                        path = new URL(
                            path,
                            window.location.origin
                        ).pathname;
                    } catch (error) {
                        path = '/';
                    }

                    const row = createElement('div', 'top-page-row');
                    const pageInfo = createElement('div', 'top-page-info');

                    const rank = createElement(
                        'span',
                        'top-page-rank',
                        `${index + 1}.`
                    );

                    const pagePath = createElement(
                        'span',
                        'top-page-path',
                        path
                    );

                    pagePath.title = String(page.url ?? path);

                    const views = createElement(
                        'span',
                        'top-page-views',
                        `${numberFormat.format(Number(page.total_views) || 0)} views`
                    );

                    pageInfo.append(rank, pagePath);
                    row.append(pageInfo, views);
                    list.appendChild(row);
                });

                topPagesContainer.appendChild(list);
            } else {
                topPagesContainer.appendChild(
                    createElement(
                        'p',
                        'analytics-empty-message',
                        'No page view records logged yet.'
                    )
                );
            }
        }

        // Section 3: Browser Demographics
        const browserContainer = document.getElementById(
            'browser-demographics-list'
        );

        const demographics = Array.isArray(data.demographics)
            ? data.demographics
            : [];

        if (browserContainer) {
            browserContainer.replaceChildren();

            if (demographics.length > 0) {
                demographics.forEach(browser => {
                    const row = createElement(
                        'div',
                        'browser-demographics-row'
                    );

                    const name = createElement(
                        'span',
                        'browser-name',
                        browser.browser || 'Unknown'
                    );

                    const visits = createElement(
                        'span',
                        'browser-visits',
                        `${numberFormat.format(Number(browser.total) || 0)} visits`
                    );

                    row.append(name, visits);
                    browserContainer.appendChild(row);
                });
            } else {
                browserContainer.appendChild(
                    createElement(
                        'p',
                        'analytics-empty-message',
                        'No browser tracking logs found yet.'
                    )
                );
            }
        }

        // Section 4: Failed Queue Jobs
        const errors = data.errors ?? {};

        setText(
            'failed-jobs-badge',
            numberFormat.format(Number(errors.failed_jobs) || 0)
        );

    } catch (error) {
        console.error('Failed to load system analytics:', error);

        setText('total-users-text', 'Unavailable');
        setText('active-users-text', 'Unavailable');
        setText('total-views-text', 'Unavailable');
        setText('disk-usage-text', 'Unavailable');
        setText('db-size-text', 'Unavailable');
        setText('php-version-text', 'Unavailable');

        const topPagesContainer = document.getElementById('top-pages-list');
        const browserContainer = document.getElementById(
            'browser-demographics-list'
        );

        if (topPagesContainer) {
            topPagesContainer.replaceChildren(
                createElement(
                    'p',
                    'analytics-error-message',
                    'Unable to load top visited pages.'
                )
            );
        }

        if (browserContainer) {
            browserContainer.replaceChildren(
                createElement(
                    'p',
                    'analytics-error-message',
                    'Unable to load browser demographics.'
                )
            );
        }
    }
});
</script>
@endsection
