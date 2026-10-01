@extends('templates.layout')

@section('CSSown')

<link rel="stylesheet" href="{{ asset('css/bhw/addcitizen.css') }}">

@endsection


@section('content')


<div class="page-top">


    <div class="page-title-group">

        <h1>
            Add Citizen & Account
        </h1>

        <p>
            Register a new barangay resident and generate their portal login credentials.
        </p>

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


    <form
        method="POST"
        action="{{ route('citizen.store') }}"
        id="citizen-form"
        novalidate
    >

        @csrf


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


            {{-- =====================================================
                 DATE OF BIRTH
                 ===================================================== --}}

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


            {{-- =====================================================
                 AGE
                 ===================================================== --}}

            <div class="form-group">

                <label for="age">
                    Age
                </label>

                <input
                    type="number"
                    name="Citizen_Age"
                    id="age"
                    readonly
                    required
                    min="0"
                >

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


            {{-- =====================================================
                 PUROK
                 ===================================================== --}}

            <div class="form-group">

                <label for="purok">
                    Purok
                </label>

                <select
                    name="Citizen_Purok"
                    id="purok"
                    required
                >

                    <option
                        value=""
                        disabled
                        selected
                    >
                        -- Select Purok --
                    </option>

                    @foreach(\App\Models\Purok::all() as $purok)

                        <option value="{{ $purok->name }}">
                            {{ $purok->name }}
                        </option>

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


            <button
                type="submit"
                class="btn-primary"
            >
                Save Citizen & Create Account
            </button>


            {{-- CANCEL BUTTON --}}

            <a
                href="{{ route('citizenlist') }}"
                class="btn-secondary btn-form-cancel"
            >
                Cancel
            </a>


        </div>


    </form>


</div>


{{-- =============================================================
     CONFIRMATION MODAL
     ============================================================= --}}

<div
    id="confirmation-modal"
>


    <div class="custom-modal-box">


        <h3>
            Confirm Citizen Registration
        </h3>


        <p id="modal-message">
            Are you sure you want to save this record?
        </p>


        <div class="custom-modal-actions">


            <button
                type="button"
                id="modal-cancel-btn"
                class="btn-modal-cancel"
            >
                Cancel
            </button>


            <button
                type="button"
                id="modal-confirm-btn"
                class="btn-modal-confirm"
            >
                Yes, Save
            </button>


        </div>


    </div>


</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        const ageInput =
            document.getElementById('age');


        const birthdateInput =
            document.getElementById('birthdate');


        const contactInput =
            document.getElementById('contact');


        const citizenForm =
            document.getElementById('citizen-form');


        const birthdateError =
            document.getElementById('birthdate-error');


        const contactError =
            document.getElementById('contact-error');


        const modal =
            document.getElementById('confirmation-modal');


        const modalMessage =
            document.getElementById('modal-message');


        const confirmBtn =
            document.getElementById('modal-confirm-btn');


        const cancelBtn =
            document.getElementById('modal-cancel-btn');


        let isFormVerified = false;


        /*
        =========================================================
        SET MAXIMUM BIRTH DATE
        =========================================================
        */

        const today =
            new Date();


        today.setDate(
            today.getDate() - 1
        );


        const maxDate =
            today
                .toISOString()
                .split('T')[0];


        birthdateInput.max =
            maxDate;


        /*
        =========================================================
        VALIDATE BIRTH DATE + CALCULATE AGE
        =========================================================
        */

        function validateBirthdate() {


            if (!birthdateInput.value) {

                birthdateError.classList.remove('show');

                ageInput.value = '';

                return false;
            }


            const birth =
                new Date(
                    birthdateInput.value + 'T00:00:00'
                );


            const currentDate =
                new Date();


            currentDate.setHours(
                0,
                0,
                0,
                0
            );


            /*
            -----------------------------------------------------
            TODAY OR FUTURE = INVALID
            -----------------------------------------------------
            */

            if (birth >= currentDate) {

                birthdateError.classList.add('show');

                ageInput.value = '';

                return false;
            }


            birthdateError.classList.remove('show');


            /*
            -----------------------------------------------------
            CALCULATE AGE
            -----------------------------------------------------
            */

            let age =
                currentDate.getFullYear() -
                birth.getFullYear();


            const monthDifference =
                currentDate.getMonth() -
                birth.getMonth();


            if (
                monthDifference < 0 ||
                (
                    monthDifference === 0 &&
                    currentDate.getDate() < birth.getDate()
                )
            ) {

                age--;

            }


            ageInput.value =
                age;


            return true;

        }


        /*
        =========================================================
        BIRTH DATE EVENTS
        =========================================================
        */

        birthdateInput.addEventListener(
            'change',
            validateBirthdate
        );


        birthdateInput.addEventListener(
            'input',
            validateBirthdate
        );


        /*
        =========================================================
        CONTACT NUMBER
        =========================================================
        */

        contactInput.addEventListener(
            'input',
            function () {


                /*
                Remove anything that is not a number.
                */

                this.value =
                    this.value
                        .replace(/\D/g, '')
                        .slice(0, 11);


                /*
                Show validation message until
                exactly 11 digits are entered.
                */

                if (
                    this.value.length === 11
                ) {

                    contactError.classList.remove('show');

                } else {

                    contactError.classList.add('show');

                }

            }
        );


        /*
        =========================================================
        FORM SUBMISSION
        =========================================================
        */

        citizenForm.addEventListener(
            'submit',
            function (e) {


                if (isFormVerified) {
                    return;
                }


                e.preventDefault();


                /*
                -------------------------------------------------
                CHECK BIRTH DATE
                -------------------------------------------------
                */

                if (
                    !validateBirthdate()
                ) {

                    birthdateInput.focus();

                    return;
                }


                /*
                -------------------------------------------------
                CHECK CONTACT NUMBER
                -------------------------------------------------
                */

                const contact =
                    contactInput.value;


                if (
                    !/^\d{11}$/.test(contact)
                ) {

                    contactError.classList.add('show');

                    contactInput.focus();

                    return;
                }


                /*
                -------------------------------------------------
                CHECK OTHER REQUIRED FIELDS
                -------------------------------------------------
                */

                if (
                    !citizenForm.checkValidity()
                ) {

                    citizenForm.reportValidity();

                    return;
                }


                /*
                -------------------------------------------------
                GET CITIZEN INFORMATION
                -------------------------------------------------
                */

                const firstName =
                    document
                        .querySelector(
                            'input[name="Citizen_FName"]'
                        )
                        .value
                        .trim();


                const lastName =
                    document
                        .querySelector(
                            'input[name="Citizen_LName"]'
                        )
                        .value
                        .trim();


                const purok =
                    document
                        .querySelector(
                            'select[name="Citizen_Purok"]'
                        )
                        .value;


                /*
                -------------------------------------------------
                SHOW CONFIRMATION MODAL
                -------------------------------------------------
                */

                modalMessage.innerText =
                    `Are you sure you want to save the record for ${firstName} ${lastName} under ${purok}?`;


                modal.style.display =
                    'flex';

            }
        );


        /*
        =========================================================
        CONFIRM SAVE
        =========================================================
        */

        confirmBtn.addEventListener(
            'click',
            function () {


                /*
                -------------------------------------------------
                FINAL BIRTH DATE CHECK
                -------------------------------------------------
                */

                if (
                    !validateBirthdate()
                ) {

                    modal.style.display =
                        'none';

                    birthdateInput.focus();

                    return;
                }


                /*
                -------------------------------------------------
                FINAL CONTACT CHECK
                -------------------------------------------------
                */

                if (
                    !/^\d{11}$/.test(
                        contactInput.value
                    )
                ) {

                    modal.style.display =
                        'none';

                    contactError.classList.add('show');

                    contactInput.focus();

                    return;
                }


                /*
                -------------------------------------------------
                SUBMIT FORM
                -------------------------------------------------
                */

                isFormVerified =
                    true;


                modal.style.display =
                    'none';


                citizenForm.submit();

            }
        );


        /*
        =========================================================
        CANCEL
        =========================================================
        */

        cancelBtn.addEventListener(
            'click',
            function () {

                modal.style.display =
                    'none';

            }
        );


        /*
        =========================================================
        CLOSE MODAL BY CLICKING BACKDROP
        =========================================================
        */

        modal.addEventListener(
            'click',
            function (e) {


                if (
                    e.target === modal
                ) {

                    modal.style.display =
                        'none';

                }

            }
        );


    }
);

</script>


@endsection