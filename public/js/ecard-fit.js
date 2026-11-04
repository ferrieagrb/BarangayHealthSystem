(function () {
    function fitCards() {
        document.querySelectorAll('.ecard-id-card-scroll[data-ecard-fit]').forEach(function (container) {
            const card = container.querySelector('.bheams-id-card');
            if (!card || container.clientWidth === 0) return;

            const currentZoom = parseFloat(window.getComputedStyle(card).zoom) || 1;
            const baseWidth = card.getBoundingClientRect().width / currentZoom;
            if (!baseWidth) return;

            const zoom = Math.min(1.3, container.clientWidth / baseWidth);
            card.style.zoom = String(zoom);
        });
    }

    function init() {
        fitCards();
        if ('ResizeObserver' in window) {
            const observer = new ResizeObserver(fitCards);
            document.querySelectorAll('.ecard-id-card-scroll[data-ecard-fit]').forEach(function (container) {
                observer.observe(container);
            });
        } else {
            window.addEventListener('resize', fitCards, { passive: true });
        }
        window.addEventListener('beforeprint', fitCards);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
