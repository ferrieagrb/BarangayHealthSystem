<div class="topbar">
    <div class="container topbar-content">
        <div class="topbar-item">
            <i class="fa-solid fa-phone"></i>
            <span>BARANGAY HOTLINE: <strong>(+63) 958 789 1234</strong></span>
        </div>
        <div class="topbar-item highlight">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>EMERGENCY: <strong>(+63) 958 789 1234</strong></span>
        </div>
    </div>
</div>

<header class="main-header">
    <div class="container nav-container">
        <a href="{{ route('landing') }}" class="brand">
            <img src="{{ \App\Models\SystemSetting::logoUrl() }}" alt="Barangay Amuyong Logo" class="brand-logo">
            <div class="brand-text">
                <span class="brand-title">BARANGAY AMUYONG</span>
                <span class="brand-subtitle">Health Services Portal</span>
            </div>
        </a>

        <button class="menu-toggle" type="button" aria-label="Open navigation menu" aria-controls="primary-navigation" aria-expanded="false">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <nav class="nav-menu" id="primary-navigation" aria-label="Public navigation">
            <ul>
                <li><a href="{{ route('landing') }}" @class(['active' => ($activeNav ?? '') === 'home']) @if(($activeNav ?? '') === 'home') aria-current="page" @endif>Home</a></li>
                <li><a href="{{ route('publiccalendar') }}" @class(['active' => ($activeNav ?? '') === 'calendar']) @if(($activeNav ?? '') === 'calendar') aria-current="page" @endif>Calendar</a></li>
                <li><a href="{{ route('public.announcements') }}" @class(['active' => ($activeNav ?? '') === 'announcements']) @if(($activeNav ?? '') === 'announcements') aria-current="page" @endif>Announcements</a></li>
                <li><a href="{{ route('public.qr-scanner', [], false) }}" @class(['active' => ($activeNav ?? '') === 'qr']) @if(($activeNav ?? '') === 'qr') aria-current="page" @endif>QR Scanner</a></li>
                <li><a href="{{ route('login') }}" class="btn-login"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Login</a></li>
            </ul>
        </nav>
    </div>
</header>

@include('partials.input-autocomplete-assets')

<div class="mobile-nav-backdrop" aria-hidden="true" hidden></div>

<script src="{{ asset('js/public/partials/header-inline-1.js') }}?v={{ filemtime(public_path('js/public/partials/header-inline-1.js')) }}"></script>
