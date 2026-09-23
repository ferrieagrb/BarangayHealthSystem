@extends('templates.citizen')

@section('content')

<div style="max-width: 1000px; margin: 30px auto; padding: 25px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">

    <div style="margin-bottom: 20px;">
        <h2>📅 Health Center Event Calendar</h2>

        <p style="color: #666; font-size: 14px;">
            Check out upcoming community health programs, checkups, and schedules.
        </p>
    </div>

    <!-- Calendar Container Element -->
    <div id="calendar"></div>

</div>

<!-- FullCalendar JS CDN -->
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

            alert(
                'Event: ' + info.event.title + '\n' +
                'Time: ' + (info.event.extendedProps.time || 'All Day') + '\n' +
                'Description: ' + (info.event.extendedProps.description || 'No description provided.')
            );

        }

    });

    calendar.render();

});
</script>

@endsection