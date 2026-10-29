<div class="topbar">
    <div class="container topbar-content">
        <div class="topbar-item">
            <i class="fa-solid fa-phone"></i>
            <span>BARANGAY HOTLINE: <strong>(+63) 958 789 1234</strong></span>
        </div>
        <div class="topbar-item highlight">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>EMERGENCY: <strong>(+63) 958 789 1234</strong></span>
        </div>
    </div>
</div>

<header class="main-header">
    <div class="container nav-container">
        <a href="{{ route('landing') }}" class="brand">
            <img src="{{ \App\Models\SystemSetting::logoUrl() }}" alt="Barangay Amuyong Logo" class="brand-logo">
            <div class="brand-text">
                <span class="brand-title">BARANGAY AMUYONG</span>
                <span class="brand-subtitle">Health Services Portal</span>
            </div>
        </a>

        <button class="menu-toggle" type="button" aria-label="Open navigation menu" aria-controls="primary-navigation" aria-expanded="false">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <nav class="nav-menu" id="primary-navigation" aria-label="Public navigation">
            <ul>
                <li><a href="{{ route('landing') }}" @class(['active' => ($activeNav ?? '') === 'home']) @if(($activeNav ?? '') === 'home') aria-current="page" @endif>Home</a></li>
                <li><a href="{{ route('publiccalendar') }}" @class(['active' => ($activeNav ?? '') === 'calendar']) @if(($activeNav ?? '') === 'calendar') aria-current="page" @endif>Calendar</a></li>
                <li><a href="{{ route('public.announcements') }}" @class(['active' => ($activeNav ?? '') === 'announcements']) @if(($activeNav ?? '') === 'announcements') aria-current="page" @endif>Announcements</a></li>
                <li><a href="{{ route('public.qr-scanner') }}" @class(['active' => ($activeNav ?? '') === 'qr']) @if(($activeNav ?? '') === 'qr') aria-current="page" @endif>QR Scanner</a></li>
                <li><a href="{{ route('login') }}" class="btn-login"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Login</a></li>
            </ul>
        </nav>
    </div>
</header>

<button class="mobile-nav-backdrop" type="button" aria-label="Close navigation menu" tabindex="-1" hidden></button>

<script>
    (() => {
        const toggle = document.querySelector('.menu-toggle');
        const toggleIcon = toggle?.querySelector('i');
        const navigation = document.querySelector('#primary-navigation');
        const header = document.querySelector('.main-header');
        const backdrop = document.querySelector('.mobile-nav-backdrop');

        if (!toggle || !toggleIcon || !navigation || !header || !backdrop) return;

        const mobileNavigation = window.matchMedia('(max-width: 900px)');
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
</script>
