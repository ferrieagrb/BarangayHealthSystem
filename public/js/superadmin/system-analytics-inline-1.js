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
