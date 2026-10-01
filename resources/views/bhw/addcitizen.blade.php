@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/addcitizen.css') }}">
<style>
    /* Main overlay container taking up the full screen */
    .custom-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 99999;
        justify-content: center;
        align-items: center;
    }
    
    /* Layer 1: The dark dimming background sits behind */
    .custom-modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
        z-index: 1;
    }

    /* Layer 2: The modal box sits strictly on top, completely unaffected */
    .custom-modal-box {
        position: relative;
        background: #ffffff;
        padding: 30px;
        border-radius: 10px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        text-align: center;
        z-index: 100000; 
        animation: modalFadeIn 0.2s ease-in-out;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .custom-modal-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 20px;
    }
    .btn-modal-cancel {
        background: #e5e7eb;
        color: #374151;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-modal-cancel:hover {
        background: #d1d5db;
    }
    .btn-modal-confirm {
        background: #2563eb;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-modal-confirm:hover {
        background: #1d4ed8;
    }
</style>
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
    <form method="POST" action="{{ route('citizen.store') }}" id="citizen-form">
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
                <input type="number" name="Citizen_Age" id="age" readonly required>
            </div>

            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="Citizen_ContactNo">
            </div>

            <div class="form-group">
                <label>Purok</label>
                <select name="Citizen_Purok" required>
                    <option value="" disabled selected>-- Select Purok --</option>
                    @foreach(\App\Models\Purok::all() as $purok)
                    <option value="{{$purok->name}}">{{$purok->name}}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Citizen</button>
            <a href="{{ route('citizenlist') }}" class="btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<!-- Custom centered confirmation modal window with separated backdrop and box layers -->
<div id="confirmation-modal" class="custom-modal-overlay">
    <div class="custom-modal-box">
        <h3 style="margin-top: 0; color: #111827; font-size: 18px;">Confirm Citizen Registration</h3>
        <p id="modal-message" style="color: #4b5563; font-size: 14px; margin: 15px 0;">Are you sure you want to save this record?</p>
        <div class="custom-modal-actions">
            <button type="button" id="modal-cancel-btn" class="btn-modal-cancel">Cancel</button>
            <button type="button" id="modal-confirm-btn" class="btn-modal-confirm">Yes, Save</button>
        </div>
    </div>
    <div class="custom-modal-backdrop"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ageInput = document.getElementById('age');
    const birthdateInput = document.getElementById('birthdate');
    const citizenForm = document.getElementById('citizen-form');
    
    const modal = document.getElementById('confirmation-modal');
    const modalMessage = document.getElementById('modal-message');
    const confirmBtn = document.getElementById('modal-confirm-btn');
    const cancelBtn = document.getElementById('modal-cancel-btn');

    let isFormVerified = false;

    // When Birth Date changes → update Age dynamically
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

    // Intercepts form submission to open custom centered modal
    citizenForm.addEventListener('submit', function(e) {
        if (!isFormVerified) {
            e.preventDefault();

            const firstName = document.querySelector('input[name="Citizen_FName"]').value;
            const lastName = document.querySelector('input[name="Citizen_LName"]').value;
            const purok = document.querySelector('select[name="Citizen_Purok"]').value;

            if(!firstName || !lastName || !purok) {
                citizenForm.reportValidity();
                return;
            }

            modalMessage.innerText = `Are you sure you want to save the record for ${firstName} ${lastName} under ${purok}?`;
            modal.style.display = 'flex';
        }
    });

    // If user clicks "Yes, Save" inside the modal, proceed with real form submission
    confirmBtn.addEventListener('click', function() {
        isFormVerified = true;
        modal.style.display = 'none';
        citizenForm.submit();
    });

    // If user clicks "Cancel" inside the modal, hide the modal and stay on page
    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
});
</script>

@endsection