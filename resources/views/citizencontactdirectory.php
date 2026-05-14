<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="{{ asset('css/citizencontactdirectory.css') }}">

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
        <h1>AGARANG </h1>
        <h1>AKSYON SA</h1>
        <h1>    IYONG KALUSUGAN</h1>
    </div>
    

    <div class="actions">
        <div class="action-box">Check Schedules</div>
        <div class="action-box">Apply as Volunteer</div>
        <div class="action-box">Contact Directory</div>
        <div class="action-box">Transparency</div>
    </div>
</div>

<div class="section">
    <h2>Contact Directory</h2>

    <div class="contact-container">

        <!-- Alfonso Hotlines -->
        <div class="contact-category">
            <h3>Alfonso Municipal Hotlines</h3>

            <div class="contact-card">
                <h4>Municipal Hall</h4>
                <p>(046) 000-0000</p>
            </div>

            <div class="contact-card">
                <h4>Municipal Health Office</h4>
                <p>(046) 000-0001</p>
            </div>

            <div class="contact-card">
                <h4>Rural Health Unit</h4>
                <p>(046) 000-0002</p>
            </div>

            <div class="contact-card">
                <h4>Alfonso Police Station</h4>
                <p>0917-111-1111</p>
            </div>

            <div class="contact-card">
                <h4>Bureau of Fire Protection</h4>
                <p>0917-222-2222</p>
            </div>
        </div>

        <!-- Barangay Amuyong -->
        <div class="contact-category">
            <h3>Barangay Amuyong Hotlines</h3>

            <div class="contact-card">
                <h4>Barangay Hall</h4>
                <p>0958-789-1234</p>
            </div>

            <div class="contact-card">
                <h4>Barangay Health Center</h4>
                <p>0958-789-5678</p>
            </div>

            <div class="contact-card">
                <h4>Barangay Emergency Response</h4>
                <p>0912-345-6789</p>
            </div>

            <div class="contact-card">
                <h4>Barangay Tanod</h4>
                <p>0918-888-8888</p>
            </div>
        </div>

        <!-- Cavite Emergency -->
        <div class="contact-category">
            <h3>Cavite Provincial Hotlines</h3>

            <div class="contact-card">
                <h4>Cavite Provincial Hospital</h4>
                <p>(046) 000-1000</p>
            </div>

            <div class="contact-card">
                <h4>Cavite Disaster Risk Reduction Office</h4>
                <p>0999-111-2222</p>
            </div>

            <div class="contact-card">
                <h4>Cavite Police Provincial Office</h4>
                <p>0917-333-4444</p>
            </div>

            <div class="contact-card">
                <h4>Cavite Rescue</h4>
                <p>0918-555-6666</p>
            </div>
        </div>

        <!-- National Government -->
        <div class="contact-category">
            <h3>General Government Hotlines</h3>

            <div class="contact-card">
                <h4>National Emergency Hotline</h4>
                <p>911</p>
            </div>

            <div class="contact-card">
                <h4>Philippine Red Cross</h4>
                <p>143</p>
            </div>

            <div class="contact-card">
                <h4>PNP Hotline</h4>
                <p>117</p>
            </div>

            <div class="contact-card">
                <h4>Bureau of Fire Protection</h4>
                <p>(02) 8426-0219</p>
            </div>

            <div class="contact-card">
                <h4>DOH Hotline</h4>
                <p>(02) 8651-7800</p>
            </div>
        </div>

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
