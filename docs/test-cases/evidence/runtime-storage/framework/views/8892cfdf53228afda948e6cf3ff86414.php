<?php if($canEdit && !(bool) $citizen->Citizen_IsDeceased): ?>
    <form id="citizen-photo-form" method="POST" action="<?php echo e(route('citizen.photo.bhw-update', $citizen->id)); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
    </form>
<?php endif; ?>
<?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/bhw/partials/citizen-photo-form.blade.php ENDPATH**/ ?>