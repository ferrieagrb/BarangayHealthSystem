<link rel="stylesheet" href="{{ asset('css/global-loader.css') }}?v={{ filemtime(public_path('css/global-loader.css')) }}">
<div id="global-loader" class="active gl-boot" role="status" aria-live="polite">
    <div class="logo-spinner-wrapper">
        <div class="spinner-ring-blue"></div>
        <div class="spinner-ring-white"></div>
        <img src="{{ asset('images/amuyong.png') }}" alt="" class="spinner-logo">
    </div>
    <span class="loader-status-text">Loading...</span>
</div>
<script src="{{ asset('js/global-loader.js') }}?v={{ filemtime(public_path('js/global-loader.js')) }}"></script>
