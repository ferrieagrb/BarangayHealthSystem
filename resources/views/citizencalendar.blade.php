<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Amuyong Calendar</title>
    
    <!-- Google Fonts & FontAwesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
    
    <!-- Theme Stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/citizenhome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calendar.css') }}">
</head>
<body>

    <!-- Topbar -->
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

    <!-- Main Navigation Header -->
    <header class="main-header">
        <div class="container nav-container">
            <a href="{{ url('/') }}" class="brand">
                <img src="{{ asset('images/amuyong.png') }}" alt="Barangay Amuyong Logo" class="brand-logo">
                <div class="brand-text">
                    <span class="brand-title">BARANGAY AMUYONG</span>
                    <span class="brand-subtitle">Health Services Portal</span>
                </div>
            </a>
            
            <nav class="nav-menu">
                <ul>
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="{{ route('publiccalendar') }}" class="active">Calendar</a></li>
                    <li><a href="{{ route('public.announcements') }}">Announcements</a></li>
                    <li><a href="{{ route('login') }}" class="btn-login"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Calendar Hero Banner -->
    <section class="hero page-hero" style="background-image: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(30, 41, 59, 0.85) 100%), url('{{ asset('images/mission.jpg') }}');">
    <div class="container hero-content">
        <span class="badge">Schedules & Events</span>
        <h1 class="hero-title">Community <br><span class="text-accent">Health Calendar</span></h1>
        <p class="hero-description">Stay informed about upcoming health programs, vaccination drives, and medical missions.</p>
    </div>
    </section>

    <!-- Main Section -->
    <main class="container main-content">
        <div class="section-header">
            <h2><i class="fa-regular fa-calendar-days"></i> Calendar Overview</h2>
        </div>
        
        <div class="calendar-card">
            <div id="calendar"></div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-grid">
            <div class="footer-col brand-col">
                <div class="footer-brand">
                    <img src="{{ asset('images/amuyong.png') }}" alt="Logo" class="footer-logo">
                    <span>BARANGAY AMUYONG</span>
                </div>
                <p class="footer-desc">Dedicated to delivering transparent and efficient public health services to our community.</p>
            </div>

            <div class="footer-col">
                <p class="footer-heading">Quick Links</p>
                <ul class="footer-links">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="{{ route('publiccalendar') }}">Calendar</a></li>
                    <li><a href="{{ route('public.announcements') }}">Announcements</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <p class="footer-heading">Contacts</p>
                <ul class="footer-links">
                    <li><a href="{{ url('/contactdirectory') }}">Contact Directory</a></li>
                    <li><a href="{{ route('login') }}">Staff Login</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <p class="footer-address">
                    <i class="fa-solid fa-location-dot"></i>
                        <a href="https://www.google.com/maps/search/?api=1&query=Amuyong+Barangay+Hall+Public+Highway+Alfonso+Cavite"
                          target="_blank"
                             rel="noopener noreferrer">
                             Amuyong Barangay Hall, Public Highway
                        </a>
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; {{ date('Y') }} Barangay Amuyong. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- FullCalendar JS Script -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        let calendarEl = document.getElementById('calendar');

        let calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: '/publicevents', 
            selectable: false,       
            editable: false,         
            eventClick: null,        
            dateClick: null,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            }
        });

        calendar.render();
    });
    </script>

</body>
</html>