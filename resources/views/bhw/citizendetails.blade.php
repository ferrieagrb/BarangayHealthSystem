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
        <h1>Citizen Details</h1>
        <p>View and update citizen information</p>
    </div>

    <div class="right">
        <a href="{{ route('citizenlist') }}" class="btn-secondary">← Back</a>
        @if((int) $citizen->Citizen_IsDeceased !== 1)
            <button type="button" class="btn-primary" id="editBtn" onclick="enableEdit()">Edit</button>
        @else
            <span class="text-danger" style="font-weight: bold; font-size: 13px; background: #fee2e2; padding: 8px 12px; border-radius: 6px; color: #991b1b;">Record Locked (Deceased)</span>
        @endif
    </div>
</div>

<div class="info-card">
    <div class="card">

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
                   disabled>

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
                   value="{{ (int) $citizen->Citizen_IsDeceased === 1 ? 'Deceased' : 'Living' }}"
                   disabled>

            {{-- Hidden input ensuring value is properly submitted to controller --}}
            <input type="hidden"
                   id="Citizen_IsDeceased"
                   name="Citizen_IsDeceased"
                   value="{{ old('Citizen_IsDeceased', $citizen->Citizen_IsDeceased ?? 0) }}">

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
                <label for="familySelect">Assign / Update Family (Grouped by Purok)</label>

                <select name="family_id"
                        id="familySelect"
                        class="form-control">
                    <option value="">-- No Family (Unassigned) --</option>

                    @foreach($families->groupBy(fn($fam) => $fam->purok->name ?? 'No Purok') as $purokName => $purokFamilies)
                        <optgroup label="Purok: {{ $purokName }}">
                            @foreach($purokFamilies as $family)
                                <option value="{{ $family->id }}"
                                        data-name="{{ $family->family_name }}"
                                        data-id="{{ $family->id }}"
                                        data-subgroup="{{ $family->subgroup->name ?? 'None' }}"
                                    {{ old('family_id', $citizen->family_id) == $family->id ? 'selected' : '' }}>
                                    {{ $family->family_name }}
                                    @if($family->subgroup)
                                        (Subgroup: {{ $family->subgroup->name }})
                                    @endif
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

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

document.getElementById('familySelect').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];

    if (this.value) {
        document.getElementById('displayFamilyId').value =
            selectedOption.dataset.id || 'None';

        document.getElementById('displayFamilyName').value =
            selectedOption.dataset.name || 'No Family Assigned';

        document.getElementById('displaySubgroupName').value =
            selectedOption.dataset.subgroup || 'None';
    } else {
        document.getElementById('displayFamilyId').value = 'None';
        document.getElementById('displayFamilyName').value = 'No Family Assigned';
        document.getElementById('displaySubgroupName').value = 'None';
    }
});
</script>

@endsection