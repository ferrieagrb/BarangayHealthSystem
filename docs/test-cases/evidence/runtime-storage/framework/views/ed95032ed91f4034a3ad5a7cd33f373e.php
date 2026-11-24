
<link rel="stylesheet" href="<?php echo e(asset('css/theme.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/form-layout.css')); ?>?v=<?php echo e(filemtime(public_path('css/form-layout.css'))); ?>">
<script src="<?php echo e(asset('js/appearance.js')); ?>?v=<?php echo e(is_file(public_path('js/appearance.js')) ? filemtime(public_path('js/appearance.js')) : '1'); ?>" defer></script>
<link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>?v=<?php echo e(is_file(public_path('css/pagination.css')) ? filemtime(public_path('css/pagination.css')) : '1'); ?>">
<script src="<?php echo e(asset('js/pagination.js')); ?>?v=<?php echo e(is_file(public_path('js/pagination.js')) ? filemtime(public_path('js/pagination.js')) : '1'); ?>" defer></script>
<?php echo $__env->make('partials.input-autocomplete-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('partials.button-action-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php if(auth()->check()): ?>
    <?php echo $__env->make('partials.medical-autocomplete-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/partials/appearance-assets.blade.php ENDPATH**/ ?>