<footer class="footer">
    <div class="container footer-grid">
        <div class="footer-col brand-col">
            <div class="footer-brand">
                <img src="{{ \App\Models\SystemSetting::logoUrl() }}" alt="Logo" class="footer-logo">
                <span>BARANGAY AMUYONG</span>
            </div>
            <p class="footer-desc">Dedicated to delivering transparent and efficient public health services to our community.</p>
        </div>

        <div class="footer-col">
            <p class="footer-heading">Quick Links</p>
            <ul class="footer-links">
                <li><a href="{{ route('landing') }}">Home</a></li>
                <li><a href="{{ route('publiccalendar') }}">Calendar</a></li>
                <li><a href="{{ route('public.announcements') }}">Announcements</a></li>
                <li><a href="{{ route('public.qr-scanner', [], false) }}">QR Scanner</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <p class="footer-heading">Contacts</p>
            <ul class="footer-links">
                <li><a href="{{ route('contactdirectory') }}">Contact Directory</a></li>
                <li><a href="tel:+639587891234">Barangay Hotline: (+63) 958 789 1234</a></li>
                <li><a href="{{ route('login') }}">Staff Login</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <p class="footer-heading">Location</p>
            <p class="footer-address">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <a href="https://www.google.com/maps/search/?api=1&amp;query=Amuyong+Barangay+Hall+Public+Highway+Alfonso+Cavite"
                   target="_blank"
                   rel="noopener noreferrer">
                    Amuyong Barangay Hall, Public Highway
                </a>
            </p>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; {{ date('Y') }} Barangay Amuyong. All rights reserved.</p>
        </div>
    </div>
</footer>
