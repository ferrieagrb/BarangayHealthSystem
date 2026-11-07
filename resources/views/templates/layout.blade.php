@php
    $user = auth()->user();
    // Get theme preference, default to 'light' if not set
    $currentTheme = ($user && $user->theme === 'dark') ? 'dark' : 'light';
    
    // Get font size preference, default to 'normal'
    $currentFontSize = ($user && $user->font_size) ? $user->font_size : 'normal';
@endphp

<!DOCTYPE html>
<html lang="en" class="theme-{{ $currentTheme }}" data-theme="{{ $currentTheme }}" data-font="{{ $currentFontSize }}">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}?v={{ filemtime(public_path('css/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/bhw/home.css') }}">
    <script src="{{ asset('js/ui-motion.js') }}" defer></script>

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.favicon')
    @include('partials.pwa-meta')

    <style>
        /* CSS para sa pantay at maayos na alignment ng sidebar icons at text */
        .sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
        }
        .sidebar ul li a i {
            font-size: 20px;
            min-width: 25px;
            text-align: center;
        }
        .sidebar ul li .logout-form button {
            gap: 12px;
        }
        .sidebar ul li .logout-form button i {
            font-size: 20px;
            width: 40px;
            min-width: 25px;
        }
        .sidebar .nav-dropdown-toggle {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
            height: 60px;
            gap: 12px;
            padding: 0 25px;
            border: 0;
            background: transparent;
            color: #fff;
            cursor: pointer;
            font: inherit;
            white-space: nowrap;
        }
        .sidebar .nav-dropdown-toggle i:first-child { display: flex; align-items: center; justify-content: center; width: 40px; min-width: 40px; font-size: 22px; text-align: center; }
        .sidebar .nav-dropdown-toggle .nav-item { opacity: 1; transform: translateX(0); }
        .sidebar .nav-dropdown-toggle .nav-chevron { margin-left: auto; font-size: 16px; transition: transform 0.24s ease; }
        .sidebar .nav-dropdown.is-open .nav-dropdown-toggle .nav-chevron { transform: rotate(180deg); }
        .sidebar ul li:has(.nav-dropdown.is-open) { height: auto; }
        .sidebar .nav-dropdown-menu { position: static; display: block; width: 100%; max-height: 0; overflow: hidden; padding: 0 0 0 65px; background: #3e5894; border: 0; border-radius: 0; box-shadow: none; opacity: 0; transform: translateY(-6px); transition: max-height 0.28s ease, padding 0.28s ease, opacity 0.2s ease, transform 0.28s ease; }
        .sidebar .nav-dropdown.is-open .nav-dropdown-menu { max-height: 96px; padding: 4px 0 8px 65px; opacity: 1; transform: translateY(0); }
        .sidebar .nav-dropdown-menu a { height: 40px; padding: 0 12px; gap: 12px; color: #fff; background: #3e5894; border-radius: 6px; font-size: 13px; }
        .sidebar .nav-dropdown-menu a:hover { background: #334b82; color: #fff; }
        .sidebar .nav-dropdown-menu a i { width: 25px; min-width: 25px; font-size: 19px; color: #fff; }
        .sidebar.collapsed .nav-dropdown-toggle { justify-content: center; padding: 0; }
        .sidebar.collapsed .nav-dropdown-toggle .nav-item,
        .sidebar.collapsed .nav-dropdown-toggle .nav-chevron { display: none; }
        .sidebar.collapsed .nav-dropdown-menu { position: absolute; top: 0; left: 80px; width: 220px; max-height: 0; padding: 0 8px; background: #fff; border: 1px solid #dce4ee; border-radius: 8px; box-shadow: 0 8px 18px rgba(15,23,42,.18); }
        .sidebar.collapsed .nav-dropdown.is-open .nav-dropdown-menu { max-height: 96px; padding: 6px 8px; }
        .sidebar.collapsed .nav-dropdown-menu a { color: #334155; background: #fff; }
        .sidebar.collapsed .nav-dropdown-menu a:hover { background: #eff6ff; color: #173b82; }
        .sidebar.collapsed .nav-dropdown-menu a i { color: #334155; }
    </style>

    @yield('CSSown')
    @include('partials.appearance-assets')
    <link rel="stylesheet" href="{{ asset('css/bhw/sidebar.css') }}?v={{ filemtime(public_path('css/bhw/sidebar.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/bhw/mobile.css') }}" media="screen and (max-width: 768px)">
    <link rel="stylesheet" href="{{ asset('css/sidebar-scroll.css') }}?v={{ filemtime(public_path('css/sidebar-scroll.css')) }}">
    <script src="{{ asset('js/bhw-mobile.js') }}" defer></script>
    <script src="{{ asset('js/sidebar-navigation.js') }}?v={{ filemtime(public_path('js/sidebar-navigation.js')) }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/typography.css') }}">
</head>
<body data-appearance-role="{{ auth()->user()->role }}" class="theme-{{ $currentTheme }} font-{{ $currentFontSize }}">
@include('partials.global-loader')

@yield('scripts')
    <div class="dashboard">


    <!-- SIDEBAR -->
    <div class="sidebar" id="bhwSidebar" role="navigation" aria-label="Primary navigation" tabindex="0" style="display: flex; flex-direction: column; height: 100vh;">
        @if(request()->routeIs('bhw.reports', 'bhw.analytics'))
            <script>
                document.getElementById('bhwSidebar').classList.toggle('collapsed', !window.matchMedia('(max-width: 768px)').matches);
            </script>
        @endif

        <div class="top sidebar-header">
            <a class="sidebar-brand" href="/home" aria-label="BHEAMS home">
                <img src="{{ asset('images/amuyong.png') }}" alt="">
                <span>BHEAMS</span>
            </a>
            <button type="button" class="topbutton sidebar-toggle" aria-label="Toggle sidebar" aria-controls="bhwSidebar" aria-expanded="true">
                <i class="bx bx-chevron-left" aria-hidden="true"></i>
            </button>
        </div>

        <!-- MAIN NAVIGATION LINKS -->
        <div class="sidebar-nav" tabindex="0" aria-label="Navigation links">
        <ul style="flex-grow: 1; list-style: none; padding: 0; margin: 0;">
            <li>
                <a href="/home">
                    <i class="bx bx-home"></i>
                    <span class="nav-item">Home</span>
                </a>
            </li>

            <li>
                <a href="/citizenlist">
                    <i class="bx bx-user"></i>
                    <span class="nav-item">Citizen</span>
                </a>
            </li>

            <li>
                <a href="/bhw/families">
                    <i class="bx bx-group"></i>
                    <span class="nav-item">Families</span>
                </a>
            </li>

            @if(auth()->user()->hasReadAccess('reports'))
                <li>
                    <div class="nav-dropdown">
                        <button type="button" class="nav-dropdown-toggle" aria-label="Reports{{ request()->routeIs('bhw.analytics') ? ': Analytics selected' : (request()->routeIs('bhw.reports') ? ': Report Builder selected' : '') }}" aria-expanded="false" aria-controls="bhwReportsMenu">
                            <i class="bx bx-file"></i>
                            <span class="nav-item">Reports</span>
                            <i class="bx bx-chevron-down nav-chevron"></i>
                        </button>
                        <div class="nav-dropdown-menu" id="bhwReportsMenu" inert>
                            <div class="nav-dropdown-inner">
                            <a href="{{ route('bhw.analytics') }}" @if(request()->routeIs('bhw.analytics')) class="active" aria-current="page" @endif>
                                <i class="bx bx-line-chart"></i>
                                <span class="nav-item">Analytics</span>
                            </a>
                            <a href="{{ route('bhw.reports') }}" @if(request()->routeIs('bhw.reports')) class="active" aria-current="page" @endif>
                                <i class="bx bx-file-blank"></i>
                                <span class="nav-item">Report Builder</span>
                            </a>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            <li>
                <a href="/supplies">
                    <i class="bx bx-package"></i>
                    <span class="nav-item">Supplies</span>
                </a>
            </li>

            <li>
                <a href="/calendar">
                    <i class="bx bx-calendar"></i>
                    <span class="nav-item">Calendar</span>
                </a>
            </li>

            @if(auth()->user()->hasWriteAccess('audit_logs'))
            <li>
                <a href="/logs">
                    <i class="bx bx-book"></i>
                    <span class="nav-item">Logs</span>
                </a>
            </li>
            @endif

            <li>
                <a href="/referrals">
                    <i class="bx bx-export"></i>
                    <span class="nav-item">Referrals</span>
                </a>
            </li>

            <li>
                <a href="/announcements">
                    <i class="bx bxs-megaphone"></i>
                    <span class="nav-item">Announcements</span>
                </a>
            </li>

            <li>
                <a href="/qr-scanner">
                    <i class="bx bx-scan"></i>
                    <span class="nav-item">QR Checker</span>
                </a>
            </li>
            <li>
                <a href="{{ route('archive.index') }}">
                    <i class="bx bx-archive"></i>
                    <span class="nav-item">Archive</span>
                </a>
            </li>
        </ul>
        </div>

        <div class="sidebar-bottom" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1);">
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li>
                    <a href="/settings">
                        <i class="bx bx-cog"></i>
                        <span class="nav-item">Settings</span>
                    </a>
                </li>
            </ul>
            @include('templates.partials.sidebar-account')
        </div>

    </div>

    <!-- MAIN -->
    <div class="main-content">
        <div class="top-bar" id="accountHeader">
            <div class="header-user" style="margin-left:auto; font-weight:500; color:#334155;">{{ auth()->user()->name }}</div>
        </div>
        @yield('content')
    </div>

</div>

@include('templates.partials.logout-modal')

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

@auth
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sessionLifetimeMinutes = {{ config('session.lifetime', 120) }};
        const sessionLimit = sessionLifetimeMinutes * 60 * 1000; 

        let timeoutHandle = setTimeout(triggerTimeout, sessionLimit);

        function resetTimer() {
            clearTimeout(timeoutHandle);
            timeoutHandle = setTimeout(triggerTimeout, sessionLimit);
        }

        function triggerTimeout() {
            document.getElementById('session-timeout-modal').style.display = 'flex';
        }

        window.addEventListener('mousemove', resetTimer);
        window.addEventListener('keypress', resetTimer);
        window.addEventListener('click', resetTimer);
        window.addEventListener('scroll', resetTimer);
    });
</script>
@endauth

<script src="{{ asset('js/password-toggle.js') . '?v=1793722773' }}"></script>
</body>
</html>
