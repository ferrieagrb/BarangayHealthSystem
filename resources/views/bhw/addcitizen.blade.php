@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/addcitizen.css') }}">
@endsection

@section('content')

<div class="page-top">
    <div class="page-title-group">
        <h1>Add Citizen</h1>
        <p>Register a new barangay resident and store their profile information.</p>
    </div>
    <a href="{{ route('citizenlist') }}" class="btn-secondary">← Back</a>
</div>

<div class="form-container">
    <form method="POST" action="{{ route('citizen.store') }}">
        @csrf

        <div class="form-grid">

            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="Citizen_FName" required>
            </div>

            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="Citizen_LName" required>
            </div>

            <div class="form-group">
                <label>Age</label>
                <input type="number" name="Citizen_Age" required>
            </div>

            <div class="form-group">
                <label>Birth Date</label>
                <input type="date" name="Citizen_BirthDate" required>
            </div>

            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="Citizen_ContactNo">
            </div>

            <div class="form-group">
                <label>Purok</label>
                <select name="Citizen_Purok" required>
                    <option value="">Select Purok</option>
                    <option>Purok 1</option>
                    <option>Purok 2</option>
                    <option>Purok 3</option>
                </select>
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Citizen</button>
            <a href="{{ route('citizenlist') }}" class="btn-secondary">Cancel</a>
        </div>

    </form>
</div>

@endsection