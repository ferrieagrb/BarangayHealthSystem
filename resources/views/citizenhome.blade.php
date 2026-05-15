<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="{{ asset('css/citizenhome.css') }}">

<title>Barangay Amuyong</title>
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

    <div class="actions">
        <div class="action-box">Check Inventory</div>
        <div class="action-box"><a href="{{ url('/contactdirectory') }}">Contact Directory</a></div>
    </div>
</div>

<div class="section">
    <h2>RECENT EVENTS</h2>

    <div class="cards">
        @forelse($recentEvents as $event)
            <div class="card">
                <img src="{{ asset($event->image ?? 'images/mission.jpg') }}" alt="{{ $event->title }}">
                <p><strong>{{ $event->title }}</strong></p>
                <p>{{ \Illuminate\Support\Str::limit($event->description ?? 'No description available', 150) }}</p>
                <p><em>{{ \Carbon\Carbon::parse($event->start)->format('M d, Y') }}</em></p>
            </div>
        @empty
            <p>No recent events available.</p>
        @endforelse
    </div>

    <div class="divider"></div>

    <div class="announcements">
        <h2>ANNOUNCEMENTS</h2>
        @forelse($recentAnnouncements as $announcement)
            <div class="announcement">
                <strong>{{ $announcement->title }}</strong> 
                - {{ \Illuminate\Support\Str::limit($announcement->description, 120) }}
                <span style="float:right; font-size:0.8em;">{{ \Carbon\Carbon::parse($announcement->created_at)->format('M d, Y') }}</span>
            </div>
        @empty
            <p>No announcements at the moment.</p>
        @endforelse
    </div>
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

<script>
window.addEventListener('scroll', function() {
    const nav = document.querySelector('.hero');
    if (window.scrollY > 50) {
        nav.style.opacity = '0.95';
    } else {
        nav.style.opacity = '1';
    }
});
</script>

</body>
</html>