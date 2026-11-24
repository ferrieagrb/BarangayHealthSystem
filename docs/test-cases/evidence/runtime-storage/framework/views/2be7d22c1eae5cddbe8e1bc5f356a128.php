<?php $__env->startSection('CSSown'); ?>
<style>
    .reports-page { padding: 24px; color: #172033; font-family: Poppins, sans-serif; }
    .reports-grid { display: grid; grid-template-columns: 1fr; gap: 18px; align-items: start; }
    .report-panel { padding: 22px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 3px 12px rgba(15,23,42,.06); }
    .report-panel h2 { margin: 0 0 6px; color: #173b82; font-size: 20px; }
    .report-panel > p { margin: 0 0 18px; color: #64748b; font-size: 13px; }
    .report-config-row { display: grid; grid-template-columns: minmax(220px, 1.2fr) repeat(3, minmax(150px, 1fr)); gap: 14px; align-items: end; }
    .report-fields { display: contents; }
    .report-field { display: grid; gap: 6px; min-width: 0; }
    .report-field[hidden] { display: none; }
    .report-field label { color: #334155; font-size: 12px; font-weight: 700; }
    .report-field input, .report-field select { width: 100%; min-height: 42px; padding: 9px 11px; border: 1px solid #cbd5e1; border-radius: 7px; box-sizing: border-box; background: #fff; color: #172033; font: inherit; }
    .report-field #reportType { cursor: pointer; }
    .report-field #reportType:focus-visible { border-color: #2563eb; outline: 3px solid rgba(37, 99, 235, .18); outline-offset: 1px; }
    .report-field #reportType optgroup { background: #f1f5f9; color: #334155; font-weight: 700; }
    .report-field #reportType option { padding: 8px 12px; background: #fff; color: #172033; font-weight: 400; }
    .report-field #reportType option:checked { background: #dbeafe; color: #173b82; font-weight: 600; }
    .report-type-field.is-open { position: relative; z-index: 20; }
    .report-type-picker { position: relative; min-width: 0; }
    .report-type-native { position: absolute !important; width: 1px !important; height: 1px !important; margin: -1px !important; padding: 0 !important; overflow: hidden !important; clip: rect(0, 0, 0, 0) !important; white-space: nowrap !important; border: 0 !important; }
    .report-type-trigger { display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; min-height: 42px; padding: 9px 11px; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; color: #172033; font: inherit; text-align: left; cursor: pointer; }
    .report-type-trigger:focus-visible { border-color: #2563eb; outline: 3px solid rgba(37, 99, 235, .18); outline-offset: 1px; }
    .report-type-current { min-width: 0; overflow-wrap: anywhere; }
    .report-type-chevron { flex: 0 0 8px; width: 8px; height: 8px; margin: -4px 3px 0 0; border-right: 1.5px solid #475569; border-bottom: 1.5px solid #475569; transform: rotate(45deg); transition: transform .16s ease; }
    .report-type-trigger[aria-expanded="true"] .report-type-chevron { margin-top: 4px; transform: rotate(225deg); }
    .report-type-options { position: absolute; top: calc(100% + 5px); right: 0; left: 0; z-index: 1100; max-height: min(320px, calc(100dvh - 24px)); overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .16); }
    .report-type-options.opens-up { top: auto; bottom: calc(100% + 5px); }
    .report-type-options[hidden] { display: none; }
    .report-type-group + .report-type-group { border-top: 1px solid #cbd5e1; }
    .report-type-group-label { padding: 9px 12px; border-bottom: 1px solid #e2e8f0; background: #f1f5f9; color: #334155; font-size: 11px; font-weight: 700; line-height: 1.35; text-transform: uppercase; }
    .report-type-option { min-height: 40px; padding: 9px 12px 9px 24px; color: #172033; font-size: 13px; line-height: 1.4; overflow-wrap: anywhere; cursor: pointer; }
    .report-type-option + .report-type-option { border-top: 1px solid #f1f5f9; }
    .report-type-option:hover, .report-type-option.is-active { background: #f1f5f9; }
    .report-type-option[aria-selected="true"] { background: #dbeafe; color: #173b82; font-weight: 600; }
    .report-type-option:focus { outline: none; }
    @media (prefers-reduced-motion: reduce) { .report-type-chevron { transition: none; } }
    .report-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-top: 18px; padding-top: 16px; border-top: 1px solid #e8edf4; }
    .report-actions button { border: 0; cursor: pointer; }
    .report-actions .btn-primary, .report-actions .btn-secondary { min-height: 40px; padding: 10px 16px; border-radius: 7px; font: inherit; font-weight: 600; }
    .report-actions .btn-primary { background: #2563eb; color: #fff; }
    .report-actions .btn-secondary { background: #f1f5f9; color: #334155; }
    .report-actions .btn-secondary:hover { background: #e2e8f0; }
    .report-graphics-toggle { display: inline-flex; align-items: center; gap: 8px; margin-right: auto; color: #475569; font-size: 13px; font-weight: 500; }
    .report-graphics-toggle[hidden] { display: none; }
    .report-preview { min-width: 0; }
    .report-meta { display: flex; justify-content: space-between; gap: 12px; align-items: start; margin-bottom: 18px; }
    .report-meta h1 { margin: 0; color: #173b82; font-size: 25px; }
    .report-meta p { margin: 5px 0 0; color: #64748b; font-size: 13px; }
    .report-section { margin-top: 20px; page-break-inside: avoid; }
    .report-section h3 { margin: 0 0 10px; padding-bottom: 7px; border-bottom: 2px solid #1d4ed8; color: #173b82; font-size: 16px; }
    .report-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .report-table th, .report-table td { padding: 8px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
    .report-table th { color: #64748b; background: #f8fafc; font-size: 10px; text-transform: uppercase; }
    .report-empty { padding: 18px; color: #64748b; text-align: center; }
    .report-validation-errors { margin-bottom: 14px; padding: 12px 14px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #991b1b; }
    .report-export-feedback { flex-basis: 100%; margin: 0; color: #991b1b; font-size: 13px; }
    .report-export-feedback[hidden] { display: none; }
    .report-graphic { display: grid; gap: 8px; margin: 12px 0 4px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
    .report-table-scroll { width: 100%; overflow-x: auto; }
    .report-details-table { min-width: 1500px; }
    .graphic-row { display: grid; grid-template-columns: minmax(90px, .35fr) 1fr auto; gap: 8px; align-items: center; color: #475569; font-size: 11px; }
    .graphic-track { height: 9px; overflow: hidden; border-radius: 99px; background: #dbeafe; }
    .graphic-fill { height: 100%; min-width: 2px; border-radius: inherit; background: #2563eb; }
    .graphic-fill.green { background: #16a34a; }
    .graphic-fill.orange { background: #f97316; }
    .graphic-fill.red { background: #dc2626; }
    .print-only { display: none; }
    <?php if(request('export_format') === 'pdf'): ?>
        @page { size: A4 <?php echo e($pdfOrientation ?? 'portrait'); ?>; margin: 12mm; }
        body { margin: 0; color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 9pt; }
        .reports-page { padding: 0; font-family: DejaVu Sans, sans-serif; }
        .reports-grid { display: block; }
        .report-builder, .no-print { display: none !important; }
        .report-panel { padding: 0; border: 0; box-shadow: none; }
        .report-meta { margin-bottom: 8px; }
        .report-meta h1 { font-size: 13pt; line-height: 1.2; }
        .report-meta p { margin-top: 3px; font-size: 9pt; }
        .report-section { margin-top: 10px; page-break-inside: auto; }
        .report-section h3 { margin-bottom: 5px; padding-bottom: 4px; font-size: 10pt; }
        .report-table { width: 100%; font-size: 9pt; line-height: 1.25; page-break-inside: auto; }
        .report-table th, .report-table td { padding: 4px; overflow-wrap: break-word; }
        .report-table th { font-size: 9pt; }
        .report-table-scroll { width: 100%; overflow: visible; }
        .report-details-table { min-width: 0; font-size: 9pt; }
        .report-table tr { page-break-inside: avoid; }
        .report-graphic { display: block; }
        .graphic-row { display: table; width: 100%; }
        .print-only { display: block; }
    <?php endif; ?>
    @media (max-width: 1000px) { .report-config-row { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 600px) {
        .reports-page { padding: 14px; }
        .report-panel { padding: 16px; }
        .report-config-row { grid-template-columns: 1fr; }
        .report-actions { align-items: stretch; }
        .report-actions button { width: 100%; }
        .report-meta { flex-direction: column; }
    }
    @media print {
        @page { size: <?php echo e(request('export_format') === 'pdf' ? 'A4 '.($pdfOrientation ?? 'portrait') : 'landscape'); ?>; margin: <?php echo e(request('export_format') === 'pdf' ? '12mm' : '14mm'); ?>; }
        html, body, .dashboard, .main-content { height: auto !important; min-height: 0 !important; overflow: visible !important; }
        .dashboard { display: block !important; }
        .sidebar, .page-top, .report-builder, .no-print { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; max-height: none !important; }
        .reports-page, .reports-grid, #printableReport, .report-section { height: auto !important; max-height: none !important; overflow: visible !important; }
        .reports-page { padding: 0; }
        .reports-grid { display: block !important; }
        .report-panel { border: 0; box-shadow: none; padding: 0; }
        .report-preview { display: block !important; }
        .report-table { width: 100%; page-break-inside: auto; }
        .report-table-scroll { width: 100%; overflow: visible !important; }
        .report-details-table { min-width: 0; font-size: <?php echo e(request('export_format') === 'pdf' ? '9pt' : '8px'); ?>; }
        .report-table thead { display: table-header-group; }
        .report-table tr { break-inside: avoid; page-break-inside: avoid; }
        .report-section { break-inside: auto; page-break-inside: auto; }
        .report-graphic { break-inside: avoid; page-break-inside: avoid; }
        .print-only { display: block; }
    }
</style>
<?php if(request('export_format') !== 'pdf'): ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/bhw/shared-ui.css')); ?>">
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/bhw/reports-inline-1.js')); ?>?v=<?php echo e(filemtime(public_path('js/bhw/reports-inline-1.js'))); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="reports-page">
    <div class="page-top no-print">
        <div class="page-title-group">
            <h1>Custom Reports</h1>
            <p>Select the information and filters needed for a printable BHW report.</p>
        </div>
    </div>

    <div class="reports-grid">
        <?php
            $selectedReport = in_array(request('report_type'), array_keys($availableSections), true)
                ? request('report_type')
                : ($selectedSections[0] ?? array_key_first($availableSections));
            $reportGroups = [
                'General Reports' => ['citizens', 'households', 'health_records', 'supplies', 'events', 'referrals'],
                'Demographic Reports' => ['age_by_purok', 'sex_by_purok', 'pregnancy_by_purok'],
                'Health Reports' => ['immunization', 'diagnosis_summary', 'diagnosis_by_purok'],
            ];
        ?>
        <form class="report-panel report-builder no-print" method="GET" action="<?php echo e(route('bhw.reports')); ?>" id="reportBuilder">
            <h2>Report Builder</h2>
            <p>Select a report and adjust only the filters that apply to it.</p>
            <?php if($errors->any()): ?>
                <div class="report-validation-errors" role="alert">
                    <strong>Please correct the report filters:</strong>
                    <ul>
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="report-config-row">
                <div class="report-field">
                    <label for="reportType" id="reportTypeLabel">Select Report Type</label>
                    <select id="reportType" name="report_type" required>
                        <?php $__currentLoopData = $reportGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupLabel => $sectionKeys): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $groupSections = array_intersect_key(array_flip($sectionKeys), $availableSections); ?>
                            <?php if($groupSections): ?>
                                <optgroup label="<?php echo e($groupLabel); ?>">
                                    <?php $__currentLoopData = array_keys($groupSections); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($key); ?>" <?php echo e($selectedReport === $key ? 'selected' : ''); ?>><?php echo e($availableSections[$key]); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </optgroup>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="report-fields">
                    <div class="report-field" data-reports="citizens,households,health_records,age_by_purok,sex_by_purok,pregnancy_by_purok,immunization,diagnosis_summary,diagnosis_by_purok,referrals">
                        <label for="reportPurok">Purok</label>
                        <select id="reportPurok" name="location" aria-label="Filter by Purok or Subgroup">
                            <option value="">All Puroks</option>

                            <?php $__currentLoopData = $puroks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <optgroup label="<?php echo e($purok->name); ?>">
                                    <option value="purok:<?php echo e($purok->id); ?>"
                                        <?php echo e(request('location') === 'purok:'.$purok->id || (request('location') === null && (string) request('purok') === (string) $purok->id && !request()->filled('subgroup')) ? 'selected' : ''); ?>>
                                        All of <?php echo e($purok->name); ?>

                                    </option>
                                    <?php $__currentLoopData = $purok->subgroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subgroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="subgroup:<?php echo e($subgroup->id); ?>"
                                            <?php echo e(request('location') === 'subgroup:'.$subgroup->id || (request('location') === null && (string) request('subgroup') === (string) $subgroup->id) ? 'selected' : ''); ?>>
                                            <?php echo e($subgroup->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </optgroup>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="report-field" data-reports="citizens,health_records,immunization,diagnosis_summary,diagnosis_by_purok">
                        <label for="reportSearch">Search citizens</label>
                        <input id="reportSearch" type="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Name or citizen ID">
                    </div>
                    <div class="report-field" data-reports="citizens">
                        <label for="citizenStatus">Citizen status</label>
                        <select id="citizenStatus" name="citizen_status">
                            <option value="">All statuses</option>
                            <option value="living" <?php echo e(request('citizen_status') === 'living' ? 'selected' : ''); ?>>Living</option>
                            <option value="deceased" <?php echo e(request('citizen_status') === 'deceased' ? 'selected' : ''); ?>>Deceased</option>
                        </select>
                    </div>
                    <div class="report-field" data-reports="citizens,age_by_purok,sex_by_purok,pregnancy_by_purok,immunization">
                        <label for="reportAgeMin">Minimum age</label>
                        <input id="reportAgeMin" type="number" name="age_min" min="0" max="120" value="<?php echo e(request('age_min')); ?>" placeholder="Any">
                    </div>
                    <div class="report-field" data-reports="citizens,age_by_purok,sex_by_purok,pregnancy_by_purok,immunization">
                        <label for="reportAgeMax">Maximum age</label>
                        <input id="reportAgeMax" type="number" name="age_max" min="0" max="120" value="<?php echo e(request('age_max')); ?>" placeholder="Any">
                    </div>
                    <div class="report-field" data-reports="citizens">
                        <label for="reportSex">Sex</label>
                        <select id="reportSex" name="sex">
                            <option value="">All</option>
                            <option value="Male" <?php echo e(request('sex') === 'Male' ? 'selected' : ''); ?>>Male</option>
                            <option value="Female" <?php echo e(request('sex') === 'Female' ? 'selected' : ''); ?>>Female</option>
                        </select>
                    </div>
                    <div class="report-field" data-reports="citizens">
                        <label for="citizenAgeSort">Sort by age</label>
                        <select id="citizenAgeSort" name="age_sort">
                            <option value="">Default order</option>
                            <option value="youngest" <?php echo e(request('age_sort') === 'youngest' ? 'selected' : ''); ?>>Youngest to oldest</option>
                            <option value="oldest" <?php echo e(request('age_sort') === 'oldest' ? 'selected' : ''); ?>>Oldest to youngest</option>
                        </select>
                    </div>
                    <div class="report-field" data-reports="health_records,events,referrals,diagnosis_summary,diagnosis_by_purok,immunization">
                        <label for="reportStart">Start date</label>
                        <input id="reportStart" type="date" name="start_date" value="<?php echo e(request('start_date')); ?>">
                    </div>
                    <div class="report-field" data-reports="health_records,events,referrals,diagnosis_summary,diagnosis_by_purok,immunization">
                        <label for="reportEnd">End date</label>
                        <input id="reportEnd" type="date" name="end_date" value="<?php echo e(request('end_date')); ?>">
                    </div>
                    <div class="report-field" data-reports="health_records,diagnosis_summary,diagnosis_by_purok">
                        <label for="reportDiagnosis">Diagnosis</label>
                        <select id="reportDiagnosis" name="diagnosis">
                            <option value="">All diagnoses</option>
                            <?php $__currentLoopData = $diagnoses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $diagnosis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($diagnosis); ?>" <?php echo e(request('diagnosis') === $diagnosis ? 'selected' : ''); ?>><?php echo e($diagnosis); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="report-field" data-reports="supplies">
                        <label for="reportSupplySearch">Search supplies</label>
                        <input id="reportSupplySearch" type="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Supply name">
                    </div>
                    <div class="report-field" data-reports="supplies">
                        <label for="reportCategory">Item category</label>
                        <select id="reportCategory" name="category">
                            <option value="">All categories</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category); ?>" <?php echo e(request('category') === $category ? 'selected' : ''); ?>><?php echo e($category); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="report-field" data-reports="supplies">
                        <label for="stockStatus">Stock status</label>
                        <select id="stockStatus" name="stock_status">
                            <option value="">All stock</option>
                            <option value="out" <?php echo e(request('stock_status') === 'out' ? 'selected' : ''); ?>>Out of stock</option>
                            <option value="low" <?php echo e(request('stock_status') === 'low' ? 'selected' : ''); ?>>Low stock</option>
                            <option value="available" <?php echo e(request('stock_status') === 'available' ? 'selected' : ''); ?>>Available</option>
                        </select>
                    </div>
                    <div class="report-field" data-reports="events">
                        <label for="eventStatus">Event status</label>
                        <select id="eventStatus" name="event_status">
                            <option value="">All events</option>
                            <option value="upcoming" <?php echo e(request('event_status') === 'upcoming' ? 'selected' : ''); ?>>Upcoming</option>
                            <option value="past" <?php echo e(request('event_status') === 'past' ? 'selected' : ''); ?>>Past</option>
                        </select>
                    </div>
                    <div class="report-field" data-reports="referrals">
                        <label for="referralStatus">Referral status</label>
                        <select id="referralStatus" name="referral_status">
                            <option value="">All statuses</option>
                            <?php $__currentLoopData = ['approved', 'released', 'returned']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status); ?>" <?php echo e(request('referral_status') === $status ? 'selected' : ''); ?>><?php echo e(ucfirst($status)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="report-field" data-reports="immunization">
                        <label for="vaccineType">Vaccine type</label>
                        <select id="vaccineType" name="vaccine_type">
                            <option value="">All vaccine types</option>
                            <?php $__currentLoopData = $vaccineTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vaccineType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($vaccineType); ?>" <?php echo e(request('vaccine_type') === $vaccineType ? 'selected' : ''); ?>><?php echo e($vaccineType); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="report-actions">
                <label class="report-graphics-toggle" style="margin-right:0" data-reports="age_by_purok,immunization,sex_by_purok,pregnancy_by_purok,diagnosis_summary">
                    <input type="checkbox" name="include_graphics" value="1" <?php echo e($includeGraphics ? 'checked' : ''); ?>>
                    Include graphic summaries
                </label>
                <label class="report-graphics-toggle" style="margin-right:0" data-reports="sex_by_purok,age_by_purok,pregnancy_by_purok">
                    <input type="checkbox" name="count_mode_only" value="1" <?php echo e(request()->boolean('count_mode_only') ? 'checked' : ''); ?>>
                    Count mode only
                </label>
                <label class="report-graphics-toggle" data-reports="sex_by_purok,age_by_purok,pregnancy_by_purok">
                    <input type="checkbox" name="include_summary" value="1" <?php echo e($includeSummary ? 'checked' : ''); ?>>
                    Include summary counts
                </label>
                <button type="submit" class="btn-primary" data-ui-action="primary">Generate Report</button>
                <a class="btn-secondary" href="<?php echo e(route('bhw.reports', ['report_type' => $selectedReport])); ?>" data-ui-action="reset">Reset Filters</a>
                <button type="submit" class="btn-secondary" name="export_format" value="xlsx" data-no-loader="true" data-ui-action="secondary">Export to Excel</button>
                <button type="submit" class="btn-secondary" name="export_format" value="pdf" data-no-loader="true" data-ui-action="secondary">Export to PDF</button>
                <p id="reportExportFeedback" class="report-export-feedback" role="alert" hidden></p>
            </div>
        </form>

        <main class="report-panel report-preview" id="printableReport" data-no-motion>
            <div class="report-meta">
                <div>
                    <h1>Barangay Health Report</h1>
                    <p>Generated <?php echo e(now()->format('F d, Y h:i A')); ?></p>
                    <p>Filters: <?php echo e($reportFilterSummary ?: 'None'); ?></p>
                </div>
                <span class="print-only">BHW Operations Report</span>
            </div>

            <?php if(in_array('citizens', $selectedSections)): ?>
                <section class="report-section">
                    <h3>Citizen Profiles (<?php echo e($citizensReport->count()); ?>)</h3>
                    <?php if($citizensReport->isEmpty()): ?> <div class="report-empty">No citizens match the selected filters.</div>
                    <?php else: ?> <table class="report-table"><thead><tr><th>Citizen ID</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Birthdate</th><th>Age</th><th>Sex</th><th>Contact No.</th><th>Purok</th><th>Family / Subgroup</th></tr></thead><tbody>
                        <?php $__currentLoopData = $citizensReport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $citizen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr><td>C-<?php echo e(str_pad($citizen->id, 4, '0', STR_PAD_LEFT)); ?></td><td><?php echo e($citizen->Citizen_FName); ?></td><td><?php echo e($citizen->Citizen_MName); ?></td><td><?php echo e($citizen->Citizen_LName); ?></td><td><?php echo e($citizen->Citizen_BirthDate ? \Illuminate\Support\Carbon::parse($citizen->Citizen_BirthDate)->format('M d, Y') : 'Not recorded'); ?></td><td><?php echo e($citizen->ageForEligibility() ?? 'Not recorded'); ?></td><td><?php echo e($citizen->Citizen_Sex ?: 'Not recorded'); ?></td><td><?php echo e($citizen->Citizen_ContactNo ?: 'Not recorded'); ?></td><td><?php echo e($citizen->subgroup?->purok?->name ?? $citizen->family?->subgroup?->purok?->name ?? $citizen->family?->purok?->name ?? ($citizen->Citizen_Purok ?: 'Unassigned')); ?></td><td><?php echo e($citizen->family->family_name ?? 'No family'); ?> / <?php echo e($citizen->subgroup->name ?? $citizen->family?->subgroup?->name ?? 'Not assigned'); ?></td></tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody></table><?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if(in_array('households', $selectedSections)): ?>
                <section class="report-section">
                    <h3>Household Profiles (<?php echo e($households->count()); ?>)</h3>
                    <?php if($households->isEmpty()): ?> <div class="report-empty">No households match the selected filters.</div>
                    <?php else: ?>
                        <style>
                            .household-table tr.household-purok-row td { background: #eef2ff; color: #173b82; font-weight: 700; }
                            .household-table tr.household-subgroup-row td { background: #f8fafc; color: #334155; font-weight: 600; }
                            .household-table tr.is-head td { font-weight: 700; }
                            .household-badge { display: inline-block; color: #173b82; font-weight: 700; }
                        </style>
                        <?php
                            $householdsByPurok = $households->groupBy(fn ($f) => $f->subgroup?->purok?->name ?? $f->purok?->name ?? 'Unassigned');
                        ?>
                        <table class="report-table household-table">
                            <thead><tr><th>Family ID</th><th>Family Name</th><th>Family Head</th><th>Member</th><th>Role</th><th>Age</th><th>Sex</th><th>Birthdate</th><th>Contact No.</th><th>Purok / Subgroup</th></tr></thead>
                            <tbody>
                            <?php $__currentLoopData = $householdsByPurok; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purokName => $purokFamilies): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="household-purok-row"><td colspan="10"><?php echo e($purokName); ?> &mdash; Qty: <?php echo e($purokFamilies->count()); ?></td></tr>
                                <?php $__currentLoopData = $purokFamilies->groupBy(fn ($f) => $f->subgroup?->name ?? 'No subgroup'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subgroupName => $subgroupFamilies): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $subgroupLabel = \App\Support\FamilyCode::subgroupLabel($purokName, $subgroupFamilies->first()->subgroup?->name); ?>
                                    <tr class="household-subgroup-row"><td colspan="10"><?php echo e($subgroupLabel); ?> &bull; Qty: <?php echo e($subgroupFamilies->count()); ?></td></tr>
                                    <?php $__currentLoopData = $subgroupFamilies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $family): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $rowCount = max(1, $family->sorted_members->count());
                                            $location = $purokName.' / '.$subgroupLabel;
                                        ?>
                                        <?php $__empty_1 = true; $__currentLoopData = $family->sorted_members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr class="<?php echo e($member->id === $family->head_id ? 'is-head' : ''); ?>">
                                                <?php if($loop->first): ?>
                                                    <td class="family-cell" rowspan="<?php echo e($rowCount); ?>"><?php echo e($family->family_code ?: 'F-'.str_pad($family->id, 4, '0', STR_PAD_LEFT)); ?></td>
                                                    <td class="family-cell" rowspan="<?php echo e($rowCount); ?>"><?php echo e($family->family_name); ?></td>
                                                    <td class="family-cell" rowspan="<?php echo e($rowCount); ?>"><?php echo e($family->head_name); ?></td>
                                                <?php endif; ?>
                                                <td><?php echo e(trim(implode(' ', array_filter([$member->Citizen_FName, $member->Citizen_MName, $member->Citizen_LName])))); ?></td>
                                                <td><?php if($member->id === $family->head_id): ?><span class="household-badge">Head</span><?php else: ?> Member <?php endif; ?></td>
                                                <td><?php echo e($member->ageForEligibility() ?? 'Not recorded'); ?></td>
                                                <td><?php echo e($member->Citizen_Sex ?: 'Not recorded'); ?></td>
                                                <td><?php echo e($member->Citizen_BirthDate ? \Illuminate\Support\Carbon::parse($member->Citizen_BirthDate)->format('M d, Y') : 'Not recorded'); ?></td>
                                                <td><?php echo e($member->Citizen_ContactNo ?: 'Not recorded'); ?></td>
                                                <td><?php echo e($location); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td class="family-cell"><?php echo e($family->family_code ?: 'F-'.str_pad($family->id, 4, '0', STR_PAD_LEFT)); ?></td>
                                                <td class="family-cell"><?php echo e($family->family_name); ?></td>
                                                <td class="family-cell"><?php echo e($family->head_name); ?></td>
                                                <td colspan="6">No members</td>
                                                <td><?php echo e($location); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if(in_array('age_by_purok', $selectedSections)): ?>
                <section class="report-section">
                    <h3>Age-Bracket Breakdown per Purok</h3>
                    <?php if($includeSummary): ?>
                    <table class="report-table">
                        <thead><tr><th>Purok</th><th>Infants</th><th>Toddlers</th><th>Children</th><th>Teenagers</th><th>Working adults</th><th>Seniors</th><th>Unknown age</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $ageByPurok; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok => $ages): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr><td><?php echo e($purok); ?></td><td><?php echo e($ages['infants']); ?></td><td><?php echo e($ages['toddlers']); ?></td><td><?php echo e($ages['children']); ?></td><td><?php echo e($ages['teenagers']); ?></td><td><?php echo e($ages['working_adults']); ?></td><td><?php echo e($ages['seniors']); ?></td><td><?php echo e($ages['unknown_age']); ?></td></tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="8" class="report-empty">No age data matches the selected filters.</td></tr>
                            <?php endif; ?>
                            <tr><th>Total</th><th><?php echo e($ageTotals['infants']); ?></th><th><?php echo e($ageTotals['toddlers']); ?></th><th><?php echo e($ageTotals['children']); ?></th><th><?php echo e($ageTotals['teenagers']); ?></th><th><?php echo e($ageTotals['working_adults']); ?></th><th><?php echo e($ageTotals['seniors']); ?></th><th><?php echo e($ageTotals['unknown_age']); ?></th></tr>
                        </tbody>
                    </table>
                    <?php if($includeGraphics && $ageByPurok->isNotEmpty()): ?>
                        <div class="report-graphic">
                            <?php $maxAgeTotal = max(1, $ageByPurok->map(fn ($row) => array_sum($row))->max()); ?>
                            <?php $__currentLoopData = $ageByPurok; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok => $ages): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $total = array_sum($ages); ?>
                                <div class="graphic-row"><span><?php echo e($purok); ?></span><div class="graphic-track"><div class="graphic-fill" style="width:<?php echo e(($total / $maxAgeTotal) * 100); ?>%"></div></div><strong><?php echo e($total); ?></strong></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if (! (request()->boolean('count_mode_only'))): ?><?php echo $__env->make('bhw.partials.report-citizen-list', ['title' => 'Age-Bracket List per Purok, Subgroup and Classification', 'groups' => $ageCitizenList], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if(in_array('immunization', $selectedSections)): ?>
                <section class="report-section">
                    <h3>Immunization and Vaccination Coverage</h3>
                    <table class="report-table">
                        <thead><tr><th>Eligible children age 0-5</th><th><?php echo e(request('vaccine_type') ? 'Children with selected vaccine recorded' : 'Children with BCG, polio, and measles groups recorded'); ?></th><th><?php echo e(request('vaccine_type') ? 'No selected vaccine record' : 'Missing one or more core vaccine groups'); ?></th></tr></thead>
                        <tbody><tr><td><?php echo e($immunizationSummary['total']); ?></td><td><?php echo e($immunizationSummary['vaccinated']); ?></td><td><?php echo e($immunizationSummary['missing']); ?></td></tr></tbody>
                    </table>
                    <?php if($includeGraphics): ?>
                        <div class="report-graphic"><div class="graphic-row"><span>Coverage</span><div class="graphic-track"><div class="graphic-fill green" style="width:<?php echo e($immunizationSummary['total'] ? ($immunizationSummary['vaccinated'] / $immunizationSummary['total']) * 100 : 0); ?>%"></div></div><strong><?php echo e($immunizationSummary['total'] ? round(($immunizationSummary['vaccinated'] / $immunizationSummary['total']) * 100) : 0); ?>%</strong></div></div>
                    <?php endif; ?>
                    <p style="margin-top:8px;color:#64748b;font-size:12px;"><?php echo e(request('vaccine_type') ? 'Counts eligible children with a matching administered record within the selected date range. Vaccination details below include all ages in the selected citizen scope.' : 'This is a count of the three named vaccine groups recorded in the selected date range; it does not determine completion of a vaccine-specific dose schedule.'); ?></p>

                    <h3>Vaccination details (<?php echo e($vaccinationDoseSummary->count()); ?> citizen/vaccine records)</h3>
                    <?php if($vaccinationDoseSummary->isEmpty()): ?>
                        <div class="report-empty">No vaccination records or scheduled doses match the selected filters.</div>
                    <?php else: ?>
                        <div class="report-table-scroll">
                            <table class="report-table report-details-table">
                            <thead><tr><th>Citizen ID</th><th>Citizen name</th><th>Age</th><th>Sex</th><th>Purok</th><th>Subgroup</th><th>Vaccine</th><th>Dose number(s)</th><th>Total administered doses</th><th>Latest vaccination date</th><th>Vaccination status</th><th>Next dose due date</th></tr></thead>
                            <tbody>
                                <?php $__currentLoopData = $vaccinationDoseSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($record['citizen_id_label']); ?></td>
                                        <td><?php echo e($record['citizen_name']); ?></td>
                                        <td><?php echo e($record['age']); ?></td>
                                        <td><?php echo e($record['sex']); ?></td>
                                        <td><?php echo e($record['purok']); ?></td>
                                        <td><?php echo e($record['subgroup']); ?></td>
                                        <td><?php echo e($record['vaccine_name']); ?></td>
                                        <td><?php echo e($record['dose_numbers'] ?: 'Not recorded'); ?></td>
                                        <td><?php echo e($record['administered_doses']); ?></td>
                                        <td><?php echo e($record['latest_vaccination_date'] ? \Illuminate\Support\Carbon::parse($record['latest_vaccination_date'])->format('M d, Y') : 'Not recorded'); ?></td>
                                        <td><?php echo e($record['vaccination_status']); ?></td>
                                        <td><?php echo e($record['next_dose_due_date'] ? \Illuminate\Support\Carbon::parse($record['next_dose_due_date'])->format('M d, Y') : 'Not recorded'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                        </div>
                        <p style="margin-top:8px;color:#64748b;font-size:12px;">Status reflects recorded vaccination entries and active schedules. No active scheduled dose does not establish completion of a vaccine series.</p>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if(in_array('sex_by_purok', $selectedSections)): ?>
                <section class="report-section">
                    <h3>Male and Female Counts by Purok</h3>
                    <?php if($includeSummary): ?>
                    <table class="report-table">
                        <thead><tr><th>Purok</th><th>Male</th><th>Female</th><th>Other / not recorded</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $sexByPurok; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok => $counts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr><td><?php echo e($purok); ?></td><td><?php echo e($counts['male']); ?></td><td><?php echo e($counts['female']); ?></td><td><?php echo e($counts['other']); ?></td></tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="4" class="report-empty">No sex-by-Purok data matches the selected filters.</td></tr>
                            <?php endif; ?>
                            <tr><th>Total</th><th><?php echo e($sexTotals['male']); ?></th><th><?php echo e($sexTotals['female']); ?></th><th><?php echo e($sexTotals['other']); ?></th></tr>
                        </tbody>
                    </table>
                    <?php if($includeGraphics && $sexByPurok->isNotEmpty()): ?>
                        <div class="report-graphic">
                            <?php $__currentLoopData = $sexByPurok; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok => $counts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $max = max(1, max($counts)); ?>
                                <div class="graphic-row"><span><?php echo e($purok); ?> male</span><div class="graphic-track"><div class="graphic-fill" style="width:<?php echo e(($counts['male'] / $max) * 100); ?>%"></div></div><strong><?php echo e($counts['male']); ?></strong></div>
                                <div class="graphic-row"><span><?php echo e($purok); ?> female</span><div class="graphic-track"><div class="graphic-fill orange" style="width:<?php echo e(($counts['female'] / $max) * 100); ?>%"></div></div><strong><?php echo e($counts['female']); ?></strong></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if (! (request()->boolean('count_mode_only'))): ?><?php echo $__env->make('bhw.partials.report-citizen-list', ['title' => 'Male and Female List per Purok and Subgroup', 'groups' => $sexCitizenList], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if(in_array('pregnancy_by_purok', $selectedSections)): ?>
                <section class="report-section">
                    <h3>Recorded Pregnancy by Purok</h3>
                    <?php if($includeSummary): ?>
                    <table class="report-table">
                        <thead><tr><th>Purok</th><th>Recorded pregnant residents</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $pregnancyByPurok; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr><td><?php echo e($purok); ?></td><td><?php echo e($count); ?></td></tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="2" class="report-empty">No pregnancy data matches the selected filters.</td></tr>
                            <?php endif; ?>
                            <tr><th>Total</th><th><?php echo e($pregnancyTotal); ?></th></tr>
                        </tbody>
                    </table>
                    <?php if($includeGraphics && $pregnancyByPurok->isNotEmpty()): ?>
                        <?php $maxPregnancy = max(1, $pregnancyByPurok->max()); ?>
                        <div class="report-graphic">
                            <?php $__currentLoopData = $pregnancyByPurok; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="graphic-row"><span><?php echo e($purok); ?></span><div class="graphic-track"><div class="graphic-fill red" style="width:<?php echo e(($count / $maxPregnancy) * 100); ?>%"></div></div><strong><?php echo e($count); ?></strong></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if (! (request()->boolean('count_mode_only'))): ?><?php echo $__env->make('bhw.partials.report-citizen-list', ['title' => 'Pregnancy List per Purok and Subgroup', 'groups' => $pregnancyCitizenList], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if(in_array('diagnosis_summary', $selectedSections)): ?>
                <section class="report-section"><h3>Diagnosis Summary</h3><table class="report-table"><thead><tr><th>Diagnosis</th><th>Recorded cases</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $diagnosisSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $diagnosis => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($diagnosis); ?></td><td><?php echo e($count); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="2" class="report-empty">No diagnosis data matches the selected dates and citizens.</td></tr><?php endif; ?></tbody></table><?php if($includeGraphics): ?> <?php $maxDiagnosis = max(1, $diagnosisSummary->max()); ?><div class="report-graphic"><?php $__currentLoopData = $diagnosisSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $diagnosis => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="graphic-row"><span><?php echo e($diagnosis); ?></span><div class="graphic-track"><div class="graphic-fill orange" style="width:<?php echo e(($count / $maxDiagnosis) * 100); ?>%"></div></div><strong><?php echo e($count); ?></strong></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?></section>
            <?php endif; ?>

            <?php if(in_array('diagnosis_by_purok', $selectedSections)): ?>
                <section class="report-section"><h3>Diagnoses by Purok</h3><table class="report-table"><thead><tr><th>Purok</th><th>Diagnosis</th><th>Cases</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $diagnosisByPurok; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?> <?php [$purok, $diagnosis] = explode('|', $key, 2); ?><tr><td><?php echo e($purok); ?></td><td><?php echo e($diagnosis); ?></td><td><?php echo e($count); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="3" class="report-empty">No diagnosis-by-Purok data matches the selected dates and citizens.</td></tr><?php endif; ?></tbody></table></section>
            <?php endif; ?>

            <?php if(in_array('health_records', $selectedSections) || in_array('diagnosis_summary', $selectedSections) || in_array('diagnosis_by_purok', $selectedSections)): ?>
                <section class="report-section">
                        <h3><?php echo e(in_array('health_records', $selectedSections) ? 'Health Record Details' : 'Diagnosis History Details'); ?> (<?php echo e($healthRecordDetails->count()); ?> records)</h3>
                        <?php if($healthRecordDetails->isEmpty()): ?>
                            <div class="report-empty">No health records match the selected citizens, diagnosis, and date range.</div>
                        <?php else: ?>
                            <div class="report-table-scroll">
                                <table class="report-table report-details-table">
                                    <thead><tr><th>Citizen name</th><th>Age</th><th>Sex</th><th>Purok</th><th>Diagnosis</th><th>Consultation date</th><th>Blood pressure</th><th>Weight kg</th><th>Height cm</th><th>BMI</th><th>Temperature °C</th></tr></thead>
                                    <tbody>
                                        <?php $__currentLoopData = $healthRecordDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($record['citizen_name']); ?></td>
                                                <td><?php echo e($record['age']); ?></td>
                                                <td><?php echo e($record['sex']); ?></td>
                                                <td><?php echo e($record['purok']); ?></td>
                                                <td><?php echo e($record['diagnosis']); ?></td>
                                                <td><?php echo e($record['date'] !== 'Not recorded' ? \Illuminate\Support\Carbon::parse($record['date'])->format('M d, Y') : $record['date']); ?></td>
                                                <td><?php echo e($record['blood_pressure']); ?></td>
                                                <td><?php echo e($record['weight']); ?></td>
                                                <td><?php echo e($record['height']); ?></td>
                                                <td><?php echo e($record['bmi']); ?></td>
                                                <td><?php echo e($record['temperature']); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if(in_array('health_records', $selectedSections)): ?>
                <section class="report-section">
                    <h3>Medication and Maintenance History (<?php echo e($medicationHistory->count()); ?> records)</h3>
                    <?php if($medicationHistory->isEmpty()): ?>
                        <div class="report-empty">No medication or maintenance medicine records match the selected citizens and date range.</div>
                    <?php else: ?>
                        <div class="report-table-scroll">
                            <table class="report-table report-details-table">
                                <thead><tr><th>Citizen ID</th><th>Citizen</th><th>Date</th><th>Medicine</th><th>Dosage</th><th>Quantity</th><th>Frequency</th><th>End date</th><th>Recorded by</th><th>Record type</th></tr></thead>
                                <tbody>
                                    <?php $__currentLoopData = $medicationHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr><td>C-<?php echo e(str_pad($record['citizen_id'], 4, '0', STR_PAD_LEFT)); ?></td><td><?php echo e($record['citizen_name']); ?></td><td><?php echo e($record['date']); ?></td><td><?php echo e($record['medicine']); ?></td><td><?php echo e($record['dosage']); ?></td><td><?php echo e($record['quantity']); ?></td><td><?php echo e($record['frequency']); ?></td><td><?php echo e($record['end_date']); ?></td><td><?php echo e($record['recorded_by']); ?></td><td><?php echo e($record['source']); ?></td></tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if(in_array('supplies', $selectedSections)): ?>
                <section class="report-section"><h3>Supply Inventory (<?php echo e($supplies->count()); ?>)</h3><table class="report-table"><thead><tr><th>Supply</th><th>Category</th><th>Quantity</th><th>Minimum</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $supplies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($supply->name); ?></td><td><?php echo e($supply->category ?? 'Uncategorized'); ?></td><td><?php echo e($supply->quantity); ?></td><td><?php echo e($supply->min_stock); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="4" class="report-empty">No supplies match the selected filters.</td></tr><?php endif; ?></tbody></table></section>
            <?php endif; ?>

            <?php if(in_array('events', $selectedSections)): ?>
                <section class="report-section"><h3>Events and Schedules (<?php echo e($events->count()); ?>)</h3><table class="report-table"><thead><tr><th>Date</th><th>Event</th><th>Description</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($event->start); ?></td><td><?php echo e($event->title); ?></td><td><?php echo e($event->description ?? '—'); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="3" class="report-empty">No events match the selected dates.</td></tr><?php endif; ?></tbody></table></section>
            <?php endif; ?>

            <?php if(in_array('referrals', $selectedSections)): ?>
                <section class="report-section"><h3>Referrals (<?php echo e($referrals->count()); ?>)</h3><table class="report-table"><thead><tr><th>Date</th><th>Name</th><th>Request</th><th>Status</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($referral->date_of_referral); ?></td><td><?php echo e($referral->name); ?></td><td><?php echo e($referral->requests_for); ?></td><td><?php echo e(ucfirst($referral->status ?? 'pending')); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="4" class="report-empty">No referrals match the selected dates.</td></tr><?php endif; ?></tbody></table></section><?php endif; ?>
        </main>
    </div>
</div>
<script src="<?php echo e(asset('js/bhw/reports-inline-2.js')); ?>?v=<?php echo e(filemtime(public_path('js/bhw/reports-inline-2.js'))); ?>"></script>
<script src="<?php echo e(asset('js/bhw/reports-inline-3.js')); ?>?v=<?php echo e(filemtime(public_path('js/bhw/reports-inline-3.js'))); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(request('export_format') === 'pdf' ? 'templates.report-pdf' : 'templates.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/bhw/reports.blade.php ENDPATH**/ ?>