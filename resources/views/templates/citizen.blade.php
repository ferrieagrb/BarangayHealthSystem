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
                <a href="/admin/home">
                    <i class="bx bx-home"></i>
                    <span class="nav-item">Home</span>
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