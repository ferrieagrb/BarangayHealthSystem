@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/addcitizen.css') }}">
@endsection

@section('content')

<div class="page-top">
    <div class="page-title-group">
        <h1>Add Citizen & Account</h1>
        <p>Register a new barangay resident and generate their portal login credentials.</p>
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
                <label>Date of Birth</label>
                <input type="date" name="Citizen_BirthDate" id="birthdate" required>
            </div>

            <div class="form-group">
                <label>Age</label>
                <input type="number" name="Citizen_Age" id="age" readonly>
            </div>

            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="Citizen_ContactNo">
            </div>

            {{-- Dynamically loaded Puroks from the database --}}
            <div class="form-group">
                <label>Purok</label>
                <select name="Citizen_Purok" required>
                    <option value="">Select Purok</option>
                    @foreach($puroks as $purok)
                        <option value="{{ $purok->name }}">{{ $purok->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- New Login Credentials Fields --}}
            <div class="form-group">
                <label>Email Address (For Login)</label>
                <input type="email" name="email" required placeholder="resident@email.com">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="At least 6 characters">
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Citizen & Create Account</button>
            <a href="{{ route('citizenlist') }}" class="btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ageInput = document.getElementById('age');
    const birthdateInput = document.getElementById('birthdate');

    // When Birth Date changes → update Age
    birthdateInput.addEventListener('input', function() {
        const birth = new Date(this.value);
        const today = new Date();
        if (!isNaN(birth.getTime())) {
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            ageInput.value = age;
        } else {
            ageInput.value = '';
        }
    });
});
</script>

@endsection
