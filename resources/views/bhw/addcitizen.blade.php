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
                <input type="number" name="Citizen_Age" id="age" min="0" required>
            </div>

            <div class="form-group">
                <label>Birth Date</label>
                <input type="date" name="Citizen_BirthDate" id="birthdate" required>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ageInput = document.getElementById('age');
    const birthdateInput = document.getElementById('birthdate');

    // When Age changes → update Birth Date
    ageInput.addEventListener('input', function() {
        const age = parseInt(this.value);
        if (!isNaN(age)) {
            const today = new Date();
            const birthYear = today.getFullYear() - age;
            const birthMonth = today.getMonth() + 1; // JS month is 0-indexed
            const birthDay = today.getDate();
            // Format as YYYY-MM-DD
            const monthStr = birthMonth < 10 ? '0'+birthMonth : birthMonth;
            const dayStr = birthDay < 10 ? '0'+birthDay : birthDay;
            birthdateInput.value = `${birthYear}-${monthStr}-${dayStr}`;
        }
    });

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
        }
    });
});
</script>

@endsection