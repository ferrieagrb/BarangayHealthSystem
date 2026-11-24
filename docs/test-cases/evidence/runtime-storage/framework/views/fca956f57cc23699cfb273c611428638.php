<?php $__env->startSection('CSSown'); ?>

<link rel="stylesheet" href="<?php echo e(asset('css/bhw/citizenfields.css')); ?>?v=<?php echo e(filemtime(public_path('css/bhw/citizenfields.css'))); ?>">

<link rel="stylesheet" href="<?php echo e(asset('css/bhw/shared-ui.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="page-top">

    <div class="page-title">

        <h1>Citizen Details</h1>

        <p>View and update citizen information</p>

    </div>

    <div class="right">
        <a href="<?php echo e(route('citizenlist')); ?>" class="btn-secondary" style="text-decoration: none !important;" data-ui-action="secondary">← Back</a>
        <?php if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1): ?>
            <button type="button" class="btn-primary" id="editBtn" onclick="enableEdit()" data-ui-action="secondary">Edit</button>
        <?php elseif(!$canEdit && !(bool) $citizen->Citizen_IsDeceased): ?>
            <span class="text-muted" style="font-weight: bold; font-size: 13px; background: #f1f5f9; padding: 8px 12px; border-radius: 6px; color: #475569;">View Only (Restricted)</span>
        <?php else: ?>
            <span class="text-danger" style="font-weight: bold; font-size: 13px; background: #fee2e2; padding: 8px 12px; border-radius: 6px; color: #991b1b;">Record Locked (Deceased)</span>
        <?php endif; ?>
    </div>
</div>

<div class="info-card">
    <div class="card">
        <form id="vaccinationForm" method="POST" action="<?php echo e(route('citizen.vaccination.store', $citizen->id)); ?>" data-citizen-birth-date="<?php echo e($citizen->Citizen_BirthDate ? \Carbon\Carbon::parse($citizen->Citizen_BirthDate)->format('Y-m-d') : ''); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" id="vaccineSelectionToken" name="vaccine_selection_token" value="">
        </form>

        <?php echo $__env->make('bhw.partials.citizen-photo-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <form method="POST"

              action="<?php echo e(route('citizen.update', $citizen->id)); ?>"

              id="citizenForm">

            <?php echo csrf_field(); ?>

            <?php echo method_field('PUT'); ?>

            <h2>

                <?php echo e($citizen->Citizen_FName); ?> <?php echo e($citizen->Citizen_LName); ?>


            </h2>

            

            <h3 class="citizen-section-title">Basic Information</h3>

            <?php echo $__env->make('bhw.partials.citizen-photo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <label for="Citizen_FName">First Name</label>

            <input type="text"

                   id="Citizen_FName"

                   name="Citizen_FName"

                   value="<?php echo e(old('Citizen_FName', $citizen->Citizen_FName)); ?>"

                   disabled>

            <label for="Citizen_MName">Middle Name</label>

            <input type="text"

                    id="Citizen_MName"

                    name="Citizen_MName"

                    value="<?php echo e(old('Citizen_MName', $citizen->Citizen_MName ?? '')); ?>"

                    placeholder="Enter middle name"

                    disabled>

            <label for="Citizen_LName">Last Name</label>

            <input type="text"

                   id="Citizen_LName"

                   name="Citizen_LName"

                   value="<?php echo e(old('Citizen_LName', $citizen->Citizen_LName)); ?>"

                   disabled>

            <label for="Citizen_Age">Age</label>

            <input type="number"

                   id="Citizen_Age"

                   name="Citizen_Age"

                   min="0"

                   value="<?php echo e(old('Citizen_Age', $citizen->Citizen_Age)); ?>"

                   disabled>

            <label for="Citizen_BirthDate">Birthdate</label>

            <input type="date"

                   id="Citizen_BirthDate"

                   name="Citizen_BirthDate"

                   value="<?php echo e(old('Citizen_BirthDate', $citizen->Citizen_BirthDate ? \Carbon\Carbon::parse($citizen->Citizen_BirthDate)->format('Y-m-d') : '')); ?>"

                   disabled>

            <label for="Citizen_ContactNo">Contact Number</label>

            <input type="text"

                   id="Citizen_ContactNo"

                   name="Citizen_ContactNo"

                   value="<?php echo e(old('Citizen_ContactNo', $citizen->Citizen_ContactNo)); ?>"

                   disabled>

            <label for="Citizen_Purok">Purok</label>

            <input type="text"
                   id="Citizen_Purok"
                   name="Citizen_Purok"
                   value="<?php echo e(old('Citizen_Purok', $citizen->Citizen_Purok)); ?>"
                   disabled
                   readonly>

                 <label for="Citizen_Subgroup">Purok Subgroup</label>
                 <input type="text"
                     id="Citizen_Subgroup"
                     value="<?php echo e($citizen->subgroup->name ?? $citizen->family?->subgroup?->name ?? 'Not assigned'); ?>"
                     disabled
                     readonly>

            <hr class="citizen-section-divider">

            

            <h3 class="citizen-section-title">Family Information</h3>

            <div class="family-info-row">
                <div class="family-info-field">
                    <label for="displayFamilyId">Family ID</label>
                    <input type="text" id="displayFamilyId" value="<?php echo e($citizen->family->family_code ?? 'None'); ?>" disabled>
                </div>
                <div class="family-info-field">
                    <label for="displayFamilyName">Family Name</label>
                    <input type="text" id="displayFamilyName" value="<?php echo e($citizen->family->family_name ?? 'No Family Assigned'); ?>" disabled>
                </div>
                <div class="family-info-field">
                    <label for="displaySubgroupName">Subgroup / Purok Section</label>
                    <input type="text" id="displaySubgroupName" value="<?php echo e($citizen->family->subgroup->name ?? 'None'); ?>" disabled>
                </div>
            </div>

            <div id="familyEditContainer" style="display: none;">

                <input type="hidden" name="family_id" id="familySelect" value="<?php echo e(old('family_id', $citizen->family_id)); ?>">

                <input type="hidden" name="subgroup_id" id="editSubgroupId" value="<?php echo e($citizen->subgroup_id ?? $citizen->family?->subgroup_id); ?>">

                <label>Assignment</label>

                <div style="display:flex; gap:10px; flex-wrap:wrap;">

                    <button type="button" class="btn btn-secondary" id="openEditFamilyPicker" data-ui-action="secondary">Select Family</button>

                </div>

            </div>

            <hr class="citizen-section-divider">

            

            <h3 class="citizen-section-title">Physical Information</h3>

                 <label for="Citizen_Height">Height (cm)</label>
                 <input type="number" id="Citizen_Height" name="Citizen_Height" min="0" step="0.01" value="<?php echo e(old('Citizen_Height', $citizen->Citizen_Height)); ?>" placeholder="e.g. 165.50" disabled>

                 <label for="Citizen_Weight">Weight (kg)</label>
                 <input type="number" id="Citizen_Weight" name="Citizen_Weight" min="0" step="0.01" value="<?php echo e(old('Citizen_Weight', $citizen->Citizen_Weight)); ?>" placeholder="e.g. 55.50" disabled>

            <label for="Citizen_Sex">Sex</label>

            <select id="Citizen_Sex" name="Citizen_Sex" disabled>

                <option value="">Select Sex</option>

                <option value="Male"

                    <?php echo e(old('Citizen_Sex', $citizen->Citizen_Sex) === 'Male' ? 'selected' : ''); ?>>

                    Male

                </option>

                <option value="Female"

                    <?php echo e(old('Citizen_Sex', $citizen->Citizen_Sex) === 'Female' ? 'selected' : ''); ?>>

                    Female

                </option>

            </select>

            <hr class="citizen-section-divider">

            

            <h3 class="citizen-section-title">Housing Status</h3>

            <label for="Citizen_HousingStatus">Residence Type</label>

            <select id="Citizen_HousingStatus"

                    name="Citizen_HousingStatus"

                    disabled>

                <option value="">Select Housing Status</option>

                <option value="Permanent Resident"

                    <?php echo e(old('Citizen_HousingStatus', $citizen->Citizen_HousingStatus) === 'Permanent Resident' ? 'selected' : ''); ?>>

                    Permanent Resident

                </option>

                <option value="Renter"

                    <?php echo e(old('Citizen_HousingStatus', $citizen->Citizen_HousingStatus) === 'Renter' ? 'selected' : ''); ?>>

                    Renter

                </option>

            </select>

            <hr class="citizen-section-divider">

            <section id="familyPlanningFields"
                     <?php if($citizen->Citizen_Sex !== 'Female'): ?> hidden <?php endif; ?>>

            

            <h3 class="citizen-section-title">Health &amp; Family Planning</h3>

            <div class="citizen-status-field">

                <label for="Citizen_Pregnant">Pregnant</label>

                <select id="Citizen_Pregnant" name="Citizen_Pregnant" disabled>

                    <option value="0"

                        <?php echo e((string) old('Citizen_Pregnant', (int) $citizen->Citizen_Pregnant) === '0' ? 'selected' : ''); ?>>

                        No

                    </option>

                    <option value="1"

                        <?php echo e((string) old('Citizen_Pregnant', (int) $citizen->Citizen_Pregnant) === '1' ? 'selected' : ''); ?>>

                        Yes

                    </option>

                </select>

            </div>

            <div class="citizen-status-field">

                <label for="Citizen_InPills">Using Birth Control Pills</label>

                <select id="Citizen_InPills" name="Citizen_InPills" disabled>

                    <option value="0"

                        <?php echo e((string) old('Citizen_InPills', (int) $citizen->Citizen_InPills) === '0' ? 'selected' : ''); ?>>

                        No

                    </option>

                    <option value="1"

                        <?php echo e((string) old('Citizen_InPills', (int) $citizen->Citizen_InPills) === '1' ? 'selected' : ''); ?>>

                        Yes

                    </option>

                </select>

            </div>

            </section>

            <hr class="citizen-section-divider">

            

            <h3 class="citizen-section-title">Vital Status</h3>

            <label>Resident Status</label>

            <input type="text"

                   id="displayDeceasedStatus"

                   value="<?php echo e($citizen->Citizen_IsDeceased ? 'Deceased' : 'Living'); ?>"

                   disabled>

            

            <input type="hidden"

                   id="Citizen_IsDeceased"

                   name="Citizen_IsDeceased"

                   value="<?php echo e(old('Citizen_IsDeceased', $citizen->Citizen_IsDeceased ? 1 : 0)); ?>">

            <hr class="citizen-section-divider">

            <hr class="citizen-section-divider">

            

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
                    <span class="diagnosis-count"><?php echo e($citizen->vaccinations->count()); ?> <?php echo e($citizen->vaccinations->count() === 1 ? 'Record' : 'Records'); ?></span>
                </div>

                <?php if(session('success') && (\Illuminate\Support\Str::contains(strtolower(session('success')), ['vaccin', 'immuniz']))): ?>
                    <div class="health-alert health-alert-success" role="status" aria-live="polite"><?php echo e(session('success')); ?></div>
                <?php endif; ?>
                <?php if($errors->any() && old('vaccine_name')): ?>
                    <div class="health-alert health-alert-error" role="alert">
                        <ul class="health-error-list"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
                    </div>
                <?php endif; ?>

                <?php
                    $vaccineDoseOptions = collect($vaccinationEligibilityOptions)
                        ->flatMap(fn ($vaccine) => collect($vaccine['doses'])->pluck('name'))
                        ->unique()
                        ->values();
                ?>

                <?php if($canEdit && (int)$citizen->Citizen_IsDeceased !== 1): ?>
                    <div class="immunization-form-grid edit-gated-form" id="vaccinationEntryForm" style="display:none;">
                        <div class="immunization-field">
                            <label for="vaccine_name">Vaccine Name</label>
                            <select id="vaccine_name" name="vaccine_name" form="vaccinationForm" data-medical-autocomplete="vaccine" required>
                                <option value="" disabled selected>-- Select Vaccine --</option>
                                <?php $__currentLoopData = $vaccinationEligibilityOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vaccineCode => $vaccine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($vaccine['label']); ?>"
                                            data-code="<?php echo e($vaccineCode); ?>"
                                            data-doses='<?php echo json_encode($vaccine["doses"], 15, 512) ?>'
                                            data-next-dose="<?php echo e($vaccine['next_dose']); ?>"
                                            data-guideline="<?php echo e($vaccine['guideline']); ?>"
                                            data-reason="<?php echo e($vaccine['reason']); ?>"
                                            data-eligibility-status="<?php echo e($vaccine['status']); ?>"
                                            title="<?php echo e($vaccine['eligible'] ? $vaccine['guideline'] : $vaccine['reason']); ?>"
                                            <?php if(!$vaccine['eligible']): echo 'disabled'; endif; ?>
                                            <?php if(old('vaccine_name') === $vaccine['label'] && $vaccine['eligible']): echo 'selected'; endif; ?>>
                                        <?php echo e($vaccine['label']); ?><?php echo e($vaccine['eligible'] ? '' : ' — Unavailable'); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="immunization-field">
                            <label for="dose_number">Dose / Booster</label>
                            <select id="dose_number" name="dose_number" form="vaccinationForm" required>
                                <option value="" disabled selected>-- Select Dose/Booster --</option>
                                <?php $__currentLoopData = $vaccineDoseOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doseName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($doseName); ?>"><?php echo e($doseName); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <option value="Booster">Booster</option>
                                <option value="Dose not specified">Dose not specified</option>
                            </select>
                        </div>

                        <div class="immunization-field">
                            <label for="date_administered">Date Administered</label>
                            <input type="date" id="date_administered" name="date_administered" value="<?php echo e(old('date_administered', now()->format('Y-m-d'))); ?>" max="<?php echo e(now()->format('Y-m-d')); ?>" form="vaccinationForm" required>
                        </div>

                        <label class="immunization-field historical-vaccination-option" style="grid-column: 1 / -1;">
                            <span>
                                <input type="checkbox" id="historical_recording" name="historical_recording" value="1" form="vaccinationForm">
                                Record a vaccine that was already administered (clinical age eligibility not confirmed)
                            </span>
                        </label>

                        <input type="hidden" id="administered_by" name="administered_by" value="<?php echo e(auth()->user()->name ?? 'BHW'); ?>" form="vaccinationForm">

                        <div class="immunization-action-wrapper diagnosis-actions record-form-actions">
                            <button type="button" class="diagnosis-clear" id="clearVaccinationBtn" data-ui-action="reset">Clear</button>
                            <button type="submit" class="btn-primary" form="vaccinationForm" data-ui-action="primary">Add Vaccination</button>
                        </div>
                    </div>
                    <p id="vaccinationEligibilityMessage" class="immunization-preview-note" role="status" aria-live="polite">
                        Available status uses configured vaccine-specific age rules on the administration date. Other recognized vaccines require historical recording.
                    </p>
                <?php elseif((int) $citizen->Citizen_IsDeceased === 1): ?>
                    <p class="immunization-preview-note">This record is deceased and locked for updates.</p>
                <?php else: ?>
                    <p class="immunization-preview-note">Read-only: this citizen is not assigned to your subgroup.</p>
                <?php endif; ?>

                <?php if($vaccinationSchedulingEnabled): ?>
                    <div class="immunization-history" style="margin-bottom: 18px;">
                        <h4>Scheduled Immunizations</h4>
                        <?php $__empty_1 = true; $__currentLoopData = $citizen->vaccinationSchedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="immunization-history-item" style="border-left: 3px solid #f59e0b;">
                                <strong><?php echo e($schedule->vaccine_name); ?></strong>
                                <span><?php echo e($schedule->dose_number ?: 'Scheduled dose'); ?> · Scheduled for <?php echo e($schedule->scheduled_date->format('M d, Y')); ?></span>
                                <small>Not yet administered<?php echo e($schedule->notes ? ' · ' . $schedule->notes : ''); ?></small>
                                <?php if($canEdit && (int)$citizen->Citizen_IsDeceased !== 1): ?>
                                    <form method="POST" action="<?php echo e(route('citizen.vaccination.schedule.administer', [$citizen->id, $schedule->id])); ?>" style="display:flex; align-items:end; gap:8px; flex-wrap:wrap; margin-top:10px;">
                                        <?php echo csrf_field(); ?>
                                        <label style="font-size:12px; color:#475569;">Date administered
                                            <input type="date" name="date_administered" value="<?php echo e(now()->format('Y-m-d')); ?>" max="<?php echo e(now()->format('Y-m-d')); ?>" required style="display:block; margin-top:4px;">
                                        </label>
                                        <button type="submit" class="btn-primary" data-ui-action="primary">Mark Administered</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p>No pending immunization schedules.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="immunization-history">
                    <h4>Immunization History</h4>
                    <?php $__empty_1 = true; $__currentLoopData = $citizen->vaccinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vaccination): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="diagnosis-history-card immunization-history-card">
                            <div class="diagnosis-history-inner">
                                <div class="diagnosis-history-top">
                                    <div>
                                        <div class="diagnosis-history-title">
                                            <?php echo e($vaccination->vaccine_name); ?>

                                            <span class="diagnosis-pill"><?php echo e($vaccination->dose_number ?: 'Dose recorded'); ?></span>
                                            <?php if($vaccination->cvx_code): ?>
                                                <span class="diagnosis-pill">CVX <?php echo e($vaccination->cvx_code); ?></span>
                                            <?php endif; ?>
                                            <?php if($vaccination->historical_recording): ?>
                                                <span class="diagnosis-pill">Historical record</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if($vaccination->vaccine_formulation): ?>
                                            <small><?php echo e($vaccination->vaccine_formulation); ?></small>
                                        <?php endif; ?>
                                        <div class="diagnosis-meta">
                                            Administered Date: <?php echo e($vaccination->date_administered ? \Carbon\Carbon::parse($vaccination->date_administered)->format('M d, Y') : '—'); ?>

                                            · Administered by <?php echo e($vaccination->administered_by ?: '—'); ?>

                                        </div>
                                        <?php if($vaccination->eligibility_review_required): ?>
                                            <p class="health-alert health-alert-error" role="status">
                                                Clinical age eligibility was not established: <?php echo e($vaccination->eligibility_review_reason); ?>

                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1): ?>
                                        <form method="POST"
                                              action="<?php echo e(route('citizen.vaccination.delete', [$citizen->id, $vaccination->id])); ?>"
                                              style="margin:0; flex-shrink:0;"
                                              data-confirm-message="Archive this vaccination record? You can restore it from the archive.">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="diagnosis-delete" style="cursor:pointer;color:#b91c1c;border-color:#fecaca;background:#fef2f2" data-ui-action="danger">Archive</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="diagnosis-empty">No immunization records yet.</div>
                    <?php endif; ?>
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
        <span class="diagnosis-count"><?php echo e($citizen->maintenanceRecords->count()); ?> <?php echo e($citizen->maintenanceRecords->count() === 1 ? 'Record' : 'Records'); ?></span>
    </div>

    <?php if(session('success') === 'Maintenance medicine added successfully.'): ?>

        <p class="health-alert health-alert-success">

            <?php echo e(session('success')); ?>


        </p>

    <?php endif; ?>

    <?php if($errors->any()): ?>

        <div class="health-alert health-alert-error">

            <ul class="health-error-list">

                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <li><?php echo e($error); ?></li>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </ul>

        </div>

    <?php endif; ?>

    <?php $__empty_1 = true; $__currentLoopData = $citizen->maintenanceRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maintenance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

        <article class="diagnosis-history-card maintenance-history-card">
            <div class="diagnosis-history-inner">
                <div class="diagnosis-history-top">
                    <div>
                        <div class="diagnosis-history-title"><?php echo e($maintenance->medicine_name); ?></div>
                        <div class="diagnosis-meta">
                            Schedule: <?php echo e($maintenance->start_date?->format('M d, Y') ?? '—'); ?>

                            – <?php echo e($maintenance->end_date?->format('M d, Y') ?? 'Ongoing'); ?>

                        </div>
                    </div>
                    <?php if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1): ?>
                        <form method="POST"
                              action="<?php echo e(route('health.maintenance.delete', $maintenance->id)); ?>"
                              style="margin:0; flex-shrink:0;"
                              data-confirm-message="Archive this maintenance medicine record? You can restore it from the archive.">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="diagnosis-delete" style="cursor:pointer;color:#b91c1c;border-color:#fecaca;background:#fef2f2" data-ui-action="danger">Archive</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="diagnosis-vitals maintenance-vitals">
                    <div><span>Quantity</span><strong><?php echo e($maintenance->quantity_dispensed ?? 1); ?></strong></div>
                    <div><span>Dosage</span><strong><?php echo e($maintenance->dosage ?: '—'); ?></strong></div>
                    <div><span>Frequency</span><strong><?php echo e($maintenance->frequency ?: '—'); ?></strong></div>
                    <div><span>Duration</span><strong><?php echo e($maintenance->end_date ? $maintenance->start_date?->diffInDays($maintenance->end_date) + 1 . ' days' : 'Ongoing'); ?></strong></div>
                </div>

                <?php if($maintenance->notes): ?>
                    <div class="diagnosis-notes"><strong>Notes:</strong> <?php echo e($maintenance->notes); ?></div>
                <?php endif; ?>

            </div>
        </article>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

        <div class="diagnosis-empty">
            <strong style="display:block;color:#334155">No Maintenance Medicines Recorded</strong>
            <span>Add a new record using the entry form below.</span>
        </div>

    <?php endif; ?>

    <form id="maintenanceForm" class="maintenance-form edit-gated-form" method="POST" action="<?php echo e(route('health.maintenance.store')); ?>" style="display:none;">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="citizen_id" value="<?php echo e($citizen->id); ?>">

        <div class="maintenance-form-grid">
            <div class="maintenance-form-field">
                <label for="maintenance_medicine_name">Medicine Name</label>
                <input type="text" name="medicine_name" id="maintenance_medicine_name" class="maintenance-field" data-medical-autocomplete="medicine" value="<?php echo e(old('medicine_name')); ?>" placeholder="Enter medicine name" maxlength="255" required>
            </div>
            <div class="maintenance-form-field">
                <label for="maintenance_quantity">Quantity</label>
                <input type="number" name="quantity_dispensed" id="maintenance_quantity" class="maintenance-field" value="<?php echo e(old('quantity_dispensed', 1)); ?>" min="1" max="100000" step="1" placeholder="e.g. 30" required>
            </div>
            
            
            <div class="maintenance-form-field-row">
                <div class="maintenance-form-field">
                    <label for="maintenance_dosage">Dosage (Number only)</label>
                    <input type="number" name="dosage" id="maintenance_dosage" class="maintenance-field" value="<?php echo e(old('dosage')); ?>" placeholder="e.g. 500" min="0.01" max="10000" step="any" required>
                </div>
                <div class="maintenance-form-field">
                    <label for="maintenance_frequency_times">Frequency (Times)</label>
                    <select name="frequency_times" id="maintenance_frequency_times" class="maintenance-field" required>
                        <option value="" disabled <?php if(!old('frequency_times')): echo 'selected'; endif; ?>>-- Select --</option>
                        <option value="Once" <?php echo e(old('frequency_times') === 'Once' ? 'selected' : ''); ?>>Once</option>
                        <option value="Twice" <?php echo e(old('frequency_times') === 'Twice' ? 'selected' : ''); ?>>Twice</option>
                        <option value="Three times" <?php echo e(old('frequency_times') === 'Three times' ? 'selected' : ''); ?>>Three times</option>
                    </select>
                </div>
                <div class="maintenance-form-field">
                    <label for="maintenance_frequency_period">Period</label>
                    <select name="frequency_period" id="maintenance_frequency_period" class="maintenance-field" required>
                        <option value="" disabled <?php if(!old('frequency_period')): echo 'selected'; endif; ?>>-- Select --</option>
                        <option value="per day" <?php echo e(old('frequency_period') === 'per day' ? 'selected' : ''); ?>>per day</option>
                        <option value="per week" <?php echo e(old('frequency_period') === 'per week' ? 'selected' : ''); ?>>per week</option>
                        <option value="every other day" <?php echo e(old('frequency_period') === 'every other day' ? 'selected' : ''); ?>>every other day</option>
                    </select>
                </div>
            </div>

            <div class="maintenance-form-field">
                <label for="maintenance_start_date">Start Date</label>
                <input type="date" name="start_date" id="maintenance_start_date" class="maintenance-field" value="<?php echo e(old('start_date', now()->format('Y-m-d'))); ?>" required>
            </div>
            <div class="maintenance-form-field">
                <label for="maintenance_end_date">End Date (Optional)</label>
                <input type="date" name="end_date" id="maintenance_end_date" class="maintenance-field" value="<?php echo e(old('end_date')); ?>">
            </div>
            <div class="maintenance-form-field maintenance-form-field-full">
                <label for="maintenance_notes">Notes</label>
                <textarea name="notes" id="maintenance_notes" class="maintenance-field" maxlength="2000" placeholder="Additional instructions or notes..."><?php echo e(old('notes')); ?></textarea>
            </div>
        </div>
        <div class="diagnosis-actions record-form-actions">
            <button type="button" class="diagnosis-clear" id="clearMaintenanceBtn" data-ui-action="reset">Clear</button>
            <button type="submit" class="btn-primary" data-ui-action="primary">Add Maintenance</button>
        </div>
    </form>

    <div id="medicinePickerModal" class="medicine-picker-modal" role="dialog" aria-modal="true" aria-labelledby="medicinePickerTitle" aria-hidden="true">

        <div class="medicine-picker-dialog">

            <div class="medicine-picker-header">

                <div>

                    <h3 id="medicinePickerTitle">Select Existing Medicine</h3>

                    <p>Showing 10 medicines per page.</p>

                </div>

                <button type="button" id="closeMedicinePicker" class="medicine-picker-close" aria-label="Close medicine picker" data-ui-action="secondary">&times;</button>

            </div>

            <div>

                <?php $__empty_1 = true; $__currentLoopData = $availableMedicines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medicine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                    <button type="button" class="medicine-option" data-ui-action="secondary" data-medicine="<?php echo e($medicine->name); ?>">

                        <?php echo e($medicine->name); ?>


                    </button>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                    <p>No medicines are currently available in supplies.</p>

                <?php endif; ?>

            </div>

            <?php if($availableMedicines->hasPages()): ?>

                <div class="medicine-picker-pagination">

                    <?php echo e($availableMedicines->onEachSide(1)->links()); ?>


                </div>

            <?php endif; ?>

        </div>

    </div>

</section>

<!-- Diagnosis history first, followed by the clinical diagnosis form -->
<section class="diagnosis-page" aria-labelledby="diagnosisHistoryTitle">
<style>
.diagnosis-page{display:grid;gap:24px;margin:24px 0;color:#0f172a}.diagnosis-panel{background:#fff;border:1px solid #dbe4ef;border-radius:16px;padding:24px;box-shadow:0 1px 3px #0f172a0b}.diagnosis-panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-bottom:16px;margin-bottom:20px;border-bottom:1px solid #edf2f7}.diagnosis-panel h3{font-size:20px;font-weight:700;margin:0;color:#0f172a}.diagnosis-subtitle{font-size:12px;color:#64748b;margin:4px 0 0}.diagnosis-count{font-size:12px;border:1px solid #dbe4ef;border-radius:999px;background:#f1f5f9;padding:5px 12px;white-space:nowrap}.diagnosis-history-card{border:1px solid #dbe4ef;border-radius:12px;margin-bottom:14px;overflow:hidden;box-shadow:0 1px 2px #0f172a0a}.diagnosis-history-card:last-child{margin-bottom:0}.diagnosis-history-card:before{content:"";display:block;height:4px;background:#3b82f6}.diagnosis-history-inner{padding:18px 20px}.diagnosis-history-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}.diagnosis-history-title{font-weight:700;font-size:15px}.diagnosis-pill{display:inline-block;font-size:11px;color:#1d4ed8;background:#eff6ff;border:1px solid #bfdbfe;border-radius:999px;padding:3px 10px;margin-left:6px}.diagnosis-meta{font-size:12px;color:#8190a5;margin-top:5px}.diagnosis-vitals{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;background:#f8fafc;border:1px solid #edf2f7;border-radius:12px;padding:14px;margin-top:14px}.diagnosis-vitals span{display:block;font-size:10px;color:#8190a5;text-transform:uppercase;letter-spacing:.04em}.diagnosis-vitals strong{font-size:12px;color:#0f172a}.diagnosis-notes{display:block;box-sizing:border-box;width:100%;max-width:100%;min-width:0;font-size:12px;background:#f8fafc;border:1px solid #edf2f7;border-radius:9px;padding:11px;margin-top:12px;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;max-height:110px;overflow-y:auto;overflow-x:hidden;line-height:1.5}.diagnosis-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.diagnosis-field{display:flex;flex-direction:column;gap:7px;min-width:0}.diagnosis-field.full{grid-column:1/-1}.diagnosis-field label{font-size:12px;font-weight:600;color:#334155}.diagnosis-field input,.diagnosis-field select,.diagnosis-field textarea{box-sizing:border-box;width:100%;padding:11px 13px;border:1px solid #dbe4ef;border-radius:10px;background:#f8fafc;color:#0f172a;font:inherit;font-size:13px}.diagnosis-field textarea{resize:vertical}.diagnosis-vitals-panel{background:#f8fafc;border:1px solid #dbe4ef;border-radius:12px;padding:16px;margin-top:18px}.diagnosis-vitals-head{display:flex;justify-content:space-between;gap:10px;padding-bottom:12px;margin-bottom:14px;border-bottom:1px solid #e2e8f0;font-size:12px;font-weight:700;text-transform:uppercase}.diagnosis-bmi{display:flex;justify-content:space-between;align-items:center;gap:12px;background:#fff;border:1px solid #dbe4ef;border-radius:10px;padding:12px;margin-top:14px;font-size:12px}.diagnosis-bmi-status{display:inline-flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:8px;padding:5px 11px;font-size:12px;font-weight:600;white-space:nowrap}.diagnosis-bmi-empty{color:#64748b;background:#f1f5f9;border-color:#e2e8f0}.diagnosis-bmi-underweight{color:#1d4ed8;background:#eff6ff;border-color:#bfdbfe}.diagnosis-bmi-normal{color:#047857;background:#ecfdf5;border-color:#a7f3d0}.diagnosis-bmi-overweight{color:#b45309;background:#fffbeb;border-color:#fde68a}.diagnosis-bmi-obese{color:#b91c1c;background:#fef2f2;border-color:#fecaca}.diagnosis-actions{display:flex;align-items:center;justify-content:flex-start;gap:10px;margin-top:20px}.diagnosis-actions button{display:inline-flex;align-items:center;justify-content:center;min-height:40px;width:auto;border:1px solid transparent;border-radius:8px;padding:9px 16px;font-size:13px;line-height:1.4;font-weight:600;cursor:pointer}.diagnosis-clear{background:#f1f5f9;color:#334155;border-color:#e2e8f0}.diagnosis-save{background:#2563eb;color:#fff;flex:0 0 auto;box-shadow:0 2px 5px #2563eb20}.diagnosis-empty{border:2px dashed #dbe4ef;border-radius:12px;background:#f8fafc;text-align:center;padding:32px 16px;color:#64748b;font-size:13px}.diagnosis-delete{font-size:11px;color:#94a3b8;background:#f1f5f9;border:1px solid #dbe4ef;padding:5px 10px;border-radius:8px}.diagnosis-error{color:#b91c1c;font-size:12px}.immunization-history-card{margin-top:14px}.immunization-history-card:first-of-type{margin-top:0}.immunization-history>h4{margin:0 0 14px;font-size:15px;font-weight:700;color:#0f172a}.diagnosis-add-section{margin-top:24px;padding-top:22px;border-top:1px solid #e2e8f0}.diagnosis-add-section .diagnosis-panel-head{margin-bottom:18px}@media(max-width:640px){.diagnosis-panel{padding:16px}.diagnosis-vitals{grid-template-columns:repeat(2,minmax(0,1fr))}.diagnosis-form-grid{grid-template-columns:1fr}.diagnosis-field.full{grid-column:auto}.diagnosis-history-top{flex-direction:column}.diagnosis-actions{flex-direction:column}.diagnosis-actions button{width:100%}}
</style>
<div class="diagnosis-panel">
 <div class="diagnosis-panel-head"><div><h3 id="diagnosisHistoryTitle">Diagnosis History</h3><p class="diagnosis-subtitle">Chronological history of diagnoses, vitals, &amp; clinical assessments</p></div><span class="diagnosis-count"><?php echo e($citizen->healthRecords->count()); ?> <?php echo e($citizen->healthRecords->count() === 1 ? 'Record' : 'Records'); ?></span></div>
 <?php if(session('success') && (\Illuminate\Support\Str::contains(strtolower(session('success')), ['diagnosis', 'health record', 'clinical']))): ?>
 <div class="health-alert health-alert-success" role="status" aria-live="polite"><?php echo e(session('success')); ?></div>
 <?php endif; ?>
 <?php if($errors->any() && old('diagnosis')): ?>
 <div class="health-alert health-alert-error" role="alert"><ul class="health-error-list"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
 <?php endif; ?>
 <?php $__empty_1 = true; $__currentLoopData = $citizen->healthRecords->sortByDesc('record_date'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
 <article class="diagnosis-history-card"><div class="diagnosis-history-inner"><div class="diagnosis-history-top"><div><div class="diagnosis-history-title"><?php echo e($record->diagnosis); ?> <span class="diagnosis-pill"><?php echo e($record->severity ?: 'Low / Mild'); ?></span></div><div class="diagnosis-meta">Assessment Date: <?php echo e($record->record_date ? \Carbon\Carbon::parse($record->record_date)->format('M d, Y') : $record->created_at?->format('M d, Y')); ?></div></div>
<?php if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1): ?>
<form method="POST" action="<?php echo e(route('health.record.delete', $record->id)); ?>" data-confirm-message="Are you sure you want to archive this diagnosis record? You can restore it from the archive." style="margin:0;flex-shrink:0"><?php echo csrf_field(); ?><button type="submit" class="diagnosis-delete" style="cursor:pointer;color:#b91c1c;border-color:#fecaca;background:#fef2f2" data-ui-action="danger">Archive</button></form>
<?php endif; ?>
</div>
 <div class="diagnosis-vitals"><div><span>BMI</span><strong><?php echo e($record->bmi !== null ? number_format((float)$record->bmi, 1).' kg/m²' : '—'); ?></strong></div><div><span>Weight / Height</span><strong><?php echo e($record->weight !== null ? rtrim(rtrim(number_format((float)$record->weight,1,'.',''),'0'),'.').'kg' : '—'); ?> / <?php echo e($record->height !== null ? rtrim(rtrim(number_format((float)$record->height,1,'.',''),'0'),'.').'cm' : '—'); ?></strong></div><div><span>Temp</span><strong><?php echo e($record->temperature !== null ? number_format((float)$record->temperature,1).' °C' : '—'); ?></strong></div><div><span>BP</span><strong><?php echo e($record->blood_pressure ?: '—'); ?></strong></div></div>
 <div class="diagnosis-notes"><strong>Notes:</strong> <?php echo e($record->comments ?: 'No notes recorded.'); ?></div></div></article>
 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <div class="diagnosis-empty"><strong style="display:block;color:#334155">No Diagnosis History Recorded</strong><span>Add a new record using the entry form below.</span></div> <?php endif; ?>
<?php if($canEdit && (int) $citizen->Citizen_IsDeceased !== 1): ?>
<div class="diagnosis-add-section edit-gated-form" style="display:none;">
 <div class="diagnosis-panel-head"><div><h3>Add Clinical Diagnosis</h3><p class="diagnosis-subtitle">Record a new diagnosis and current patient vitals</p></div></div>
 <form id="diagnosisForm" method="POST" action="<?php echo e(route('health.record.store')); ?>"><?php echo csrf_field(); ?>
 <input type="hidden" name="citizen_id" value="<?php echo e($citizen->id); ?>">
 <div class="diagnosis-form-grid">
  <div class="diagnosis-field full"><label for="diagnosisInput">Primary Diagnosis <span style="color:#ef4444">*</span></label><textarea name="diagnosis" id="diagnosisInput" data-medical-autocomplete="diagnosis" required rows="2" placeholder="e.g. Mild Seasonal Upper Respiratory Infection"></textarea></div>
  <div class="diagnosis-field"><label for="severityInput">Severity / Status</label><select name="severity" id="severityInput"><option>Low / Mild</option><option>Moderate</option><option>High / Severe</option><option>Critical</option><option>Resolved</option></select></div>
  <div class="diagnosis-field"><label for="recordDateInput">Assessment Date <span style="color:#ef4444">*</span></label><input type="date" name="record_date" id="recordDateInput" required value="<?php echo e(now()->format('Y-m-d')); ?>"></div>
 </div>
 <div class="diagnosis-vitals-panel"><div class="diagnosis-vitals-head"><span>Record Patient Vitals</span><span style="font-size:10px;color:#2563eb;text-transform:none">Auto-BMI Enabled</span></div>
 <div class="diagnosis-form-grid">
  <div class="diagnosis-field"><label for="weightInput">Weight (kg)</label><input type="number" name="weight" id="weightInput" step="0.1" min="1" max="500" placeholder="70"></div>
  <div class="diagnosis-field"><label for="heightInput">Height (cm)</label><input type="number" name="height" id="heightInput" step="0.1" min="1" max="300" placeholder="178"></div>
 </div><div class="diagnosis-bmi"><div><span style="display:block;color:#64748b;font-size:10px">Calculated BMI</span><strong id="bmiDisplay" style="font-size:17px">—</strong></div><span id="bmiStatus" class="diagnosis-bmi-status diagnosis-bmi-empty">Enter Height &amp; Weight</span></div>
 <div class="diagnosis-form-grid" style="margin-top:14px"><div class="diagnosis-field"><label for="temperatureInput">Temp (°C) <span style="font-weight:400;color:#64748b">Optional</span></label><input type="number" name="temperature" id="temperatureInput" step="0.1" min="25" max="45" placeholder="36.6"></div><div class="diagnosis-field"><label for="bloodPressureInput">BP (mmHg)</label><input type="text" name="blood_pressure" id="bloodPressureInput" maxlength="20" placeholder="120/80"></div></div>
 </div>
 <div class="diagnosis-field" style="margin-top:18px"><label for="commentsInput">Clinical Comments / Notes</label><textarea name="comments" id="commentsInput" rows="3" placeholder="Enter clinical observations, prescribed medications, or follow-up recommendations..."></textarea></div>
 <div class="diagnosis-actions"><button type="button" class="diagnosis-clear" id="clearDiagnosisBtn" data-ui-action="reset">Clear</button><button type="submit" class="diagnosis-save" id="saveDiagnosisBtn" data-ui-action="primary">Add Diagnosis</button></div>
</form></div>
<?php else: ?> <div class="diagnosis-add-section"><p class="diagnosis-subtitle">This citizen record is read-only. A diagnosis cannot be added from this account.</p></div> <?php endif; ?>
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

            <button type="button" class="family-picker-close" data-close-edit-modal data-ui-action="secondary">&times;</button>

        </div>

        <input type="search" id="editFamilySearch" class="form-control" placeholder="Search family..." style="margin:16px 0;">

        <div class="edit-assignment-list">

            <?php $__currentLoopData = $families; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $family): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                <button type="button" class="edit-assignment-option edit-family-option" data-family-id="<?php echo e($family->id); ?>" data-family-code="<?php echo e($family->family_code); ?>" data-family-name="<?php echo e($family->family_name); ?>" data-purok="<?php echo e($family->purok->name ?? ''); ?>" data-subgroup-id="<?php echo e($family->subgroup->id ?? ''); ?>" data-subgroup-name="<?php echo e($family->subgroup->name ?? ''); ?>" data-ui-action="secondary">

                    <span><strong><?php echo e($family->family_code); ?> · <?php echo e($family->family_name); ?></strong><br><small><?php echo e($family->purok->name ?? 'No Purok'); ?> / <?php echo e($family->subgroup->name ?? 'No subgroup'); ?></small></span><span>Select</span>

                </button>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>

    </div>

</div>

<div class="edit-assignment-modal" id="editManualPicker" aria-hidden="true">
    <div class="edit-assignment-box">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2 style="margin:0;">Select Purok / Subgroup</h2>
            <button type="button" class="family-picker-close" data-close-edit-modal data-ui-action="secondary">&times;</button>
        </div>
        <div class="edit-assignment-list" style="margin-top:16px;">
            <?php $__currentLoopData = $puroks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <strong style="color:#173b82;"><?php echo e($purok->name); ?></strong>
                <?php $__currentLoopData = $purok->subgroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subgroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" class="edit-assignment-option edit-manual-option" data-purok="<?php echo e($purok->name); ?>" data-subgroup-id="<?php echo e($subgroup->id); ?>" data-subgroup-name="<?php echo e($subgroup->name); ?>" data-ui-action="secondary"><span><?php echo e($purok->name); ?> / <?php echo e($subgroup->name); ?></span><span>Select</span></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>

<div class="citizen-form-actions" style="display:flex; justify-content:flex-end; gap:10px; margin:24px 0;">
    <button type="button" class="btn-primary" id="saveBtn" style="display:none;" onclick="handlePreSaveCheck()" data-ui-action="primary">Save Changes</button>
    <button type="button" class="btn-secondary" id="cancelBtn" style="display:none;" onclick="disableEdit()" data-ui-action="secondary">Cancel</button>
</div>



<div id="deceasedConfirmModal" class="deceased-modal-overlay">

    <div class="deceased-modal-box">

        <h2 style="color: #991b1b; font-size: 20px; font-weight: bold; margin-bottom: 10px;">

            Mark Citizen as Deceased?

        </h2>

        <p style="color: #4b5563; margin-bottom: 20px; font-size: 14px;">

            Are you sure you want to update <strong><?php echo e($citizen->Citizen_FName); ?> <?php echo e($citizen->Citizen_LName); ?></strong> as deceased? This will change their vital status record.

        </p>

        <div class="deceased-modal-buttons">

            <button type="button" class="btn-secondary" onclick="closeDeceasedModal()" data-ui-action="secondary">Cancel</button>

            <button type="button" class="btn-danger" id="confirmDeceasedSaveBtn" style="background: #dc2626; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;" data-ui-action="danger">Yes, Mark Deceased</button>

        </div>

    </div>

</div>

<script>

let originalData = {};

let originalFamilyDatabaseId = <?php echo json_encode((string) ($citizen->family_id ?? ''), 15, 512) ?>;

let originalFamilyCode = <?php echo json_encode($citizen->family->family_code ?? '', 15, 512) ?>;

let originalFamilyName = <?php echo json_encode($citizen->family->family_name ?? 'No Family Assigned', 15, 512) ?>;

let originalFamilySubgroup = <?php echo json_encode($citizen->family->subgroup->name ?? 'None', 15, 512) ?>;

function enableEdit() {

    const form = document.getElementById('citizenForm');
    const photoField = document.getElementById('photo');
    if (photoField) photoField.disabled = false;

    const inputs = form.querySelectorAll('input:not([type="hidden"]), select');

    originalData = {};

    inputs.forEach(field => {

        if (field.name) {

            originalData[field.name] = field.value;

            field.disabled = false;

        }

    });

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

    document.getElementById('familyEditContainer').style.display = 'block';

    document.getElementById('openEditManualPicker').style.display = 'inline-block';

    document.getElementById('openEditManualPicker').disabled = Boolean(originalFamilyDatabaseId);

    document.getElementById('saveBtn').style.display = 'inline-block';

    document.getElementById('cancelBtn').style.display = 'inline-block';

    document.getElementById('editBtn').style.display = 'none';
    setEditGatedState(true);
    if (window.updateFamilyPlanningEligibility) window.updateFamilyPlanningEligibility();
    if (window.updateVaccinationEligibility) window.updateVaccinationEligibility();


    const vaccinationEntryForm = document.getElementById('vaccinationEntryForm');
    if (vaccinationEntryForm) vaccinationEntryForm.style.display = 'grid';
}

function disableEdit() {

    const form = document.getElementById('citizenForm');
    const photoInput = document.getElementById('photo');
    if (photoInput) {
        photoInput.value = '';
        photoInput.dispatchEvent(new Event('change'));
        photoInput.disabled = true;
    }

    const fields = form.querySelectorAll('input:not([type="hidden"]), select');

    fields.forEach(field => {

        if (field.name && Object.prototype.hasOwnProperty.call(originalData, field.name)) {

            field.value = originalData[field.name];

        }

        field.disabled = true;

    });

    document.getElementById('markDeceasedCheckbox').disabled = true;

    document.getElementById('displayDeceasedStatus').value = document.getElementById('Citizen_IsDeceased').value === '1' ? 'Deceased' : 'Living';

    document.getElementById('familySelect').value = originalFamilyDatabaseId;

    document.getElementById('displayFamilyId').value = originalFamilyCode || 'None';

    document.getElementById('displayFamilyName').value = originalFamilyName;

    document.getElementById('displaySubgroupName').value = originalFamilySubgroup;

    document.getElementById('displayFamilyName').style.display = 'block';

    document.getElementById('familyEditContainer').style.display = 'none';

    document.getElementById('openEditManualPicker').style.display = 'none';

    document.getElementById('saveBtn').style.display = 'none';

    document.getElementById('cancelBtn').style.display = 'none';

    document.getElementById('editBtn').style.display = 'inline-block';
    setEditGatedState(false);
    if (window.updateFamilyPlanningEligibility) window.updateFamilyPlanningEligibility();
    if (window.updateVaccinationEligibility) window.updateVaccinationEligibility();

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

    const wasAlreadyDeceased = <?php echo json_encode((string) $citizen->Citizen_IsDeceased, 15, 512) ?> === '1';

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

    document.getElementById('displayFamilyId').value = option.dataset.familyCode;

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

<script>
window.updateFamilyPlanningEligibility = function () {
    const sex = document.getElementById('Citizen_Sex');
    const birthDate = document.getElementById('Citizen_BirthDate');
    const familyPlanningFields = document.getElementById('familyPlanningFields');
    const pregnant = document.getElementById('Citizen_Pregnant');
    const pills = document.getElementById('Citizen_InPills');

    if (!sex || !birthDate || !familyPlanningFields || !pregnant) return;

    const isFemale = sex.value === 'Female';
    const isEditing = !sex.disabled;
    const dateOfBirth = birthDate.value ? new Date(`${birthDate.value}T00:00:00`) : null;
    let age = isEditing ? null : <?php echo json_encode($citizen->ageForEligibility(), 15, 512) ?>;

    if (dateOfBirth && !Number.isNaN(dateOfBirth.getTime())) {
        const today = new Date();
        age = today.getFullYear() - dateOfBirth.getFullYear();
        if (today.getMonth() < dateOfBirth.getMonth()
            || (today.getMonth() === dateOfBirth.getMonth() && today.getDate() < dateOfBirth.getDate())) {
            age--;
        }
    }

    familyPlanningFields.hidden = !isFemale;
    const pregnancyEligible = isFemale && age !== null && age >= 15;
    pregnant.disabled = !isEditing || !pregnancyEligible;

    if (!isFemale) {
        pregnant.value = '0';
    }

    if (pills) {
        const pillsEligible = isFemale && age !== null && age >= 16;
        pills.disabled = !isEditing || !pillsEligible;
        if (!pillsEligible) pills.value = '0';
    }
};

function initializeFamilyPlanningEligibility() {
    const sex = document.getElementById('Citizen_Sex');
    const birthDate = document.getElementById('Citizen_BirthDate');

    if (sex) sex.addEventListener('change', window.updateFamilyPlanningEligibility);
    if (birthDate) birthDate.addEventListener('change', window.updateFamilyPlanningEligibility);
    window.updateFamilyPlanningEligibility();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFamilyPlanningEligibility);
} else {
    initializeFamilyPlanningEligibility();
}
</script>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>

<script src="<?php echo e(asset('js/vaccination-eligibility.js')); ?>?v=<?php echo e(filemtime(public_path('js/vaccination-eligibility.js'))); ?>" defer></script>

<script src="<?php echo e(asset('js/bhw/citizendetails-inline-3.js')); ?>?v=<?php echo e(filemtime(public_path('js/bhw/citizendetails-inline-3.js'))); ?>"></script>

<script src="<?php echo e(asset('js/bhw/citizendetails-inline-4.js')); ?>?v=<?php echo e(filemtime(public_path('js/bhw/citizendetails-inline-4.js'))); ?>"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/bhw/citizendetails.blade.php ENDPATH**/ ?>