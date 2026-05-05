@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/citizenfields.css') }}">
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Citizen Details</h1>
        <p>View and update citizen information</p>
    </div>

    <div class="right">
        <a href="{{ route('citizenlist') }}" class="btn-secondary">← Back</a>
        <button type="button" class="btn-primary" onclick="enableEdit()">Edit</button>
    </div>
</div>

<br>

<div class="info-card">
    <div class="card">

        <form method="POST" action="{{ route('citizen.update', $citizen->id) }}" id="citizenForm">
            @csrf
            @method('PUT')

            <h2>
                {{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}
            </h2>

            <br>

            <!-- FIRST NAME -->
            <label>First Name</label>
            <input type="text"
                   name="Citizen_FName"
                   value="{{ $citizen->Citizen_FName }}"
                   disabled>

            <!-- LAST NAME -->
            <label>Last Name</label>
            <input type="text"
                   name="Citizen_LName"
                   value="{{ $citizen->Citizen_LName }}"
                   disabled>

            <!-- AGE -->
            <label>Age</label>
            <input type="number"
                   name="Citizen_Age"
                   value="{{ $citizen->Citizen_Age }}"
                   disabled>

            <!-- BIRTHDATE -->
            <label>Birthdate</label>
            <input type="date"
                   name="Citizen_BirthDate"
                   value="{{ $citizen->Citizen_BirthDate }}"
                   disabled>

            <!-- CONTACT -->
            <label>Contact Number</label>
            <input type="text"
                   name="Citizen_ContactNo"
                   value="{{ $citizen->Citizen_ContactNo }}"
                   disabled>

            <!-- PUROK -->
            <label>Purok</label>
            <input type="text"
                   name="Citizen_Purok"
                   value="{{ $citizen->Citizen_Purok }}"
                   disabled>

            <br>

            <button type="submit" class="btn-primary" id="saveBtn" style="display:none;">
                Save Changes
            </button>

            <button type="button" class="btn-secondary" id="cancelBtn" style="display:none;" onclick="disableEdit()">
                Cancel
            </button>

        </form>

    </div>
</div>

<!-- JS TOGGLE -->
<script>
let originalData = {};

function enableEdit() {
    const inputs = document.querySelectorAll('#citizenForm input');

    // 🔥 Save original values BEFORE editing
    inputs.forEach(input => {
        originalData[input.name] = input.value;
        input.disabled = false;
    });

    document.getElementById('saveBtn').style.display = 'inline-block';
    document.getElementById('cancelBtn').style.display = 'inline-block';
}

function disableEdit() {
    const inputs = document.querySelectorAll('#citizenForm input');

    // 🔥 Restore original values
    inputs.forEach(input => {
        if (originalData[input.name] !== undefined) {
            input.value = originalData[input.name];
        }
        input.disabled = true;
    });

    document.getElementById('saveBtn').style.display = 'none';
    document.getElementById('cancelBtn').style.display = 'none';
}
</script>

@endsection