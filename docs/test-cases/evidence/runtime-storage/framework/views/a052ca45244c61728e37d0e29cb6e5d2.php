<?php if($paginator->hasPages()): ?>
    <?php
        $numbered = method_exists($paginator, 'lastPage');
        $current = method_exists($paginator, 'currentPage') ? $paginator->currentPage() : null;
        $pages = collect();
        if ($numbered) {
            $last = $paginator->lastPage();
            $window = min($last, max(1, $current));
            $start = max(1, $window - 1);
            $end = min($last, $window + 1);
            if ($window <= 3) $end = min($last, max(3, $end));
            if ($window >= $last - 2) $start = max(1, min($last - 2, $start));
            $pages = collect([1, $last])->merge(range($start, $end))->unique()->sort()->values();
        }
        $previousPage = null;
    ?>
    <nav class="bheams-pagination" aria-label="Pagination navigation">
        <p class="bheams-pagination-summary">
            <?php if($numbered): ?>
                Showing <?php echo e($paginator->firstItem() ?? 0); ?> to <?php echo e($paginator->lastItem() ?? 0); ?> of <?php echo e($paginator->total()); ?> results
            <?php elseif($current): ?>
                Page <?php echo e($current); ?>

            <?php else: ?>
                More results
            <?php endif; ?>
        </p>
        <div class="bheams-pagination-scroll" data-pagination-scroll tabindex="0" aria-label="Page links, scroll horizontally for more">
            <ul class="bheams-page-list">
                <li>
                    <?php if($paginator->onFirstPage()): ?>
                        <span class="bheams-page-link is-disabled" aria-disabled="true" aria-label="Previous page">&lsaquo;</span>
                    <?php else: ?>
                        <a class="bheams-page-link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" aria-label="Previous page">&lsaquo;</a>
                    <?php endif; ?>
                </li>
                <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($previousPage !== null && $page > $previousPage + 1): ?>
                        <li><span class="bheams-page-link is-gap" aria-hidden="true">&hellip;</span></li>
                    <?php endif; ?>
                    <li>
                        <?php if($page === $current): ?>
                            <span class="bheams-page-link is-current" aria-current="page" aria-label="Page <?php echo e($page); ?>"><?php echo e($page); ?></span>
                        <?php else: ?>
                            <a class="bheams-page-link" href="<?php echo e($paginator->url($page)); ?>" aria-label="Page <?php echo e($page); ?>"><?php echo e($page); ?></a>
                        <?php endif; ?>
                    </li>
                    <?php $previousPage = $page; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <?php if($paginator->hasMorePages()): ?>
                        <a class="bheams-page-link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" aria-label="Next page">&rsaquo;</a>
                    <?php else: ?>
                        <span class="bheams-page-link is-disabled" aria-disabled="true" aria-label="Next page">&rsaquo;</span>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/partials/pagination.blade.php ENDPATH**/ ?>