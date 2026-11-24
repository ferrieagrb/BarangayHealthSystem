<?php $__env->startSection('CSSown'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/bhw/supply_create.css')); ?>?v=<?php echo e(filemtime(public_path('css/bhw/supply_create.css'))); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/bhw/shared-ui.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="page-top">
    <div class="page-title">
        <h1>Add Supply Item Catalog</h1>
        <p>Create a new generic supply entry</p>
    </div>
</div>

<div class="form-container">

    <?php if($errors->any()): ?>
        <div class="supply-form-errors" role="alert">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($error); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('supplies.store')); ?>" id="supplyForm">
        <?php echo csrf_field(); ?>

        <div class="form-grid">

            <!-- ITEM NAME -->
            <div class="form-group" style="grid-column: span 2;">
                <label>Item Name</label>
                <input type="text" name="name" data-medical-autocomplete="supply" value="<?php echo e(old('name')); ?>" maxlength="255" required placeholder="Select category, then search item name">
            </div>

            <!-- CATEGORY (Dynamic from Database) -->
            <div class="form-group" style="grid-column: span 2;">
                <label>Category</label>
                <select name="category" required>
                    <option value="" disabled selected>Select Category</option>
                    <?php if(isset($categories)): ?>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->name); ?>" <?php if(old('category') === $cat->name): echo 'selected'; endif; ?>><?php echo e($cat->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- MINIMUM STOCK THRESHOLD -->
            <div class="form-group" style="grid-column: span 2;">
                <label>Minimum Stock Alert Level</label>
                <input type="number" name="min_stock" required min="0" value="5">
            </div>

            <!-- DESCRIPTION -->
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Description / Notes</label>
                <textarea name="description" placeholder="Enter general item notes..."></textarea>
            </div>

        </div>

        <!-- ACTIONS -->
        <div class="form-actions">
            <button type="submit" class="btn-primary" data-ui-action="primary">Save Item</button>
            <a href="<?php echo e(url('/supplies')); ?>" class="btn-secondary" data-ui-action="secondary">Cancel</a>
        </div>

    </form>

</div>


<!-- SUPPLY ITEM CONFIRMATION MODAL -->

<div id="supplyConfirmModal" class="confirm-overlay">

    <div class="confirm-modal">

        <h2>Confirm Supply Item</h2>

        <p>
            Are you sure you want to save
            <strong id="confirmSupplyName"></strong>
            under
            <strong id="confirmSupplyCategory"></strong>?
        </p>

        <div class="confirm-buttons">

            <button
                type="button"
                id="cancelSupplyConfirm"
                class="confirm-cancel" data-ui-action="secondary">
                Cancel
            </button>

            <button
                type="button"
                id="saveSupplyConfirm"
                class="confirm-save" data-ui-action="primary">
                Yes, Save
            </button>

        </div>

    </div>

</div>


<!-- ACTION CONFIRMATION JAVASCRIPT -->

<script src="<?php echo e(asset('js/bhw/supply_create-inline-1.js')); ?>?v=<?php echo e(filemtime(public_path('js/bhw/supply_create-inline-1.js'))); ?>"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/bhw/supply_create.blade.php ENDPATH**/ ?>