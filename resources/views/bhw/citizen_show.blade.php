@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/citizen_show.css') }}">
<style>
    .maintenance-source-select,
    .maintenance-custom-input,
    .maintenance-field {
        width: 100%;
        margin: 6px 0 14px;
        padding: 11px 13px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #1e293b;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .maintenance-source-select:focus,
    .maintenance-custom-input:focus,
    .maintenance-field:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
        outline: none;
    }

    .medicine-picker-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        margin: 6px 0 14px;
        padding: 11px 13px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #334155;
        cursor: pointer;
        text-align: left;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .medicine-picker-trigger:hover {
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .08);
        transform: translateY(-1px);
    }

    .medicine-picker-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .48);
        opacity: 0;
        visibility: hidden;
        transition: opacity .2s ease, visibility .2s ease;
    }

    .medicine-picker-modal.is-open {
        opacity: 1;
        visibility: visible;
    }

    .medicine-picker-dialog {
        width: min(520px, 100%);
        max-height: min(680px, 90vh);
        overflow: auto;
        padding: 22px;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .25);
        transform: translateY(14px) scale(.98);
        transition: transform .25s ease;
    }

    .medicine-picker-modal.is-open .medicine-picker-dialog {
        transform: translateY(0) scale(1);
    }

    .medicine-picker-header,
    .medicine-picker-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .medicine-picker-close {
        border: 0;
        background: transparent;
        color: #64748b;
        font-size: 24px;
        cursor: pointer;
    }

    .medicine-option {
        display: block;
        width: 100%;
        margin-top: 8px;
        padding: 12px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #f8fafc;
        color: #1e293b;
        cursor: pointer;
        text-align: left;
        transition: background .2s ease, border-color .2s ease, transform .2s ease;
    }

    .medicine-option:hover {
        border-color: #93c5fd;
        background: #eff6ff;
        transform: translateX(2px);
    }

    .medicine-picker-pagination {
        margin-top: 18px;
    }

    .medicine-picker-pagination nav > div:first-child {
        display: none;
    }

    .medicine-picker-pagination nav > div:last-child {
        display: flex;
        justify-content: center;
    }
</style>
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
     MAINTENANCE MEDICINE
     ================================================================ -->

<br>

<div class="maintenance-section">
    <h3>Maintenance Medicines</h3>

    @if(session('success'))
        <p style="color: #166534; background: #dcfce7; padding: 10px; border-radius: 6px;">
            {{ session('success') }}
        </p>
    @endif

    @if($errors->any())
        <div style="color: #991b1b; background: #fee2e2; padding: 10px; border-radius: 6px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse($citizen->maintenanceRecords as $maintenance)
        <div style="padding: 10px; border: 1px solid #ccc; margin-bottom: 10px;">
            <p><strong>Medicine:</strong> {{ $maintenance->medicine_name }}</p>
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

    <form method="POST" action="{{ route('health.maintenance.store') }}" style="margin-top: 20px;">
        @csrf
        <input type="hidden" name="citizen_id" value="{{ $citizen->id }}">

        <label for="medicine_source">Medicine Source</label>
        <select name="medicine_source" id="medicine_source" class="maintenance-source-select" required>
            <option value="existing" {{ old('medicine_source', 'existing') === 'existing' ? 'selected' : '' }}>Existing medicine</option>
            <option value="custom" {{ old('medicine_source') === 'custom' ? 'selected' : '' }}>Add reference-only medicine</option>
        </select>

        <div id="existingMedicineGroup">
            <label for="existing_medicine">Select Medicine</label>
            <input type="hidden" name="existing_medicine" id="existing_medicine" value="{{ old('existing_medicine') }}">
            <button type="button" id="openMedicinePicker" class="medicine-picker-trigger">
                <span id="selectedMedicineLabel">{{ old('existing_medicine', 'Select an available medicine') }}</span>
                <span aria-hidden="true">&#9662;</span>
            </button>
        </div>

        <div id="customMedicineGroup" style="display: none;">
            <label for="custom_medicine">Reference-Only Medicine Name</label>
            <input type="text" name="custom_medicine" id="custom_medicine" class="maintenance-custom-input" value="{{ old('custom_medicine') }}" placeholder="Enter medicine name">
            <small>This name is saved only in the citizen's maintenance record, not in supplies.</small>
        </div>

        <label for="maintenance_dosage">Dosage</label>
        <input type="text" name="dosage" id="maintenance_dosage" class="maintenance-field" value="{{ old('dosage') }}" placeholder="e.g. 500 mg" required>

        <label for="maintenance_frequency">Frequency</label>
        <input type="text" name="frequency" id="maintenance_frequency" class="maintenance-field" value="{{ old('frequency') }}" placeholder="e.g. Once daily" required>

        <label for="maintenance_start_date">Start Date</label>
        <input type="date" name="start_date" id="maintenance_start_date" class="maintenance-field" value="{{ old('start_date', now()->format('Y-m-d')) }}" required>

        <label for="maintenance_end_date">End Date (Optional)</label>
        <input type="date" name="end_date" id="maintenance_end_date" class="maintenance-field" value="{{ old('end_date') }}">

        <label for="maintenance_notes">Notes</label>
        <textarea name="notes" id="maintenance_notes" class="maintenance-field" placeholder="Additional instructions or notes...">{{ old('notes') }}</textarea>

        <button type="submit" class="btn-primary">Add Maintenance</button>
    </form>

    <div id="medicinePickerModal" class="medicine-picker-modal" role="dialog" aria-modal="true" aria-labelledby="medicinePickerTitle" aria-hidden="true">
        <div class="medicine-picker-dialog">
            <div class="medicine-picker-header">
                <div>
                    <h3 id="medicinePickerTitle">Select Existing Medicine</h3>
                    <p>Showing 10 medicines per page.</p>
                </div>
                <button type="button" id="closeMedicinePicker" class="medicine-picker-close" aria-label="Close medicine picker">&times;</button>
            </div>

            <div>
                @forelse($availableMedicines as $medicine)
                    <button type="button" class="medicine-option" data-medicine="{{ $medicine->name }}">
                        {{ $medicine->name }}
                    </button>
                @empty
                    <p>No medicines are currently available in supplies.</p>
                @endforelse
            </div>

            @if($availableMedicines->hasPages())
                <div class="medicine-picker-pagination">
                    {{ $availableMedicines->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
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

    const medicineSource = document.getElementById("medicine_source");
    const existingMedicineGroup = document.getElementById("existingMedicineGroup");
    const customMedicineGroup = document.getElementById("customMedicineGroup");
    const existingMedicine = document.getElementById("existing_medicine");
    const customMedicine = document.getElementById("custom_medicine");
    const medicinePickerModal = document.getElementById("medicinePickerModal");
    const openMedicinePicker = document.getElementById("openMedicinePicker");
    const closeMedicinePicker = document.getElementById("closeMedicinePicker");
    const selectedMedicineLabel = document.getElementById("selectedMedicineLabel");

    function closeMedicinePickerModal() {
        medicinePickerModal.classList.remove("is-open");
        medicinePickerModal.setAttribute("aria-hidden", "true");
    }

    openMedicinePicker.addEventListener("click", function () {
        medicinePickerModal.classList.add("is-open");
        medicinePickerModal.setAttribute("aria-hidden", "false");
    });

    closeMedicinePicker.addEventListener("click", closeMedicinePickerModal);

    medicinePickerModal.addEventListener("click", function (event) {
        if (event.target === medicinePickerModal) {
            closeMedicinePickerModal();
        }
    });

    document.querySelectorAll(".medicine-option").forEach(function (option) {
        option.addEventListener("click", function () {
            existingMedicine.value = option.dataset.medicine;
            selectedMedicineLabel.textContent = option.dataset.medicine;
            closeMedicinePickerModal();
        });
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeMedicinePickerModal();
        }
    });

    function updateMedicineSourceFields() {
        const isCustom = medicineSource.value === "custom";
        existingMedicineGroup.style.display = isCustom ? "none" : "block";
        customMedicineGroup.style.display = isCustom ? "block" : "none";
        existingMedicine.required = !isCustom;
        customMedicine.required = isCustom;
        existingMedicine.disabled = isCustom;
        customMedicine.disabled = !isCustom;
    }

    medicineSource.addEventListener("change", updateMedicineSourceFields);
    updateMedicineSourceFields();

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