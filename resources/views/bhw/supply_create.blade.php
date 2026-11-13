@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/supply_create.css') }}?v={{ filemtime(public_path('css/bhw/supply_create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Add Supply Item Catalog</h1>
        <p>Create a new generic supply entry</p>
    </div>
</div>

<div class="form-container">

    @if($errors->any())
        <div class="supply-form-errors" role="alert">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('supplies.store') }}" id="supplyForm">
        @csrf

        <div class="form-grid">

            <!-- ITEM NAME -->
            <div class="form-group" style="grid-column: span 2;">
                <label>Item Name</label>
                <input type="text" name="name" data-medical-autocomplete="supply" value="{{ old('name') }}" maxlength="255" required placeholder="Select category, then search item name">
            </div>

            <!-- CATEGORY (Dynamic from Database) -->
            <div class="form-group" style="grid-column: span 2;">
                <label>Category</label>
                <select name="category" required>
                    <option value="" disabled selected>Select Category</option>
                    @isset($categories)
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" @selected(old('category') === $cat->name)>{{ $cat->name }}</option>
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
            <button type="submit" class="btn-primary" data-ui-action="primary">Save Item</button>
            <a href="{{ url('/supplies') }}" class="btn-secondary" data-ui-action="secondary">Cancel</a>
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

<script src="{{ asset('js/bhw/supply_create-inline-1.js') }}?v={{ filemtime(public_path('js/bhw/supply_create-inline-1.js')) }}"></script>

@endsection
