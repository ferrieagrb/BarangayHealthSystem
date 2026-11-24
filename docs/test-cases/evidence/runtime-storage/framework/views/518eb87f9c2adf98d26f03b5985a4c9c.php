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
        <a href="<?php echo e(route('landing')); ?>" class="brand">
            <img src="<?php echo e(\App\Models\SystemSetting::logoUrl()); ?>" alt="Barangay Amuyong Logo" class="brand-logo">
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
                <li><a href="<?php echo e(route('landing')); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['active' => ($activeNav ?? '') === 'home']); ?>" <?php if(($activeNav ?? '') === 'home'): ?> aria-current="page" <?php endif; ?>>Home</a></li>
                <li><a href="<?php echo e(route('publiccalendar')); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['active' => ($activeNav ?? '') === 'calendar']); ?>" <?php if(($activeNav ?? '') === 'calendar'): ?> aria-current="page" <?php endif; ?>>Calendar</a></li>
                <li><a href="<?php echo e(route('public.announcements')); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['active' => ($activeNav ?? '') === 'announcements']); ?>" <?php if(($activeNav ?? '') === 'announcements'): ?> aria-current="page" <?php endif; ?>>Announcements</a></li>
                <li><a href="<?php echo e(route('public.qr-scanner', [], false)); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['active' => ($activeNav ?? '') === 'qr']); ?>" <?php if(($activeNav ?? '') === 'qr'): ?> aria-current="page" <?php endif; ?>>QR Scanner</a></li>
                <li><a href="<?php echo e(route('login')); ?>" class="btn-login"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Login</a></li>
            </ul>
        </nav>
    </div>
</header>

<?php echo $__env->make('partials.input-autocomplete-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="mobile-nav-backdrop" aria-hidden="true" hidden></div>

<script src="<?php echo e(asset('js/public/partials/header-inline-1.js')); ?>?v=<?php echo e(filemtime(public_path('js/public/partials/header-inline-1.js'))); ?>"></script>
<?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/public/partials/header.blade.php ENDPATH**/ ?>