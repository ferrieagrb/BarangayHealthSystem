<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Amuyong Calendar</title>
    @include('partials.favicon')
    
    <!-- Google Fonts & FontAwesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Theme Stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/citizenhome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/citizencalendar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/typography.css') }}">
    @include('partials.button-action-assets')
</head>
<body>

    @include('public.partials.header', ['activeNav' => 'calendar'])

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

        <dialog class="event-details-dialog" aria-labelledby="event-details-title">
            <div class="event-details-header">
                <h2 id="event-details-title"></h2>
                <button class="event-details-close" type="button" aria-label="Close event details">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
            <dl class="event-details-list">
                <div>
                    <dt>Date</dt>
                    <dd id="event-details-date"></dd>
                </div>
                <div>
                    <dt>Time</dt>
                    <dd id="event-details-time"></dd>
                </div>
                <div>
                    <dt>Description</dt>
                    <dd id="event-details-description"></dd>
                </div>
            </dl>
        </dialog>
    </main>

    @include('public.partials.footer')

    <!-- FullCalendar JS Script -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="{{ asset('js/citizencalendar-inline-1.js') }}?v={{ filemtime(public_path('js/citizencalendar-inline-1.js')) }}"></script>

</body>
</html>