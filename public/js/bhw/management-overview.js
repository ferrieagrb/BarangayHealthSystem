document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') return;

    document.querySelectorAll('[data-overview-chart]').forEach(function (element) {
        const categories = JSON.parse(element.dataset.chartLabels || '[]');
        const values = JSON.parse(element.dataset.chartValues || '[]').map(Number);
        if (!values.length) return;

        // Keep subgroup names readable when an assigned area has many groups.
        if (categories.length > 8) element.style.minWidth = `${categories.length * 90}px`;

        const palette = getComputedStyle(document.documentElement);
        const options = {
            series: [{ name: 'Count', data: values }],
            chart: {
                type: 'bar', height: 280, width: '100%', parentHeightOffset: 0,
                fontFamily: 'Poppins, sans-serif',
                toolbar: { show: false }
            },
            responsive: [{ breakpoint: 600, options: { chart: { height: 240 } } }],
            plotOptions: {
                bar: { borderRadius: 4, distributed: true, columnWidth: `${Math.min(45, categories.length * 15)}%` }
            },
            colors: ['#3b82f6', '#14b8a6', '#f97316'],
            dataLabels: {
                enabled: true,
                formatter: value => Math.floor(value),
                style: { fontSize: '13px', fontWeight: 600, colors: ['#0f172a'] },
                offsetY: -4
            },
            legend: { show: false },
            xaxis: {
                categories,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { fontSize: '12px', colors: palette.getPropertyValue('--theme-muted').trim() } }
            },
            yaxis: {
                min: 0,
                tickAmount: 3,
                labels: {
                    style: { fontSize: '12px', colors: palette.getPropertyValue('--theme-muted').trim() },
                    formatter: value => Math.floor(value)
                }
            },
            grid: {
                borderColor: palette.getPropertyValue('--theme-border').trim(),
                strokeDashArray: 4,
                xaxis: { lines: { show: false } }
            }
        };

        element.replaceChildren();
        new ApexCharts(element, options).render();
    });
});
