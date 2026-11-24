<?php $__env->startSection('CSSown'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/admin/admin_supplies.css')); ?>?v=<?php echo e(filemtime(public_path('css/admin/admin_supplies.css'))); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<?php if($errors->any()): ?>
    <div class="inventory-alert inventory-alert-error" role="alert">
        <ul>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>


<?php if(session('success')): ?>
    <div class="inventory-alert inventory-alert-success" role="alert">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>


<div class="page-top inventory-page-header">
    <div class="page-title-group">
        <h1>Manage Inventory</h1>
        <p>
            Monitor health center medical supplies, stock levels,
            and hierarchical batch distributions.
        </p>
    </div>

    <div class="inventory-header-actions">
        <?php if (\Illuminate\Support\Facades\Blade::check('canWrite', 'supplies')): ?>
            <button
                type="button"
                onclick="openCategoryModal()"
                class="inventory-button inventory-button-secondary"
             data-ui-action="secondary">
                Manage Categories
            </button>

            <?php if(auth()->user()->hasWriteAccess('supplies')): ?>
                <button
                    type="button"
                    onclick="openCatalogModal()"
                    class="btn-primary"
                 data-ui-action="primary">
                    + New Catalog Item
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>


<div class="summary-cards inventory-summary-cards">

    <div class="summary-card inventory-summary-card">
        <span>Total Stock Qty</span>
        <strong><?php echo e($totalsupply); ?></strong>
    </div>

    <div class="summary-card inventory-summary-card">
        <span>Well-Stocked Items</span>
        <strong class="inventory-stock-good"><?php echo e($wellStocked); ?></strong>
    </div>

    <div class="summary-card inventory-summary-card">
        <span>Low Stock Alerts</span>
        <strong class="inventory-stock-low"><?php echo e($lowStock); ?></strong>
    </div>

</div>


<div class="toolbar inventory-toolbar">
    <form method="GET" action="<?php echo e(route('admin.supplies')); ?>">
        <input
            type="text"
            name="search"
            value="<?php echo e(request('search')); ?>"
            placeholder="Search item name or code..."
            aria-label="Search supplies"
        >
    </form>
</div>


<div class="table-container inventory-container">

    <h3 class="inventory-section-title">
        Master Inventory Hierarchy
    </h3>

    <?php
        $groupedSupplies = $supplies->groupBy('name');
    ?>

    <?php $__empty_1 = true; $__currentLoopData = $groupedSupplies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

        <?php
            $firstItem = $group->first();
            $totalQty = $group->sum('quantity');
            $category = $firstItem ? $firstItem->category : 'N/A';
            $minThreshold = $firstItem
                ? ($firstItem->min_stock ?? 10)
                : 10;

            if ($totalQty <= 0) {
                $statusLabel = 'Out of Stock';
                $statusClass = 'inventory-status-out';
            } elseif ($totalQty <= $minThreshold) {
                $statusLabel = 'Low Stock';
                $statusClass = 'inventory-status-low';
            } else {
                $statusLabel = 'In Stock';
                $statusClass = 'inventory-status-good';
            }
        ?>

        <details class="inventory-item">

            <summary class="inventory-item-summary">

                <span class="inventory-item-information">
                    <strong><?php echo e($name); ?></strong>

                    <span class="inventory-item-category">
                        Category: <?php echo e($category); ?>

                    </span>

                    <span class="inventory-item-quantity">
                        Total Qty: <?php echo e($totalQty); ?>

                    </span>
                </span>

                <span class="inventory-status <?php echo e($statusClass); ?>">
                    <?php echo e($statusLabel); ?>

                </span>

            </summary>

            <div class="inventory-batch-content">

                <div class="inventory-batch-header">

                    <p>
                        <strong>
                            Active Batch Records (<?php echo e(count($group)); ?> packs):
                        </strong>
                    </p>

                    <?php if(auth()->user()->hasWriteAccess('supplies')): ?>
                    <div class="inventory-item-actions">
                    <button
                        type="button"
                        onclick="openDepositModalForItem(<?php echo \Illuminate\Support\Js::from($name)->toHtml() ?>)"
                        class="inventory-button inventory-button-deposit"
                     data-ui-action="secondary">
                        + Deposit Stock For This Item
                    </button>
                    <form action="<?php echo e(route('supplies.archive-item', $group->first()->id)); ?>" method="POST" data-confirm-message="Archive this item and all its packs? You can restore them from the Supplies archive.">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="inventory-button" data-ui-action="danger">Archive Item</button>
                    </form>
                    </div>
                    <?php endif; ?>

                </div>

                <div class="inventory-table-wrapper">

                    <table class="inventory-batch-table">

                        <thead>
                            <tr>
                                <th>Item #</th>
                                <th>Serial #</th>
                                <th>Quantity</th>
                                <th>Expiration Date</th>
                                <th>Supplier</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $group; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <?php echo e($batch->item_number ?? 'N/A'); ?>

                                    </td>

                                    <td>
                                        <?php echo e($batch->serial_number ?? 'N/A'); ?>

                                    </td>

                                    <td>
                                        <?php echo e($batch->quantity); ?>

                                        <?php echo e($batch->unit); ?>

                                    </td>

                                    <td>
                                        <?php echo e($batch->expiration_date
                                            ? \Carbon\Carbon::parse($batch->expiration_date)->format('Y-m-d')
                                            : 'N/A'); ?>

                                    </td>

                                    <td>
                                        <?php echo e($batch->supplier ?? 'N/A'); ?>

                                    </td>
                                    <td>
                                        <?php if(auth()->user()->hasWriteAccess('supplies')): ?>
                                            <form class="inventory-pack-actions" action="<?php echo e(url('/supplies/' . $batch->id)); ?>" method="POST" data-confirm-message="Archive this supply pack? You can restore it from the Supplies archive.">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="inventory-button" data-ui-action="danger">Archive</button>
                                            </form>
                                        <?php else: ?>
                                            <span>Read Only</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>

                    </table>

                </div>

            </div>

        </details>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="inventory-empty-state">
            No supplies found.
        </p>
    <?php endif; ?>

</div>


<div id="categoryModal" class="inventory-modal">

    <div class="inventory-modal-content inventory-category-modal">

        <div class="inventory-modal-header">
            <h3>⚙️ Manage Inventory Categories</h3>

            <button
                type="button"
                onclick="closeCategoryModal()"
                class="inventory-modal-close"
                aria-label="Close category modal"
             data-ui-action="secondary">
                &times;
            </button>
        </div>

        
        <form
            id="categoryForm"
            action="<?php echo e(route('admin.categories.store')); ?>"
            method="POST"
            class="inventory-category-form"
        >
            <?php echo csrf_field(); ?>

            <input
                type="hidden"
                name="_method"
                id="category_method_field"
                value="POST"
            >

            <input
                type="hidden"
                id="edit_category_id"
                value=""
            >

            <label
                id="category_form_label"
                for="category_name_input"
            >
                Add New Category
            </label>

            <div class="inventory-category-form-row">

                <input
                    type="text"
                    name="name"
                    id="category_name_input"
                    placeholder="Category name..."
                    required
                >

                <button
                    type="submit"
                    id="category_submit_btn"
                    class="inventory-button inventory-button-primary"
                 data-ui-action="primary">
                    Add
                </button>

                <button
                    type="button"
                    id="category_cancel_edit_btn"
                    onclick="resetCategoryForm()"
                    class="inventory-button inventory-button-secondary"
                    hidden
                 data-ui-action="secondary">
                    Cancel
                </button>

            </div>
        </form>

        
        <p class="inventory-category-list-label">
            Existing Categories:
        </p>

        <div class="inventory-category-list">

            <table>
                <tbody>

                    <?php if(isset($categories)): ?>

                        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>

                                <td><?php echo e($cat->name); ?></td>

                                <td class="inventory-category-actions">

                                    <button
                                        type="button"
                                        onclick="editCategory(
                                            <?php echo \Illuminate\Support\Js::from($cat->id)->toHtml() ?>,
                                            <?php echo \Illuminate\Support\Js::from($cat->name)->toHtml() ?>
                                        )"
                                        class="inventory-link-button"
                                     data-ui-action="secondary">
                                        Edit
                                    </button>

                                    <form
                                        action="<?php echo e(route('admin.categories.destroy', $cat->id)); ?>"
                                        method="POST"
                                        data-confirm-message="Archive this category? You can restore it from the archive."
                                    >
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>

                                        <button
                                            type="submit"
                                            class="inventory-link-button inventory-link-danger"
                                         data-ui-action="danger">
                                            Archive
                                        </button>
                                    </form>

                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td class="inventory-empty-state">
                                    No categories available.
                                </td>
                            </tr>
                        <?php endif; ?>

                    <?php else: ?>
                        <tr>
                            <td class="inventory-empty-state">
                                No categories available.
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>

        </div>

        <div class="inventory-modal-footer">
            <button
                type="button"
                onclick="closeCategoryModal()"
                class="inventory-button inventory-button-secondary"
             data-ui-action="secondary">
                Close
            </button>
        </div>

    </div>

</div>


<div id="catalogModal" class="inventory-modal">

    <div class="inventory-modal-content inventory-catalog-modal">

        <div class="inventory-modal-header">
            <h3>Create New Catalog Item</h3>

            <button
                type="button"
                onclick="closeCatalogModal()"
                class="inventory-modal-close"
                aria-label="Close catalog modal"
             data-ui-action="secondary">
                &times;
            </button>
        </div>

        <form action="<?php echo e(url('/supplies')); ?>" method="POST">

            <?php echo csrf_field(); ?>

            <div class="inventory-form-group">
                <label for="catalog_item_name">Item Name</label>

                <input
                    type="text"
                    name="name"
                    id="catalog_item_name"
                    data-medical-autocomplete="supply"
                    maxlength="255"
                    placeholder="Select category, then search item name"
                    required
                >
            </div>

            <div class="inventory-form-group">
                <label for="catalog_category">Category</label>

                <select
                    name="category"
                    id="catalog_category"
                    required
                >
                    <option value="" disabled selected>
                        Select Category
                    </option>

                    <?php if(isset($categories)): ?>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->name); ?>">
                                <?php echo e($cat->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>

                </select>
            </div>

            <div class="inventory-modal-footer">

                <button
                    type="button"
                    onclick="closeCatalogModal()"
                    class="inventory-button inventory-button-secondary"
                 data-ui-action="secondary">
                    Cancel
                </button>

                <button
                    type="submit"
                    class="inventory-button inventory-button-primary"
                 data-ui-action="primary">
                    Save
                </button>

            </div>

        </form>

    </div>

</div>


<div id="depositModal" class="inventory-modal">

    <div class="inventory-modal-content inventory-deposit-modal">

        <div class="inventory-modal-header">
            <h3>Deposit Stock Batches</h3>

            <button
                type="button"
                onclick="closeDepositModal()"
                class="inventory-modal-close"
                aria-label="Close deposit modal"
             data-ui-action="secondary">
                &times;
            </button>
        </div>

        <form
            action="<?php echo e(route('admin.supplies.deposit')); ?>"
            method="POST"
        >

            <?php echo csrf_field(); ?>

            <div class="inventory-form-group">
                <label for="deposit_modal_item_name">
                    Main Item Name / Target
                </label>

                <input
                    type="text"
                    name="name"
                    id="deposit_modal_item_name"
                    required
                    placeholder="Enter item name"
                >
            </div>

            <div class="inventory-deposit-table-wrapper">

                <table
                    id="depositRowsTable"
                    class="inventory-deposit-table"
                >

                    <thead>
                        <tr>
                            <th>Item/Code</th>
                            <th>Serial #</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Expiration</th>
                            <th>Supplier</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>
                                <input
                                    type="text"
                                    name="packs[0][item_number]"
                                    placeholder="Item/Code"
                                    required
                                >
                            </td>

                            <td>
                                <input
                                    type="text"
                                    name="packs[0][serial_number]"
                                    placeholder="Serial #"
                                >
                            </td>

                            <td>
                                <input
                                    type="number"
                                    name="packs[0][quantity]"
                                    placeholder="Qty"
                                    required
                                    min="1"
                                >
                            </td>

                            <td>
                                <input
                                    type="text"
                                    name="packs[0][unit]"
                                    placeholder="e.g. box"
                                >
                            </td>

                            <td>
                                <input
                                    type="date"
                                    name="packs[0][expiration_date]"
                                    required
                                >
                            </td>

                            <td>
                                <input
                                    type="text"
                                    name="packs[0][supplier]"
                                    placeholder="Supplier"
                                >
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="inventory-remove-row removeRowBtn"
                                    disabled
                                    aria-label="Remove row"
                                 data-ui-action="secondary">
                                    &times;
                                </button>
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

            <button
                type="button"
                id="addRowBtn"
                class="inventory-button inventory-button-secondary inventory-add-row"
             data-ui-action="primary">
                + Add Another Row
            </button>

            <div class="inventory-modal-footer">

                <button
                    type="button"
                    onclick="closeDepositModal()"
                    class="inventory-button inventory-button-secondary"
                 data-ui-action="secondary">
                    Cancel
                </button>

                <button
                    type="submit"
                    class="inventory-button inventory-button-primary"
                 data-ui-action="primary">
                    Confirm Deposit
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const categoryModal = document.getElementById('categoryModal');
    const catalogModal = document.getElementById('catalogModal');
    const depositModal = document.getElementById('depositModal');

    const categoryForm = document.getElementById('categoryForm');
    const categoryMethodField = document.getElementById('category_method_field');
    const categoryFormLabel = document.getElementById('category_form_label');
    const categoryNameInput = document.getElementById('category_name_input');
    const categorySubmitButton = document.getElementById('category_submit_btn');
    const categoryCancelButton = document.getElementById('category_cancel_edit_btn');

    const depositItemName = document.getElementById('deposit_modal_item_name');
    const addRowButton = document.getElementById('addRowBtn');
    const depositRowsTable = document
        .getElementById('depositRowsTable')
        .querySelector('tbody');

    let rowIndex = 1;

    // CATEGORY MODAL

    window.openCategoryModal = function () {
        categoryModal.style.display = 'flex';
    };

    window.closeCategoryModal = function () {
        categoryModal.style.display = 'none';
        window.resetCategoryForm();
    };

    window.editCategory = function (id, name) {
        categoryForm.action = '/admin/categories/' + encodeURIComponent(id);

        categoryMethodField.value = 'PUT';
        categoryFormLabel.textContent = 'Edit Category: ' + name;
        categoryNameInput.value = name;
        categorySubmitButton.textContent = 'Update';
        categoryCancelButton.hidden = false;
    };

    window.resetCategoryForm = function () {
        categoryForm.action = <?php echo json_encode(route('admin.categories.store'), 15, 512) ?>;

        categoryMethodField.value = 'POST';
        categoryFormLabel.textContent = 'Add New Category';
        categoryNameInput.value = '';
        categorySubmitButton.textContent = 'Add';
        categoryCancelButton.hidden = true;
    };

    // CATALOG MODAL

    window.openCatalogModal = function () {
        catalogModal.style.display = 'flex';
    };

    window.closeCatalogModal = function () {
        catalogModal.style.display = 'none';
    };

    // DEPOSIT MODAL

    window.openDepositModal = function () {
        depositItemName.value = '';
        depositModal.style.display = 'flex';
    };

    window.openDepositModalForItem = function (itemName) {
        depositItemName.value = itemName;
        depositModal.style.display = 'flex';
    };

    window.closeDepositModal = function () {
        depositModal.style.display = 'none';
    };

    // ADD DYNAMIC DEPOSIT ROW

    addRowButton.addEventListener('click', function () {

        const newRow = depositRowsTable.insertRow();

        newRow.innerHTML = `
            <td>
                <input
                    type="text"
                    name="packs[${rowIndex}][item_number]"
                    placeholder="Item/Code"
                    required
                >
            </td>

            <td>
                <input
                    type="text"
                    name="packs[${rowIndex}][serial_number]"
                    placeholder="Serial #"
                >
            </td>

            <td>
                <input
                    type="number"
                    name="packs[${rowIndex}][quantity]"
                    placeholder="Qty"
                    required
                    min="1"
                >
            </td>

            <td>
                <input
                    type="text"
                    name="packs[${rowIndex}][unit]"
                    placeholder="e.g. box"
                >
            </td>

            <td>
                <input
                    type="date"
                    name="packs[${rowIndex}][expiration_date]"
                    required
                >
            </td>

            <td>
                <input
                    type="text"
                    name="packs[${rowIndex}][supplier]"
                    placeholder="Supplier"
                >
            </td>

            <td>
                <button
                    type="button"
                    class="inventory-remove-row removeRowBtn"
                    aria-label="Remove row"
                 data-ui-action="secondary">
                    &times;
                </button>
            </td>
        `;

        rowIndex++;
        updateRemoveButtons();
    });

    // REMOVE DEPOSIT ROW

    document.addEventListener('click', function (event) {

        const removeButton = event.target.closest('.removeRowBtn');

        if (!removeButton || removeButton.disabled) {
            return;
        }

        const row = removeButton.closest('tr');

        if (row) {
            row.remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const removeButtons = depositRowsTable.querySelectorAll('.removeRowBtn');
        const disableRemoval = depositRowsTable.rows.length <= 1;

        removeButtons.forEach(function (button) {
            button.disabled = disableRemoval;
        });
    }

    // CLOSE MODALS WHEN CLICKING OUTSIDE

    [categoryModal, catalogModal, depositModal].forEach(function (modal) {

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.style.display = 'none';

                if (modal === categoryModal) {
                    window.resetCategoryForm();
                }
            }
        });
    });

    // CLOSE MODALS WITH ESCAPE

    document.addEventListener('keydown', function (event) {

        if (event.key !== 'Escape') {
            return;
        }

        categoryModal.style.display = 'none';
        catalogModal.style.display = 'none';
        depositModal.style.display = 'none';

        window.resetCategoryForm();
    });

    updateRemoveButtons();

});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/admin/admin_supplies.blade.php ENDPATH**/ ?>