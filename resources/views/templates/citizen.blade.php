@php
    $user = auth()->user();
    // Get theme preference, default to 'light' if not set
    $currentTheme = ($user && $user->theme === 'dark') ? 'dark' : 'light';
    // Get font size preference, default to 'normal'
    $currentFontSize = ($user && $user->font_size) ? $user->font_size : 'normal';
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $currentTheme }}" data-font="{{ $currentFontSize }}">
    <head>
        <meta charset="UTF-8">
        <title>Citizen Dashboard</title>
        <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="{{ asset('fav.png') }}" type="image/x-icon">
        @yield('CSSown')<style>
/* Citizen-only layout: full-width content and an off-canvas menu */ html, body {
    width:100%;
    min-width:0;
    margin:0;
}
body {
    overflow-x:hidden;
}
.dashboard {
    display:block !important;
    width:100%;
    min-height:100vh;
}
.main-content {
    margin:0 !important;
    width:100% !important;
    max-width:none !important;
    min-height:100vh;
    height:auto !important;
    padding:0 !important;
    overflow:visible !important;
    background:#f3f6fb;
}

/* Dashboard header styled like the requested reference */
.top-bar {
    position: sticky;
    top: 0;
    z-index: 1000;
    display: grid !important;
    grid-template-columns: minmax(100px, 1fr) auto minmax(170px, 1fr);
    align-items: center;
    gap: 18px;
    min-height: 76px;
    padding: 8px clamp(16px, 2vw, 30px);
    background: #d9d9d9;
    border-top: 7px solid #075078;
    border-bottom: 1px solid #c8cdd4;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .05);
}
.c-header-logo {
    display: flex;
    align-items: center;
    justify-self: start;
    min-width: 0;
}
.c-header-logo img {
    width: 48px;
    height: 48px;
    object-fit: contain;
    border-radius: 50%;
}
.c-header-section-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    min-width: 0;
}
.c-header-section-nav a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 0 12px;
    border-radius: 10px;
    color: #182230;
    text-decoration: none;
    white-space: nowrap;
    font-size: 13px;
    font-weight: 750;
    transition: background .18s ease, color .18s ease, box-shadow .18s ease;
}
.c-header-section-nav a:hover {
    background: #e8eefe;
    color: #1d4ed8;
}
.c-header-section-nav a.active {
    background: #1d4ed8;
    color: #fff;
    box-shadow: 0 3px 8px rgba(29, 78, 216, .18);
}
.c-header-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
}
.c-settings-link {
    color: #172033;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    padding: 10px 8px;
}
.c-settings-link:hover { color: #1d4ed8; }
.top-bar .citizen-menu-button {
    min-height: 42px;
    min-width: 44px;
    padding: 7px 10px;
    border-radius: 9px;
    font-size: 14px;
}
.top-bar .citizen-menu-button i { font-size: 23px; }
.top-bar .btn-logout {
    min-height: 42px;
    padding: 9px 15px !important;
    border-radius: 9px !important;
    font-size: 13px !important;
}
@media (max-width: 900px) {
    .top-bar {
        grid-template-columns: auto 1fr;
        gap: 8px;
    }
    .c-header-logo { grid-column: 1; grid-row: 1; }
    .c-header-actions { grid-column: 2; grid-row: 1; }
    .c-header-section-nav {
        grid-column: 1 / -1;
        grid-row: 2;
        justify-content: flex-start;
        overflow-x: auto;
        padding: 2px 0 4px;
        scrollbar-width: none;
    }
    .c-header-section-nav::-webkit-scrollbar { display: none; }
    .top-bar { padding: 8px 14px; }
}
@media (max-width: 480px) {
    .c-header-logo img { width: 40px; height: 40px; }
    .c-header-section-nav a { min-height: 36px; padding: 0 10px; font-size: 12px; }
    .c-settings-link { font-size: 12px; padding: 8px 3px; }
    .top-bar .citizen-menu-button span { display: none; }
    .top-bar .btn-logout { padding: 8px 10px !important; }
}

.citizen-menu-button {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    min-width:54px;
    min-height:50px;
    padding:10px 16px;
    border:2px solid #1e3a8a;
    border-radius:12px;
    background:#1e3a8a;
    color:#fff;
    font-size:17px;
    font-weight:800;
    cursor:pointer;
    box-shadow:0 3px 8px #1e3a8a22;
}
.citizen-menu-button i {
    font-size:28px;
    line-height:1;
}
.citizen-menu-button:focus-visible, .sidebar a:focus-visible, .sidebar .topbutton:focus-visible {
    outline:3px solid #f59e0b;
    outline-offset:3px;
}
.sidebar, .sidebar.collapsed {
    position:fixed !important;
    inset:0 auto 0 0 !important;
    width:min(320px,86vw) !important;
    min-width:0 !important;
    max-width:none !important;
    height:100vh !important;
    height:100dvh !important;
    padding:14px 12px !important;
    transform:translateX(-105%);
    visibility:hidden;
    box-shadow:8px 0 30px rgba(15,23,42,.22);
    transition:transform .25s ease, visibility .25s ease;
    z-index:1200 !important;
    overflow-y:auto;
}
.sidebar.menu-open, .sidebar.collapsed.menu-open {
    transform:translateX(0);
    visibility:visible;
}
.sidebar .top {
    margin-top:0 !important;
    justify-content:flex-end !important;
    padding:0 8px 12px !important;
}
.sidebar .topbutton {
    display:flex;
    align-items:center;
    justify-content:center;
    min-width:48px;
    min-height:48px;
    border-radius:10px;
    cursor:pointer;
}
.sidebar .topbutton i {
    font-size:30px !important;
}
.sidebar ul {
    margin-top:10px !important;
}
.sidebar ul li {
    min-height:58px;
    height:auto !important;
}
.sidebar ul li a {
    min-height:58px;
    padding:10px 16px !important;
    gap:15px !important;
    border-radius:10px;
    font-size:16px;
}
.sidebar ul li a i {
    font-size:25px !important;
    min-width:30px !important;
    width:30px !important;
}
.sidebar ul li a span {
    opacity:1 !important;
    width:auto !important;
    position:static !important;
    overflow:visible !important;
    white-space:normal;
}
.sidebar.collapsed ul li a {
    justify-content:flex-start !important;
    padding-left:16px !important;
}
.sidebar.collapsed ul li a i {
    width:30px !important;
}
.sidebar-backdrop {
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.48);
    opacity:0;
    visibility:hidden;
    transition:opacity .25s ease, visibility .25s ease;
    z-index:1100;
}
.sidebar-backdrop.visible {
    opacity:1;
    visibility:visible;
}
.btn-logout {
    min-height:48px;
    padding:11px 18px !important;
    font-size:16px !important;
    border-radius:10px !important;
    font-weight:700 !important;
}
@media(max-width:600px) {
    .top-bar {
        min-height:68px;
        padding:10px 14px;
    }
    .citizen-menu-button {
        min-height:48px;
    }
}
@media(prefers-reduced-motion:reduce) {
    .sidebar,.sidebar-backdrop {
        transition:none !important;
    }
}

/* ANNOUNCEMENT CARD DESIGN */ .c-ann-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}
.c-ann-card {
    position: relative;
    min-width: 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 18px rgba(15,23,42,.07);
    transition: transform .2s, box-shadow .2s;
}
.c-ann-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(15,23,42,.12);
}
.c-ann-cover {
    min-height: 150px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    color: white;
    background: linear-gradient(135deg, #173b82, #2563eb);
}
.c-ann-card:nth-child(4n+2) .c-ann-cover {
    background: linear-gradient(135deg, #047857, #34a853);
}
.c-ann-card:nth-child(4n+3) .c-ann-cover {
    background: linear-gradient(135deg, #9a3412, #f97316);
}
.c-ann-card:nth-child(4n+4) .c-ann-cover {
    background: linear-gradient(135deg, #6b21a8, #a855f7);
}
.c-ann-label {
    display: inline-block;
    width: fit-content;
    padding: 5px 9px;
    border-radius: 5px;
    background: rgba(255,255,255,.2);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .6px;
    text-transform: uppercase;
}
.c-ann-cover h3 {
    margin: 18px 0 0;
    color: white;
    font-size: clamp(17px, 1.5vw, 22px);
    line-height: 1.3;
    font-weight: 800;
    overflow-wrap: anywhere;
}
.c-ann-body {
    padding: 17px;
}
.c-ann-body p {
    margin: 0;
    color: #475569;
    font-size: 13px;
    line-height: 1.65;
    overflow-wrap: anywhere;
}
.c-ann-date {
    display: block;
    margin-top: 14px;
    color: #64748b;
    font-size: 11px;
}
.c-ann-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 11px;
}
@media (max-width: 600px) {
    .c-ann-grid {
        grid-template-columns: 1fr;
    }
}

/* Responsive citizen header and navigation */
.top-bar {
    grid-template-columns: minmax(56px, 1fr) auto minmax(150px, 1fr);
    border-top: 5px solid #075078;
}
@media (max-width: 900px) {
    .top-bar {
        display: grid !important;
        grid-template-columns: 48px minmax(0, 1fr);
        grid-template-rows: auto auto;
        align-items: center;
        gap: 8px 12px;
        min-height: 0;
        padding: 10px 14px 8px;
    }

    .c-header-logo {
        grid-column: 1;
        grid-row: 1;
    }

    .c-header-actions {
        grid-column: 2;
        grid-row: 1;
        gap: 8px;
        min-width: 0;
    }

    .c-header-section-nav {
        grid-column: 1 / -1;
        grid-row: 2;
        display: flex;
        justify-content: flex-start;
        gap: 5px;
        width: 100%;
        min-width: 0;
        padding: 3px 0 2px;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .c-header-section-nav::-webkit-scrollbar {
        display: none;
    }

    .c-header-section-nav a {
        flex: 0 0 auto;
        min-height: 38px;
        padding: 0 13px;
        border-radius: 9px;
        font-size: 12px;
    }

    .top-bar .citizen-menu-button {
        min-height: 40px;
        padding: 7px 10px;
        font-size: 13px;
    }

    .top-bar .citizen-menu-button span {
        display: inline;
    }

    .top-bar .btn-logout {
        min-height: 40px;
        padding: 8px 12px !important;
    }

    .c-settings-link {
        padding: 8px 4px;
        font-size: 12px;
    }
}

@media (max-width: 520px) {
    .top-bar {
        grid-template-columns: 40px minmax(0, 1fr);
        gap: 7px 9px;
        padding: 9px 10px 7px;
    }

    .c-header-logo img {
        width: 38px;
        height: 38px;
    }

    .c-header-actions {
        gap: 6px;
    }

    .top-bar .citizen-menu-button {
        min-width: 40px;
        min-height: 38px;
        padding: 6px 8px;
        gap: 5px;
    }

    .top-bar .citizen-menu-button i {
        font-size: 20px;
    }

    .top-bar .citizen-menu-button span {
        display: inline;
    }

    .c-settings-link {
        font-size: 11px;
        padding: 7px 2px;
    }

    .top-bar .btn-logout {
        min-height: 38px;
        padding: 7px 9px !important;
        font-size: 12px !important;
    }

    .c-header-section-nav a {
        min-height: 36px;
        padding: 0 11px;
        font-size: 11px;
    }
}

@media (max-width: 360px) {
    .top-bar .citizen-menu-button span {
        display: none;
    }
    .c-header-actions {
        gap: 5px;
    }
    .c-header-section-nav a {
        padding: 0 9px;
        font-size: 10px;
    }
}


/* FINAL MOBILE HEADER FIX: keep all tabs visible and the blue strip full width */
html,
body,
.dashboard,
.main-content {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.top-bar {
    width: 100%;
    box-sizing: border-box;
    border-top: 5px solid #075078 !important;
    background: #d9d9d9;
}

@media (max-width: 520px) {
    .top-bar {
        display: grid !important;
        grid-template-columns: 40px minmax(0, 1fr);
        grid-template-rows: auto auto;
        width: 100%;
        gap: 8px;
        padding: 9px 10px 8px;
        border-top-width: 5px !important;
    }

    .c-header-logo {
        grid-column: 1;
        grid-row: 1;
    }

    .c-header-actions {
        grid-column: 2;
        grid-row: 1;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: clamp(4px, 1.5vw, 8px);
        min-width: 0;
        width: 100%;
    }

    .c-header-logo img {
        display: block;
        width: 38px;
        height: 38px;
    }

    .top-bar .citizen-menu-button {
        min-width: 36px;
        min-height: 36px;
        padding: 5px 7px;
        gap: 4px;
        font-size: 12px;
        white-space: nowrap;
    }

    .top-bar .citizen-menu-button i {
        font-size: 19px;
    }

    .top-bar .citizen-menu-button span {
        display: inline;
    }

    .c-settings-link {
        padding: 6px 2px;
        font-size: 11px;
        white-space: nowrap;
    }

    .top-bar .btn-logout {
        min-height: 36px;
        padding: 6px 8px !important;
        font-size: 11px !important;
        white-space: nowrap;
    }

    .c-header-section-nav {
        grid-column: 1 / -1;
        grid-row: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2px;
        width: 100%;
        min-width: 0;
        padding: 3px 0 1px;
        overflow: visible;
    }

    .c-header-section-nav a {
        flex: 0 1 auto;
        min-width: 0;
        min-height: 35px;
        padding: 0 5px;
        border-radius: 8px;
        font-size: clamp(9px, 2.6vw, 11px);
        letter-spacing: -0.15px;
        white-space: nowrap;
    }
}

@media (max-width: 370px) {
    .top-bar {
        padding-left: 7px;
        padding-right: 7px;
    }

    .c-header-actions {
        gap: 4px;
    }

    .top-bar .citizen-menu-button {
        padding: 5px 6px;
    }

    .c-settings-link {
        font-size: 10px;
    }

    .top-bar .btn-logout {
        padding: 6px 7px !important;
        font-size: 10px !important;
    }

    .c-header-section-nav a {
        padding: 0 3px;
        font-size: 9px;
    }
}
/* FIX BLUE TOP BAR */
.top-bar {
    position: sticky !important;
    top: 0;
    left: auto;
    right: auto;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    border-top: 15px solid #075078 !important;
    overflow: visible !important;
}

.top-bar::before {
    content: none !important;
    display: none !important;
}

.top-bar > * {
    position: static;
}

/* MOBILE: keep the complete citizen header fixed while scrolling */
@media (max-width: 600px) {
    .top-bar {
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        z-index: 2000 !important;
        box-sizing: border-box;
    }

    .main-content {
        padding-top: 0 !important;
    }

    /* Keep page content below the fixed header */
    .c-home {
        padding-top: 125px !important;
    }

    /* If the navigation is in the header, don't add another nav row */
    .c-header-section-nav {
        min-width: 0;
    }

    .c-section {
        scroll-margin-top: 150px !important;
    }
}

</style>
</head>
<body class="theme-{{ $currentTheme }} font-{{ $currentFontSize }}">
    @yield('scripts')

    <div class="dashboard">
        <!-- Custom Logo Loader -->
        <div id="global-loader">
            <div class="logo-spinner-wrapper">
                <div class="spinner-ring-blue">
                </div>
                <div class="spinner-ring-white">
                </div>
                <img src="{{ asset('images/amuyong.png') }}" alt="Logo" class="spinner-logo">
            </div>
            <span class="loader-status-text">Processing, please wait...</span>
        </div><script>
    (function() {
            const loader = document.getElementById('global-loader');
            if (!loader) return;

            // Function to show loader
            window.showLoader = function() {
                loader.classList.add('active');
            };

            // Function to hide loader
            window.hideLoader = function() {
                loader.classList.remove('active');
            };

            // Trigger on any form submission across the site
            document.addEventListener('submit', function(e) {
                // Optional: check if form is valid before showing loader
                showLoader();
            });

            // Trigger on regular link clicks (excluding anchors or target="_blank")
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');

                if (!link) return;

                // Do not show the loader for same-page section tabs.
                const href = link.getAttribute('href');

                if (!href ||
                    href.startsWith('#') ||
                    href.startsWith('javascript:') ||
                    link.getAttribute('target') === '_blank') {
                    return;
                }

                // Show the loader only for actual same-site navigation.
                if (link.href && link.hostname === window.location.hostname) {
                    showLoader();
                }
            });

            // Hide loader when page finishes fully loading or comes from cache (back button)
            window.addEventListener('pageshow', function(event) {
                hideLoader();
            });

            window.addEventListener('load', function() {
                hideLoader();
            });
        })();
</script><!-- SIDEBAR -->
<div class="sidebar" id="citizenSidebar" aria-label="Citizen navigation" aria-hidden="true">
    <!-- CLOSE MENU -->
    <div class="top">
        <button type="button" class="topbutton" id="citizenMenuClose" aria-label="Close navigation menu" style="border:0;background:transparent;color:white;">
            <i class="bx bx-x" aria-hidden="true">
            </i>
        </button>
    </div>
    <ul>
        <!-- Dashboard / Home -->
        <li>
            <a href="{{ route('citizen.dashboard') }}">
                <i class="bx bx-home">
                </i>
                <span class="nav-item">Home</span>
            </a>
        </li>
        <!-- Supplies -->
        <li>
            <a href="{{ route('citizen.supplies') }}">
                <i class="bx bx-package">
                </i>
                <span class="nav-item">Supplies</span>
            </a>
        </li>
        <!-- Calendar -->
        <li>
            <a href="{{ route('citizen.calendar') }}">
                <i class="bx bx-calendar">
                </i>
                <span class="nav-item">Calendar</span>
            </a>
        </li>
        <!-- Health Card -->
        <li>
            <a href="{{ route('citizen.ecard') }}">
                <i class="bx bx-id-card">
                </i>
                <span class="nav-item">Health Card</span>
            </a>
        </li>
        <!-- Announcements -->
        <li>
            <a href="{{ route('citizen.announcements') }}">
                <i class="bx bx-bell">
                </i>
                <span class="nav-item">Announcements</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-bottom" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1);">
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li>
                <a href="/settings">
                    <i class="bx bx-cog">
                    </i>
                    <span class="nav-item">Settings</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<div class="sidebar-backdrop" id="citizenSidebarBackdrop" aria-hidden="true">
</div>
<!-- MAIN -->
<div class="main-content">
    <div class="top-bar">
        <a class="c-header-logo" href="{{ route('citizen.dashboard') }}" aria-label="Citizen dashboard">
            <img src="{{ asset('images/amuyong.png') }}" alt="Barangay Amuyong logo">
        </a>

        @if(request()->routeIs('citizen.dashboard'))
            <nav class="c-header-section-nav" id="citizenSectionNav" aria-label="Citizen page sections">
                <a href="#announcements" class="active">Announcement</a>
                <a href="#citizen-card">Healthcard</a>
                <a href="#health-information">Health Information</a>
                <a href="#request-referral">Referral</a>
            </nav>
        @else
            <div></div>
        @endif

        <div class="c-header-actions">
            <button type="button" class="citizen-menu-button" id="citizenMenuOpen" aria-label="Open navigation menu" aria-controls="citizenSidebar" aria-expanded="false">
                <i class="bx bx-menu" aria-hidden="true"></i>
                <span>Menu</span>
            </button>
            <a class="c-settings-link" href="{{ route('settings') }}">Settings</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </div>
    </div>
    @yield('content')
</div>
</div>
@auth
    <div id="session-timeout-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 99999; justify-content: center; align-items: center; backdrop-filter: blur(3px);">
        <div style="background: white; padding: 30px; border-radius: 12px; width: 400px; max-width: 90%; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="font-size: 40px; margin-bottom: 10px;">⏰</div>
            <h2 style="font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 10px;">Session Expired</h2>
            <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">Your session has timed out due to inactivity or expiration. Please log back in to continue.</p>
            <a href="{{ route('login') }}" style="display: block; width: 100%; padding: 12px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; box-sizing: border-box;">Log Back In</a>
        </div>
    </div>
@endauth
<!-- Citizen navigation menu --><script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('citizenSidebar');
        const backdrop = document.getElementById('citizenSidebarBackdrop');
        const openButton = document.getElementById('citizenMenuOpen');
        const closeButton = document.getElementById('citizenMenuClose');
        if (!sidebar || !backdrop || !openButton || !closeButton) return;

        function openMenu() {
            sidebar.classList.add('menu-open');
            backdrop.classList.add('visible');
            sidebar.setAttribute('aria-hidden', 'false');
            backdrop.setAttribute('aria-hidden', 'false');
            openButton.setAttribute('aria-expanded', 'true');
            closeButton.focus();
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            sidebar.classList.remove('menu-open');
            backdrop.classList.remove('visible');
            sidebar.setAttribute('aria-hidden', 'true');
            backdrop.setAttribute('aria-hidden', 'true');
            openButton.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
        openButton.addEventListener('click', openMenu);
        closeButton.addEventListener('click', closeMenu);
        backdrop.addEventListener('click', closeMenu);
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && sidebar.classList.contains('menu-open')) {
                closeMenu();
                openButton.focus();
            }
        });
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });
    });
</script>@auth<script>
    document.addEventListener("DOMContentLoaded", function () {
            // Dynamically grab SESSION_LIFETIME from Laravel config (in minutes) and convert to milliseconds
            const sessionLifetimeMinutes = {{ config('session.lifetime', 120) }};
            const sessionLimit = sessionLifetimeMinutes * 60 * 1000;

            let timeoutHandle = setTimeout(triggerTimeout, sessionLimit);

            function resetTimer() {
                clearTimeout(timeoutHandle);
                timeoutHandle = setTimeout(triggerTimeout, sessionLimit);
            }

            function triggerTimeout() {
                // Show the popup modal wherever the user is
                document.getElementById('session-timeout-modal').style.display = 'flex';
            }

            // Reset timer on user interaction (mouse movement, clicks, keystrokes)
            window.addEventListener('mousemove', resetTimer);
            window.addEventListener('keypress', resetTimer);
            window.addEventListener('click', resetTimer);
            window.addEventListener('scroll', resetTimer);
        });
</script>@endauth
</body>
</html>
