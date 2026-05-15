<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

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
        <!--
             <li>
                <a href="/calendar">
                    <i class="bx bx-calendar"></i>
                    <span class="nav-item">Schedule</span>
                </a>
        </li>
            -->

            <li>
                <a href="/logs">
                    <i class="bx bx-book"></i>
                    <span class="nav-item">Logs</span>
                </a>
            </li>

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

<!-- JS -->
<script>
const sidebar = document.querySelector(".sidebar");
const toggleBtn = document.querySelector(".topbutton");

toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
});
</script>

</body>
</html>