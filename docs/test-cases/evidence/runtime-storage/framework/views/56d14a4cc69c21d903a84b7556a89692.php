<?php $__env->startSection('CSSown'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/bhw/shared-ui.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/bhw/ecard.css')); ?>?v=<?php echo e(filemtime(public_path('css/bhw/ecard.css'))); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/bhw/health-card.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="bhw-ecard-page citizen-ecard-page mx-auto max-w-7xl px-4 py-8">
    <div class="ecard-page-header print:hidden">
        <h1>Citizen ID & Electronic Health Card</h1>
        <a href="<?php echo e(route('citizen.dashboard')); ?>" class="ecard-button ecard-button-secondary" data-ui-action="secondary">Back to Dashboard</a>
    </div>
    <div class="ecard-content-stack">
<div class="ecard-id-card-scroll" role="region" aria-label="Citizen Health Card" tabindex="0" data-ecard-fit>
            <?php echo $__env->make('partials.health-card', ['citizen' => $citizen, 'qrImage' => $qrImage], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div class="ecard-section-actions print:hidden" aria-label="Health card download and print options">
            <button type="button" class="ecard-print-button" data-ecard-download="id" data-format="png" data-ui-action="secondary">Download PNG</button>
            <button type="button" class="ecard-print-button" data-ecard-download="id" data-format="pdf" data-ui-action="secondary">Download PDF</button>
            <button type="button" class="ecard-print-button" onclick="printCitizenSection('id')" data-ui-action="secondary">Print Health Card Only</button>
        </div>
        <section class="print-health-record min-w-0 rounded-xl border-2 border-gray-300 bg-white p-5 shadow-lg sm:p-6">

            <div class="mb-5 border-b-2 border-gray-800 pb-3">
                <h2 class="text-2xl font-black uppercase text-gray-900">
                    Amuyong Health Record
                </h2>
                <p class="mt-1 text-xs text-gray-600">
                    Medical information about the vaccines and medicines received.
                </p>
            </div>

            
            <div class="mb-6 grid grid-cols-1 gap-4 border-b border-gray-300 pb-4 text-sm sm:grid-cols-2">

                <div>
                    <span class="block text-xs text-gray-500">Last Name, First Name</span>
                    <span class="font-bold">
                        <?php echo e($citizen->Citizen_LName); ?>,
                        <?php echo e($citizen->Citizen_FName); ?>

                        <?php echo e($citizen->Citizen_MName ?: ''); ?>

                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Purok / Location</span>
                    <span class="font-semibold">
                        <?php echo e($citizen->Citizen_Purok ?: 'N/A'); ?>

                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Date of Birth</span>
                    <span class="font-semibold">
                        <?php if($citizen->Citizen_BirthDate): ?>
                            <?php echo e(\Carbon\Carbon::parse($citizen->Citizen_BirthDate)->format('F d, Y')); ?>

                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Age</span>
                    <span class="font-semibold">
                        <?php if($citizen->Citizen_BirthDate): ?>
                            <?php echo e(\Carbon\Carbon::parse($citizen->Citizen_BirthDate)->age); ?> years old
                        <?php elseif(!is_null($citizen->Citizen_Age)): ?>
                            <?php echo e($citizen->Citizen_Age); ?> years old
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Height</span>
                    <span class="font-semibold"><?php echo e($citizen->Citizen_Height !== null && $citizen->Citizen_Height !== '' ? $citizen->Citizen_Height . ' cm' : 'N/A'); ?></span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Weight</span>
                    <span class="font-semibold"><?php echo e($citizen->Citizen_Weight !== null && $citizen->Citizen_Weight !== '' ? $citizen->Citizen_Weight . ' kg' : 'N/A'); ?></span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Sex</span>
                    <span class="font-semibold"><?php echo e($citizen->Citizen_Sex ?: 'N/A'); ?></span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Patient Contact Number</span>
                    <span class="font-semibold">
                        <?php echo e($citizen->Citizen_ContactNo ?: 'N/A'); ?>

                    </span>
                </div>

            </div>

            <?php ($citizenAge = $citizen->ageForEligibility()); ?>
            <div class="mb-6 grid grid-cols-1 gap-4 border-b border-gray-300 pb-4 text-sm sm:grid-cols-2">
                <div><span class="block text-xs text-gray-500">Housing Status</span><span class="font-semibold"><?php echo e($citizen->Citizen_HousingStatus ?: 'N/A'); ?></span></div>
                <?php if($citizen->Citizen_Sex === 'Female'): ?>
                    <div><span class="block text-xs text-gray-500">Pregnancy Status</span><span class="font-semibold"><?php echo e((bool) $citizen->Citizen_Pregnant ? 'Yes' : 'No'); ?></span></div>
                    <?php if($citizenAge !== null && $citizenAge >= 16): ?>
                        <div><span class="block text-xs text-gray-500">Taking Pills</span><span class="font-semibold"><?php echo e((bool) $citizen->Citizen_InPills ? 'Yes' : 'No'); ?></span></div>
                    <?php endif; ?>
                <?php endif; ?>
                <div><span class="block text-xs text-gray-500">Deceased Status</span><span class="font-semibold"><?php echo e(!empty($citizen->Citizen_IsDeceased) ? 'Deceased' : 'Living'); ?></span></div>
                <div><span class="block text-xs text-gray-500">Family ID</span><span class="font-semibold"><?php echo e($citizen->family?->family_code ?? 'None'); ?></span></div>
                <div><span class="block text-xs text-gray-500">Family Name</span><span class="font-semibold"><?php echo e($citizen->family?->family_name ?: 'No family assigned'); ?></span></div>
                <div><span class="block text-xs text-gray-500">Subgroup</span><span class="font-semibold"><?php echo e($citizen->subgroup?->name ?: ($citizen->family?->subgroup?->name ?: 'Not assigned')); ?></span></div>
            </div>
            
            <div class="mb-6">
                <h3 class="mb-2 border border-gray-300 bg-gray-100 p-2 text-sm font-bold uppercase text-gray-700">
                    Diagnosis & Health Records
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-400 text-xs text-left">
                        <thead>
                            <tr class="bg-gray-200 text-gray-800">
                                <th class="border border-gray-400 p-2">Date Recorded</th>
                                <th class="border border-gray-400 p-2">Diagnosis</th>
                                <th class="border border-gray-400 p-2">BMI</th>
                                <th class="border border-gray-400 p-2">Weight / Height</th>
                                <th class="border border-gray-400 p-2">Temperature</th>
                                <th class="border border-gray-400 p-2">BP</th>
                                <th class="border border-gray-400 p-2">Comments / Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $citizen->healthRecords->sortByDesc('record_date'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="border border-gray-400 p-2">
                                        <?php echo e($record->record_date ? \Carbon\Carbon::parse($record->record_date)->format('M d, Y') : 'N/A'); ?>

                                    </td>
                                    <td class="border border-gray-400 p-2 font-semibold"><?php echo e($record->diagnosis ?: 'N/A'); ?></td>
                                    <td class="border border-gray-400 p-2"><?php echo e($record->bmi !== null ? number_format((float)$record->bmi, 1) . ' kg/m²' : '—'); ?></td>
                                    <td class="border border-gray-400 p-2"><?php echo e($record->weight !== null ? $record->weight . ' kg' : '—'); ?> / <?php echo e($record->height !== null ? $record->height . ' cm' : '—'); ?></td>
                                    <td class="border border-gray-400 p-2"><?php echo e($record->temperature !== null ? number_format((float)$record->temperature, 1) . ' °C' : '—'); ?></td>
                                    <td class="border border-gray-400 p-2"><?php echo e($record->blood_pressure ?: '—'); ?></td>
                                    <td class="border border-gray-400 p-2"><?php echo e($record->comments ?: '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="border border-gray-400 p-3 text-center italic text-gray-500">
                                        No diagnosis or health records found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="mb-6">
                <h3 class="mb-2 border border-gray-300 bg-gray-100 p-2 text-sm font-bold uppercase text-gray-700">
                    Immunization History
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-400 text-xs text-left">
                        <thead>
                            <tr class="bg-gray-200 text-gray-800">
                                <th class="border border-gray-400 p-2">Vaccine</th>
                                <th class="border border-gray-400 p-2">Product Name / Dose Number</th>
                                <th class="border border-gray-400 p-2">Date Administered</th>
                                <th class="border border-gray-400 p-2">Healthcare Professional / Clinic Site</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $citizen->vaccinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="border border-gray-400 p-2 font-semibold">
                                        <?php echo e($vax->vaccine_name); ?>

                                        <?php if($vax->historical_recording): ?>
                                            <div class="mt-1 text-xs font-normal text-slate-600">Historical record</div>
                                        <?php endif; ?>
                                        <?php if($vax->eligibility_review_required): ?>
                                            <div class="mt-1 text-xs font-normal text-amber-700">Clinical age eligibility not established: <?php echo e($vax->eligibility_review_reason); ?></div>
                                        <?php endif; ?>
                                        <?php if($vax->catch_up_reviewed): ?>
                                            <div class="mt-1 text-xs font-normal text-blue-700">Catch-up schedule confirmed by health center.</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        <?php echo e($vax->vaccine_name); ?>

                                        — <?php echo e($vax->dose_number); ?>

                                        <?php if($vax->cvx_code): ?>
                                            <div class="text-xs">CDC CVX <?php echo e($vax->cvx_code); ?></div>
                                        <?php endif; ?>
                                        <?php if($vax->vaccine_formulation): ?>
                                            <div class="text-xs"><?php echo e($vax->vaccine_formulation); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        <?php echo e($vax->date_administered); ?>

                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        <?php echo e($vax->administered_by); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="border border-gray-400 p-3 text-center italic text-gray-500">
                                        No vaccination records found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="mb-6">
                <h3 class="mb-2 border border-gray-300 bg-gray-100 p-2 text-sm font-bold uppercase text-gray-700">
                    Maintenance Medicines
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-400 text-xs text-left">
                        <thead>
                            <tr class="bg-gray-200 text-gray-800">
                                <th class="border border-gray-400 p-2">Medicine</th>
                                <th class="border border-gray-400 p-2">Quantity</th>
                                <th class="border border-gray-400 p-2">Dosage</th>
                                <th class="border border-gray-400 p-2">Frequency</th>
                                <th class="border border-gray-400 p-2">Start Date</th>
                                <th class="border border-gray-400 p-2">End Date / Status</th>
                                <th class="border border-gray-400 p-2">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = ($citizen->maintenanceRecords ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maintenance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="border border-gray-400 p-2 font-semibold"><?php echo e($maintenance->medicine_name ?: 'N/A'); ?></td>
                                    <td class="border border-gray-400 p-2"><?php echo e($maintenance->quantity_dispensed ?? 'N/A'); ?></td>
                                    <td class="border border-gray-400 p-2"><?php echo e($maintenance->dosage ?: 'N/A'); ?></td>
                                    <td class="border border-gray-400 p-2"><?php echo e($maintenance->frequency ?: 'N/A'); ?></td>
                                    <td class="border border-gray-400 p-2">
                                        <?php echo e($maintenance->start_date ? \Carbon\Carbon::parse($maintenance->start_date)->format('M d, Y') : 'N/A'); ?>

                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        <?php echo e($maintenance->end_date ? \Carbon\Carbon::parse($maintenance->end_date)->format('M d, Y') : 'Ongoing'); ?>

                                    </td>
                                    <td class="border border-gray-400 p-2"><?php echo e($maintenance->notes ?: '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="border border-gray-400 p-3 text-center italic text-gray-500">
                                        No maintenance medicines recorded.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
</section>
        <div class="ecard-section-actions print:hidden" aria-label="Health information download and print options">
            <button type="button" class="ecard-print-button ecard-print-record" data-ecard-download="record" data-format="png" data-ui-action="secondary">Download PNG</button>
            <button type="button" class="ecard-print-button ecard-print-record" data-ecard-download="record" data-format="pdf" data-ui-action="secondary">Download PDF</button>
            <button type="button" class="ecard-print-button ecard-print-record" onclick="printCitizenSection('record')" data-ui-action="secondary">Print Health Information Only</button>
        </div>
    </div>
</div>

<script src="<?php echo e(asset('js/citizen/ecard-inline-1.js')); ?>?v=<?php echo e(filemtime(public_path('js/citizen/ecard-inline-1.js'))); ?>"></script>
<script src="<?php echo e(asset('js/ecard-download.js')); ?>?v=<?php echo e(filemtime(public_path('js/ecard-download.js'))); ?>"></script>
<script src="<?php echo e(asset('js/ecard-preview.js')); ?>?v=<?php echo e(filemtime(public_path('js/ecard-preview.js'))); ?>"></script>
<script src="<?php echo e(asset('js/ecard-fit.js')); ?>?v=<?php echo e(filemtime(public_path('js/ecard-fit.js'))); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.citizen', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/citizen/ecard.blade.php ENDPATH**/ ?>