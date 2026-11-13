    (() => {
        const toggle = document.querySelector('.menu-toggle');
        const toggleIcon = toggle?.querySelector('i');
        const navigation = document.querySelector('#primary-navigation');
        const header = document.querySelector('.main-header');
        const backdrop = document.querySelector('.mobile-nav-backdrop');

        if (!toggle || !toggleIcon || !navigation || !header || !backdrop) return;

        const mobileNavigation = window.matchMedia('(max-width: 768px)');
        let isOpen = false;

        const updateMenuPosition = () => {
            const menuTop = Math.max(0, header.getBoundingClientRect().bottom);
            navigation.style.setProperty('--menu-top', `${menuTop}px`);
        };

        const setMenuOpen = (open) => {
            isOpen = open && mobileNavigation.matches;
            navigation.classList.toggle('is-open', isOpen);
            toggle.setAttribute('aria-expanded', String(isOpen));
            toggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
            toggleIcon.classList.toggle('fa-bars', !isOpen);
            toggleIcon.classList.toggle('fa-xmark', isOpen);
            backdrop.hidden = !isOpen;
            document.body.classList.toggle('nav-menu-open', isOpen);

            if (isOpen) updateMenuPosition();
        };

        toggle.addEventListener('click', () => setMenuOpen(!isOpen));
        backdrop.addEventListener('click', () => setMenuOpen(false));
        navigation.addEventListener('click', (event) => {
            if (event.target.closest('a')) setMenuOpen(false);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && isOpen) {
                setMenuOpen(false);
                toggle.focus();
            }
        });

        const refreshNavigation = () => {
            if (!mobileNavigation.matches) {
                setMenuOpen(false);
                navigation.classList.remove('is-open');
                return;
            }

            if (isOpen) updateMenuPosition();
        };

        window.addEventListener('resize', refreshNavigation);
        window.addEventListener('orientationchange', refreshNavigation);
        mobileNavigation.addEventListener('change', refreshNavigation);
    })();
