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

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('fav.png') }}" type="image/x-icon">

    @yield('CSSown')
</head>
<body class="theme-{{ $currentTheme }} font-{{ $currentFontSize }}">

@yield('scripts')
<div class="dashboard">

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
                    <i class="bx bx-group"></i>
                    <span class="nav-item">Citizen</span>
                </a>
            </li>

            <li>
                <a href="/bhw/families">
                    <i class="bx bx-group"></i>
                    <span class="nav-item">Families</span>
                </a>
            </li>

            <li>
                <a href="/healthrecord">
                    <i class="bx bx-heart"></i>
                    <span class="nav-item">Health Records</span>
                </a>
            </li>

            <li>
                <a href="/supplies">
                    <i class="bx bx-box"></i>
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
                    <i class="bx bx-heart"></i>
                    <span class="nav-item">Referrals</span>
                </a>
            </li>

            <li>
                <a href="/announcements">
                    <i class="bx bx-megaphone"></i>
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
            </ul>
        </div>

    </div>

    <!-- MAIN -->
    <div class="main-content">
        <div class="top-bar">
            <div></div> <!-- empty left space -->

            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    Logout
                </button>
            </form>
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