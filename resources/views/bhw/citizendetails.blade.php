@extends('templates.layout')

@section('CSSown')

<link rel="stylesheet" href="{{ asset('css/bhw/citizenfields.css') }}?v={{ filemtime(public_path('css/bhw/citizenfields.css')) }}">

<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
@endsection

@section('content')

<div class="page-top">

    <div class="page-title">

        <h1>Citizen Details</h1>

        <p>View and update citizen information</p>

    </div>

    <div class="right">
        <a href="{{ route('citizenlist') }}" class="btn-secondary" style="text-decoration: none !important;">← Back</a>
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

        </form>

    </div>

</div>

<!-- IMMUNIZATION / VACCINATION -->
            <section class="immunization-section diagnosis-panel" aria-labelledby="immunizationTitle">
                <div class="immunization-heading diagnosis-panel-head">
                    <div>
                        <h3 id="immunizationTitle">Immunization / Vaccination</h3>
                        <p>Select a vaccine, record the dose/booster, date and save it to this citizen's history.</p>
                    </div>
                    <span class="diagnosis-count">{{ $citizen->vaccinations->count() }} {{ $citizen->vaccinations->count() === 1 ? 'Record' : 'Records' }}</span>
                </div>

                @if(session('success') && (\Illuminate\Support\Str::contains(strtolower(session('success')), ['vaccin', 'immuniz'])))
                    <div class="health-alert health-alert-success" role="status" aria-live="polite">{{ session('success') }}</div>
                @endif
                @if($errors->any() && old('vaccine_name'))
                    <div class="health-alert health-alert-error" role="alert">
                        <ul class="health-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

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

                @if($canEdit && (int)$citizen->Citizen_IsDeceased !== 1)
                    <div class="immunization-form-grid edit-gated-form" id="vaccinationEntryForm" style="display:none;">
                        <div class="immunization-field">
                            <label for="vaccine_name">Vaccine Name</label>
                            <select id="vaccine_name" name="vaccine_name" form="vaccinationForm" required>
                                <option value="" disabled selected>-- Select Vaccine --</option>
                                @foreach ($vaccines as $vaccineKey => $vaccineLabel)
                                    <option value="{{ $vaccineLabel }}" {{ old('vaccine_name') === $vaccineLabel ? 'selected' : '' }}>
                                        {{ $vaccineLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="immunization-field">
                            <label for="dose_number">Dose / Booster</label>
                            <select id="dose_number" name="dose_number" form="vaccinationForm" required>
                                <option value="" disabled selected>-- Select Dose/Booster --</option>
                                <option value="Single Dose">Single Dose</option>
                                <option value="Dose 1">Dose 1</option>
                                <option value="Dose 2">Dose 2</option>
                                <option value="Dose 3">Dose 3</option>
                                <option value="Booster 1">Booster 1</option>
                                <option value="Booster 2">Booster 2</option>
                            </select>
                        </div>

                        <div class="immunization-field">
                            <label for="date_administered">Date Administered</label>
                            <input type="date" id="date_administered" name="date_administered" value="{{ old('date_administered', now()->format('Y-m-d')) }}" form="vaccinationForm" required>
                        </div>

                        <input type="hidden" id="administered_by" name="administered_by" value="{{ auth()->user()->name ?? 'BHW' }}" form="vaccinationForm">

                        <div class="immunization-action-wrapper diagnosis-actions record-form-actions">
                            <button type="button" class="diagnosis-clear" id="clearVaccinationBtn">Clear</button>
                            <button type="submit" class="btn-primary" form="vaccinationForm">Add Vaccination</button>
                        </div>
                    </div>
                @elseif((int) $citizen->Citizen_IsDeceased === 1)
                    <p class="immunization-preview-note">This record is deceased and locked for updates.</p>
                @else
                    <p class="immunization-preview-note">Read-only: this citizen is not assigned to your subgroup.</p>
                @endif

                @if($vaccinationSchedulingEnabled)
                    <div class="immunization-history" style="margin-bottom: 18px;">
                        <h4>Scheduled Immunizations</h4>
                        @forelse ($citizen->vaccinationSchedules as $schedule)
                            <div class="immunization-history-item" style="border-left: 3px solid #f59e0b;">
                                <strong>{{ $schedule->vaccine_name }}</strong>
                                <span>{{ $schedule->dose_number ?: 'Scheduled dose' }} · Scheduled for {{ $schedule->scheduled_date->format('M d, Y') }}</span>
                                <small>Not yet administered{{ $schedule->notes ? ' · ' . $schedule->notes : '' }}</small>
                                @if($canEdit && (int)$citizen->Citizen_IsDeceased !== 1)
                                    <form method="POST" action="{{ route('citizen.vaccination.schedule.administer', [$citizen->id, $schedule->id]) }}" style="display:flex; align-items:end; gap:8px; flex-wrap:wrap; margin-top:10px;">
                                        @csrf
                                        <label style="font-size:12px; color:#475569;">Date administered
                                            <input type="date" name="date_administered" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" required style="display:block; margin-top:4px;">
                                        </label>
                                        <button type="submit" class="btn-primary">Mark Administered</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p>No pending immunization schedules.</p>
                        @endforelse
                    </div>
                @endif

                <div class="immunization-history">
                    <h4>Immunization History</h4>
                    @forelse ($citizen->vaccinations as $vaccination)
                        <article class="diagnosis-history-card immunization-history-card">
                            <div class="diagnosis-history-inner">
                                <div class="diagnosis-history-top">
                                    <div>
                                        <div class="diagnosis-history-title">
                                            {{ $vaccination->vaccine_name }}
                                            <span class="diagnosis-pill">{{ $vaccination->dose_number ?: 'Dose recorded' }}</span>
                                        </div>
                                        <div class="diagnosis-meta">
                                            Administered Date: {{ $vaccination->date_administered ? \Carbon\Carbon::parse($vaccination->date_administered)->format('M d, Y') : '—' }}
                                            · Administered by {{ $vaccination->administered_by ?: '—' }}
                                        </div>
                                    </div>
                                    @if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1)
                                        <form method="POST"
                                              action="{{ route('citizen.vaccination.delete', [$citizen->id, $vaccination->id]) }}"
                                              style="margin:0; flex-shrink:0;"
                                              onsubmit="return confirm('Delete this vaccination record? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="diagnosis-delete" style="cursor:pointer;color:#b91c1c;border-color:#fecaca;background:#fef2f2">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="diagnosis-empty">No immunization records yet.</div>
                    @endforelse
                </div>
            </section>
            
<!-- ================================================================

     MAINTENANCE MEDICINE

     ================================================================ -->

<section class="maintenance-container-box diagnosis-panel" aria-labelledby="maintenanceTitle">
    <div class="diagnosis-panel-head">
        <div>
            <h3 id="maintenanceTitle">Maintenance Medicines</h3>
            <p class="diagnosis-subtitle">Recorded maintenance medicines and medication details</p>
        </div>
        <span class="diagnosis-count">{{ $citizen->maintenanceRecords->count() }} {{ $citizen->maintenanceRecords->count() === 1 ? 'Record' : 'Records' }}</span>
    </div>

    @if(session('success') === 'Maintenance medicine added successfully.')

        <p class="health-alert health-alert-success">

            {{ session('success') }}

        </p>

    @endif

    @if($errors->any())

        <div class="health-alert health-alert-error">

            <ul class="health-error-list">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    @forelse ($citizen->maintenanceRecords as $maintenance)

        <article class="diagnosis-history-card maintenance-history-card">
            <div class="diagnosis-history-inner">
                <div class="diagnosis-history-top">
                    <div>
                        <div class="diagnosis-history-title">{{ $maintenance->medicine_name }}</div>
                        <div class="diagnosis-meta">
                            Schedule: {{ $maintenance->start_date?->format('M d, Y') ?? '—' }}
                            – {{ $maintenance->end_date?->format('M d, Y') ?? 'Ongoing' }}
                        </div>
                    </div>
                    @if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1)
                        <form method="POST"
                              action="{{ route('health.maintenance.delete', $maintenance->id) }}"
                              style="margin:0; flex-shrink:0;"
                              onsubmit="return confirm('Delete this maintenance medicine record? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="diagnosis-delete" style="cursor:pointer;color:#b91c1c;border-color:#fecaca;background:#fef2f2">Delete</button>
                        </form>
                    @endif
                </div>

                <div class="diagnosis-vitals maintenance-vitals">
                    <div><span>Quantity</span><strong>{{ $maintenance->quantity_dispensed ?? 1 }}</strong></div>
                    <div><span>Dosage</span><strong>{{ $maintenance->dosage ?: '—' }}</strong></div>
                    <div><span>Frequency</span><strong>{{ $maintenance->frequency ?: '—' }}</strong></div>
                    <div><span>Duration</span><strong>{{ $maintenance->end_date ? $maintenance->start_date?->diffInDays($maintenance->end_date) + 1 . ' days' : 'Ongoing' }}</strong></div>
                </div>

                @if($maintenance->notes)
                    <div class="diagnosis-notes"><strong>Notes:</strong> {{ $maintenance->notes }}</div>
                @endif

            </div>
        </article>

    @empty

        <div class="diagnosis-empty">
            <strong style="display:block;color:#334155">No Maintenance Medicines Recorded</strong>
            <span>Add a new record using the entry form below.</span>
        </div>

    @endforelse

    <form id="maintenanceForm" class="maintenance-form edit-gated-form" method="POST" action="{{ route('health.maintenance.store') }}" style="display:none;">
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
        <div class="diagnosis-actions record-form-actions">
            <button type="button" class="diagnosis-clear" id="clearMaintenanceBtn">Clear</button>
            <button type="submit" class="btn-primary">Add Maintenance</button>
        </div>
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

                @forelse ($availableMedicines as $medicine)

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

</section>

<!-- Diagnosis history first, followed by the clinical diagnosis form -->
<section class="diagnosis-page" aria-labelledby="diagnosisHistoryTitle">
<style>
.diagnosis-page{display:grid;gap:24px;margin:24px 0;color:#0f172a}.diagnosis-panel{background:#fff;border:1px solid #dbe4ef;border-radius:16px;padding:24px;box-shadow:0 1px 3px #0f172a0b}.diagnosis-panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-bottom:16px;margin-bottom:20px;border-bottom:1px solid #edf2f7}.diagnosis-panel h3{font-size:20px;font-weight:700;margin:0;color:#0f172a}.diagnosis-subtitle{font-size:12px;color:#64748b;margin:4px 0 0}.diagnosis-count{font-size:12px;border:1px solid #dbe4ef;border-radius:999px;background:#f1f5f9;padding:5px 12px;white-space:nowrap}.diagnosis-history-card{border:1px solid #dbe4ef;border-radius:12px;margin-bottom:14px;overflow:hidden;box-shadow:0 1px 2px #0f172a0a}.diagnosis-history-card:last-child{margin-bottom:0}.diagnosis-history-card:before{content:"";display:block;height:4px;background:#3b82f6}.diagnosis-history-inner{padding:18px 20px}.diagnosis-history-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}.diagnosis-history-title{font-weight:700;font-size:15px}.diagnosis-pill{display:inline-block;font-size:11px;color:#1d4ed8;background:#eff6ff;border:1px solid #bfdbfe;border-radius:999px;padding:3px 10px;margin-left:6px}.diagnosis-meta{font-size:12px;color:#8190a5;margin-top:5px}.diagnosis-vitals{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;background:#f8fafc;border:1px solid #edf2f7;border-radius:12px;padding:14px;margin-top:14px}.diagnosis-vitals span{display:block;font-size:10px;color:#8190a5;text-transform:uppercase;letter-spacing:.04em}.diagnosis-vitals strong{font-size:12px;color:#0f172a}.diagnosis-notes{display:block;box-sizing:border-box;width:100%;max-width:100%;min-width:0;font-size:12px;background:#f8fafc;border:1px solid #edf2f7;border-radius:9px;padding:11px;margin-top:12px;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;max-height:110px;overflow-y:auto;overflow-x:hidden;line-height:1.5}.diagnosis-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.diagnosis-field{display:flex;flex-direction:column;gap:7px;min-width:0}.diagnosis-field.full{grid-column:1/-1}.diagnosis-field label{font-size:12px;font-weight:600;color:#334155}.diagnosis-field input,.diagnosis-field select,.diagnosis-field textarea{box-sizing:border-box;width:100%;padding:11px 13px;border:1px solid #dbe4ef;border-radius:10px;background:#f8fafc;color:#0f172a;font:inherit;font-size:13px}.diagnosis-field textarea{resize:vertical}.diagnosis-vitals-panel{background:#f8fafc;border:1px solid #dbe4ef;border-radius:12px;padding:16px;margin-top:18px}.diagnosis-vitals-head{display:flex;justify-content:space-between;gap:10px;padding-bottom:12px;margin-bottom:14px;border-bottom:1px solid #e2e8f0;font-size:12px;font-weight:700;text-transform:uppercase}.diagnosis-bmi{display:flex;justify-content:space-between;align-items:center;gap:12px;background:#fff;border:1px solid #dbe4ef;border-radius:10px;padding:12px;margin-top:14px;font-size:12px}.diagnosis-bmi-status{display:inline-flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:8px;padding:5px 11px;font-size:12px;font-weight:600;white-space:nowrap}.diagnosis-bmi-empty{color:#64748b;background:#f1f5f9;border-color:#e2e8f0}.diagnosis-bmi-underweight{color:#1d4ed8;background:#eff6ff;border-color:#bfdbfe}.diagnosis-bmi-normal{color:#047857;background:#ecfdf5;border-color:#a7f3d0}.diagnosis-bmi-overweight{color:#b45309;background:#fffbeb;border-color:#fde68a}.diagnosis-bmi-obese{color:#b91c1c;background:#fef2f2;border-color:#fecaca}.diagnosis-actions{display:flex;align-items:center;justify-content:flex-start;gap:10px;margin-top:20px}.diagnosis-actions button{display:inline-flex;align-items:center;justify-content:center;min-height:40px;width:auto;border:1px solid transparent;border-radius:8px;padding:9px 16px;font-size:13px;line-height:1.4;font-weight:600;cursor:pointer}.diagnosis-clear{background:#f1f5f9;color:#334155;border-color:#e2e8f0}.diagnosis-save{background:#2563eb;color:#fff;flex:0 0 auto;box-shadow:0 2px 5px #2563eb20}.diagnosis-empty{border:2px dashed #dbe4ef;border-radius:12px;background:#f8fafc;text-align:center;padding:32px 16px;color:#64748b;font-size:13px}.diagnosis-delete{font-size:11px;color:#94a3b8;background:#f1f5f9;border:1px solid #dbe4ef;padding:5px 10px;border-radius:8px}.diagnosis-error{color:#b91c1c;font-size:12px}.immunization-history-card{margin-top:14px}.immunization-history-card:first-of-type{margin-top:0}.immunization-history>h4{margin:0 0 14px;font-size:15px;font-weight:700;color:#0f172a}.diagnosis-add-section{margin-top:24px;padding-top:22px;border-top:1px solid #e2e8f0}.diagnosis-add-section .diagnosis-panel-head{margin-bottom:18px}@media(max-width:640px){.diagnosis-panel{padding:16px}.diagnosis-vitals{grid-template-columns:repeat(2,minmax(0,1fr))}.diagnosis-form-grid{grid-template-columns:1fr}.diagnosis-field.full{grid-column:auto}.diagnosis-history-top{flex-direction:column}.diagnosis-actions{flex-direction:column}.diagnosis-actions button{width:100%}}
</style>
<div class="diagnosis-panel">
 <div class="diagnosis-panel-head"><div><h3 id="diagnosisHistoryTitle">Diagnosis History</h3><p class="diagnosis-subtitle">Chronological history of diagnoses, vitals, &amp; clinical assessments</p></div><span class="diagnosis-count">{{ $citizen->healthRecords->count() }} {{ $citizen->healthRecords->count() === 1 ? 'Record' : 'Records' }}</span></div>
 @if(session('success') && (\Illuminate\Support\Str::contains(strtolower(session('success')), ['diagnosis', 'health record', 'clinical'])))
 <div class="health-alert health-alert-success" role="status" aria-live="polite">{{ session('success') }}</div>
 @endif
 @if($errors->any() && old('diagnosis'))
 <div class="health-alert health-alert-error" role="alert"><ul class="health-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
 @endif
 @forelse($citizen->healthRecords->sortByDesc('record_date') as $record)
 <article class="diagnosis-history-card"><div class="diagnosis-history-inner"><div class="diagnosis-history-top"><div><div class="diagnosis-history-title">{{ $record->diagnosis }} <span class="diagnosis-pill">{{ $record->severity ?: 'Low / Mild' }}</span></div><div class="diagnosis-meta">Assessment Date: {{ $record->record_date ? \Carbon\Carbon::parse($record->record_date)->format('M d, Y') : $record->created_at?->format('M d, Y') }}</div></div>
@if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1)
<form method="POST" action="{{ route('health.record.delete', $record->id) }}" onsubmit="return confirm('Are you sure you want to delete this diagnosis record? This action cannot be undone.');" style="margin:0;flex-shrink:0">@csrf<button type="submit" class="diagnosis-delete" style="cursor:pointer;color:#b91c1c;border-color:#fecaca;background:#fef2f2">Delete</button></form>
@endif
</div>
 <div class="diagnosis-vitals"><div><span>BMI</span><strong>{{ $record->bmi !== null ? number_format((float)$record->bmi, 1).' kg/m²' : '—' }}</strong></div><div><span>Weight / Height</span><strong>{{ $record->weight !== null ? rtrim(rtrim(number_format((float)$record->weight,1,'.',''),'0'),'.').'kg' : '—' }} / {{ $record->height !== null ? rtrim(rtrim(number_format((float)$record->height,1,'.',''),'0'),'.').'cm' : '—' }}</strong></div><div><span>Temp</span><strong>{{ $record->temperature !== null ? number_format((float)$record->temperature,1).' °C' : '—' }}</strong></div><div><span>BP</span><strong>{{ $record->blood_pressure ?: '—' }}</strong></div></div>
 <div class="diagnosis-notes"><strong>Notes:</strong> {{ $record->comments ?: 'No notes recorded.' }}</div></div></article>
 @empty <div class="diagnosis-empty"><strong style="display:block;color:#334155">No Diagnosis History Recorded</strong><span>Add a new record using the entry form below.</span></div> @endforelse
@if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1)
<div class="diagnosis-add-section edit-gated-form" style="display:none;">
 <div class="diagnosis-panel-head"><div><h3>Add Clinical Diagnosis</h3><p class="diagnosis-subtitle">Record a new diagnosis and current patient vitals</p></div></div>
 <form id="diagnosisForm" method="POST" action="{{ route('health.record.store') }}">@csrf
 <input type="hidden" name="citizen_id" value="{{ $citizen->id }}">
 <div class="diagnosis-form-grid">
  <div class="diagnosis-field full"><label for="diagnosisInput">Primary Diagnosis <span style="color:#ef4444">*</span></label><textarea name="diagnosis" id="diagnosisInput" required rows="2" placeholder="e.g. Mild Seasonal Upper Respiratory Infection"></textarea></div>
  <div class="diagnosis-field"><label for="severityInput">Severity / Status</label><select name="severity" id="severityInput"><option>Low / Mild</option><option>Moderate</option><option>High / Severe</option><option>Critical</option><option>Resolved</option></select></div>
  <div class="diagnosis-field"><label for="recordDateInput">Assessment Date <span style="color:#ef4444">*</span></label><input type="date" name="record_date" id="recordDateInput" required value="{{ now()->format('Y-m-d') }}"></div>
 </div>
 <div class="diagnosis-vitals-panel"><div class="diagnosis-vitals-head"><span>Record Patient Vitals</span><span style="font-size:10px;color:#2563eb;text-transform:none">Auto-BMI Enabled</span></div>
 <div class="diagnosis-form-grid">
  <div class="diagnosis-field"><label for="weightInput">Weight (kg)</label><input type="number" name="weight" id="weightInput" step="0.1" min="1" max="500" placeholder="70"></div>
  <div class="diagnosis-field"><label for="heightInput">Height (cm)</label><input type="number" name="height" id="heightInput" step="0.1" min="1" max="300" placeholder="178"></div>
 </div><div class="diagnosis-bmi"><div><span style="display:block;color:#64748b;font-size:10px">Calculated BMI</span><strong id="bmiDisplay" style="font-size:17px">—</strong></div><span id="bmiStatus" class="diagnosis-bmi-status diagnosis-bmi-empty">Enter Height &amp; Weight</span></div>
 <div class="diagnosis-form-grid" style="margin-top:14px"><div class="diagnosis-field"><label for="temperatureInput">Temp (°C) <span style="font-weight:400;color:#64748b">Optional</span></label><input type="number" name="temperature" id="temperatureInput" step="0.1" min="25" max="45" placeholder="36.6"></div><div class="diagnosis-field"><label for="bloodPressureInput">BP (mmHg)</label><input type="text" name="blood_pressure" id="bloodPressureInput" maxlength="20" placeholder="120/80"></div></div>
 </div>
 <div class="diagnosis-field" style="margin-top:18px"><label for="commentsInput">Clinical Comments / Notes</label><textarea name="comments" id="commentsInput" rows="3" placeholder="Enter clinical observations, prescribed medications, or follow-up recommendations..."></textarea></div>
 <div class="diagnosis-actions"><button type="button" class="diagnosis-clear" id="clearDiagnosisBtn">Clear</button><button type="submit" class="diagnosis-save" id="saveDiagnosisBtn">Save Record</button></div>
</form></div>
@else <div class="diagnosis-add-section"><p class="diagnosis-subtitle">This citizen record is read-only. A diagnosis cannot be added from this account.</p></div> @endif
</div>
</section>

<style>
    .record-form-actions { display:flex; align-items:center; justify-content:flex-start; flex-wrap:wrap; gap:10px; margin-top:14px; }
    .record-form-actions button { display:inline-flex; align-items:center; justify-content:center; width:auto; min-height:40px; padding:9px 16px; border-radius:8px; font-size:13px; line-height:1.4; font-weight:600; cursor:pointer; }
    .record-form-actions .diagnosis-clear { border:1px solid #e2e8f0; background:#f1f5f9; color:#334155; }
    .record-form-actions .btn-primary { border:1px solid #0e42b1; background:#0e42b1; color:#fff; }
    @media(max-width:640px) { .record-form-actions { flex-direction:column; align-items:stretch; } .record-form-actions button { width:100%; } }

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

            @foreach ($families as $family)

                <button type="button" class="edit-assignment-option edit-family-option" data-family-id="{{ $family->id }}" data-family-name="{{ $family->family_name }}" data-purok="{{ $family->purok->name ?? '' }}" data-subgroup-id="{{ $family->subgroup->id ?? '' }}" data-subgroup-name="{{ $family->subgroup->name ?? '' }}">

                    <span><strong>F-{{ str_pad($family->id, 4, '0', STR_PAD_LEFT) }} · {{ $family->family_name }}</strong><br><small>{{$family->purok->name ?? 'No Purok' }} / {{ $family->subgroup->name ?? 'No subgroup' }}</small></span><span>Select</span>

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

            @foreach ($puroks as $purok)

                <strong style="color:#173b82;">{{ $purok->name }}</strong>

                @foreach ($purok->subgroups as $subgroup)

                    <button type="button" class="edit-assignment-option edit-manual-option" data-purok="{{ $purok->name }}" data-subgroup-id="{{ $subgroup->id }}" data-subgroup-name="{{ $subgroup->name }}"><span>{{ $purok->name }} / {{$subgroup->name }}</span><span>Select</span></button>

                @endforeach

            @endforeach

        </div>

    </div>

</div>

<div class="citizen-form-actions" style="display:flex; justify-content:flex-end; gap:10px; margin:24px 0;">
    <button type="button" class="btn-primary" id="saveBtn" style="display:none;" onclick="handlePreSaveCheck()">Save Changes</button>
    <button type="button" class="btn-secondary" id="cancelBtn" style="display:none;" onclick="disableEdit()">Cancel</button>
</div>

{{-- DECEASED CONFIRMATION MODAL --}}

<div id="deceasedConfirmModal" class="deceased-modal-overlay">

    <div class="deceased-modal-box">

        <h2 style="color: #991b1b; font-size: 20px; font-weight: bold; margin-bottom: 10px;">

            Mark Citizen as Deceased?

        </h2>

        <p style="color: #4b5563; margin-bottom: 20px; font-size: 14px;">

            Are you sure you want to update <strong>{{ $citizen->Citizen_FName }} {{$citizen->Citizen_LName }}</strong> as deceased? This will change their vital status record.

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

    // Purok Subgroup is assigned through the Family/Assignment picker only.
    // Keep it non-editable even after the main Edit button is pressed.
    const subgroupField = document.getElementById('Citizen_Subgroup');
    if (subgroupField) {
        subgroupField.readOnly = true;
        subgroupField.disabled = true;
        subgroupField.setAttribute('aria-readonly', 'true');
    }

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
    setEditGatedState(true);


    const vaccinationEntryForm = document.getElementById('vaccinationEntryForm');
    if (vaccinationEntryForm) vaccinationEntryForm.style.display = 'grid';
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
    setEditGatedState(false);

}

function setEditGatedState(enabled) {
    document.querySelectorAll('.edit-gated-form').forEach(function (form) {
        form.style.display = enabled ? 'block' : 'none';
    });

    document.querySelectorAll('#maintenanceForm input, #maintenanceForm select, #maintenanceForm textarea, #maintenanceForm button, #diagnosisForm input, #diagnosisForm textarea, #diagnosisForm button, [form="vaccinationForm"]').forEach(function (field) {
        if (field.type !== 'hidden') {
            field.disabled = !enabled;
        }
    });
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

setEditGatedState(false);

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

        medicinePickerModal.classList.remove("active");

        medicinePickerModal.setAttribute("aria-hidden", "true");

    }

    openMedicinePicker.addEventListener("click", function () {

        medicinePickerModal.classList.add("active");

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
    setEditGatedState(false);

    /* ============================================================

       DIAGNOSIS FORM ELEMENTS

       ============================================================ */

    const diagnosisInput =

        document.getElementById("diagnosisInput");

    const recordDateInput =

        document.getElementById("recordDateInput");

    const commentsInput =

        document.getElementById("commentsInput");

    const saveDiagnosisBtn =

        document.getElementById("saveDiagnosisBtn");



</script>

<script>
(function () {
    function updateDiagnosisBMI() {
        const weightInput = document.getElementById('weightInput');
        const heightInput = document.getElementById('heightInput');
        const bmiDisplay = document.getElementById('bmiDisplay');
        const bmiStatus = document.getElementById('bmiStatus');

        if (!weightInput || !heightInput || !bmiDisplay || !bmiStatus) return;

        const weight = Number.parseFloat(weightInput.value);
        const height = Number.parseFloat(heightInput.value);
        bmiStatus.className = 'diagnosis-bmi-status';

        if (Number.isFinite(weight) && Number.isFinite(height) && weight > 0 && height > 0) {
            const bmi = weight / Math.pow(height / 100, 2);
            bmiDisplay.textContent = bmi.toFixed(1);

            if (bmi < 18.5) {
                bmiStatus.textContent = 'Underweight';
                bmiStatus.classList.add('diagnosis-bmi-underweight');
            } else if (bmi < 25) {
                bmiStatus.textContent = 'Normal Weight';
                bmiStatus.classList.add('diagnosis-bmi-normal');
            } else if (bmi < 30) {
                bmiStatus.textContent = 'Overweight';
                bmiStatus.classList.add('diagnosis-bmi-overweight');
            } else if (bmi < 35) {
                bmiStatus.textContent = 'Obesity Class I';
                bmiStatus.classList.add('diagnosis-bmi-obese');
            } else if (bmi < 40) {
                bmiStatus.textContent = 'Obesity Class II';
                bmiStatus.classList.add('diagnosis-bmi-obese');
            } else {
                bmiStatus.textContent = 'Obesity Class III';
                bmiStatus.classList.add('diagnosis-bmi-obese');
            }
        } else {
            bmiDisplay.textContent = '—';
            bmiStatus.textContent = 'Enter Height & Weight';
            bmiStatus.classList.add('diagnosis-bmi-empty');
        }
    }

    // Delegate input events so BMI still updates even if other page scripts fail.
    document.addEventListener('input', function (event) {
        if (event.target && (event.target.id === 'weightInput' || event.target.id === 'heightInput')) {
            updateDiagnosisBMI();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        updateDiagnosisBMI();

        const diagnosisForm = document.getElementById('diagnosisForm');
        const clearButton = document.getElementById('clearDiagnosisBtn');

        const vaccinationForm = document.getElementById('vaccinationForm');
        const clearVaccinationButton = document.getElementById('clearVaccinationBtn');
        if (clearVaccinationButton && vaccinationForm) {
            clearVaccinationButton.addEventListener('click', function () {
                vaccinationForm.reset();
                const vaccineSelect = document.getElementById('vaccine_name');
                const doseSelect = document.getElementById('dose_number');
                const administeredDate = document.getElementById('date_administered');
                if (vaccineSelect) vaccineSelect.selectedIndex = 0;
                if (doseSelect) doseSelect.selectedIndex = 0;
                if (administeredDate) administeredDate.value = new Date().toISOString().slice(0, 10);
            });
        }

        const maintenanceForm = document.getElementById('maintenanceForm');
        const clearMaintenanceButton = document.getElementById('clearMaintenanceBtn');
        if (clearMaintenanceButton && maintenanceForm) {
            clearMaintenanceButton.addEventListener('click', function () {
                maintenanceForm.reset();
                const quantityInput = document.getElementById('maintenance_quantity');
                const startDateInput = document.getElementById('maintenance_start_date');
                if (quantityInput) quantityInput.value = '1';
                if (startDateInput) startDateInput.value = new Date().toISOString().slice(0, 10);
            });
        }

        if (clearButton && diagnosisForm) {
            clearButton.addEventListener('click', function () {
                diagnosisForm.reset();
                const dateInput = document.getElementById('recordDateInput');
                if (dateInput) dateInput.value = new Date().toISOString().slice(0, 10);
                updateDiagnosisBMI();
            });
        }

        if (diagnosisForm) {
            diagnosisForm.addEventListener('submit', function () {
                const saveButton = document.getElementById('saveDiagnosisBtn');
                if (saveButton) {
                    saveButton.disabled = true;
                    saveButton.textContent = 'Saving...';
                }
            });
        }
    });

    // In case this script is loaded after DOMContentLoaded.
    if (document.readyState !== 'loading') updateDiagnosisBMI();
})();
</script>

@endsection