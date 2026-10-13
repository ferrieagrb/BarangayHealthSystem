@extends('templates.admin')

@section('CSSown')
    <link rel="stylesheet" href="{{ asset('css/admin/admin_supplies.css') }}">
@endsection

@section('content')

{{-- VALIDATION ERRORS --}}
@if ($errors->any())
    <div class="inventory-alert inventory-alert-error" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- SUCCESS MESSAGE --}}
@if (session('success'))
    <div class="inventory-alert inventory-alert-success" role="alert">
        {{ session('success') }}
    </div>
@endif

{{-- PAGE HEADER --}}
<div class="page-top inventory-page-header">
    <div>
        <h1>Manage Inventory</h1>
        <p>
            Monitor health center medical supplies, stock levels,
            and hierarchical batch distributions.
        </p>
    </div>

    <div class="inventory-header-actions">
        @canWrite('supplies')
            <button
                type="button"
                onclick="openCategoryModal()"
                class="inventory-button inventory-button-secondary"
            >
                Manage Categories
            </button>

            @if(auth()->user()->hasWriteAccess('supplies'))
                <button
                    type="button"
                    onclick="openCatalogModal()"
                    class="btn-primary"
                >
                    + New Catalog Item
                </button>
            @endif
        @endcanWrite
    </div>
</div>

{{-- INVENTORY SUMMARY --}}
<div class="summary-cards inventory-summary-cards">

    <div class="summary-card inventory-summary-card">
        <span>Total Stock Qty</span>
        <strong>{{ $totalsupply }}</strong>
    </div>

    <div class="summary-card inventory-summary-card">
        <span>Well-Stocked Items</span>
        <strong class="inventory-stock-good">{{ $wellStocked }}</strong>
    </div>

    <div class="summary-card inventory-summary-card">
        <span>Low Stock Alerts</span>
        <strong class="inventory-stock-low">{{ $lowStock }}</strong>
    </div>

</div>

{{-- SEARCH TOOLBAR --}}
<div class="toolbar inventory-toolbar">
    <form method="GET" action="{{ route('admin.supplies') }}">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search item name or code..."
            aria-label="Search supplies"
        >
    </form>
</div>

{{-- MASTER INVENTORY --}}
<div class="table-container inventory-container">

    <h3 class="inventory-section-title">
        📋 Master Inventory Hierarchy
    </h3>

    @php
        $groupedSupplies = $supplies->groupBy('name');
    @endphp

    @forelse($groupedSupplies as $name => $group)

        @php
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
        @endphp

        <details class="inventory-item">

            <summary class="inventory-item-summary">

                <span class="inventory-item-information">
                    <strong>{{ $name }}</strong>

                    <span class="inventory-item-category">
                        Category: {{ $category }}
                    </span>

                    <span class="inventory-item-quantity">
                        Total Qty: {{ $totalQty }}
                    </span>
                </span>

                <span class="inventory-status {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>

            </summary>

            <div class="inventory-batch-content">

                <div class="inventory-batch-header">

                    <p>
                        <strong>
                            Active Batch Records ({{ count($group) }} packs):
                        </strong>
                    </p>

                    <button
                        type="button"
                        onclick="openDepositModalForItem(@js($name))"
                        class="inventory-button inventory-button-deposit"
                    >
                        + Deposit Stock For This Item
                    </button>

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
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($group as $batch)
                                <tr>
                                    <td>
                                        {{ $batch->item_number ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $batch->serial_number ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $batch->quantity }}
                                        {{ $batch->unit }}
                                    </td>

                                    <td>
                                        {{ $batch->expiration_date
                                            ? \Carbon\Carbon::parse($batch->expiration_date)->format('Y-m-d')
                                            : 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $batch->supplier ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>

            </div>

        </details>

    @empty
        <p class="inventory-empty-state">
            No supplies found.
        </p>
    @endforelse

</div>

{{-- =========================================
     CATEGORY MANAGEMENT MODAL
========================================= --}}
<div id="categoryModal" class="inventory-modal">

    <div class="inventory-modal-content inventory-category-modal">

        <div class="inventory-modal-header">
            <h3>⚙️ Manage Inventory Categories</h3>

            <button
                type="button"
                onclick="closeCategoryModal()"
                class="inventory-modal-close"
                aria-label="Close category modal"
            >
                &times;
            </button>
        </div>

        {{-- ADD / EDIT CATEGORY --}}
        <form
            id="categoryForm"
            action="{{ route('admin.categories.store') }}"
            method="POST"
            class="inventory-category-form"
        >
            @csrf

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
                >
                    Add
                </button>

                <button
                    type="button"
                    id="category_cancel_edit_btn"
                    onclick="resetCategoryForm()"
                    class="inventory-button inventory-button-secondary"
                    hidden
                >
                    Cancel
                </button>

            </div>
        </form>

        {{-- EXISTING CATEGORIES --}}
        <p class="inventory-category-list-label">
            Existing Categories:
        </p>

        <div class="inventory-category-list">

            <table>
                <tbody>

                    @isset($categories)

                        @forelse($categories as $cat)
                            <tr>

                                <td>{{ $cat->name }}</td>

                                <td class="inventory-category-actions">

                                    <button
                                        type="button"
                                        onclick="editCategory(
                                            @js($cat->id),
                                            @js($cat->name)
                                        )"
                                        class="inventory-link-button"
                                    >
                                        Edit
                                    </button>

                                    <form
                                        action="{{ route('admin.categories.destroy', $cat->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this category?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="inventory-link-button inventory-link-danger"
                                        >
                                            Delete
                                        </button>
                                    </form>

                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td class="inventory-empty-state">
                                    No categories available.
                                </td>
                            </tr>
                        @endforelse

                    @else
                        <tr>
                            <td class="inventory-empty-state">
                                No categories available.
                            </td>
                        </tr>
                    @endisset

                </tbody>
            </table>

        </div>

        <div class="inventory-modal-footer">
            <button
                type="button"
                onclick="closeCategoryModal()"
                class="inventory-button inventory-button-secondary"
            >
                Close
            </button>
        </div>

    </div>

</div>

{{-- =========================================
     CATALOG CREATION MODAL
========================================= --}}
<div id="catalogModal" class="inventory-modal">

    <div class="inventory-modal-content inventory-catalog-modal">

        <div class="inventory-modal-header">
            <h3>Create New Catalog Item</h3>

            <button
                type="button"
                onclick="closeCatalogModal()"
                class="inventory-modal-close"
                aria-label="Close catalog modal"
            >
                &times;
            </button>
        </div>

        <form action="{{ route('supplies.store') }}" method="POST">

            @csrf

            <div class="inventory-form-group">
                <label for="catalog_item_name">Item Name</label>

                <input
                    type="text"
                    name="name"
                    id="catalog_item_name"
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

                    @isset($categories)
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}">
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    @endisset

                </select>
            </div>

            <div class="inventory-modal-footer">

                <button
                    type="button"
                    onclick="closeCatalogModal()"
                    class="inventory-button inventory-button-secondary"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="inventory-button inventory-button-primary"
                >
                    Save
                </button>

            </div>

        </form>

    </div>

</div>

{{-- =========================================
     DEPOSIT STOCK MODAL
========================================= --}}
<div id="depositModal" class="inventory-modal">

    <div class="inventory-modal-content inventory-deposit-modal">

        <div class="inventory-modal-header">
            <h3>Deposit Stock Batches</h3>

            <button
                type="button"
                onclick="closeDepositModal()"
                class="inventory-modal-close"
                aria-label="Close deposit modal"
            >
                &times;
            </button>
        </div>

        <form
            action="{{ route('admin.supplies.deposit') }}"
            method="POST"
        >

            @csrf

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
                                >
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
            >
                + Add Another Row
            </button>

            <div class="inventory-modal-footer">

                <button
                    type="button"
                    onclick="closeDepositModal()"
                    class="inventory-button inventory-button-secondary"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="inventory-button inventory-button-primary"
                >
                    Confirm Deposit
                </button>

            </div>

        </form>

    </div>

</div>

{{-- =========================================
     JAVASCRIPT
========================================= --}}
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
        categoryForm.action = @json(route('admin.categories.store'));

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
                >
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

@endsection