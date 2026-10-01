@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/citizenfields.css') }}">
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Citizen Details</h1>
        <p>View and update citizen information</p>
    </div>

    <div class="right">
        <a href="{{ route('citizenlist') }}" class="btn-secondary">← Back</a>
        <button type="button" class="btn-primary" onclick="enableEdit()">Edit</button>
    </div>
</div>

<br>

<div class="info-card">
    <div class="card">

        <form method="POST" action="{{ route('citizen.update', $citizen->id) }}" id="citizenForm">
            @csrf
            @method('PUT')

            <h2>
                {{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}
            </h2>

            <br>

            <!-- FIRST NAME -->
            <label>First Name</label>
            <input type="text"
                   name="Citizen_FName"
                   value="{{ $citizen->Citizen_FName }}"
                   disabled>

            <!-- LAST NAME -->
            <label>Last Name</label>
            <input type="text"
                   name="Citizen_LName"
                   value="{{ $citizen->Citizen_LName }}"
                   disabled>

            <!-- AGE -->
            <label>Age</label>
            <input type="number"
                   name="Citizen_Age"
                   value="{{ $citizen->Citizen_Age }}"
                   disabled>

            <!-- BIRTHDATE -->
            <label>Birthdate</label>
            <input type="date"
                   name="Citizen_BirthDate"
                   value="{{ $citizen->Citizen_BirthDate }}"
                   disabled>

            <!-- CONTACT -->
            <label>Contact Number</label>
            <input type="text"
                   name="Citizen_ContactNo"
                   value="{{ $citizen->Citizen_ContactNo }}"
                   disabled>

            <!-- PUROK -->
            <label>Purok</label>
            <input type="text"
                   name="Citizen_Purok"
                   value="{{ $citizen->Citizen_Purok }}"
                   disabled>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

            <!-- FAMILY ID (Read-only / Auto-updated) -->
            <label>Family ID</label>
            <input type="text" 
                   id="displayFamilyId" 
                   value="{{ $citizen->family->id ?? 'None' }}" 
                   disabled>

            <!-- FAMILY NAME (Read-only view mode) -->
            <label>Family Name</label>
            <input type="text" 
                   id="displayFamilyName" 
                   value="{{ $citizen->family->family_name ?? 'No Family Assigned' }}" 
                   disabled>

            <!-- SUBGROUP (Read-only / Auto-updated) -->
            <label>Subgroup / Purok Section</label>
            <input type="text" 
                   id="displaySubgroupName" 
                   value="{{ $citizen->family->subgroup->name ?? 'None' }}" 
                   disabled>

            <!-- FAMILY SELECTOR (Edit mode only) -->
            <div id="familyEditContainer" style="display: none;">
                <label>Assign / Update Family (Grouped by Purok)</label>
                <select name="family_id" id="familySelect" class="form-control" style="width: 100%; padding: 10px; margin-bottom: 15px;">
                    <option value="">-- No Family (Unassigned) --</option>
                    
                    {{-- Group families by Purok --}}
                    @foreach($families->groupBy(fn($fam) => $fam->purok->name ?? 'No Purok') as $purokName => $purokFamilies)
                        <optgroup label="Purok: {{ $purokName }}">
                            @foreach($purokFamilies as $family)
                                <option value="{{ $family->id }}" 
                                        data-name="{{ $family->family_name }}"
                                        data-id="{{ $family->id }}"
                                        data-subgroup="{{ $family->subgroup->name ?? 'None' }}"
                                        {{ $citizen->family_id == $family->id ? 'selected' : '' }}>
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

            <br>

            <button type="submit" class="btn-primary" id="saveBtn" style="display:none;">
                Save Changes
            </button>

            <button type="button" class="btn-secondary" id="cancelBtn" style="display:none;" onclick="disableEdit()">
                Cancel
            </button>

        </form>

    </div>
</div>

<!-- JS TOGGLE & DYNAMIC FAMILY AUTO-UPDATE -->
<script>
let originalData = {};
let originalFamilyId = "{{ $citizen->family_id }}";
let originalFamilyName = "{{ $citizen->family->family_name ?? 'No Family Assigned' }}";
let originalFamilySubgroup = "{{ $citizen->family->subgroup->name ?? 'None' }}";

function enableEdit() {
    const inputs = document.querySelectorAll('#citizenForm input');

    inputs.forEach(input => {
        originalData[input.name] = input.value;
        input.disabled = false;
    });

    // Keep disabled since they auto-update via dropdown
    document.getElementById('displayFamilyId').disabled = true;
    document.getElementById('displayFamilyName').disabled = true;
    document.getElementById('displaySubgroupName').disabled = true;

    // Hide text view of family name and show the grouped dropdown selection
    document.getElementById('displayFamilyName').style.display = 'none';
    document.getElementById('familyEditContainer').style.display = 'block';

    document.getElementById('saveBtn').style.display = 'inline-block';
    document.getElementById('cancelBtn').style.display = 'inline-block';
}

function disableEdit() {
    const inputs = document.querySelectorAll('#citizenForm input');

    inputs.forEach(input => {
        if (originalData[input.name] !== undefined) {
            input.value = originalData[input.name];
        }
        input.disabled = true;
    });

    // Reset family fields back to original view state
    document.getElementById('familySelect').value = originalFamilyId;
    document.getElementById('displayFamilyName').value = originalFamilyName;
    document.getElementById('displayFamilyId').value = originalFamilyId || 'None';
    document.getElementById('displaySubgroupName').value = originalFamilySubgroup;

    document.getElementById('displayFamilyName').style.display = 'block';
    document.getElementById('familyEditContainer').style.display = 'none';

    document.getElementById('saveBtn').style.display = 'none';
    document.getElementById('cancelBtn').style.display = 'none';
}

// Auto-update the Family ID, Name, and Subgroup preview fields on change
document.getElementById('familySelect').addEventListener('change', function() {
    let selectedOption = this.options[this.selectedIndex];
    if (this.value) {
        document.getElementById('displayFamilyId').value = selectedOption.getAttribute('data-id');
        document.getElementById('displayFamilyName').value = selectedOption.getAttribute('data-name');
        document.getElementById('displaySubgroupName').value = selectedOption.getAttribute('data-subgroup');
    } else {
        document.getElementById('displayFamilyId').value = 'None';
        document.getElementById('displayFamilyName').value = 'No Family Assigned';
        document.getElementById('displaySubgroupName').value = 'None';
    }
});
</script>

@endsection