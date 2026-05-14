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
        <img src="../images/amuyong.png" height="75px" width="75px">
        <h2>BARANGAY AMUYONG</h2>
        <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">Calendar</a></li>
            <li><a href="#">Announcements</a></li>
            <li><a href="/login">Login</a></li>
        </ul>
    </div>

    <div class="tagline">
        <h1>TESTING </h1>
        <h1>AKSYON SA</h1>
        <h1>    IYONG KALUSUGAN</h1>
    </div>
    

    <div class="actions">
        <div class="action-box">Check Schedules</div>
        <div class="action-box"></div>
        <div class="action-box">Contact Directory</div>
        <div class="action-box">QR Scanner</div>
    </div>
</div>

<div class="section">
    <h2>Announcements</h2>
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
// Simple scroll effect for navbar
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
