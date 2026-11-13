document.addEventListener('DOMContentLoaded', async function () {

    // User Growth Analytics
    try {
        const userResponse = await fetch('/superadmin/user-analytics');

        if (!userResponse.ok) {
            throw new Error('Unable to fetch user analytics.');
        }

        const userData = await userResponse.json();

        const userOptions = {
            series: [{
                name: 'Added Users',
                data: userData.map(item => Number(item.count) || 0)
            }],
            chart: {
                type: 'area',
                width: '100%',
                height: '100%',
                toolbar: { show: true }
            },
            grid: {
                padding: {
                    left: 5,
                    right: 5,
                    top: 0,
                    bottom: 0
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            xaxis: {
                categories: userData.map(item => item.date),
                type: 'category',
                labels: {
                    style: { fontSize: '11px' }
                }
            },
            yaxis: {
                min: 0,
                tickAmount: 5
            },
            colors: ['#3b82f6'],
            title: {
                text: '',
                align: 'left'
            },
            noData: {
                text: 'No user analytics available'
            }
        };

        const userChart = new ApexCharts(
            document.querySelector('#userAnalyticsApexChart'),
            userOptions
        );

        userChart.render();

    } catch (error) {
        console.error('Failed to load user analytics:', error);
    }

    // Website Traffic and Performance
    try {
        const perfResponse = await fetch('/superadmin/website-performance');

        if (!perfResponse.ok) {
            throw new Error('Unable to fetch website performance.');
        }

        const perfData = await perfResponse.json();

        const perfOptions = {
            series: [
                {
                    name: 'Page Views',
                    data: perfData.map(item => Number(item.views) || 0)
                },
                {
                    name: 'Avg Load Time (ms)',
                    data: perfData.map(
                        item => Number(item.avg_load_time) || 0
                    )
                }
            ],
            chart: {
                type: 'bar',
                height: '100%',
                width: '100%',
                toolbar: { show: true }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 3
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: perfData.map(item => item.date),
                labels: {
                    style: { fontSize: '11px' }
                }
            },
            yaxis: [
                {
                    title: { text: 'Views' },
                    labels: {
                        style: { fontSize: '11px' }
                    }
                },
                {
                    opposite: true,
                    title: { text: 'Load Time (ms)' },
                    labels: {
                        style: { fontSize: '11px' }
                    }
                }
            ],
            fill: { opacity: 1 },
            colors: ['#10b981', '#f59e0b'],
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            noData: {
                text: 'No website performance data available'
            }
        };

        const perfChart = new ApexCharts(
            document.querySelector('#websitePerformanceChart'),
            perfOptions
        );

        perfChart.render();

    } catch (error) {
        console.error('Failed to load website performance data:', error);
    }

    // Refresh Active Sessions Every 10 Seconds
    async function updateActiveSessions() {
        try {
            const response = await fetch('/superadmin/active-sessions-json');

            if (!response.ok) {
                throw new Error('Unable to fetch active sessions.');
            }

            const data = await response.json();

            const badge = document.querySelector(
                '#active-session-count-badge'
            );

            if (badge) {
                badge.textContent = `${data.count ?? 0} Online Now`;
            }

            const tbody = document.querySelector(
                '#active-sessions-table-body'
            );

            if (!tbody) return;

            if (!data.sessions || data.sessions.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="empty-table">
                            No active sessions found.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.replaceChildren();

            data.sessions.forEach(session => {
                const row = document.createElement('tr');

                const userCell = document.createElement('td');

                const userName = document.createElement('div');
                userName.className = 'table-user-name';
                userName.textContent = session.name ?? 'Unknown user';

                const userEmail = document.createElement('div');
                userEmail.className = 'table-user-email';
                userEmail.textContent = session.email ?? '';

                userCell.append(userName, userEmail);

                const locationCell = document.createElement('td');
                locationCell.className = 'table-subtext';
                locationCell.textContent =
                    session.location ?? 'Location unavailable';

                const activityCell = document.createElement('td');
                activityCell.className = 'table-subtext';
                activityCell.textContent = session.last_activity ?? 'Unknown';

                row.append(userCell, locationCell, activityCell);
                tbody.appendChild(row);
            });

        } catch (error) {
            console.error(
                'Failed to fetch real-time active sessions:',
                error
            );
        }
    }

    updateActiveSessions();
    setInterval(updateActiveSessions, 10000);
});
