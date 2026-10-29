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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const eventDialog = document.querySelector('.event-details-dialog');
            const eventTitle = document.getElementById('event-details-title');
            const eventDate = document.getElementById('event-details-date');
            const eventTime = document.getElementById('event-details-time');
            const eventDescription = document.getElementById('event-details-description');
            const closeEventDialog = eventDialog.querySelector('.event-details-close');
            const compactToolbar = window.matchMedia(
                '(max-width: 680px), (max-width: 1024px) and (max-height: 480px)'
            );
            const getToolbar = (compact) => compact
                ? { left: 'prev,next', center: 'title', right: 'today' }
                : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' };

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                contentHeight: 'auto',
                expandRows: true,
                events: '/publicevents',
                selectable: false,
                editable: false,
                headerToolbar: getToolbar(compactToolbar.matches),
                eventClick({ event }) {
                    eventTitle.textContent = event.title;
                    eventDate.textContent = calendar.formatDate(event.start, {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    eventTime.textContent = event.extendedProps.time
                        || (event.allDay
                            ? 'All day'
                            : calendar.formatDate(event.start, { hour: 'numeric', minute: '2-digit' }));
                    eventDescription.textContent = event.extendedProps.description || 'No description available.';
                    eventDialog.showModal();
                }
            });

            closeEventDialog.addEventListener('click', () => eventDialog.close());
            eventDialog.addEventListener('click', (clickEvent) => {
                if (clickEvent.target === eventDialog) {
                    eventDialog.close();
                }
            });
            document.addEventListener('keydown', (keyEvent) => {
                if (keyEvent.key === 'Escape' && eventDialog.open) {
                    eventDialog.close();
                }
            });

            calendar.render();

            compactToolbar.addEventListener('change', (event) => {
                calendar.setOption('headerToolbar', getToolbar(event.matches));
            });
        });
    </script>

</body>
</html>