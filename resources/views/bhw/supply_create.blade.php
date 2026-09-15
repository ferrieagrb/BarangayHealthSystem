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

    <form method="POST" action="{{ route('supplies.store') }}">
        @csrf

        <div class="form-grid">

            <!-- ITEM NAME -->
            <div class="form-group" style="grid-column: span 2;">
                <label>Item Name</label>
                <input type="text" name="name" required placeholder="e.g., Biogesic">
            </div>

            <!-- CATEGORY -->
            <div class="form-group" style="grid-column: span 2;">
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

@endsection