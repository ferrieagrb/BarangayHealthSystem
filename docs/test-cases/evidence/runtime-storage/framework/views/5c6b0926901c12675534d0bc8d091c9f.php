<?php
    $user = auth()->user();
    // Get theme preference, default to 'light' if not set
    $currentTheme = ($user && $user->theme === 'dark') ? 'dark' : 'light';
    
    // Get font size preference, default to 'normal'
    $currentFontSize = ($user && $user->font_size) ? $user->font_size : 'normal';
?>
<!DOCTYPE html>
<html lang="en" class="theme-<?php echo e($currentTheme); ?>" data-theme="<?php echo e($currentTheme); ?>" data-font="<?php echo e($currentFontSize); ?>">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <link rel="stylesheet" href="<?php echo e(asset('css/layout.css')); ?>?v=<?php echo e(filemtime(public_path('css/layout.css'))); ?>">
    <script src="<?php echo e(asset('js/ui-motion.js')); ?>" defer></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('partials.pwa-meta', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script src="<?php echo e(asset('js/templates/superadmin-inline-1.js')); ?>?v=<?php echo e(filemtime(public_path('js/templates/superadmin-inline-1.js'))); ?>"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .main-content .page-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .main-content .page-title-group h1 {
            margin: 0;
            color: #0f172a;
            font-size: 30px;
            font-weight: 500;
            line-height: 1.35;
        }
        .main-content .page-title-group p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 14px;
        }
    </style>
    <?php echo $__env->yieldContent('CSSown'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/admin/responsive.css')); ?>">
    <?php echo $__env->make('partials.appearance-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/typography.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/sidebar-scroll.css')); ?>?v=<?php echo e(filemtime(public_path('css/sidebar-scroll.css'))); ?>">
</head>



<body data-appearance-role="<?php echo e(auth()->user()->role); ?>" class="theme-<?php echo e($currentTheme); ?> font-<?php echo e($currentFontSize); ?>">
<?php echo $__env->make('partials.global-loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->yieldContent('scripts'); ?>
<div class="dashboard">

    <!-- SIDEBAR -->
    <div class="sidebar" id="adminSidebar" role="navigation" aria-label="Primary navigation">

        <div class="top sidebar-header">
            <a class="sidebar-brand" href="<?php echo e(auth()->user()->isSuperAdmin() ? route('superadmin.dashboard') : route('admin.home')); ?>" aria-label="BHEAMS home">
                <img src="<?php echo e(asset('images/amuyong.png')); ?>" alt="">
                <span>BHEAMS</span>
            </a>
            <button type="button" class="topbutton sidebar-toggle" aria-label="Collapse sidebar" aria-controls="adminSidebar" aria-expanded="true">
                <i class="bx bx-chevron-left" aria-hidden="true"></i>
            </button>
        </div>

        <div class="sidebar-nav" tabindex="0" aria-label="Navigation links">
        <ul>
            <li>
                <a href="<?php echo e(auth()->user()->isSuperAdmin() ? route('superadmin.dashboard') : route('admin.home')); ?>">
                    <i class="bx bx-home"></i>
                    <span class="nav-item">Home</span>
                </a>
            </li>

            <li class="<?php echo e(request()->routeIs('superadmin.users') || request()->routeIs('admin.users') ? 'active' : ''); ?>">
                <a href="<?php echo e(auth()->user()->isSuperAdmin() ? route('superadmin.users') : route('admin.users')); ?>">
                    <i class="bx bx-user"></i>
                    <span class="nav-item">Users</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('superadmin.system.analytics.page')); ?>">
                    <i class="bx bx-network-chart"></i>
                    <span class="nav-item">System Analytics</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('superadmin.system.connections.page')); ?>">
                    <i class="bx bx-link-alt"></i>
                    <span class="nav-item">Connections</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('superadmin.audit-logs')); ?>">
                    <i class="bx bx-folder"></i>
                    <span class="nav-item">System Logs</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('superadmin.system.settings')); ?>">
                    <i class="bx bx-cog"></i>
                    <span class="nav-item">System Settings</span>
                </a>
            </li>

        </ul>
        </div>

        <div class="sidebar-bottom" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1);">
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li>
                    <a href="<?php echo e(route('settings')); ?>">
                        <i class="bx bx-cog"></i>
                        <span class="nav-item">Settings</span>
                    </a>
                </li>
            </ul>
            <?php echo $__env->make('templates.partials.sidebar-account', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

    </div>

    <!-- MAIN -->
    <div class="main-content">
        <div class="top-bar">
    <div class="header-user" style="margin-left:auto; font-weight:500; color:#334155;"><?php echo e(auth()->user()->name); ?></div>
    </div>
        <?php echo $__env->yieldContent('content'); ?>
    </div>

</div>

<?php echo $__env->make('templates.partials.logout-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if(auth()->guard()->check()): ?>
<div id="session-timeout-modal" data-session-lifetime="<?php echo e(config('session.lifetime', 120)); ?>" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 99999; justify-content: center; align-items: center; backdrop-filter: blur(3px);">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 400px; max-width: 90%; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="font-size: 40px; margin-bottom: 10px;">⏰</div>
        <h2 style="font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 10px;">Session Expired</h2>
        <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">Your session has timed out due to inactivity or expiration. Please log back in to continue.</p>
        <a href="<?php echo e(route('login')); ?>" style="display: block; width: 100%; padding: 12px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; box-sizing: border-box;">Log Back In</a>
    </div>
</div>
<?php endif; ?>

<!-- JS -->
<script src="<?php echo e(asset('js/templates/superadmin-inline-2.js')); ?>?v=<?php echo e(filemtime(public_path('js/templates/superadmin-inline-2.js'))); ?>"></script>

<?php if(auth()->guard()->check()): ?>
    
<script src="<?php echo e(asset('js/templates/session-timeout.js')); ?>?v=<?php echo e(filemtime(public_path('js/templates/session-timeout.js'))); ?>"></script>

<?php endif; ?>

<script src="<?php echo e(asset('js/password-toggle.js') . '?v=1793722773'); ?>"></script>
</body>
</html><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/templates/superadmin.blade.php ENDPATH**/ ?>