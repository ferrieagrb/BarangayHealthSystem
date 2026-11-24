<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Amuyong - Health Information System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for modern icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>">
<?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('partials.button-action-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('partials.input-autocomplete-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body id="appActionControls">
<?php echo $__env->make('partials.global-loader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="login-card">
        <div class="top-nav">
            <a href="<?php echo e(route('landing')); ?>" class="back-home">
                <i class="fa-solid fa-arrow-left"></i> Home Page
            </a>
        </div>

        <div class="welcome-header">
            <img src="<?php echo e(\App\Models\SystemSetting::logoUrl()); ?>" alt="Barangay Amuyong Logo" class="logo" height="100" width="100">
            <h2>Barangay Amuyong<br><span style="font-size: 1.1rem; font-weight: 500; color: #64748b;">Health Information System</span></h2>
            <p>Please enter your credentials to access your dashboard</p>
        </div>

        <form action="<?php echo e(route('login.submit')); ?>" method="POST" data-browser-autocomplete="allow">
            <?php echo csrf_field(); ?>  
            
            <?php if(session('error')): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo e(session('error')); ?></span>
                </div>
            <?php endif; ?>

            <?php if(session('status')): ?>
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?php echo e(session('status')); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="input-group">
                <label for="Username">Username or Email</label>
                <div class="input-field-wrapper">
                    <i class="fa-regular fa-user"></i>
                    <input type="text" id="Username" placeholder="Enter your username" name="Username" autocomplete="username" required>
                </div>
            </div>

            <div class="input-group">
                <div class="password-label">
                    <label for="Password">Password</label>
                    <a href="<?php echo e(route('password.request')); ?>" class="forgot-password">Forgot password?</a>
                </div>
                <div class="input-field-wrapper">
                    <i class="fa-lock fa-solid"></i>
                    <input type="password" id="Password" placeholder="Enter your password" name="Password" autocomplete="current-password" required>
                </div>
            </div>

            <button type="submit" class="submit-btn" data-ui-action="primary">
                <span>Sign In</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="portal-footer">
            <p>&copy; 2026 Barangay Amuyong. All rights reserved.</p>
        </div>
    </div> 

<script src="<?php echo e(asset('js/password-toggle.js') . '?v=1793722773'); ?>"></script>
</body>
</html><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/login.blade.php ENDPATH**/ ?>