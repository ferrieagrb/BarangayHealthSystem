    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('citizenSidebar');
        const backdrop = document.getElementById('citizenSidebarBackdrop');
        const openButton = document.getElementById('citizenMenuOpen');
        const closeButton = document.getElementById('citizenMenuClose');
        const topBar = document.querySelector('.top-bar');
        if (!sidebar || !backdrop || !openButton || !closeButton) return;
        const homeLink = sidebar.querySelector('ul > li > a');
        const dashboardHome = document.getElementById('announcements');
        const sidebarSections = [...sidebar.querySelectorAll('a[href*="#"]')]
            .map(link => {
                const targetId = new URL(link.href, window.location.href).hash.slice(1);
                return { link, target: document.getElementById(targetId) };
            })
            .filter(section => section.target);

        let previousBodyOverflow = '';
        let previousHtmlOverflow = '';

        function updateHeaderHeight() {
            if (!topBar) return;
            document.documentElement.style.setProperty(
                '--citizen-header-height',
                `${Math.ceil(topBar.getBoundingClientRect().height)}px`
            );
        }

        function updateActiveSidebarLink() {
            if (!homeLink || !dashboardHome || !sidebarSections.length) return;

            const sections = [{ link: homeLink, target: dashboardHome }, ...sidebarSections];
            const activationPoint = (topBar ? topBar.offsetHeight : 0) + 25;
            let current = sections[0];

            sections.forEach(section => {
                if (section.target.getBoundingClientRect().top <= activationPoint) {
                    current = section;
                }
            });

            sections.forEach(section => {
                const active = section.link === current.link;
                section.link.classList.toggle('active', active);
                if (active) {
                    section.link.setAttribute(
                        'aria-current',
                        section.link === homeLink ? 'page' : 'location'
                    );
                } else {
                    section.link.removeAttribute('aria-current');
                }
            });
        }

        function openMenu() {
            updateHeaderHeight();
            previousBodyOverflow = document.body.style.overflow;
            previousHtmlOverflow = document.documentElement.style.overflow;
            sidebar.classList.add('menu-open');
            backdrop.classList.add('visible');
            sidebar.setAttribute('aria-hidden', 'false');
            backdrop.setAttribute('aria-hidden', 'false');
            openButton.setAttribute('aria-expanded', 'true');
            closeButton.setAttribute('aria-expanded', 'true');
            document.body.classList.add('citizen-sidebar-open');
            closeButton.focus();
            document.body.style.overflow = 'hidden';
            document.documentElement.style.overflow = 'hidden';
        }

        function closeMenu(restoreFocus = false) {
            sidebar.classList.remove('menu-open');
            backdrop.classList.remove('visible');
            sidebar.setAttribute('aria-hidden', 'true');
            backdrop.setAttribute('aria-hidden', 'true');
            openButton.setAttribute('aria-expanded', 'false');
            closeButton.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('citizen-sidebar-open');
            document.body.style.overflow = previousBodyOverflow;
            document.documentElement.style.overflow = previousHtmlOverflow;
            if (restoreFocus) openButton.focus();
        }

        updateHeaderHeight();
        updateActiveSidebarLink();
        window.addEventListener('resize', updateHeaderHeight, { passive: true });
        window.addEventListener('load', updateHeaderHeight, { once: true });
        window.addEventListener('scroll', updateActiveSidebarLink, { passive: true });
        window.addEventListener('hashchange', updateActiveSidebarLink);

        openButton.addEventListener('click', function () {
            if (sidebar.classList.contains('menu-open')) {
                closeMenu(true);
            } else {
                openMenu();
            }
        });
        closeButton.addEventListener('click', function () {
            closeMenu(true);
        });
        backdrop.addEventListener('click', function () {
            closeMenu(true);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && sidebar.classList.contains('menu-open')) {
                closeMenu(true);
            }
        });
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function (event) {
                const destination = new URL(link.href, window.location.href);
                const sectionId = destination.hash.slice(1);
                const navbarLink = sectionId
                    ? document.querySelector('#citizenSectionNav a[href="#' + CSS.escape(sectionId) + '"]')
                    : null;

                if (navbarLink && destination.pathname === window.location.pathname) {
                    event.preventDefault();
                    navbarLink.click();
                }
                closeMenu(false);
            });
        });
    });
