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
    <title>Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bhw/home.css') }}">
    <script src="{{ asset('js/ui-motion.js') }}" defer></script>

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

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
    </style>

    @yield('CSSown')
</head>
<body class="theme-{{ $currentTheme }} font-{{ $currentFontSize }}">

@yield('scripts')
    <div class="dashboard">

        <div id="global-loader">
    <div class="logo-spinner-wrapper">
        <div class="spinner-ring-blue"></div>
        <div class="spinner-ring-white"></div>
        <img src="{{ asset('images/amuyong.png') }}" alt="Logo" class="spinner-logo">
    </div>
    <span class="loader-status-text">Processing, please wait...</span>
</div>

<script>
    (function() {
        const loader = document.getElementById('global-loader');
        if (!loader) return;

        window.showLoader = function() {
            loader.classList.add('active');
        };

        window.hideLoader = function() {
            loader.classList.remove('active');
        };

        document.addEventListener('submit', function(e) {
            if (e.target.closest('[data-no-loader="true"]')) return;
            showLoader();
        });

        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && 
                link.getAttribute('target') !== '_blank' && 
                !link.href.startsWith('#') && 
                !link.href.startsWith('javascript:') &&
                link.hostname === window.location.hostname) {
                showLoader();
            }
        });

        window.addEventListener('pageshow', function(event) {
            hideLoader();
        });
        
        window.addEventListener('load', function() {
            hideLoader();
        });
    })();
</script>

    <!-- SIDEBAR -->
    <div class="sidebar" style="display: flex; flex-direction: column; height: 100vh;">

        <!-- TOP TOGGLE -->
        <div class="top">
            <div class="topbutton">
                <i class="bx bx-sidebar"></i>
            </div>
        </div>

        <!-- MAIN NAVIGATION LINKS -->
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
                    <a href="{{ route('bhw.reports') }}">
                        <i class="bx bx-file"></i>
                        <span class="nav-item">Reports</span>
                    </a>
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
        </ul>

        <!-- SEPARATE BOTTOM SETTINGS SECTION -->
        <div class="sidebar-bottom" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1);">
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li>
                    <a href="/settings">
                        <i class="bx bx-cog"></i>
                        <span class="nav-item">Settings</span>
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit">
                            <i class="bx bx-log-out" style="font-size:20px; min-width:25px; text-align:center;"></i>
                            <span class="nav-item">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>

    </div>

    <!-- MAIN -->
    <div class="main-content">
        <div class="top-bar">
            <div style="margin-left:auto; font-weight:500; color:#334155;">{{ auth()->user()->name }}</div>
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

<!-- JS -->
<script>
const sidebar = document.querySelector(".sidebar");
const toggleBtn = document.querySelector(".topbutton");

toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
});
</script>

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

</body>
</html>