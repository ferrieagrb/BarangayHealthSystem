// The BHW mobile rail uses .collapsed for its OPEN drawer. Do not change the
// existing desktop toggle or navigation handlers.
(() => {
    const sidebar = document.getElementById('bhwSidebar');
    if (!sidebar) return;
    const media = window.matchMedia('(max-width: 768px)');
    const main = sidebar.parentElement.querySelector('.main-content');
    const toggle = sidebar.querySelector('.topbutton');
    let backdrop;
    let locked = false;
    let priorInert = false;
    let savedScroll = 0;
    let savedPosition;

    function closeDrawer(restoreFocus = false) {
        if (!media.matches) return;
        sidebar.classList.remove('collapsed');
        toggle.setAttribute('aria-expanded', 'false');
        sidebar.querySelectorAll('.nav-dropdown').forEach(dropdown => {
            dropdown.classList.remove('is-open');
            dropdown.querySelector('.nav-dropdown-toggle').setAttribute('aria-expanded', 'false');
            dropdown.querySelector('.nav-dropdown-menu').inert = true;
        });
        syncDrawer();
        if (restoreFocus) toggle.focus({ preventScroll: true });
    }

    function syncDrawer() {
        const open = media.matches && sidebar.classList.contains('collapsed');
        const expanded = media.matches ? open : !sidebar.classList.contains('collapsed');
        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.setAttribute('aria-label', media.matches
            ? (open ? 'Close sidebar' : 'Open sidebar')
            : (expanded ? 'Collapse sidebar' : 'Expand sidebar'));
        toggle.querySelector('i').className = expanded ? 'bx bx-chevron-left' : 'bx bx-chevron-right';
        if (media.matches && !backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'bhw-mobile-backdrop';
            backdrop.hidden = true;
            backdrop.setAttribute('aria-hidden', 'true');
            sidebar.after(backdrop);
            backdrop.addEventListener('click', () => closeDrawer(true));
        }
        if (backdrop) backdrop.hidden = !open;
        document.body.classList.toggle('bhw-mobile-drawer-open', open);
        document.documentElement.classList.toggle('bhw-mobile-drawer-open', open);
        if (open && !locked) {
            savedScroll = window.scrollY;
            savedPosition = ['position', 'top', 'width'].map(property => document.body.style[property]);
            document.body.style.position = 'fixed';
            document.body.style.top = `-${savedScroll}px`;
            document.body.style.width = '100%';
            priorInert = main.inert;
            main.inert = true;
            locked = true;
        } else if (!open && locked) {
            ['position', 'top', 'width'].forEach((property, index) => { document.body.style[property] = savedPosition[index]; });
            main.inert = priorInert;
            locked = false;
            window.scrollTo(0, savedScroll);
        }
        if (!media.matches) {
            backdrop?.remove();
            backdrop = undefined;
        }
    }

    function prepareMobileContent() {
        if (!media.matches) {
            main.querySelectorAll('.bhw-mobile-table-scroll').forEach(wrapper => wrapper.replaceWith(...wrapper.childNodes));
            sidebar.querySelectorAll('[data-bhw-mobile-active]').forEach(link => {
                link.removeAttribute('aria-current');
                delete link.dataset.bhwMobileActive;
            });
            return;
        }
        main.querySelectorAll('table').forEach(table => {
            if (table.matches('.home-table') || table.closest('#calendar, .fc, .bhw-mobile-table-scroll, .citizen-table-scroll')) return;
            const wrapper = document.createElement('div');
            wrapper.className = 'bhw-mobile-table-scroll';
            wrapper.tabIndex = 0;
            wrapper.setAttribute('role', 'region');
            wrapper.setAttribute('aria-label', table.getAttribute('aria-label') || table.caption?.textContent || 'Scrollable records table');
            table.before(wrapper);
            wrapper.append(table);
        });
        const current = window.location.pathname.replace(/\/$/, '');
        const links = [...sidebar.querySelectorAll('a[href]')].map(link => ({
            link, path: new URL(link.href, window.location.href).pathname.replace(/\/$/, '')
        }));
        const citizenPage = /^\/(addcitizen|citizendetails|citizen|healthrecord)(\/|$)/.test(current);
        const match = links.find(item => item.path === current)
            || (citizenPage ? links.find(item => item.path === '/citizenlist') : undefined)
            || links.filter(item => item.path && current.startsWith(item.path + '/'))
                .sort((a, b) => b.path.length - a.path.length)[0];
        if (match && !match.link.hasAttribute('aria-current')) {
            match.link.setAttribute('aria-current', 'page');
            match.link.dataset.bhwMobileActive = 'true';
        }
    }

    sidebar.addEventListener('click', event => {
        if (!media.matches || !event.target.closest('a[href]') || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        closeDrawer();
    });
    sidebar.addEventListener('submit', event => {
        if (event.target.matches('.logout-form')) closeDrawer();
    });
    document.addEventListener('keydown', event => {
        if (!locked) return;
        if (event.key === 'Escape') { event.preventDefault(); closeDrawer(true); }
        if (event.key !== 'Tab') return;
        const items = [...sidebar.querySelectorAll('a[href], button:not([disabled])')]
            .filter(item => !item.closest('[inert]') && item.getClientRects().length);
        const first = items[0], last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
    new MutationObserver(syncDrawer).observe(sidebar, { attributes: true, attributeFilter: ['class'] });
    let pending = false;
    new MutationObserver(() => {
        if (pending || !media.matches) return;
        pending = true;
        requestAnimationFrame(() => { pending = false; prepareMobileContent(); });
    }).observe(main, { childList: true, subtree: true });
    media.addEventListener('change', () => { syncDrawer(); prepareMobileContent(); });
    window.addEventListener('pageshow', () => { if (media.matches) closeDrawer(); });
    prepareMobileContent();
    syncDrawer();
})();
