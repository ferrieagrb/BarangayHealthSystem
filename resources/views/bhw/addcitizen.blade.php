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


    {{-- BACK BUTTON --}}

    <a
        href="{{ route('citizenlist') }}"
        class="btn-secondary btn-page-back"
    >
        ← Back
    </a>


</div>



<div class="form-container">
    <form method="POST"
          action="{{ route('citizen.store') }}"
          id="citizen-form"
          enctype="multipart/form-data">
        @csrf

        {{-- DISPLAY VALIDATION ERRORS --}}
        @if ($errors->any())
            <div style="background:#fee2e2; color:#991b1b; padding:15px; margin-bottom:20px; border:1px solid #f87171; border-radius:8px;">
                <strong>Citizen registration failed:</strong>

                <ul style="margin-top:8px; padding-left:20px; list-style:disc;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- DISPLAY SESSION ERROR MESSAGES --}}
        @if (session('error'))
            <div style="background:#fee2e2; color:#991b1b; padding:15px; margin-bottom:20px; border-radius:8px;">
                {{ session('error') }}
            </div>
        @endif

        <div class="form-grid">



            {{-- =====================================================
                 FIRST NAME
                 ===================================================== --}}

            <div class="form-group">

                <label for="first_name">
                    First Name
                </label>

                <input
                    type="text"
                    name="Citizen_FName"
                    id="first_name"
                    required
                    maxlength="255"
                    autocomplete="given-name"
                >

            </div>


            {{-- =====================================================
                 LAST NAME
                 ===================================================== --}}

            <div class="form-group">

                <label for="last_name">
                    Last Name
                </label>

                <input
                    type="text"
                    name="Citizen_LName"
                    id="last_name"
                    required
                    maxlength="255"
                    autocomplete="family-name"
                >

            </div>

            <div class="form-group">

                <label for="birthdate">
                    Date of Birth
                </label>

                <input
                    type="date"
                    name="Citizen_BirthDate"
                    id="birthdate"
                    required
                >

                <small
                    id="birthdate-error"
                    class="validation-message"
                >
                    Date of birth must be before today.
                </small>

            </div>

            <div class="form-group">
                <label>Age</label>
                <input type="number" name="Citizen_Age" id="age" readonly required>
            </div>

            {{-- =====================================================
                 PHOTO UPLOAD
                ===================================================== --}}
            <div class="form-group">
                <label for="photo">Citizen Photo</label>

                <input
                    type="file"
                    name="photo"
                    id="photo"
                    accept="image/jpeg,image/png,image/webp"
                >

                <small>Optional. JPG, PNG, or WEBP; maximum 2 MB.</small>
            </div>

            {{-- =====================================================
                 CONTACT NUMBER
                 ===================================================== --}}

            <div class="form-group">

                <label for="contact">
                    Contact Number
                </label>

                <input
                    type="text"
                    name="Citizen_ContactNo"
                    id="contact"
                    required
                    inputmode="numeric"
                    autocomplete="tel"
                    maxlength="11"
                    minlength="11"
                    pattern="[0-9]{11}"
                    placeholder="09XXXXXXXXX"
                >

                <small
                    id="contact-error"
                    class="validation-message"
                >
                    Contact number must contain exactly 11 digits.
                </small>

            </div>

            {{-- Dynamically loaded Puroks from the database --}}
            <div class="form-group">
                <label>Purok</label>
                <select name="Citizen_Purok" required>
                    <option value="" disabled selected>-- Select Purok --</option>
                    @foreach(\App\Models\Purok::all() as $purok)
                    <option value="{{$purok->name}}">{{$purok->name}}</option>
                    @endforeach

                </select>

            </div>


            {{-- =====================================================
                 EMAIL
                 ===================================================== --}}

            <div class="form-group">

                <label for="email">
                    Email Address (For Login)
                </label>

                <input
                    type="email"
                    name="email"
                    id="email"
                    required
                    placeholder="resident@email.com"
                    autocomplete="email"
                >

            </div>


            {{-- =====================================================
                 PASSWORD
                 ===================================================== --}}

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    minlength="6"
                    placeholder="At least 6 characters"
                    autocomplete="new-password"
                >

            </div>

        </div>


        {{-- =========================================================
             FORM ACTIONS
             ========================================================= --}}

        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Citizen & Create Account</button>
            <a href="{{ route('citizenlist') }}" class="btn-secondary">Cancel</a>
        </div>


    </form>


</div>

<!-- Custom centered confirmation modal window with separated backdrop and box layers -->
<div id="confirmation-modal" style="display: none;">
    <div class="custom-modal-box">
        <h3 style="margin-top: 0; color: #111827; font-size: 18px;">Confirm Citizen Registration</h3>
        <p id="modal-message" style="color: #4b5563; font-size: 14px; margin: 15px 0;">Are you sure you want to save this record?</p>
        <div class="custom-modal-actions">
            <button type="button" id="modal-cancel-btn" class="btn-modal-cancel">Cancel</button>
            <button type="button" id="modal-confirm-btn" class="btn-modal-confirm">Yes, Save</button>
        </div>
    </div>
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


            ageInput.value =
                age;


            return true;

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
