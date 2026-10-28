@extends('templates.citizen')

@section('content')

<main class="citizen-calendar-page">

    <header class="citizen-calendar-heading">
        <h1>Health Center Event Calendar</h1>

        <p>
            Check out upcoming community health programs, checkups, and schedules.
        </p>
    </header>

    <div id="calendar"></div>

</main>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'dayGridMonth',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },

        // Pulls event data from the existing public events route
        events: '{{ route('publicevents') }}',

        eventClick: function(info) {

            const details = [
                'Event: ' + info.event.title,
                'Time: ' + (info.event.extendedProps.time || 'All Day'),
                'Description: ' + (info.event.extendedProps.description || 'No description provided.')
            ].join('\n');
            window.alert(details);

        }

    });

    calendar.render();

});
</script>

@endsection