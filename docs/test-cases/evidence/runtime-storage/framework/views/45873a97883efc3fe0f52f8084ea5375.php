<?php $__env->startSection('CSSown'); ?>

<link rel="stylesheet" href="<?php echo e(asset('css/bhw/shared-ui.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<h2 style="text-align:center;">Barangay Health Referral Form</h2>

<p><strong>Date of Referral:</strong> <?php echo e($referral->date_of_referral); ?></p>

<hr>

<p><strong>Name:</strong> <?php echo e($referral->name); ?></p>
<p><strong>Age:</strong> <?php echo e($referral->age); ?></p>
<p><strong>Gender:</strong> <?php echo e($referral->gender); ?></p>
<p><strong>Address:</strong> <?php echo e($referral->address); ?></p>

<hr>

<p><strong>Request/s For:</strong></p>
<p><?php echo e($referral->requests_for); ?></p>

<p><strong>Vital Signs:</strong></p>
<p><?php echo e($referral->vital_signs ?? 'N/A'); ?></p>

<p><strong>Treatment / Intervention Given:</strong></p>
<p><?php echo e($referral->treatment_given ?? 'N/A'); ?></p>

<p><strong>Name of Medication Given:</strong></p>
<p><?php echo e($referral->medication_given ?? 'N/A'); ?></p>

<p><strong>If Self-Medication:</strong></p>
<p><?php echo e($referral->self_medication ?? 'N/A'); ?></p>

<p><strong>Maintenance Schedule/s:</strong></p>
<p><?php echo e($referral->maintenance_schedule ?? 'N/A'); ?></p>

<hr>

<p><strong>Status:</strong> <?php echo e(ucfirst($referral->status)); ?></p>
<p><strong>Referred By:</strong> <?php echo e($referral->referred_by); ?></p>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('templates.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/bhw/referrals/pdf.blade.php ENDPATH**/ ?>