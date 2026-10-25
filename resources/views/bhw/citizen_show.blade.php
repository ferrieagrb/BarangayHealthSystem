@extends('templates.layout')



@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/citizen_show.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
@endsection



@section('content')



<div class="page-top">

    <div class="page-title">

        <h1>Citizen Health Record</h1>

        <p>Medical history details</p>

    </div>



    <div class="right">

        <a href="{{ route('healthrecord') }}" class="btn-secondary">

            ← Back

        </a>

    </div>

</div>



<br>





<div class="info-card">



    <div class="card">



        <h2>

            {{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}

        </h2>



        <p>

            <strong>Age:</strong>

            {{ $citizen->Citizen_Age }}

        </p>



        <p>

            <strong>Purok:</strong>

            {{ $citizen->Citizen_Purok }}

        </p>



        <p>

            <strong>Contact:</strong>

            {{ $citizen->Citizen_ContactNo }}

        </p>



    </div>



</div>



<br>

<div class="diagnosis-section">



    <h3>Health Records</h3>



    @if($citizen->healthRecords->isEmpty())


        <p>No health records yet.</p>

    @endif

    @foreach($citizen->healthRecords as $record)

        <div

            class="diagnosis"

        >

            <p>

                <strong>Diagnosis:</strong>

                {{ $record->diagnosis }}

            </p>

            <p>

                <strong>Date:</strong>


                {{ $record->record_date ?? $record->created_at->format('Y-m-d') }}

            </p>


            <p>

                <strong>Comments:</strong>



                {{ $record->comments ?? 'No notes' }}



            </p>





            <p>

                <small>

                    Added:

                    {{ $record->created_at->format('Y-m-d h:i A') }}

                </small>

            </p>



        </div>



    @endforeach



</div>





<!-- IMMUNIZATION / VACCINATION -->
<section class="immunization-section" aria-labelledby="immunizationTitle">
    <div class="immunization-heading">
        <div>
            <h3 id="immunizationTitle">Immunization / Vaccination</h3>
            <p>Select a vaccine, record the dose and save it to this citizen's history.</p>
        </div>
        <span class="immunization-count" id="immunizationCount" aria-live="polite">0 selected</span>
    </div>
    @php
        $vaccines = [
            'BCG' => 'BCG',
            'HepB' => 'Hepatitis B (HepB)',
            'Penta' => 'Pentavalent (Penta)',
            'OPV' => 'Oral Polio Vaccine (OPV)',
            'IPV' => 'Inactivated Polio Vaccine (IPV)',
            'PCV' => 'Pneumococcal Conjugate Vaccine (PCV)',
            'MMR' => 'Measles, Mumps, Rubella (MMR)',
            'MR' => 'Measles-Rubella (MR)',
            'Td' => 'Tetanus-Diphtheria (Td)',
            'HPV' => 'Human Papillomavirus (HPV)',
        ];
    @endphp
    <form method="POST" action="{{ route('citizen.vaccination.store', $citizen->id) }}" class="immunization-form">
        @csrf
        <div class="immunization-grid" id="immunizationGrid">
            @foreach($vaccines as $vaccineKey => $vaccineLabel)
                <label class="vaccine-option" for="vaccine_{{ $vaccineKey }}">
                    <input type="radio" id="vaccine_{{ $vaccineKey }}" name="vaccine_name" value="{{ $vaccineLabel }}" class="vaccine-checkbox" required>
                    <span class="vaccine-checkmark" aria-hidden="true"></span>
                    <span class="vaccine-label">{{ $vaccineLabel }}</span>
                </label>
            @endforeach
        </div>
        <div class="immunization-fields">
            <label>Dose
                <input type="text" name="dose_number" placeholder="e.g. Dose 1 or Booster" required>
            </label>
            <label>Date administered
                <input type="date" name="date_administered" value="{{ now()->format('Y-m-d') }}" required>
            </label>
            <label>Administered by
                <input type="text" name="administered_by" value="{{ auth()->user()->name }}" required>
            </label>
        </div>
        <button type="submit" class="btn-primary">Save Vaccination</button>
    </form>

    <div class="immunization-history">
        <h4>Immunization History</h4>
        @forelse($citizen->vaccinations as $vaccination)
            <div class="immunization-history-item">
                <strong>{{ $vaccination->vaccine_name }}</strong>
                <span>{{ $vaccination->dose_number }} · {{ $vaccination->date_administered }}</span>
                <small>Administered by {{ $vaccination->administered_by }}</small>
            </div>
        @empty
            <p>No immunization records yet.</p>
        @endforelse
    </div>
</section>

<!-- ================================================================

     MAINTENANCE MEDICINE

     ================================================================ -->



<br>



<div class="maintenance-section">

    <h3>Maintenance Medicines</h3>



    @if(session('success'))

        <p class="health-alert health-alert-success">

            {{ session('success') }}

        </p>

    @endif



    @if($errors->any())

        <div class="health-alert health-alert-error">

            <ul class="health-error-list">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif



    @forelse($citizen->maintenanceRecords as $maintenance)

        <div class="maintenance-record-card">

            <p><strong>Medicine:</strong> {{ $maintenance->medicine_name }}</p>

            <p><strong>Quantity:</strong> {{ $maintenance->quantity_dispensed ?? 1 }}</p>

            <p><strong>Dosage:</strong> {{ $maintenance->dosage }}</p>

            <p><strong>Frequency:</strong> {{ $maintenance->frequency }}</p>

            <p>

                <strong>Schedule:</strong>

                {{ $maintenance->start_date->format('Y-m-d') }}

                to

                {{ $maintenance->end_date?->format('Y-m-d') ?? 'Ongoing' }}

            </p>

            @if($maintenance->notes)

                <p><strong>Notes:</strong> {{ $maintenance->notes }}</p>

            @endif

        </div>

    @empty

        <p>No maintenance medicines recorded.</p>

    @endforelse



    <form class="maintenance-form" method="POST" action="{{ route('health.maintenance.store') }}">
        @csrf
        <input type="hidden" name="citizen_id" value="{{ $citizen->id }}">

        <div class="maintenance-form-grid">
            <div class="maintenance-form-field">
                <label for="maintenance_medicine_name">Medicine Name</label>
                <input type="text" name="medicine_name" id="maintenance_medicine_name" class="maintenance-field" value="{{ old('medicine_name') }}" placeholder="Enter medicine name" maxlength="255" required>
            </div>
            <div class="maintenance-form-field">
                <label for="maintenance_quantity">Quantity</label>
                <input type="number" name="quantity_dispensed" id="maintenance_quantity" class="maintenance-field" value="{{ old('quantity_dispensed', 1) }}" min="1" max="100000" step="1" placeholder="e.g. 30" required>
            </div>
            <div class="maintenance-form-field">
                <label for="maintenance_dosage">Dosage</label>
                <input type="text" name="dosage" id="maintenance_dosage" class="maintenance-field" value="{{ old('dosage') }}" placeholder="e.g. 500 mg" maxlength="100" required>
            </div>
            <div class="maintenance-form-field">
                <label for="maintenance_frequency">Frequency</label>
                <input type="text" name="frequency" id="maintenance_frequency" class="maintenance-field" value="{{ old('frequency') }}" placeholder="e.g. Once daily" maxlength="100" required>
            </div>
            <div class="maintenance-form-field">
                <label for="maintenance_start_date">Start Date</label>
                <input type="date" name="start_date" id="maintenance_start_date" class="maintenance-field" value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
            </div>
            <div class="maintenance-form-field">
                <label for="maintenance_end_date">End Date (Optional)</label>
                <input type="date" name="end_date" id="maintenance_end_date" class="maintenance-field" value="{{ old('end_date') }}">
            </div>
            <div class="maintenance-form-field maintenance-form-field-full">
                <label for="maintenance_notes">Notes</label>
                <textarea name="notes" id="maintenance_notes" class="maintenance-field" maxlength="2000" placeholder="Additional instructions or notes...">{{ old('notes') }}</textarea>
            </div>
        </div>
        <button type="submit" class="btn-primary">Add Maintenance</button>
    </form>





<!-- ================================================================

     ADD NEW DIAGNOSIS

     ================================================================ -->



<br>

<br>



<h3>Add Diagnosis</h3>





<form

    id="diagnosisForm"

    method="POST"

    action="{{ route('citizen.health-info.diagnosis.store', ['id' => $citizen->id]) }}"

\>



    @csrf





    <input

        type="hidden"

        name="citizen_id"

        value="{{ $citizen->id }}"

    >





    <!-- Diagnosis (manual input) -->



    <label>

        Diagnosis

    </label>



    <textarea

        name="diagnosis"

        id="diagnosisInput"

        required

        placeholder="Enter diagnosis..."

    ></textarea>





    <!-- Date -->



    <label>

        Date

    </label>



    <input

        type="date"

        name="record_date"

        id="recordDateInput"

        required

    >





    <!-- Comments -->



    <label>

        Comments / Notes

    </label>



    <textarea

        name="comments"

        id="commentsInput"

        placeholder="Enter notes..."

    ></textarea>





    <!-- SAVE -->



    <button

        type="submit"

        id="saveDiagnosisBtn"

    >

        Save

    </button>



</form>





<!-- ================================================================

     DIAGNOSIS CONFIRMATION MODAL

     ================================================================ -->



<div

    id="diagnosisConfirmModal"

    class="diagnosis-confirm-overlay"

\>



    <div class="diagnosis-confirm-modal">



        <h2>

            Confirm New Diagnosis

        </h2>





        <p>

            Are you sure you want to save this new diagnosis

            for

            <strong>

                {{ $citizen->Citizen_FName }}

                {{ $citizen->Citizen_LName }}

            </strong>?

        </p>





        <!-- CONFIRMATION DETAILS -->



        <div class="diagnosis-confirm-details">



            <div class="diagnosis-confirm-detail">



                <span>

                    Diagnosis

                </span>



                <strong id="confirmDiagnosis">

                    —

                </strong>



            </div>





            <div class="diagnosis-confirm-detail">



                <span>

                    Date

                </span>



                <strong id="confirmDiagnosisDate">

                    —

                </strong>



            </div>





            <div class="diagnosis-confirm-detail">



                <span>

                    Comments / Notes

                </span>



                <strong id="confirmDiagnosisComments">

                    No notes

                </strong>



            </div>



        </div>





        <!-- CONFIRMATION BUTTONS -->



        <div class="diagnosis-confirm-buttons">



            <button

                type="button"

                id="cancelDiagnosisConfirmation"

                class="diagnosis-confirm-cancel"

            >

                Cancel

            </button>





            <button

                type="button"

                id="confirmDiagnosisSubmission"

                class="diagnosis-confirm-save"

            >

                Yes, Save

            </button>



        </div>



    </div>



</div>





@endsection





@section('scripts')



<script>



document.addEventListener("DOMContentLoaded", function () {



    // Immunization UI preview only; this is not connected to a database.
    const vaccineCheckboxes = document.querySelectorAll(".vaccine-checkbox");
    const immunizationCount = document.getElementById("immunizationCount");

    function updateImmunizationCount() {
        if (!immunizationCount) return;
        const selected = document.querySelectorAll(".vaccine-checkbox:checked").length;
        immunizationCount.textContent = `${selected} of 10 selected`;
    }

    vaccineCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener("change", updateImmunizationCount);
    });
    updateImmunizationCount();




    /* ============================================================

       DIAGNOSIS FORM ELEMENTS

       ============================================================ */



    const diagnosisForm =

        document.getElementById("diagnosisForm");



    const diagnosisInput =

        document.getElementById("diagnosisInput");



    const recordDateInput =

        document.getElementById("recordDateInput");



    const commentsInput =

        document.getElementById("commentsInput");



    const saveDiagnosisBtn =

        document.getElementById("saveDiagnosisBtn");





    /* ============================================================

       CONFIRMATION MODAL ELEMENTS

       ============================================================ */



    const diagnosisConfirmModal =

        document.getElementById("diagnosisConfirmModal");



    const confirmDiagnosis =

        document.getElementById("confirmDiagnosis");



    const confirmDiagnosisDate =

        document.getElementById("confirmDiagnosisDate");



    const confirmDiagnosisComments =

        document.getElementById("confirmDiagnosisComments");



    const cancelDiagnosisConfirmation =

        document.getElementById(

            "cancelDiagnosisConfirmation"

        );



    const confirmDiagnosisSubmission =

        document.getElementById(

            "confirmDiagnosisSubmission"

        );





    /* ============================================================

       FORM SUBMISSION



       Instead of immediately saving, prevent the form from

       submitting and show the confirmation modal.

       ============================================================ */



    diagnosisForm.addEventListener("submit", function (event) {



        event.preventDefault();





        /* Get entered values */



        const diagnosis =

            diagnosisInput.value.trim();



        const recordDate =

            recordDateInput.value;



        const comments =

            commentsInput.value.trim();





        /* ========================================================

           EXTRA SAFETY CHECK



           HTML required attributes already handle this, but this

           prevents the confirmation from appearing if the fields

           are somehow submitted empty.

           ======================================================== */



        if (!diagnosis || !recordDate) {



            diagnosisForm.reportValidity();



            return;



        }





        /* ========================================================

           DISPLAY DATA IN CONFIRMATION MODAL

           ======================================================== */



        confirmDiagnosis.textContent =

            diagnosis;





        confirmDiagnosisDate.textContent =

            recordDate;





        confirmDiagnosisComments.textContent =

            comments || "No notes";





        /* ========================================================

           SHOW CONFIRMATION MODAL

           ======================================================== */



        diagnosisConfirmModal.classList.add("active");



    });





    /* ============================================================

       CANCEL CONFIRMATION

       ============================================================ */



    cancelDiagnosisConfirmation.addEventListener(

        "click",

        function () {



            diagnosisConfirmModal.classList.remove(

                "active"

            );



        }

    );





    /* ============================================================

       CONFIRM AND SAVE

       ============================================================ */



    confirmDiagnosisSubmission.addEventListener(

        "click",

        function () {



            /*

             * Disable the button so the user cannot accidentally

             * submit the form multiple times.

             */



            confirmDiagnosisSubmission.disabled = true;



            confirmDiagnosisSubmission.textContent =

                "Saving...";





            /*

             * Close confirmation modal.

             */



            diagnosisConfirmModal.classList.remove(

                "active"

            );





            /*

             * Disable the original Save button as well.

             */



            saveDiagnosisBtn.disabled = true;





            /*

             * Native form.submit() bypasses the submit event,

             * so the confirmation modal will NOT appear again.

             */



            diagnosisForm.submit();



        }

    );





    /* ============================================================

       CLICK OUTSIDE CONFIRMATION MODAL

       ============================================================ */



    window.addEventListener(

        "click",

        function (event) {



            if (

                event.target === diagnosisConfirmModal

            ) {



                diagnosisConfirmModal.classList.remove(

                    "active"

                );



            }



        }

    );





    /* ============================================================

       ESC KEY

       ============================================================ */



    document.addEventListener(

        "keydown",

        function (event) {



            if (event.key === "Escape") {



                diagnosisConfirmModal.classList.remove(

                    "active"

                );



            }



        }

    );



});



</script>



@endsection