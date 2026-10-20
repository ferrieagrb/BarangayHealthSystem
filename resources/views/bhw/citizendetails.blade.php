@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/citizenfields.css') }}">
<style>
    /* Deceased warning section and confirmation modal styling */
    .deceased-section-box {
        background: #fef2f2;
        border: 1px solid #fecaca;
        padding: 18px;
        border-radius: 10px;
        margin: 20px 0;
    }
    .deceased-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        padding: 20px;
    }
    .deceased-modal-overlay.active {
        display: flex;
    }
    .deceased-modal-box {
        background: white;
        width: 100%;
        max-width: 400px;
        padding: 30px;
        border-radius: 14px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .deceased-modal-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 20px;
    }
</style>
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Citizen Details Test</h1>
        <p>View and update citizen information</p>
    </div>

    <div class="right">
        <a href="{{ route('citizenlist') }}" class="btn-secondary">← Back</a>
        @if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1)
            <button type="button" class="btn-primary" id="editBtn" onclick="enableEdit()">Edit</button>
        @elseif(!$canEdit && !(bool) $citizen->Citizen_IsDeceased)
            <span class="text-muted" style="font-weight: bold; font-size: 13px; background: #f1f5f9; padding: 8px 12px; border-radius: 6px; color: #475569;">Read Only (Unassigned)</span>
        @else
            <span class="text-danger" style="font-weight: bold; font-size: 13px; background: #fee2e2; padding: 8px 12px; border-radius: 6px; color: #991b1b;">Record Locked (Deceased)</span>
        @endif
    </div>
</div>

<div class="info-card">
    <div class="card">

        <form id="vaccinationForm" method="POST" action="{{ route('citizen.vaccination.store', $citizen->id) }}">
            @csrf
        </form>

        <form method="POST"
              action="{{ route('citizen.update', $citizen->id) }}"
              id="citizenForm">
            @csrf
            @method('PUT')

            <h2>
                {{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}
            </h2>

            {{-- BASIC INFORMATION --}}
            <h3 class="citizen-section-title">Basic Information</h3>

            <label for="Citizen_FName">First Name</label>
            <input type="text"
                   id="Citizen_FName"
                   name="Citizen_FName"
                   value="{{ old('Citizen_FName', $citizen->Citizen_FName) }}"
                   disabled>

            <label for="Citizen_MName">Middle Name</label>
            <input type="text"
                    id="Citizen_MName"
                    name="Citizen_MName"
                    value="{{ old('Citizen_MName', $citizen->Citizen_MName ?? '') }}"
                    placeholder="Enter middle name"
                    disabled>

            <label for="Citizen_LName">Last Name</label>
            <input type="text"
                   id="Citizen_LName"
                   name="Citizen_LName"
                   value="{{ old('Citizen_LName', $citizen->Citizen_LName) }}"
                   disabled>

            <label for="Citizen_Age">Age</label>
            <input type="number"
                   id="Citizen_Age"
                   name="Citizen_Age"
                   min="0"
                   value="{{ old('Citizen_Age', $citizen->Citizen_Age) }}"
                   disabled>

            <label for="Citizen_BirthDate">Birthdate</label>
            <input type="date"
                   id="Citizen_BirthDate"
                   name="Citizen_BirthDate"
                   value="{{ old('Citizen_BirthDate', $citizen->Citizen_BirthDate ? \Carbon\Carbon::parse($citizen->Citizen_BirthDate)->format('Y-m-d') : '') }}"
                   disabled>

            <label for="Citizen_ContactNo">Contact Number</label>
            <input type="text"
                   id="Citizen_ContactNo"
                   name="Citizen_ContactNo"
                   value="{{ old('Citizen_ContactNo', $citizen->Citizen_ContactNo) }}"
                   disabled>

            <label for="Citizen_Purok">Purok</label>
            <input type="text"
                   id="Citizen_Purok"
                   name="Citizen_Purok"
                   value="{{ old('Citizen_Purok', $citizen->Citizen_Purok) }}"
                     disabled
                     readonly>

                 <label for="Citizen_Subgroup">Purok Subgroup</label>
                 <input type="text"
                     id="Citizen_Subgroup"
                     value="{{ $citizen->subgroup->name ?? $citizen->family?->subgroup?->name ?? 'Not assigned' }}"
                     readonly>
                 <button type="button" class="btn-secondary" id="openEditManualPicker" style="display:none;">Assign without family</button>

            <hr class="citizen-section-divider">

            {{-- FAMILY INFORMATION --}}
            <h3 class="citizen-section-title">Family Information</h3>

            <label for="displayFamilyId">Family ID</label>
            <input type="text"
                   id="displayFamilyId"
                   value="{{ $citizen->family->id ?? 'None' }}"
                   disabled>

            <label for="displayFamilyName">Family Name</label>
            <input type="text"
                   id="displayFamilyName"
                   value="{{ $citizen->family->family_name ?? 'No Family Assigned' }}"
                   disabled>

            <label for="displaySubgroupName">Subgroup / Purok Section</label>
            <input type="text"
                   id="displaySubgroupName"
                   value="{{ $citizen->family->subgroup->name ?? 'None' }}"
                   disabled>

            <div id="familyEditContainer" style="display: none;">
                <input type="hidden" name="family_id" id="familySelect" value="{{ old('family_id', $citizen->family_id) }}">
                <input type="hidden" name="subgroup_id" id="editSubgroupId" value="{{ $citizen->subgroup_id ?? $citizen->family?->subgroup_id }}">
                <label>Assignment</label>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="button" class="btn btn-secondary" id="openEditFamilyPicker">Select Family</button>
                </div>
            </div>

            <hr class="citizen-section-divider">

            {{-- PHYSICAL INFORMATION --}}
            <h3 class="citizen-section-title">Physical Information</h3>

            <label for="Citizen_Height">Height (cm)</label>
            <input type="number"
                   id="Citizen_Height"
                   name="Citizen_Height"
                   min="0"
                   step="0.01"
                   value="{{ old('Citizen_Height', $citizen->Citizen_Height) }}"
                   placeholder="e.g. 165.50"
                   disabled>

            <label for="Citizen_Weight">Weight (kg)</label>
            <input type="number"
                   id="Citizen_Weight"
                   name="Citizen_Weight"
                   min="0"
                   step="0.01"
                   value="{{ old('Citizen_Weight', $citizen->Citizen_Weight) }}"
                   placeholder="e.g. 55.50"
                   disabled>

            <label for="Citizen_Sex">Sex</label>
            <select id="Citizen_Sex" name="Citizen_Sex" disabled>
                <option value="">Select Sex</option>
                <option value="Male"
                    {{ old('Citizen_Sex', $citizen->Citizen_Sex) === 'Male' ? 'selected' : '' }}>
                    Male
                </option>
                <option value="Female"
                    {{ old('Citizen_Sex', $citizen->Citizen_Sex) === 'Female' ? 'selected' : '' }}>
                    Female
                </option>
            </select>

            <hr class="citizen-section-divider">

            {{-- HOUSING STATUS --}}
            <h3 class="citizen-section-title">Housing Status</h3>

            <label for="Citizen_HousingStatus">Residence Type</label>
            <select id="Citizen_HousingStatus"
                    name="Citizen_HousingStatus"
                    disabled>
                <option value="">Select Housing Status</option>
                <option value="Permanent Resident"
                    {{ old('Citizen_HousingStatus', $citizen->Citizen_HousingStatus) === 'Permanent Resident' ? 'selected' : '' }}>
                    Permanent Resident
                </option>
                <option value="Renter"
                    {{ old('Citizen_HousingStatus', $citizen->Citizen_HousingStatus) === 'Renter' ? 'selected' : '' }}>
                    Renter
                </option>
            </select>

            <hr class="citizen-section-divider">

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
    @if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1)
    <div class="immunization-form">
        <div class="immunization-grid" id="immunizationGrid">
            @foreach($vaccines as $vaccineKey => $vaccineLabel)
                <label class="vaccine-option" for="vaccine_{{ $vaccineKey }}">
                    <input type="radio" id="vaccine_{{ $vaccineKey }}" name="vaccine_name" value="{{ $vaccineLabel }}" class="vaccine-checkbox" form="vaccinationForm" required>
                    <span class="vaccine-checkmark" aria-hidden="true"></span>
                    <span class="vaccine-label">{{ $vaccineLabel }}</span>
                </label>
            @endforeach
        </div>
        <div class="immunization-fields">
            <label>Dose
                <input type="text" name="dose_number" placeholder="e.g. Dose 1 or Booster" form="vaccinationForm" required>
            </label>
            <label>Date administered
                <input type="date" name="date_administered" value="{{ now()->format('Y-m-d') }}" form="vaccinationForm" required>
            </label>
            <label>Administered by
                <input type="text" name="administered_by" value="{{ auth()->user()->name }}" form="vaccinationForm" required>
            </label>
        </div>
        <button type="submit" class="btn-primary" form="vaccinationForm">Save Vaccination</button>
    </div>
    @elseif((int) $citizen->Citizen_IsDeceased === 1)
        <p class="immunization-preview-note">This record is deceased and locked for updates.</p>
    @else
        <p class="immunization-preview-note">Read-only: this citizen is not assigned to your subgroup.</p>
    @endif

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

        <div id="customMedicineGroup" class="custom-medicine-group" hidden>

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


            {{-- HEALTH & FAMILY PLANNING --}}
            <h3 class="citizen-section-title">Health &amp; Family Planning</h3>

            <div class="citizen-status-field">
                <label for="Citizen_Pregnant">Pregnant</label>
                <select id="Citizen_Pregnant" name="Citizen_Pregnant" disabled>
                    <option value="0"
                        {{ (string) old('Citizen_Pregnant', (int) $citizen->Citizen_Pregnant) === '0' ? 'selected' : '' }}>
                        No
                    </option>
                    <option value="1"
                        {{ (string) old('Citizen_Pregnant', (int) $citizen->Citizen_Pregnant) === '1' ? 'selected' : '' }}>
                        Yes
                    </option>
                </select>
            </div>

            <div class="citizen-status-field">
                <label for="Citizen_InPills">Using Birth Control Pills</label>
                <select id="Citizen_InPills" name="Citizen_InPills" disabled>
                    <option value="0"
                        {{ (string) old('Citizen_InPills', (int) $citizen->Citizen_InPills) === '0' ? 'selected' : '' }}>
                        No
                    </option>
                    <option value="1"
                        {{ (string) old('Citizen_InPills', (int) $citizen->Citizen_InPills) === '1' ? 'selected' : '' }}>
                        Yes
                    </option>
                </select>
            </div>

            <hr class="citizen-section-divider">

            {{-- VITAL STATUS --}}
            <h3 class="citizen-section-title">Vital Status</h3>

            <label>Resident Status</label>
            <input type="text"
                   id="displayDeceasedStatus"
                   value="{{ $citizen->Citizen_IsDeceased ? 'Deceased' : 'Living' }}"
                   disabled>

            {{-- Hidden input ensuring value is properly submitted to controller --}}
            <input type="hidden"
                   id="Citizen_IsDeceased"
                   name="Citizen_IsDeceased"
                   value="{{ old('Citizen_IsDeceased', $citizen->Citizen_IsDeceased ? 1 : 0) }}">

            <hr class="citizen-section-divider">

            <hr class="citizen-section-divider">

            {{-- DECEASED STATUS MARKER SECTION (BEFORE SAVE) --}}
            <div class="deceased-section-box">
                <h3 class="citizen-section-title" style="color: #991b1b; margin-top: 0;">Deceased Registry Status</h3>
                <p style="font-size: 13px; color: #b91c1c; margin-bottom: 12px;">
                    Marking this resident as deceased will archive their active status across barangay tracking records.
                </p>
                <label style="display: flex; align-items: center; gap: 8px; font-weight: bold; cursor: pointer;">
                    <input type="checkbox" id="markDeceasedCheckbox" disabled style="width: 18px; height: 18px;">
                    <span>Mark this resident as Deceased</span>
                </label>
            </div>

            <div class="citizen-form-actions">
                <button type="button"
                        class="btn-primary"
                        id="saveBtn"
                        style="display: none;"
                        onclick="handlePreSaveCheck()">
                    Save Changes
                </button>

                <button type="button"
                        class="btn-secondary"
                        id="cancelBtn"
                        style="display: none;"
                        onclick="disableEdit()">
                    Cancel
                </button>
            </div>

        </form>
    </div>
</div>

<style>
    .edit-assignment-modal { display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; padding:20px; background:rgba(15,23,42,.55); }
    .edit-assignment-modal.open { display:flex; }
    .edit-assignment-box { width:min(700px,100%); max-height:90vh; overflow:auto; padding:24px; border-radius:12px; background:#fff; box-shadow:0 20px 50px rgba(15,23,42,.25); }
    .edit-assignment-list { display:grid; gap:8px; max-height:55vh; overflow:auto; }
    .edit-assignment-option { display:flex; justify-content:space-between; gap:12px; padding:12px; border:1px solid #dbe3ef; border-radius:7px; background:#fff; cursor:pointer; text-align:left; }
    .edit-assignment-option:hover { border-color:#2563eb; background:#eff6ff; }
</style>

<div class="edit-assignment-modal" id="editFamilyPicker" aria-hidden="true">
    <div class="edit-assignment-box">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2 style="margin:0;">Select Family</h2>
            <button type="button" class="family-picker-close" data-close-edit-modal>&times;</button>
        </div>
        <input type="search" id="editFamilySearch" class="form-control" placeholder="Search family..." style="margin:16px 0;">
        <div class="edit-assignment-list">
            @foreach($families as $family)
                <button type="button" class="edit-assignment-option edit-family-option" data-family-id="{{ $family->id }}" data-family-name="{{ $family->family_name }}" data-purok="{{ $family->purok->name ?? '' }}" data-subgroup-id="{{ $family->subgroup->id ?? '' }}" data-subgroup-name="{{ $family->subgroup->name ?? '' }}">
                    <span><strong>F-{{ str_pad($family->id, 4, '0', STR_PAD_LEFT) }} · {{ $family->family_name }}</strong><br><small>{{ $family->purok->name ?? 'No Purok' }} / {{ $family->subgroup->name ?? 'No subgroup' }}</small></span><span>Select</span>
                </button>
            @endforeach
        </div>
    </div>
</div>

<div class="edit-assignment-modal" id="editManualPicker" aria-hidden="true">
    <div class="edit-assignment-box">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2 style="margin:0;">Select Purok / Subgroup</h2>
            <button type="button" class="family-picker-close" data-close-edit-modal>&times;</button>
        </div>
        <div class="edit-assignment-list" style="margin-top:16px;">
            @foreach($puroks as $purok)
                <strong style="color:#173b82;">{{ $purok->name }}</strong>
                @foreach($purok->subgroups as $subgroup)
                    <button type="button" class="edit-assignment-option edit-manual-option" data-purok="{{ $purok->name }}" data-subgroup-id="{{ $subgroup->id }}" data-subgroup-name="{{ $subgroup->name }}"><span>{{ $purok->name }} / {{ $subgroup->name }}</span><span>Select</span></button>
                @endforeach
            @endforeach
        </div>
    </div>
</div>

{{-- DECEASED CONFIRMATION MODAL --}}
<div id="deceasedConfirmModal" class="deceased-modal-overlay">
    <div class="deceased-modal-box">
        <h2 style="color: #991b1b; font-size: 20px; font-weight: bold; margin-bottom: 10px;">
            Mark Citizen as Deceased?
        </h2>
        <p style="color: #4b5563; margin-bottom: 20px; font-size: 14px;">
            Are you sure you want to update <strong>{{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}</strong> as deceased? This will change their vital status record.
        </p>
        <div class="deceased-modal-buttons">
            <button type="button" class="btn-secondary" onclick="closeDeceasedModal()">Cancel</button>
            <button type="button" class="btn-danger" id="confirmDeceasedSaveBtn" style="background: #dc2626; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">Yes, Mark Deceased</button>
        </div>
    </div>
</div>

<script>
let originalData = {};
let originalFamilyId = @json((string) ($citizen->family_id ?? ''));
let originalFamilyName = @json($citizen->family->family_name ?? 'No Family Assigned');
let originalFamilySubgroup = @json($citizen->family->subgroup->name ?? 'None');

function enableEdit() {
    const form = document.getElementById('citizenForm');
    const inputs = form.querySelectorAll('input:not([type="hidden"]), select');

    originalData = {};

    inputs.forEach(field => {
        if (field.name) {
            originalData[field.name] = field.value;
            field.disabled = false;
        }
    });

    const deceasedInput = document.getElementById('Citizen_IsDeceased');
    const markCheckbox = document.getElementById('markDeceasedCheckbox');
    markCheckbox.disabled = false;
    markCheckbox.checked = deceasedInput.value === '1';

    ['displayFamilyId', 'displayFamilyName', 'displaySubgroupName', 'displayDeceasedStatus'].forEach(id => {
        document.getElementById(id).disabled = true;
    });

    document.getElementById('displayFamilyName').style.display = 'none';
    document.getElementById('familyEditContainer').style.display = 'block';
    document.getElementById('openEditManualPicker').style.display = 'inline-block';
    document.getElementById('openEditManualPicker').disabled = Boolean(originalFamilyId);

    document.getElementById('saveBtn').style.display = 'inline-block';
    document.getElementById('cancelBtn').style.display = 'inline-block';
    document.getElementById('editBtn').style.display = 'none';
}

function disableEdit() {
    const form = document.getElementById('citizenForm');
    const fields = form.querySelectorAll('input:not([type="hidden"]), select');

    fields.forEach(field => {
        if (field.name && Object.prototype.hasOwnProperty.call(originalData, field.name)) {
            field.value = originalData[field.name];
        }

        field.disabled = true;
    });

    document.getElementById('markDeceasedCheckbox').disabled = true;
    document.getElementById('displayDeceasedStatus').value = document.getElementById('Citizen_IsDeceased').value === '1' ? 'Deceased' : 'Living';
    document.getElementById('familySelect').value = originalFamilyId;
    document.getElementById('displayFamilyId').value = originalFamilyId || 'None';
    document.getElementById('displayFamilyName').value = originalFamilyName;
    document.getElementById('displaySubgroupName').value = originalFamilySubgroup;

    document.getElementById('displayFamilyName').style.display = 'block';
    document.getElementById('familyEditContainer').style.display = 'none';
    document.getElementById('openEditManualPicker').style.display = 'none';

    document.getElementById('saveBtn').style.display = 'none';
    document.getElementById('cancelBtn').style.display = 'none';
    document.getElementById('editBtn').style.display = 'inline-block';
}

document.getElementById('markDeceasedCheckbox').addEventListener('change', function() {
    const deceasedInput = document.getElementById('Citizen_IsDeceased');
    const displayStatus = document.getElementById('displayDeceasedStatus');

    deceasedInput.value = this.checked ? '1' : '0';
    displayStatus.value = this.checked ? 'Deceased' : 'Living';
});

function handlePreSaveCheck() {
    const deceasedInput = document.getElementById('Citizen_IsDeceased');
    const wasAlreadyDeceased = @json((string) $citizen->Citizen_IsDeceased) === '1';

    if (deceasedInput.value === '1' && !wasAlreadyDeceased) {
        document.getElementById('deceasedConfirmModal').classList.add('active');
    } else {
        document.getElementById('citizenForm').submit();
    }
}

function closeDeceasedModal() {
    document.getElementById('deceasedConfirmModal').classList.remove('active');
}

document.getElementById('confirmDeceasedSaveBtn').addEventListener('click', function() {
    document.getElementById('citizenForm').submit();
});

function toggleEditAssignmentModal(id, open) {
    const modal = document.getElementById(id);
    modal.classList.toggle('open', open);
    modal.setAttribute('aria-hidden', String(!open));
}

document.getElementById('openEditFamilyPicker').addEventListener('click', () => toggleEditAssignmentModal('editFamilyPicker', true));
document.getElementById('openEditManualPicker').addEventListener('click', () => toggleEditAssignmentModal('editManualPicker', true));
document.querySelectorAll('[data-close-edit-modal]').forEach(button => button.addEventListener('click', () => {
    toggleEditAssignmentModal('editFamilyPicker', false);
    toggleEditAssignmentModal('editManualPicker', false);
}));

document.querySelectorAll('.edit-family-option').forEach(option => option.addEventListener('click', function () {
    document.getElementById('familySelect').value = option.dataset.familyId;
    document.getElementById('editSubgroupId').value = option.dataset.subgroupId;
    document.getElementById('Citizen_Purok').value = option.dataset.purok;
    document.getElementById('Citizen_Subgroup').value = option.dataset.subgroupName;
    document.getElementById('displayFamilyId').value = `F-${String(option.dataset.familyId).padStart(4, '0')}`;
    document.getElementById('displayFamilyName').value = option.dataset.familyName;
    document.getElementById('displaySubgroupName').value = option.dataset.subgroupName;
    document.getElementById('openEditManualPicker').disabled = true;
    toggleEditAssignmentModal('editFamilyPicker', false);
}));

document.querySelectorAll('.edit-manual-option').forEach(option => option.addEventListener('click', function () {
    document.getElementById('familySelect').value = '';
    document.getElementById('editSubgroupId').value = option.dataset.subgroupId;
    document.getElementById('Citizen_Purok').value = option.dataset.purok;
    document.getElementById('Citizen_Subgroup').value = option.dataset.subgroupName;
    document.getElementById('displayFamilyId').value = 'None';
    document.getElementById('displayFamilyName').value = 'No Family Assigned';
    document.getElementById('displaySubgroupName').value = option.dataset.subgroupName;
    document.getElementById('openEditManualPicker').disabled = false;
    toggleEditAssignmentModal('editManualPicker', false);
}));

document.getElementById('editFamilySearch').addEventListener('input', function () {
    const search = this.value.toLowerCase();
    document.querySelectorAll('.edit-family-option').forEach(option => {
        option.hidden = !option.textContent.toLowerCase().includes(search);
    });
});
</script>

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

        existingMedicineGroup.hidden = isCustom;

        customMedicineGroup.hidden = !isCustom;

        existingMedicine.required = !isCustom;

        customMedicine.required = isCustom;

        existingMedicine.disabled = isCustom;

        customMedicine.disabled = !isCustom;

    }

    medicineSource.addEventListener("change", updateMedicineSourceFields);

    updateMedicineSourceFields();

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
