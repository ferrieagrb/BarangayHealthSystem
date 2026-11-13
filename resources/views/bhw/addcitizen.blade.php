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
    #familyPlanningSection { display: contents; }
    #familyPlanningSection[hidden], #pillsField[hidden] { display: none !important; }
</style>

<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
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
     data-ui-action="secondary">
        ← Back
    </a>


</div>



<div class="form-container">
    <form method="POST"
          action="{{ route('citizen.store') }}"
          id="citizen-form"
          data-quick-family-url="{{ route('bhw.families.quick.store') }}"
          data-old-family-id="{{ old('family_id') }}"
          data-old-subgroup-id="{{ old('subgroup_id') }}"
          novalidate
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
                    autocomplete="off"
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
                    autocomplete="off"
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
                <label for="contact">Contact Number (Optional)</label>
                <input
                    type="text"
                    name="Citizen_ContactNo"
                    id="contact"
                    inputmode="numeric"
                    autocomplete="off"
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
                <input type="hidden" name="family_id" id="citizenFamilyId" value="{{ old('family_id') }}">
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <input type="text" id="citizenFamilyDisplay" class="form-control" value="" placeholder="Select a family or use manual assignment" readonly>
                    <button type="button" class="btn-secondary" id="openFamilyPicker" data-ui-action="secondary">Select Family</button>
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
                <button type="button" class="btn-secondary" id="openManualAssignment" data-ui-action="secondary">Assign without family</button>
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


            <div id="familyPlanningSection" hidden>
                <h3 class="citizen-section-title" style="grid-column: 1 / -1;">Health &amp; Family Planning</h3>

                <div class="form-group">
                    <label for="Citizen_Pregnant">Pregnant</label>
                    <select id="Citizen_Pregnant" name="Citizen_Pregnant">
                        <option value="0" {{ old('Citizen_Pregnant', '0') === '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('Citizen_Pregnant') === '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                <div class="form-group" id="pillsField">
                    <label for="Citizen_InPills">Using Birth Control Pills</label>
                    <select id="Citizen_InPills" name="Citizen_InPills">
                        <option value="0" {{ old('Citizen_InPills', '0') === '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('Citizen_InPills') === '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>
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
                <label for="email">Email Address (Optional)</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    placeholder="resident@email.com"
                    value="{{ old('email') }}"
                    autocomplete="off"
                >
            </div>

        </div>


        {{-- =========================================================
             FORM ACTIONS
             ========================================================= --}}

        <div class="form-actions">
            <button type="submit" class="btn-primary" data-ui-action="primary">Save Citizen & Create Account</button>
            <a href="{{ route('citizenlist') }}" class="btn-secondary" data-ui-action="secondary">Cancel</a>
        </div>
        <p id="citizenSaveError" role="alert" hidden style="margin:12px 0 0;color:#b91c1c;"></p>


    </form>


</div>

<!-- Custom centered confirmation modal window with separated backdrop and box layers -->
<div id="confirmation-modal" style="display: none;">
    <div class="custom-modal-box">
        <h3 style="margin-top: 0; color: #111827; font-size: 18px;">Confirm Citizen Registration</h3>
        <p id="modal-message" style="color: #4b5563; font-size: 14px; margin: 15px 0;">Are you sure you want to save this record?</p>
        <div class="custom-modal-actions">
            <button type="button" id="modal-cancel-btn" class="btn-modal-cancel" data-ui-action="secondary">Cancel</button>
            <button type="button" id="modal-confirm-btn" class="btn-modal-confirm" data-ui-action="primary">Yes, Save</button>
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
    #familyPickerModal .family-picker-box { width:min(600px,100%); padding:24px; background:var(--theme-surface,#fff); color:var(--theme-text,#172b4d); }
    #familyPickerModal .family-picker-header { align-items:flex-start; margin-bottom:20px; }
    #familyPickerTitle { font-size:22px; line-height:1.4; }
    .family-picker-description { margin:5px 0 0; color:var(--theme-muted,#64748b); font-size:13px; line-height:1.5; }
    #familyPickerModal #familySearch { display:block; width:100%; height:46px; margin:0 0 16px; padding:10px 14px; box-sizing:border-box; border:1px solid var(--theme-border,#dce4ee); border-radius:9px; background:var(--theme-surface,#fff); color:var(--theme-text,#172b4d); font:inherit; font-size:14px; }
    #familyPickerModal #familySearch:focus { outline:2px solid #93b6ed; outline-offset:2px; }
    #familyPickerModal .family-option { display:flex !important; justify-content:space-between !important; width:100%; min-height:76px; padding:14px 16px !important; gap:16px; border:1px solid var(--theme-border,#dce4ee) !important; border-radius:10px; background:var(--theme-surface,#fff) !important; color:var(--theme-text,#172b4d) !important; text-align:left !important; box-shadow:none !important; }
    #familyPickerModal .family-option[hidden] { display:none !important; }
    #familyPickerModal .family-option:hover,
    #familyPickerModal .family-option:focus-visible { background:var(--theme-hover,#e8eef7) !important; border-color:#7298cf !important; }
    #familyPickerModal .family-option > span:first-child { flex:1; min-width:0; overflow-wrap:anywhere; }
    #familyPickerModal .family-option strong { display:block; font-size:14px; font-weight:600; line-height:1.5; }
    #familyPickerModal .family-option small { display:block; margin-top:4px; color:var(--theme-muted,#64748b); font-size:12px; }
    #familyPickerModal .family-option > span:last-child { flex:none; padding:6px 10px; border-radius:6px; background:var(--theme-raised,#f1f5f9); color:var(--theme-link,#234f9b); font-size:12px; font-weight:600; }
    #familyPickerModal .family-picker-close { flex:none; width:36px; min-height:36px; padding:0 !important; }
    .family-picker-footer { display:flex; justify-content:flex-end; margin-top:20px; padding-top:16px; border-top:1px solid var(--theme-border,#dce4ee); }
    #familyPickerEmpty { padding:24px 12px; margin:0; text-align:center; color:var(--theme-muted,#64748b); font-size:14px; }
    #familyPickerEmpty[hidden] { display:none; }
    @media(max-width:480px) { #familyPickerModal .family-picker-box { padding:18px; } #familyPickerModal .family-option { padding:12px !important; gap:10px; } }
</style>

<div class="family-picker-modal" id="familyPickerModal" aria-hidden="true">
    <div class="family-picker-box" role="dialog" aria-modal="true" aria-labelledby="familyPickerTitle">
        <div class="family-picker-header">
            <div>
                <h2 id="familyPickerTitle" style="margin:0;">Select Family</h2>
                <p class="family-picker-description">Choose the citizen's household.</p>
            </div>
            <button type="button" class="family-picker-close" id="closeFamilyPicker" aria-label="Close" data-ui-action="secondary">&times;</button>
        </div>
        <input type="search" id="familySearch" class="form-control" placeholder="Search family name or ID..." aria-label="Search family name or ID" autocomplete="off">
        <div class="family-picker-list" id="familyPickerList">
            @foreach($families as $family)
                <button type="button" class="family-option" data-family-id="{{ $family->id }}" data-family-label="{{ $family->family_code }} · {{ $family->family_name }} - {{ $family->purok->name ?? 'No Purok' }} / {{ $family->subgroup->name ?? 'No subgroup' }}" data-purok="{{ $family->purok->name ?? '' }}" data-subgroup="{{ $family->subgroup->id ?? '' }}" data-subgroup-name="{{ $family->subgroup->name ?? '' }}" data-ui-action="secondary">
                    <span><strong>{{ $family->family_code }} · {{ $family->family_name }}</strong><small>{{ $family->purok->name ?? 'No Purok' }} / {{ $family->subgroup->name ?? 'No subgroup' }}</small></span>
                    <span>Select</span>
                </button>
            @endforeach
        </div>
        <p id="familyPickerEmpty" role="status" @if($families->isNotEmpty()) hidden @endif>No families found.</p>
        <div class="family-picker-footer">
            <button type="button" class="btn-primary" id="openNewFamilyModal" data-ui-action="primary">+ Add New Family</button>
        </div>
    </div>
</div>

<div class="family-picker-modal" id="manualAssignmentModal" aria-hidden="true">
    <div class="family-picker-box" role="dialog" aria-modal="true" aria-labelledby="manualAssignmentTitle">
        <div class="family-picker-header">
            <h2 id="manualAssignmentTitle" style="margin:0;">Choose Purok and Subgroup</h2>
            <button type="button" class="family-picker-close" id="closeManualAssignment" aria-label="Close" data-ui-action="secondary">&times;</button>
        </div>
        <div id="manualAssignmentList" class="family-picker-list">
            @foreach($puroks as $purok)
                <strong style="margin-top:8px; color:#173b82;">{{ $purok->name }}</strong>
                @forelse($purok->subgroups as $subgroup)
                    <button type="button" class="family-option manual-assignment-option" data-purok="{{ $purok->name }}" data-subgroup-id="{{ $subgroup->id }}" data-subgroup-name="{{ $subgroup->name }}" data-ui-action="secondary">
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
            <button type="button" class="family-picker-close close-new-family" aria-label="Close" data-ui-action="secondary">&times;</button>
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
            <button type="submit" class="btn-primary" data-ui-action="primary">Create and Select Family</button>
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
            <button type="button" id="cancelPhotoCrop" class="btn-secondary" data-ui-action="secondary">Cancel</button>
            <button type="button" id="confirmPhotoCrop" class="btn-primary" data-ui-action="primary">Use Cropped Photo</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script src="{{ asset('js/bhw/addcitizen.js') }}?v={{ filemtime(public_path('js/bhw/addcitizen.js')) }}"></script>

@endsection
