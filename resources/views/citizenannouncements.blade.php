<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Amuyong | Announcements</title>
    
    <!-- Google Fonts & FontAwesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/citizenhome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/citizenann.css') }}">
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
                    <li><a href="{{ route('publiccalendar') }}">Calendar</a></li>
                    <li><a href="{{ route('public.announcements') }}" class="active">Announcements</a></li>
                    <li><a href="{{ route('login') }}" class="btn-login"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section with Background Image -->
    <section class="hero page-hero" style="background-image: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(30, 41, 59, 0.85) 100%), url('{{ asset('images/mission.jpg') }}');">
        <div class="container hero-content">
            <span class="badge">Official Updates</span>
            <h1 class="hero-title">Barangay <br><span class="text-accent">Announcements</span></h1>
            <p class="hero-description">Stay informed with official notices, health advisories, and community news from Barangay Amuyong.</p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container main-content">
        <div class="section-header">
            <h2><i class="fa-solid fa-bullhorn"></i> Public Bulletins</h2>
        </div>

        @if($announcements->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <p>No announcements available at the moment.</p>
            </div>
        @else
            <div class="announcements-grid">
                @foreach($announcements as $announcement)
                    <article class="announcement-card">
                        <div class="announcement-header">
                            <span class="category-badge">{{ $announcement->category ?? 'General' }}</span>
                            <time class="post-date"><i class="fa-regular fa-clock"></i> {{ $announcement->created_at->format('M d, Y') }}</time>
                        </div>
                        <h3 class="announcement-title">{{ $announcement->title }}</h3>
                        <p class="announcement-body">{{ $announcement->description }}</p>
                    </article>
                @endforeach
            </div>
        @endif
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
                <p class="footer-heading">Location</p>
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

</body>
</html>