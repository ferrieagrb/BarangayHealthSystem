<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="{{ asset('css/citizenhome.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">

<title>Barangay Amuyong Calendar</title>

<style>
#calendar {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    width: 100%;
    min-height: 80vh;
}
.fc .fc-daygrid-event {
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 14px;
    min-height: 36px;
    display: flex;
    align-items: center;
    white-space: normal !important;
}
.fc-event-title {
    white-space: normal !important;
    overflow: visible !important;
}
</style>
</head>
<body>

<div class="topbar">
    <div>BARANGAY HOTLINE: (+63) 958 789 1234</div>
    <div>EMERGENCY HOTLINE: (+63) 958 789 1234</div>
</div>

<div class="hero">
    <div class="nav">
        <img src="{{ asset('images/amuyong.png') }}" height="75px" width="75px">
        <h2>BARANGAY AMUYONG</h2>
        <ul>
            <li><a href="{{ url('/') }}">Home</a></li>
            <li><a href="{{ route('publiccalendar') }}">Calendar</a></li>
            <li><a href="{{ route('public.announcements') }}">Announcements</a></li>
            <li><a href="{{ route('login') }}">Login</a></li>
        </ul>
    </div>

    <div class="tagline">
        <h1>TESTING</h1>
        <h1>AKSYON SA</h1>
        <h1>IYONG KALUSUGAN</h1>
    </div>
</div>

<div class="section">
    <h2>Calendar Overview</h2>
    <div id="calendar"></div>
</div>

<div class="footer">
    <div class="footer-content">
        <div>
            <p>Quick Links</p>
            <ul>
                <li>Home</li>
                <li>Calendar</li>
                <li>Announcements</li>
            </ul>
        </div>
        <div>
            <p>Contacts</p>
            <ul>
                <li>Apply Now</li>
            </ul>
        </div>
        <div>
            <p>Connect to us</p>
            <p>Amuyong Barangay Hall</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let calendarEl = document.getElementById('calendar');

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: '/publicevents', // fetch events from controller
        selectable: false,       // disable date selection
        editable: false,         // disable drag & drop
        eventClick: null,        // disable event click
        dateClick: null           // disable date click
    });

    calendar.render();
});
</script>

</body>
</html>