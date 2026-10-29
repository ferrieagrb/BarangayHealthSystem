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
@include('partials.favicon')
</head>
<body>

    @include('public.partials.header', ['activeNav' => 'home'])

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
                    <article class="card event-list-item">
                        <div class="card-body">
                            <span class="card-date-badge">
                                <i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($event->start)->format('M d, Y') }}
                            </span>
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
                    <a href="{{ route('public.announcements.show', $announcement) }}" class="announcement-item announcement-link">
                        @php $logoUrl = \App\Models\SystemSetting::logoUrl(); @endphp
                        <img src="{{ $announcement->images->isNotEmpty() ? $announcement->images->first()->url : $logoUrl }}" alt="{{ $announcement->title }}" class="announcement-image" onerror="this.onerror=null;this.src='{{ $logoUrl }}';this.classList.add('is-logo');">
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
                    </a>
                @empty
                    <div class="empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <p>No announcements posted right now.</p>
                    </div>
                @endforelse
            </div>
        </section>

    </main>

    @include('public.partials.footer')
</body>
</html>