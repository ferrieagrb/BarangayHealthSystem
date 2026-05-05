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
                <input type="text" name="name" required>
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

            <!-- QUANTITY -->
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" required>
            </div>

            <!-- MIN STOCK -->
            <div class="form-group">
                <label>Minimum Stock</label>
                <input type="number" name="min_stock" required>
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