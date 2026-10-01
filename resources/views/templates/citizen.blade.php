<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Citizen Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('fav.png') }}" type="image/x-icon">
    @yield('CSSown')
</head>

<body>
@yield('scripts')

<div class="dashboard">

    <!-- SIDEBAR -->
    <div class="sidebar">

        <!-- TOP TOGGLE -->
        <div class="top">
            <div class="topbutton">
                <i class="bx bx-sidebar"></i>
            </div>
        </div>

        <ul>

            <!-- Dashboard / Home -->
            <li>
                <a href="{{ route('citizen.dashboard') }}">
                    <i class="bx bx-home"></i>
                    <span class="nav-item">Home</span>
                </a>
            </li>

            <!-- Supplies -->
            <li>
                <a href="{{ route('citizen.supplies') }}">
                    <i class="bx bx-package"></i>
                    <span class="nav-item">Supplies</span>
                </a>
            </li>

            <!-- Calendar -->
            <li>
                <a href="{{ route('citizen.calendar') }}">
                    <i class="bx bx-calendar"></i>
                    <span class="nav-item">Calendar</span>
                </a>
            </li>

            <!-- Health Card -->
            <li>
                <a href="{{ route('citizen.ecard') }}">
                    <i class="bx bx-id-card"></i>
                    <span class="nav-item">Health Card</span>
                </a>
            </li>

            <!-- Announcements -->
            <li>
                <a href="{{ route('citizen.announcements') }}">
                    <i class="bx bx-bell"></i>
                    <span class="nav-item">Announcements</span>
                </a>
            </li>

        </ul>

    </div>

    <!-- MAIN -->
    <div class="main-content">

        <div class="top-bar">
            <div></div>

            <form method="POST" action="{{ route('logout') }}">
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
</script>

@endauth

</body>
</html>