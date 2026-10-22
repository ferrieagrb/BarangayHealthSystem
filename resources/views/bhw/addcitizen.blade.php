@extends('templates.layout')

@section('CSSown')

<link rel="stylesheet" href="{{ asset('css/bhw/addcitizen.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">

<style>
    .photo-crop-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .65);
    }

    .photo-crop-modal.open { display: flex; }
    .photo-crop-dialog { width: min(720px, 100%); padding: 24px; border-radius: 14px; background: #fff; box-shadow: 0 24px 70px rgba(15, 23, 42, .3); }
    .photo-crop-stage { height: min(58vh, 480px); margin-top: 16px; overflow: hidden; background: #0f172a; }
    .photo-crop-stage img { display: block; max-width: 100%; }
    .photo-crop-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; }
</style>

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
                 BASIC INFORMATION
                 ===================================================== --}}
            <h3 class="citizen-section-title" style="grid-column: 1 / -1;">Basic Information</h3>

            <div class="form-group">
                <label for="first_name">First Name</label>
                <input
                    type="text"
                    name="Citizen_FName"
                    id="first_name"
                    required
                    maxlength="255"
                    value="{{ old('Citizen_FName') }}"
                    autocomplete="given-name"
                >
            </div>

            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input
                    type="text"
                    name="Citizen_MName"
                    id="middle_name"
                    maxlength="255"
                    value="{{ old('Citizen_MName') }}"
                    placeholder="Enter middle name"
                >
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input
                    type="text"
                    name="Citizen_LName"
                    id="last_name"
                    required
                    maxlength="255"
                    value="{{ old('Citizen_LName') }}"
                    autocomplete="family-name"
                >
            </div>

            <div class="form-group">
                <label for="birthdate">Date of Birth</label>
                <input
                    type="date"
                    name="Citizen_BirthDate"
                    id="birthdate"
                    required
                    value="{{ old('Citizen_BirthDate') }}"
                >
                <small id="birthdate-error" class="validation-message">
                    Date of birth must be before today.
                </small>
            </div>

            <div class="form-group">
                <label>Age</label>
                <input type="number" name="Citizen_Age" id="age" readonly required value="{{ old('Citizen_Age') }}">
            </div>

            <div class="form-group">
                <label for="contact">Contact Number</label>
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
                    value="{{ old('Citizen_ContactNo') }}"
                >
                <small id="contact-error" class="validation-message">
                    Contact number must contain exactly 11 digits.
                </small>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="citizenFamilyDisplay">Family</label>
                <input type="hidden" name="family_id" id="citizenFamilyId" value="{{ old('family_id') }}" required>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <input type="text" id="citizenFamilyDisplay" class="form-control" value="" placeholder="Select a family or use manual assignment" readonly>
                    <button type="button" class="btn-secondary" id="openFamilyPicker">Select Family</button>
                </div>
                <small id="assignmentStatus">The citizen's Purok and subgroup will come from the selected family.</small>
            </div>

            <div class="form-group">
                <label for="citizenPurokDisplay">Purok</label>
                <input type="hidden" name="Citizen_Purok" id="citizenPurok">
                <input type="text" id="citizenPurokDisplay" class="form-control" placeholder="Selected from family or manual assignment" readonly>
            </div>

            <div class="form-group">
                <label for="citizenSubgroupDisplay">Purok Subgroup</label>
                <input type="hidden" name="subgroup_id" id="citizenSubgroup">
                <input type="text" id="citizenSubgroupDisplay" class="form-control" placeholder="Selected from family or manual assignment" readonly>
                <button type="button" class="btn-secondary" id="openManualAssignment">Assign without family</button>
            </div>

            <div class="form-group photo-upload-group">
                <label for="photo">Citizen Photo</label>
                <input
                    type="file"
                    name="photo"
                    id="photo"
                    accept="image/jpeg,image/png,image/webp"
                >
                <small>Optional. JPG, PNG, or WEBP; maximum 2 MB.</small>
            </div>

            <div class="photo-preview-panel" aria-live="polite">
                <span id="photoPreviewEmpty">Photo preview</span>
                <img id="photoPreview" alt="Citizen photo preview" hidden>
            </div>


            {{-- =====================================================
                 PHYSICAL INFORMATION
                 ===================================================== --}}
            <h3 class="citizen-section-title" style="grid-column: 1 / -1;">Physical Information</h3>

            <div class="form-group">
                <label for="Citizen_Height">Height (cm)</label>
                <input type="number"
                       id="Citizen_Height"
                       name="Citizen_Height"
                       min="0"
                       step="0.01"
                       value="{{ old('Citizen_Height') }}"
                       placeholder="e.g. 165.50">
            </div>

            <div class="form-group">
                <label for="Citizen_Weight">Weight (kg)</label>
                <input type="number"
                       id="Citizen_Weight"
                       name="Citizen_Weight"
                       min="0"
                       step="0.01"
                       value="{{ old('Citizen_Weight') }}"
                       placeholder="e.g. 55.50">
            </div>

            <div class="form-group">
                <label for="Citizen_Sex">Sex</label>
                <select id="Citizen_Sex" name="Citizen_Sex" required>
                    <option value="">Select Sex</option>
                    <option value="Male" {{ old('Citizen_Sex') === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('Citizen_Sex') === 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>


            {{-- =====================================================
                 HOUSING STATUS
                 ===================================================== --}}
            <h3 class="citizen-section-title" style="grid-column: 1 / -1;">Housing Status</h3>

            <div class="form-group">
                <label for="Citizen_HousingStatus">Residence Type</label>
                <select id="Citizen_HousingStatus" name="Citizen_HousingStatus">
                    <option value="">Select Housing Status</option>
                    <option value="Permanent Resident" {{ old('Citizen_HousingStatus') === 'Permanent Resident' ? 'selected' : '' }}>Permanent Resident</option>
                    <option value="Renter" {{ old('Citizen_HousingStatus') === 'Renter' ? 'selected' : '' }}>Renter</option>
                </select>
            </div>


            {{-- =====================================================
                 HEALTH & FAMILY PLANNING
                 ===================================================== --}}
            <h3 class="citizen-section-title" style="grid-column: 1 / -1;">Health &amp; Family Planning</h3>

            <div class="form-group">
                <label for="Citizen_Pregnant">Pregnant</label>
                <select id="Citizen_Pregnant" name="Citizen_Pregnant">
                    <option value="0" {{ old('Citizen_Pregnant') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('Citizen_Pregnant') === '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>

            <div class="form-group">
                <label for="Citizen_InPills">Using Birth Control Pills</label>
                <select id="Citizen_InPills" name="Citizen_InPills">
                    <option value="0" {{ old('Citizen_InPills') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('Citizen_InPills') === '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>



            {{-- =====================================================
                 VITAL STATUS
                 ===================================================== --}}
            <h3 class="citizen-section-title" style="grid-column: 1 / -1;">Vital Status</h3>

            <div class="form-group">
                <label for="Citizen_IsDeceased">Resident Status</label>
                <select id="Citizen_IsDeceased" name="Citizen_IsDeceased">
                    <option value="0" {{ old('Citizen_IsDeceased') === '0' ? 'selected' : '' }}>Living</option>
                    <option value="1" {{ old('Citizen_IsDeceased') === '1' ? 'selected' : '' }}>Deceased</option>
                </select>
            </div>


            {{-- =====================================================
                 LOGIN CREDENTIALS
                 ===================================================== --}}
            <h3 class="citizen-section-title" style="grid-column: 1 / -1;">Portal Login Credentials</h3>

            <div class="form-group">
                <label for="email">Email Address (For Login)</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    required
                    placeholder="resident@email.com"
                    value="{{ old('email') }}"
                    autocomplete="email"
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

<style>
    .family-picker-modal { display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; padding:20px; background:rgba(15,23,42,.55); }
    .family-picker-modal.open { display:flex; }
    .family-picker-box { width:min(760px,100%); max-height:90vh; overflow:auto; padding:24px; border-radius:12px; background:#fff; box-shadow:0 20px 50px rgba(15,23,42,.25); }
    .family-picker-header { display:flex; justify-content:space-between; gap:12px; align-items:center; margin-bottom:16px; }
    .family-picker-list { display:grid; gap:8px; max-height:360px; overflow:auto; }
    .family-option { display:flex; justify-content:space-between; gap:12px; align-items:center; padding:12px; border:1px solid #dbe3ef; border-radius:8px; background:#fff; text-align:left; cursor:pointer; }
    .family-option:hover { border-color:#2563eb; background:#eff6ff; }
    .family-picker-close { border:0; background:transparent; font-size:24px; cursor:pointer; }
    .family-create-form { display:grid; gap:12px; margin-top:20px; padding-top:20px; border-top:1px solid #e5e7eb; }
</style>

<div class="family-picker-modal" id="familyPickerModal" aria-hidden="true">
    <div class="family-picker-box" role="dialog" aria-modal="true" aria-labelledby="familyPickerTitle">
        <div class="family-picker-header">
            <h2 id="familyPickerTitle" style="margin:0;">Select Family</h2>
            <button type="button" class="family-picker-close" id="closeFamilyPicker" aria-label="Close">&times;</button>
        </div>
        <input type="search" id="familySearch" class="form-control" placeholder="Search family name...">
        <div class="family-picker-list" id="familyPickerList">
            @foreach($families as $family)
                <button type="button" class="family-option" data-family-id="{{ $family->id }}" data-family-label="{{ $family->family_name }} - {{ $family->purok->name ?? 'No Purok' }} / {{ $family->subgroup->name ?? 'No subgroup' }}" data-purok="{{ $family->purok->name ?? '' }}" data-subgroup="{{ $family->subgroup->id ?? '' }}" data-subgroup-name="{{ $family->subgroup->name ?? '' }}">
                    <span><strong>{{ $family->family_name }}</strong><br><small>{{ $family->purok->name ?? 'No Purok' }} / {{ $family->subgroup->name ?? 'No subgroup' }}</small></span>
                    <span>Select</span>
                </button>
            @endforeach
        </div>
        <button type="button" class="btn-primary" id="openNewFamilyModal" style="margin-top:16px;">+ Add New Family</button>
    </div>
</div>

<div class="family-picker-modal" id="manualAssignmentModal" aria-hidden="true">
    <div class="family-picker-box" role="dialog" aria-modal="true" aria-labelledby="manualAssignmentTitle">
        <div class="family-picker-header">
            <h2 id="manualAssignmentTitle" style="margin:0;">Choose Purok and Subgroup</h2>
            <button type="button" class="family-picker-close" id="closeManualAssignment" aria-label="Close">&times;</button>
        </div>
        <div id="manualAssignmentList" class="family-picker-list">
            @foreach($puroks as $purok)
                <strong style="margin-top:8px; color:#173b82;">{{ $purok->name }}</strong>
                @forelse($purok->subgroups as $subgroup)
                    <button type="button" class="family-option manual-assignment-option" data-purok="{{ $purok->name }}" data-subgroup-id="{{ $subgroup->id }}" data-subgroup-name="{{ $subgroup->name }}">
                        <span>{{ $purok->name }} / {{ $subgroup->name }}</span><span>Select</span>
                    </button>
                @empty
                    <small style="color:#64748b;">No subgroups configured for this Purok.</small>
                @endforelse
            @endforeach
        </div>
    </div>
</div>

<div class="family-picker-modal" id="newFamilyModal" aria-hidden="true">
    <div class="family-picker-box" role="dialog" aria-modal="true" aria-labelledby="newFamilyTitle">
        <div class="family-picker-header">
            <h2 id="newFamilyTitle" style="margin:0;">Register New Family</h2>
            <button type="button" class="family-picker-close close-new-family" aria-label="Close">&times;</button>
        </div>
        <form id="quickFamilyForm" class="family-create-form" data-no-loader="true">
            @csrf
            <input type="text" name="family_name" class="form-control" placeholder="Family name" required>
            <select name="subgroup_id" class="form-control" required>
                <option value="">-- Select Purok Subgroup --</option>
                @foreach($puroks as $purok)
                    @foreach($purok->subgroups as $subgroup)
                        <option value="{{ $subgroup->id }}">{{ $purok->name }} / {{ $subgroup->name }}</option>
                    @endforeach
                @endforeach
            </select>
            <div id="quickFamilyError" style="color:#b91c1c;"></div>
            <button type="submit" class="btn-primary">Create and Select Family</button>
        </form>
    </div>
</div>

<div id="photoCropModal" class="photo-crop-modal" role="dialog" aria-modal="true" aria-labelledby="photoCropTitle">
    <div class="photo-crop-dialog">
        <h2 id="photoCropTitle" style="margin:0; color:#172033;">Crop Citizen Photo</h2>
        <p style="margin:6px 0 0; color:#64748b; font-size:13px;">Adjust the square 2×2 crop before saving the citizen.</p>
        <div class="photo-crop-stage">
            <img id="photoCropImage" alt="Photo crop preview">
        </div>
        <div class="photo-crop-actions">
            <button type="button" id="cancelPhotoCrop" class="btn-secondary">Cancel</button>
            <button type="button" id="confirmPhotoCrop" class="btn-primary">Use Cropped Photo</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const familyPickerModal = document.getElementById('familyPickerModal');
    const newFamilyModal = document.getElementById('newFamilyModal');
    const familyIdInput = document.getElementById('citizenFamilyId');
    const familyDisplay = document.getElementById('citizenFamilyDisplay');
    const purokInput = document.getElementById('citizenPurok');
    const subgroupInput = document.getElementById('citizenSubgroup');
    const purokDisplay = document.getElementById('citizenPurokDisplay');
    const subgroupDisplay = document.getElementById('citizenSubgroupDisplay');
    const manualAssignmentButton = document.getElementById('openManualAssignment');
    const manualAssignmentModal = document.getElementById('manualAssignmentModal');
    const assignmentStatus = document.getElementById('assignmentStatus');

    function useManualAssignment() {
        familyIdInput.value = '';
        manualAssignmentButton.disabled = false;
        familyIdInput.removeAttribute('required');
        familyDisplay.value = '';
        familyDisplay.removeAttribute('required');
        familyIdInput.value = '';
        purokInput.value = '';
        subgroupInput.value = '';
        purokDisplay.value = '';
        subgroupDisplay.value = '';
        assignmentStatus.textContent = 'Select the citizen\'s Purok and subgroup manually.';
    }

    function useFamilyAssignment() {
        familyIdInput.required = true;
        manualAssignmentButton.disabled = true;
        assignmentStatus.textContent = 'The citizen\'s Purok and subgroup will come from the selected family.';
    }

    function openModal(modal) { modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); }
    function closeModal(modal) { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }

    document.getElementById('openFamilyPicker').addEventListener('click', () => openModal(familyPickerModal));
    document.getElementById('openManualAssignment').addEventListener('click', () => { useManualAssignment(); openModal(manualAssignmentModal); });
    document.getElementById('closeManualAssignment').addEventListener('click', () => closeModal(manualAssignmentModal));
    document.getElementById('closeFamilyPicker').addEventListener('click', () => closeModal(familyPickerModal));
    document.getElementById('openNewFamilyModal').addEventListener('click', () => { closeModal(familyPickerModal); openModal(newFamilyModal); });
    document.querySelectorAll('.close-new-family').forEach(button => button.addEventListener('click', () => closeModal(newFamilyModal)));

    document.querySelectorAll('.family-option').forEach(option => option.addEventListener('click', () => {
        familyIdInput.value = option.dataset.familyId;
        familyDisplay.value = option.dataset.familyLabel;
        purokInput.value = option.dataset.purok;
        subgroupInput.value = option.dataset.subgroup;
        purokDisplay.value = option.dataset.purok;
        subgroupDisplay.value = option.dataset.subgroupName;
        useFamilyAssignment();
        closeModal(familyPickerModal);
    }));

    document.getElementById('familySearch').addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.family-option').forEach(option => {
            option.hidden = !option.textContent.toLowerCase().includes(term);
        });
    });

    document.getElementById('quickFamilyForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        const error = document.getElementById('quickFamilyError');
        const submitButton = this.querySelector('button[type="submit"]');
        error.textContent = '';
        submitButton.disabled = true;
        submitButton.textContent = 'Creating...';

        try {
            const response = await fetch('{{ route('bhw.families.quick.store') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json' },
                body: new FormData(this)
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok || !payload.id || !payload.purok || !payload.subgroup) {
                error.textContent = payload.message || 'Unable to create family. Check the selected subgroup and try again.';
                return;
            }

            familyIdInput.value = payload.id;
            familyDisplay.value = `${payload.family_name} - ${payload.purok.name} / ${payload.subgroup.name}`;
            purokInput.value = payload.purok.name;
            subgroupInput.value = payload.subgroup.id;
            purokDisplay.value = payload.purok.name;
            subgroupDisplay.value = payload.subgroup.name;
            useFamilyAssignment();
            closeModal(newFamilyModal);
            this.reset();
        } catch (requestError) {
            error.textContent = 'Unable to connect while creating the family. Please try again.';
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Create and Select Family';
        }
    });

    document.querySelectorAll('.manual-assignment-option').forEach(option => option.addEventListener('click', function () {
        purokInput.value = option.dataset.purok;
        subgroupInput.value = option.dataset.subgroupId;
        purokDisplay.value = option.dataset.purok;
        subgroupDisplay.value = option.dataset.subgroupName;
        closeModal(manualAssignmentModal);
    }));

    if (familyIdInput.value) useFamilyAssignment();
    else useManualAssignment();

    const ageInput = document.getElementById('age');
    const birthdateInput = document.getElementById('birthdate');
    const citizenForm = document.getElementById('citizen-form');
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    const photoPreviewEmpty = document.getElementById('photoPreviewEmpty');
    const photoCropModal = document.getElementById('photoCropModal');
    const photoCropImage = document.getElementById('photoCropImage');
    const cancelPhotoCrop = document.getElementById('cancelPhotoCrop');
    const confirmPhotoCrop = document.getElementById('confirmPhotoCrop');
    let photoCropper = null;

    photoInput.addEventListener('change', function () {
        const file = this.files?.[0];
        if (!file) {
            photoPreview.hidden = true;
            photoPreview.removeAttribute('src');
            photoPreviewEmpty.hidden = false;
            return;
        }

        const reader = new FileReader();
        reader.onload = event => {
            photoCropImage.src = event.target.result;
            photoCropModal.classList.add('open');
            photoCropper?.destroy();
            photoCropper = new Cropper(photoCropImage, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: .9,
                responsive: true,
            });
        };
        reader.readAsDataURL(file);
    });

    cancelPhotoCrop.addEventListener('click', function () {
        photoCropper?.destroy();
        photoCropper = null;
        photoCropModal.classList.remove('open');
        photoInput.value = '';
    });

    confirmPhotoCrop.addEventListener('click', function () {
        if (!photoCropper) return;

        photoCropper.getCroppedCanvas({ width: 600, height: 600 }).toBlob(function (blob) {
            const croppedFile = new File([blob], 'citizen-photo.jpg', { type: 'image/jpeg' });
            const transfer = new DataTransfer();
            transfer.items.add(croppedFile);
            photoInput.files = transfer.files;
            photoPreview.src = URL.createObjectURL(blob);
            photoPreview.hidden = false;
            photoPreviewEmpty.hidden = true;
            photoCropper.destroy();
            photoCropper = null;
            photoCropModal.classList.remove('open');
        }, 'image/jpeg', .9);
    });
    
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
            return true;
        }
    });

    // Intercepts form submission to open custom centered modal
    citizenForm.addEventListener('submit', function(e) {
        if (!isFormVerified) {
            e.preventDefault();

            const firstName = document.querySelector('input[name="Citizen_FName"]').value;
            const lastName = document.querySelector('input[name="Citizen_LName"]').value;
            const family = familyDisplay.value;
            const manualAssignment = purokInput.value && subgroupInput.value;

            if(!firstName || !lastName || (!family && !manualAssignment)) {
                citizenForm.reportValidity();
                return;
            }

            modalMessage.innerText = `Are you sure you want to save the record for ${firstName} ${lastName} under ${family}?`;
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