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

    @include('public.partials.header', ['activeNav' => 'announcements'])

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
                    <a href="{{ route('public.announcements.show', $announcement) }}" class="announcement-card">
                        <div class="announcement-gallery">
                            @php $logoUrl = \App\Models\SystemSetting::logoUrl(); @endphp
                            @forelse($announcement->images as $image)
                                <img src="{{ $image->url }}" alt="{{ $announcement->title }}" onerror="this.onerror=null;this.src='{{ $logoUrl }}';this.classList.add('is-logo');">
                            @empty
                                <img src="{{ $logoUrl }}" alt="{{ $announcement->title }}" class="is-logo">
                            @endforelse
                        </div>
                        <div class="announcement-header">
                            <span class="category-badge">{{ $announcement->category ?? 'General' }}</span>
                            <time class="post-date"><i class="fa-regular fa-clock"></i> {{ $announcement->created_at->format('M d, Y') }}</time>
                        </div>
                        <h3 class="announcement-title">{{ $announcement->title }}</h3>
                        <p class="announcement-body">{{ $announcement->description }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </main>

    @include('public.partials.footer')

</body>
</html>