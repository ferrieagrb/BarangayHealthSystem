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

            <!-- Vaccinations -->
            <li>
                <a href="{{ route('citizen.vaccination') }}">
                    <i class="bx bx-shield-plus"></i>
                    <span class="nav-item">Vaccinations</span>
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