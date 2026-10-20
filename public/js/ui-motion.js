(function () {
    'use strict';

    function animateCounters() {
        const counters = document.querySelectorAll(
            '.summary-card strong, .admin-metric-card h2'
        );

        counters.forEach(function (counter) {
            if (counter.dataset.motionCounted === 'true') return;

            const text = counter.textContent.trim();
            const match = text.match(/^(\D*)(-?[\d,]+(?:\.\d+)?)(\D*)$/);
            if (!match) return;

            const target = Number(match[2].replace(/,/g, ''));
            if (!Number.isFinite(target)) return;

            counter.dataset.motionCounted = 'true';
            const prefix = match[1];
            const suffix = match[3];
            const startedAt = performance.now();
            const duration = 750;

            function tick(now) {
                const progress = Math.min((now - startedAt) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                counter.textContent = prefix + Math.round(target * eased).toLocaleString() + suffix;

                if (progress < 1) requestAnimationFrame(tick);
            }

            counter.textContent = prefix + '0' + suffix;
            requestAnimationFrame(tick);
        });
    }

    function revealContent() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const revealTargets = document.querySelectorAll(
            '.summary-card, .admin-metric-card, .report-card, .report-panel, .dashboard-card, .list-item, .announcement-item, table tbody tr'
        );

        if (!('IntersectionObserver' in window)) return;

        const observer = new IntersectionObserver(function (entries, currentObserver) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('motion-visible');
                currentObserver.unobserve(entry.target);
            });
        }, { threshold: 0.08 });

        revealTargets.forEach(function (element, index) {
            if (element.closest('[role="dialog"], .modal-overlay, .supplies-modal-overlay')) return;
            element.classList.add('motion-reveal');
            element.style.setProperty('--motion-delay', `${Math.min(index * 45, 360)}ms`);
            observer.observe(element);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        animateCounters();
        revealContent();
    });
})();