<?php $__env->startSection('CSSown'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/bhw/referral.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/bhw/shared-ui.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="main bhw-referrals-page">

    <div class="page-top">

        <div class="page-title-group">
            <h1>Referral Dashboard</h1>
            <p>Manage and monitor barangay health referrals.</p>
        </div>

        <a href="<?php echo e(route('referrals.create')); ?>" class="btn-primary" data-ui-action="primary">
            Create Referral
        </a>

    </div>

    <?php if(session('success')): ?>
        <div class="alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="toolbar">

        <div class="toolbar-left">

            <form method="GET" class="search-box">
                <input
                    type="text"
                    name="search"
                    placeholder="Search patient..."
                    value="<?php echo e(request('search')); ?>"
                >
            </form>

        </div>

    </div>

    <div class="table-container">

        <table class="bhw-referrals-table" role="table" aria-label="Referral records">

            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col">Date</th>
                    <th scope="col">Patient</th>
                    <th scope="col">Request For</th>
                    <th scope="col">Status</th>
                    <th scope="col">Update Status</th>
                    <th scope="col">File</th>
                </tr>
            </thead>

            <tbody role="rowgroup">

                <?php $__empty_1 = true; $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                    <tr role="row">

                        <td role="cell" data-label="Date">
                            <?php echo e($referral->date_of_referral); ?>

                        </td>

                        <td role="cell" data-label="Patient">
                            <span class="citizen-name">
                                <?php echo e($referral->name); ?>

                            </span>

                            <span class="citizen-sub">
                                <?php echo e($referral->gender); ?>,
                                <?php echo e($referral->age); ?> years old
                            </span>
                        </td>

                        <td role="cell" data-label="Request For">
                            <?php echo e($referral->requests_for); ?>

                        </td>

                        <td role="cell" data-label="Status">

                            <?php if($referral->status == 'approved'): ?>
                                <span class="status-badge status-approved">
                                    Approved
                                </span>

                            <?php elseif($referral->status == 'released'): ?>
                                <span class="status-badge status-released">
                                    Released
                                </span>

                            <?php elseif($referral->status == 'returned'): ?>
                                <span class="status-badge status-returned">
                                    Returned
                                </span>
                            <?php endif; ?>

                        </td>

                        <td role="cell" data-label="Update Status">

                            <form
                                action="<?php echo e(route('referrals.status', $referral->id)); ?>"
                                method="POST"
                                class="action-group"
                            >

                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>

                                <select
                                    name="status"
                                    class="status-select"
                                    aria-label="Update referral status for <?php echo e($referral->name); ?>"
                                >

                                    <option value="approved"
                                        <?php echo e($referral->status == 'approved' ? 'selected' : ''); ?>>
                                        Approved
                                    </option>

                                    <option value="released"
                                        <?php echo e($referral->status == 'released' ? 'selected' : ''); ?>>
                                        Released
                                    </option>

                                    <option value="returned"
                                        <?php echo e($referral->status == 'returned' ? 'selected' : ''); ?>>
                                        Returned
                                    </option>

                                </select>

                                <button type="submit" class="btn-primary" data-ui-action="primary">
                                    Update
                                </button>

                            </form>

                        </td>

                        <td role="cell" data-label="File">

                            <?php if($referral->file_path): ?>

                                <a
                                    href="<?php echo e(route('referrals.download', $referral->id)); ?>"
                                    class="btn-primary"
                                    data-no-loader
                                 data-ui-action="secondary">
                                    Download PDF
                                </a>

                            <?php else: ?>

                                No file

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                    <tr>
                        <td colspan="6">
                            No referrals found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>
        <?php echo e($referrals->links('partials.pagination')); ?>


    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/bhw/referrals/index.blade.php ENDPATH**/ ?>