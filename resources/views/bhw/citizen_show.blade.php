@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/citizen_show.css') }}">
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
            style="padding:10px; border:1px solid #ccc; margin-bottom:10px;"
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


<!-- ================================================================
     ADD NEW DIAGNOSIS
     ================================================================ -->

<br>
<br>

<h3>Add Diagnosis</h3>


<form
    id="diagnosisForm"
    method="POST"
    action="{{ route('health.record.store') }}"
>

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
>

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