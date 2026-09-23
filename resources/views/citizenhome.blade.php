<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Amuyong | Health & Community Portal</title>
    
    <!-- Google Fonts & FontAwesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/citizenhome.css') }}">
</head>
<body>

    <!-- Top Info Bar -->
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

    <!-- Navigation Header -->
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
                    <li><a href="{{ url('/') }}" class="active">Home</a></li>
                    <li><a href="{{ route('publiccalendar') }}">Calendar</a></li>
                    <li><a href="{{ route('public.announcements') }}">Announcements</a></li>
                    <li><a href="{{ route('login') }}" class="btn-login"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" style="background-image: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(30, 41, 59, 0.85) 100%), url('{{ asset('images/mission.jpg') }}');">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <span class="badge">Community Health Care</span>
            <h1 class="hero-title">Aksyon sa <br><span class="text-accent">Iyong Kalusugan</span></h1>
            <p class="hero-description">Connecting residents to accessible health services, real-time announcements, and community updates.</p>
            
            <div class="hero-actions">
                <a href="{{ url('/contactdirectory') }}" class="btn btn-secondary"><i class="fa-solid fa-address-book"></i> Contact Directory</a>
            </div>
        </div>
    </section>

    <!-- Main Content Area -->
    <main class="container main-content">
        
        <!-- Recent Events Section -->
        <section class="content-section">
            <div class="section-header">
                <h2>Recent Events</h2>
                <a href="{{ route('publiccalendar') }}" class="view-all">View Calendar <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="cards-grid">
                @forelse($recentEvents as $event)
                    <article class="card">
                        <div class="card-image-wrapper">
                            <img src="{{ asset($event->image ?? 'images/mission.jpg') }}" alt="{{ $event->title }}" class="card-img">
                            <span class="card-date-badge">
                                <i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($event->start)->format('M d, Y') }}
                            </span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">{{ $event->title }}</h3>
                            <p class="card-text">{{ \Illuminate\Support\Str::limit($event->description ?? 'No description available', 120) }}</p>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        <p>No recent events available at the moment.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Announcements Section -->
        <section class="content-section">
            <div class="section-header">
                <h2>Announcements</h2>
                <a href="{{ route('public.announcements') }}" class="view-all">All Announcements <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="announcements-list">
                @forelse($recentAnnouncements as $announcement)
                    <div class="announcement-item">
                        <div class="announcement-icon">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <div class="announcement-details">
                            <div class="announcement-meta">
                                <h3 class="announcement-title">{{ $announcement->title }}</h3>
                                <time class="announcement-date">
                                    <i class="fa-regular fa-clock"></i> {{ \Carbon\Carbon::parse($announcement->created_at)->format('M d, Y') }}
                                </time>
                            </div>
                            <p class="announcement-text">{{ \Illuminate\Support\Str::limit($announcement->description, 150) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <p>No announcements posted right now.</p>
                    </div>
                @endforelse
            </div>
        </section>

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