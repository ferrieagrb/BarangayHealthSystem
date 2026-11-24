<?php
    $accountUser = auth()->user();
    $accountRole = match ($accountUser->role) {
        'superadmin' => 'Super Admin User',
        'admin' => 'Admin User',
        'bhw' => 'BHW User',
        'citizen' => 'Citizen User',
        default => ucfirst(str_replace('_', ' ', $accountUser->role)).' User',
    };
?>
<div class="sidebar-account">
    <span class="sidebar-avatar" aria-hidden="true">
        <i class="bx bx-user"></i>
    </span>
    <span class="sidebar-account-copy">
        <span class="sidebar-account-name"><?php echo e($accountUser->name); ?></span>
        <span class="sidebar-account-role"><?php echo e($accountRole); ?></span>
    </span>
    <form method="POST" action="<?php echo e(route('logout')); ?>" class="logout-form sidebar-account-logout" data-no-loader="true">
        <?php echo csrf_field(); ?>
        <button type="submit" aria-label="Log out" title="Log out">
            <i class="bx bx-log-out" aria-hidden="true"></i>
        </button>
    </form>
</div>
<?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/templates/partials/sidebar-account.blade.php ENDPATH**/ ?>