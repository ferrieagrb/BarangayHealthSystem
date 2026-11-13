document.querySelectorAll('.ann-card[href]').forEach((link) => {
    link.addEventListener('click', () => {
        const destination = new URL(link.href, window.location.href);
        destination.searchParams.set('scroll', String(Math.round(window.scrollY)));
        link.href = destination.toString();
    });
});

window.addEventListener('pageshow', () => {
    const scroll = new URLSearchParams(window.location.search).get('scroll');
    if (scroll && /^\d+$/.test(scroll)) {
        window.requestAnimationFrame(() => window.scrollTo(0, Number(scroll)));
    }
});
