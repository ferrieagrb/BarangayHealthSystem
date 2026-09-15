@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/supply_create.css') }}">
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Add Supply Item</h1>
        <p>Create a new health supply record</p>
    </div>
</div>

<div class="form-container">

    <form method="POST" action="{{ route('supplies.store') }}">
        @csrf

        <div class="form-grid">

            <!-- ITEM NAME -->
            <div class="form-group">
                <label>Item Name</label>
                <input type="text" name="name" required placeholder="e.g., Biogesic">
            </div>

            <!-- CATEGORY (WITH CHOICES) -->
            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="Medicine">Medicine</option>
                    <option value="Medical Equipment">Medical Equipment</option>
                    <option value="First Aid">First Aid</option>
                    <option value="Vaccines">Vaccines</option>
                    <option value="Supplies">Supplies</option>
                </select>
            </div>

            <!-- ITEM NUMBER -->
            <div class="form-group">
                <label>Item Number / Code</label>
                <input type="text" name="item_number" placeholder="Optional custom code">
            </div>

            <!-- SERIAL NUMBER -->
            <div class="form-group">
                <label>Serial Number</label>
                <input type="text" name="serial_number" placeholder="Optional serial number">
            </div>

            <!-- QUANTITY -->
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" required min="0">
            </div>

            <!-- UNIT -->
            <div class="form-group">
                <label>Unit</label>
                <input type="text" name="unit" placeholder="e.g., pcs, box, vials">
            </div>

            <!-- MIN STOCK -->
            <div class="form-group">
                <label>Minimum Stock</label>
                <input type="number" name="min_stock" required min="0" value="5">
            </div>

            <!-- EXPIRATION DATE -->
            <div class="form-group">
                <label>Expiration Date</label>
                <input type="date" name="expiration_date">
            </div>

            <!-- SUPPLIER -->
            <div class="form-group">
                <label>Supplier</label>
                <input type="text" name="supplier" placeholder="Supplier name">
            </div>

            <!-- STATUS -->
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="Available">Available</option>
                    <option value="Low Stock">Low Stock</option>
                    <option value="Expired">Expired</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>

            <!-- DESCRIPTION (Full width or span if grid allows) -->
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Description / Notes</label>
                <textarea name="description" placeholder="Enter item specs or notes..."></textarea>
            </div>

        </div>

        <!-- ACTIONS -->
        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Item</button>
            <a href="{{ url('/supplies') }}" class="btn-secondary">Cancel</a>
        </div>

    </form>

</div>

@endsection