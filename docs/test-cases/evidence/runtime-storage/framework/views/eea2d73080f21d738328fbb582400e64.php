<?php $__env->startSection('CSSown'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/admin/record-pages.css')); ?>?v=<?php echo e(filemtime(public_path('css/admin/record-pages.css'))); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-record-page archive-page">
    <div class="page-top">
        <div class="page-title-group">
            <h1>Archive</h1>
            <p>Records are never deleted. Archived records are kept here and can be restored at any time.</p>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="archive-alert ok"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <nav class="archive-tabs" data-archive-tabs aria-label="Archived record categories">
        <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route($indexRoute, ['type' => $key])); ?>" class="<?php echo e($key === $type ? 'active' : ''); ?>" <?php if($key === $type): ?> aria-current="page" <?php endif; ?>><?php echo e($tab['label']); ?><span>(<?php echo e($tab['count']); ?>)</span></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <div class="archive-card">
        <table class="archive-table">
            <thead>
                <tr><th>Record</th><th>Details</th><th>Archived on</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php [$title, $detail] = $labeler($item); ?>
                    <tr>
                        <td><?php echo e($title ?: '#' . $item->id); ?></td>
                        <td><?php echo e(trim((string) $detail) !== '' ? $detail : '—'); ?></td>
                        <td><?php echo e(optional($item->deleted_at)->format('M d, Y h:i A')); ?></td>
                        <td>
                            <form method="POST" action="<?php echo e(route($restoreRoute, ['type' => $type, 'id' => $item->id])); ?>" data-confirm-message="Restore this record?" data-confirm-danger="false">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="archive-restore" data-ui-action="primary">Restore</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="archive-empty">Nothing archived here.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <?php echo e($items->links('partials.pagination')); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/audit-log-tabs.js')); ?>?v=<?php echo e(filemtime(public_path('js/audit-log-tabs.js'))); ?>" defer></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($layout, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/archive/index.blade.php ENDPATH**/ ?>