@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/supply_create.css') }}">
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Add Supply Item Catalog</h1>
        <p>Create a new generic supply entry</p>
    </div>
</div>

<div class="form-container">

    <form method="POST" action="{{ route('supplies.store') }}" id="supplyForm">
        @csrf

        <div class="form-grid">

            <!-- ITEM NAME -->
            <div class="form-group" style="grid-column: span 2;">
                <label>Item Name</label>
                <input type="text" name="name" required placeholder="e.g., Biogesic">
            </div>

            <!-- CATEGORY (Dynamic from Database) -->
            <div class="form-group" style="grid-column: span 2;">
                <label>Category</label>
                <select name="category" required>
                    <option value="" disabled selected>Select Category</option>
                    @isset($categories)
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                        @endforeach
                    @endisset
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
            <button type="submit" class="btn-primary">Save Item</button>
            <a href="{{ url('/supplies') }}" class="btn-secondary">Cancel</a>
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
                class="confirm-cancel">
                Cancel
            </button>

            <button
                type="button"
                id="saveSupplyConfirm"
                class="confirm-save">
                Yes, Save
            </button>

        </div>

    </div>

</div>


<!-- ACTION CONFIRMATION JAVASCRIPT -->

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Get the original Laravel form
    const supplyForm = document.getElementById('supplyForm');

    // Get confirmation modal elements
    const supplyConfirmModal =
        document.getElementById('supplyConfirmModal');

    const confirmSupplyName =
        document.getElementById('confirmSupplyName');

    const confirmSupplyCategory =
        document.getElementById('confirmSupplyCategory');

    const cancelSupplyConfirm =
        document.getElementById('cancelSupplyConfirm');

    const saveSupplyConfirm =
        document.getElementById('saveSupplyConfirm');


    /*  WHEN USER CLICKS "SAVE ITEM" */

    supplyForm.addEventListener('submit', function (event) {

        // Stop the form from submitting immediately
        event.preventDefault();


        // Check Laravel/HTML required fields first
        if (!supplyForm.checkValidity()) {

            supplyForm.reportValidity();

            return;
        }


        // Get Item Name
        const itemName =
            supplyForm.querySelector('input[name="name"]').value.trim();


        // Get Category
        const categorySelect =
            supplyForm.querySelector('select[name="category"]');

        const category =
            categorySelect.options[
                categorySelect.selectedIndex
            ].text;


        // Put the values into the confirmation message
        confirmSupplyName.textContent = itemName;

        confirmSupplyCategory.textContent = category;


        // Show confirmation modal
        supplyConfirmModal.classList.add('active');

    });


    /* CANCEL CONFIRMATION */

    cancelSupplyConfirm.addEventListener('click', function () {

        // Close modal
        supplyConfirmModal.classList.remove('active');

    });


    /* YES, SAVE */

    saveSupplyConfirm.addEventListener('click', function () {

        // Close modal
        supplyConfirmModal.classList.remove('active');


        // Submit the ORIGINAL Laravel form
        // This preserves:
        // - POST method
        // - route('supplies.store')
        // - @csrf
        // - name
        // - category
        // - min_stock
        // - description

        supplyForm.submit();

    });


    /* CLICKING OUTSIDE THE MODAL */

    supplyConfirmModal.addEventListener('click', function (event) {

        // Only close when clicking the dark background,
        // not when clicking the white modal itself.

        if (event.target === supplyConfirmModal) {

            supplyConfirmModal.classList.remove('active');

        }

    });


    /* ESC KEY */

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {

            supplyConfirmModal.classList.remove('active');

        }

    });

});
</script>

@endsection