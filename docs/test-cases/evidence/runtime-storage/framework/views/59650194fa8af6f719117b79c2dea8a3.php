<?php $__env->startSection('CSSown'); ?>
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/admin/demographics.css')); ?>?v=<?php echo e(filemtime(public_path('css/admin/demographics.css'))); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<main class="demographics-container" id="demographicsApp" data-initial-tab="<?php echo e(old('demographics_tab', session('demographics_tab', 'overview'))); ?>">
    <header class="demographics-header">
        <div>

            <h1>Demographics, Puroks &amp; Subgroups</h1>
            <p>Manage community areas, map coverage, subgroup assignments, and local health workers.</p>
        </div>
        <button type="button" class="btn btn-primary demographics-header-action" data-open-tab="map">Open map workspace</button>
    </header>

    <?php if(session('success')): ?>
        <div class="demographics-alert success" role="status"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="demographics-alert error" role="alert">
            <strong>Please review the following:</strong>
            <ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        </div>
    <?php endif; ?>

    <nav class="demographics-tabs" role="tablist" aria-label="Demographics management sections">
        <button type="button" class="demographics-tab active" role="tab" aria-selected="true" data-tab="overview">Overview</button>
        <button type="button" class="demographics-tab" role="tab" aria-selected="false" data-tab="puroks">Purok management</button>
        <button type="button" class="demographics-tab" role="tab" aria-selected="false" data-tab="subgroups">Subgroups &amp; BHWs</button>
        <button type="button" class="demographics-tab" role="tab" aria-selected="false" data-tab="map">Map &amp; boundaries</button>
    </nav>

    <section class="demographics-panel active" role="tabpanel" data-panel="overview">
        <div class="demographics-stats">
            <article class="demographics-stat"><span>Puroks</span><strong><?php echo e($puroks->count()); ?></strong></article>
            <article class="demographics-stat"><span>Subgroups</span><strong><?php echo e($subgroups->count()); ?></strong></article>
            <article class="demographics-stat"><span>Barangay health workers</span><strong><?php echo e($bhws->count()); ?></strong></article>
            <article class="demographics-stat"><span>Map pins saved</span><strong><?php echo e($puroks->filter(fn ($purok) => $purok->latitude !== null && $purok->longitude !== null)->count() + $subgroups->filter(fn ($subgroup) => $subgroup->latitude !== null && $subgroup->longitude !== null)->count()); ?></strong></article>
        </div>

        <section class="demographics-card workflow-card">
            <div class="section-heading">
                <div><h2>Recommended workflow</h2><p>Set up the area first, then add local coverage and assignments.</p></div>
            </div>
            <ol class="workflow-steps">
                <li><span>1</span><div><strong>Create or select a Purok</strong><small>Add the area name and an optional description.</small></div></li>
                <li><span>2</span><div><strong>Set its pin and boundary</strong><small>Choose the Purok on the map, place its marker, and draw the coverage outline.</small></div></li>
                <li><span>3</span><div><strong>Create subgroups and assign BHWs</strong><small>Each subgroup can have its own pin, boundary, and multiple BHWs.</small></div></li>
                <li><span>4</span><div><strong>Review and save</strong><small>Map changes are saved explicitly and remain independent for each area.</small></div></li>
            </ol>
        </section>

        <section class="demographics-card overview-list">
            <div class="section-heading"><div><h2>Purok summary</h2><p>Current locations and subgroup coverage.</p></div><button type="button" class="btn btn-primary" data-open-tab="puroks">Manage Puroks</button></div>
            <?php $__empty_1 = true; $__currentLoopData = $puroks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="overview-purok">
                    <div><strong><?php echo e($purok->name); ?></strong><small><?php echo e($purok->description ?: 'No description provided'); ?></small></div>
                    <div class="overview-badges"><span><?php echo e($purok->subgroups_count); ?> subgroups</span><span><?php echo e($purok->latitude !== null ? 'Pin saved' : 'No pin'); ?></span><span><?php echo e($purok->boundary ? 'Boundary saved' : 'No boundary'); ?></span></div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="empty-state">No Puroks yet. Create a Purok to begin.</p>
            <?php endif; ?>
        </section>
    </section>

    <section class="demographics-panel" role="tabpanel" data-panel="puroks" hidden>
        <div class="demographics-card">
            <div class="section-heading"><div><h2>Create a Purok</h2><p>Names must be unique. Add a description if it helps identify the area.</p></div></div>
            <form action="<?php echo e(route('admin.puroks.store')); ?>" method="POST" class="purok-create-form">
                <?php echo csrf_field(); ?>
                <label class="field"><span>Purok name</span><input type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="e.g. Purok 1" maxlength="255" required></label>
                <label class="field"><span>Description <small>(optional)</small></span><input type="text" name="description" value="<?php echo e(old('description')); ?>" placeholder="Area notes or landmarks" maxlength="2000"></label>
                <button type="submit" class="btn btn-primary">Add Purok</button>
            </form>
        </div>

        <div class="demographics-card">
            <div class="section-heading"><div><h2>Existing Puroks</h2><p>Update names and descriptions. Locations and boundaries are edited in the map workspace.</p></div></div>
            <div class="purok-list">
                <?php $__empty_1 = true; $__currentLoopData = $puroks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="purok-record">
                        <form action="<?php echo e(route('admin.puroks.update', $purok->id)); ?>" method="POST" class="purok-edit-form">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <label class="field"><span>Name</span><input type="text" name="name" value="<?php echo e($purok->name); ?>" maxlength="255" required></label>
                            <label class="field"><span>Description</span><input type="text" name="description" value="<?php echo e($purok->description); ?>" maxlength="2000" placeholder="Optional"></label>
                            <div class="record-meta"><span><?php echo e($purok->families_count); ?> families</span><span><?php echo e($purok->subgroups_count); ?> subgroups</span><span><?php echo e($purok->purge_citizens_count); ?> citizens</span></div>
                            <div class="record-actions"><button type="submit" class="btn btn-secondary">Save changes</button></div>
                        </form>
                        <form action="<?php echo e(route('admin.puroks.destroy', $purok->id)); ?>" method="POST" class="delete-form" data-confirm-message="Archive this Purok? It can only be archived after its families, subgroups, and citizen references have been moved. You can restore it later from the archive.">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <label class="field delete-password-field"><span>Admin password</span><input type="password" name="current_password" autocomplete="current-password" required></label>
                            <button type="submit" class="btn btn-danger" <?php if($purok->families_count || $purok->subgroups_count): echo 'disabled'; endif; ?> title="Move dependent records before archiving">Archive</button>
                        </form>
                        <form action="<?php echo e(route('admin.puroks.citizens.destroy', $purok->id)); ?>" method="POST" class="citizen-purge-form" data-confirm-message="Archive all <?php echo e($purok->purge_citizens_count); ?> citizen records in <?php echo e($purok->name); ?>? They can be restored from the archive.">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <input type="hidden" name="demographics_tab" value="puroks">
                            <div class="purge-heading"><strong>Archive citizens</strong><span><?php echo e($purok->purge_citizens_count); ?> citizens</span></div>
                            <p>Archives citizen profiles and linked citizen login accounts. Nothing is permanently deleted and they can be restored from the archive. Families and BHW accounts are kept.</p>
                            <label class="field"><span>Type <strong><?php echo e($purok->name); ?></strong> to confirm</span><input type="text" name="confirmation" maxlength="255" autocomplete="off" required></label>
                            <label class="field"><span>Admin password</span><input type="password" name="current_password" autocomplete="current-password" required></label>
                            <button type="submit" class="btn btn-danger" <?php if(!$purok->purge_citizens_count): echo 'disabled'; endif; ?>>Archive citizens</button>
                        </form>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="empty-state">No Puroks have been created yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="demographics-panel" role="tabpanel" data-panel="subgroups" hidden>
        <div class="demographics-card subgroup-section-card">
            <div class="section-heading subgroup-section-header subgroup-create-header">
                <div>
                    <h2>Create a Subgroup</h2>
                    <p>Choose a parent Purok and enter the subgroup details.</p>
                </div>
                <span class="required-fields-note">* Required fields</span>
            </div>
            <form action="<?php echo e(route('admin.subgroups.store')); ?>" method="POST" class="subgroup-create-form">
                <?php echo csrf_field(); ?>
                <div class="subgroup-create-body">
                    <div class="subgroup-form-fields">
                        <label class="field"><span>Parent Purok <b aria-hidden="true">*</b></span><select name="purok_id" required><option value="">Select Purok</option><?php $__currentLoopData = $puroks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($purok->id); ?>" <?php echo e(old('purok_id') == $purok->id ? 'selected' : ''); ?>><?php echo e($purok->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                        <label class="field"><span>Subgroup name <b aria-hidden="true">*</b></span><input type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="e.g. P1A, Sector 3" maxlength="255" required></label>
                        <label class="field"><span>Description <small>(optional)</small></span><input type="text" name="description" value="<?php echo e(old('description')); ?>" placeholder="Optional area notes or landmarks" maxlength="2000"></label>
                    </div>
                    <div class="subgroup-create-footer">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-plus-circle" aria-hidden="true"></i> Add Subgroup</button>
                    </div>
                </div>
            </form>
        </div>

        <section class="demographics-card">
            <div class="section-heading"><div><h2>BHW User List</h2><p>Choose coverage using Assignment and apply the selection to save it.</p></div></div>
                <?php $__empty_1 = true; $__currentLoopData = $bhws; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bhw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $assignedIds = $subgroups->filter(fn ($group) => $group->bhws->contains('id', $bhw->id) || (!$group->bhws->count() && $group->user_id == $bhw->id))->pluck('id')->all();
                        $selectedIds = old('assignments.'.$bhw->id.'.subgroup_ids', old('assignments.'.$bhw->id.'.present') ? [] : $assignedIds);
                    ?>
                    <form action="<?php echo e(route('admin.bhw-assignments.update')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <input type="hidden" name="demographics_tab" value="subgroups">
                    <article class="bhw-user-row">
                        <div><strong><?php echo e($bhw->name ?: $bhw->email); ?></strong><small><?php echo e($bhw->email); ?></small><small data-assignment-summary="<?php echo e($bhw->id); ?>"></small></div>
                        <input type="hidden" name="assignments[<?php echo e($bhw->id); ?>][present]" value="1">
                        <button type="button" class="btn btn-secondary" data-open-assignment="<?php echo e($bhw->id); ?>">Assignment</button>
                    </article>
                    <dialog class="bhw-assignment-dialog" id="bhwAssignment-<?php echo e($bhw->id); ?>" aria-labelledby="bhwAssignmentTitle-<?php echo e($bhw->id); ?>" data-bhw-id="<?php echo e($bhw->id); ?>">
                        <div class="section-heading assignment-dialog-heading"><div><h2 id="bhwAssignmentTitle-<?php echo e($bhw->id); ?>">Assignment: <?php echo e($bhw->name ?: $bhw->email); ?></h2><p>Select all subgroups covered by this BHW.</p></div><button type="button" class="assignment-dialog-close" data-cancel-assignment data-ui-action="secondary" aria-label="Close assignment">&times;</button></div>
                        <div class="assignment-area-list">
                            <?php $__currentLoopData = $puroks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <fieldset class="assignment-purok"><legend><?php echo e($purok->name); ?></legend>
                                    <button type="button" class="text-button" data-select-purok data-ui-action="secondary">Select all</button>
                                    <?php $__empty_2 = true; $__currentLoopData = $subgroups->where('purok_id', $purok->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                        <label class="assignment-area-option"><input type="checkbox" name="assignments[<?php echo e($bhw->id); ?>][subgroup_ids][]" value="<?php echo e($group->id); ?>" data-area-label="<?php echo e($purok->name); ?> / <?php echo e($group->name); ?>" <?php if(in_array($group->id, $selectedIds)): echo 'checked'; endif; ?>> <span><?php echo e($group->name); ?></span></label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                        <p class="empty-state">No subgroups in this Purok.</p>
                                    <?php endif; ?>
                                </fieldset>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="record-actions"><button type="button" class="btn btn-secondary" data-cancel-assignment>Cancel</button><button type="submit" class="btn btn-primary" data-apply-assignment>Apply selection</button></div>
                    </dialog>
                    </form>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="empty-state">No BHW accounts available.</p>
                <?php endif; ?>
        </section>
        <div class="demographics-card subgroup-section-card">
            <div class="section-heading subgroup-section-header subgroup-list-header">
                <div class="subgroup-list-title">
                    <h2>Manage Subgroups</h2>
                    <p>Edit subgroup details here. Manage BHW coverage in the user list above.</p>
                </div>
                <div class="subgroup-list-filters">
                    <label class="subgroup-filter-search">
                        <i class="bx bx-search" aria-hidden="true"></i>
                        <input type="search" id="subgroupSearch" placeholder="Search subgroup or BHW..." aria-label="Search subgroups and assigned BHWs">
                    </label>
                    <label class="subgroup-filter-purok">
                        <span class="visually-hidden">Filter by Parent Purok</span>
                        <select id="subgroupPurokFilter" aria-label="Filter by Parent Purok">
                            <option value="">All Parent Puroks</option>
                            <?php $__currentLoopData = $puroks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($purok->id); ?>"><?php echo e($purok->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>
                </div>
            </div>
            <div class="subgroup-list-body">
            <div class="subgroup-list">
                <?php $__empty_1 = true; $__currentLoopData = $subgroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subgroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article
                        class="subgroup-record"
                        data-subgroup-card
                        data-subgroup-name="<?php echo e($subgroup->name); ?>"
                        data-purok-name="<?php echo e($subgroup->purok->name); ?>"
                        data-purok-id="<?php echo e($subgroup->purok_id); ?>"
                        data-citizen-count="<?php echo e($subgroup->purge_citizens_count); ?>"
                        data-confirmation-name="<?php echo e($subgroup->name); ?> - <?php echo e($subgroup->purok->name); ?>"
                        data-citizen-archive-action="<?php echo e(route('admin.subgroups.citizens.destroy', $subgroup->id)); ?>"
                        data-subgroup-archive-action="<?php echo e(route('admin.subgroups.destroy', $subgroup->id)); ?>"
                    >
                        <form id="subgroup-edit-<?php echo e($subgroup->id); ?>" action="<?php echo e(route('admin.subgroups.update', $subgroup->id)); ?>" method="POST" class="subgroup-edit-form">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div class="subgroup-form-fields">
                                <label class="field"><span>Parent Purok</span><select name="purok_id" required><?php $__currentLoopData = $puroks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($purok->id); ?>" <?php echo e($subgroup->purok_id == $purok->id ? 'selected' : ''); ?>><?php echo e($purok->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                                <label class="field"><span>Subgroup name</span><input type="text" name="name" value="<?php echo e($subgroup->name); ?>" maxlength="255" required></label>
                                <label class="field"><span>Description</span><input type="text" name="description" value="<?php echo e($subgroup->description); ?>" maxlength="2000" placeholder="Optional"></label>
                            </div>
                            <div class="subgroup-record-footer">
                                <div class="record-meta">
                                    <span><i class="bx bx-home-alt" aria-hidden="true"></i> <?php echo e($subgroup->families_count); ?> families</span>
                                    <span class="citizen-count-badge"><i class="bx bx-group" aria-hidden="true"></i> <?php echo e($subgroup->purge_citizens_count); ?> citizens</span>
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['pin-status-badge' => $subgroup->latitude !== null]); ?>"><i class="bx bx-map-pin" aria-hidden="true"></i> <?php echo e($subgroup->latitude !== null ? 'Pinned on map' : 'No pin'); ?></span>
                                </div>
                                <div class="record-actions">
                                    <button type="submit" class="btn btn-primary"><i class="bx bx-save" aria-hidden="true"></i> Save subgroup</button>
                                    <button type="button" class="btn btn-archive" data-open-archive><i class="bx bx-archive" aria-hidden="true"></i> Archive</button>
                                </div>
                            </div>
                        </form>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="empty-state">No subgroups have been created yet.</p>
                <?php endif; ?>
            </div>
            <p class="empty-state subgroup-no-results" hidden>No subgroups match your filters.</p>
            </div>
        </div>
    </section>

    <dialog class="subgroup-archive-dialog" id="subgroupArchiveDialog" aria-labelledby="subgroupArchiveTitle" aria-describedby="subgroupArchiveDescription">
        <form class="subgroup-archive-form" id="subgroupArchiveForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_method" value="DELETE">
            <div class="subgroup-archive-heading">
                <span class="subgroup-archive-icon" aria-hidden="true"><i class="bx bx-archive"></i></span>
                <div>
                    <h2 id="subgroupArchiveTitle">Archive subgroup</h2>
                    <p id="subgroupArchiveDescription"></p>
                </div>
                <button type="button" class="subgroup-archive-close" data-close-archive aria-label="Close archive confirmation">&times;</button>
            </div>
            <div class="subgroup-archive-scope" id="subgroupArchiveScope"></div>
            <div class="subgroup-archive-error" id="subgroupArchiveError" role="alert" hidden></div>
            <label class="field" for="subgroupArchiveConfirmation">
                <span>Type <strong id="subgroupArchiveConfirmationName"></strong> to confirm</span>
                <input id="subgroupArchiveConfirmation" name="confirmation" type="text" maxlength="513" autocomplete="off" required>
            </label>
            <label class="field subgroup-archive-password" for="subgroupArchivePassword">
                <span>Admin password</span>
                <span class="password-input-wrap">
                    <input id="subgroupArchivePassword" name="current_password" type="password" autocomplete="current-password" required>
                    <button type="button" class="password-visibility-toggle" aria-label="Show password" aria-pressed="false" data-toggle-password>
                        <i class="bx bx-show" aria-hidden="true"></i>
                    </button>
                </span>
            </label>
            <div class="subgroup-archive-actions">
                <button type="button" class="btn btn-archive-cancel" data-close-archive>Cancel</button>
                <button type="submit" class="btn btn-danger" id="subgroupArchiveConfirm" disabled>Confirm Archive</button>
            </div>
        </form>
    </dialog>

    <section class="demographics-panel map-workspace" role="tabpanel" data-panel="map" hidden>
        <div class="demographics-card map-controls">
            <div class="section-heading"><div><h2>Map &amp; boundary workspace</h2><p>Choose a Purok or subgroup, then edit its pin or polygon. Save each change when ready.</p></div></div>
            <div class="map-selector-grid">
                <label class="field"><span>Purok</span><select id="mapPurok"><option value="">All Puroks / choose one</option><?php $__currentLoopData = $puroks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purok): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($purok->id); ?>"><?php echo e($purok->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                <label class="field"><span>Map target</span><select id="mapTargetType"><option value="purok">Selected Purok</option><option value="subgroup">Subgroup</option></select></label>
                <label class="field" id="subgroupTargetField"><span>Subgroup</span><select id="mapSubgroup"><option value="">Select a subgroup</option></select></label>
            </div>
            <div class="map-mode-controls">
                <div class="button-group" aria-label="Map editing mode">
                    <button type="button" class="btn btn-secondary active" id="pinMode" aria-pressed="true">Place pin</button>
                    <button type="button" class="btn btn-secondary" id="drawPurokBoundary" aria-pressed="false">Draw Purok boundary</button>
                    <button type="button" class="btn btn-secondary" id="drawSubgroupBoundary" aria-pressed="false">Draw subgroup boundary</button>
                    <button type="button" class="btn btn-secondary" id="editBoundary" aria-pressed="false">Edit saved boundary</button>
                    <button type="button" class="btn btn-secondary" id="finishBoundary" aria-pressed="false" disabled>Finish drawing</button>
                </div>
                <div class="button-group map-edit-actions">
                    <button type="button" class="btn btn-secondary" id="undoBoundary" disabled>Undo point</button>
                    <button type="button" class="btn btn-secondary" id="cancelMapChanges" disabled>Cancel changes</button>
                    <button type="button" class="btn btn-secondary" id="resetPin">Reset pin</button>
                    <button type="button" class="btn btn-primary" id="savePin" disabled>Save pin</button>
                    <button type="button" class="btn btn-primary" id="saveBoundary" disabled>Save boundary</button>
                    <button type="button" class="btn btn-danger" id="deleteBoundary" disabled>Delete Purok boundary</button>
                </div>
            </div>
            <p class="map-status" id="mapStatus" role="status">Choose a Purok to view its map details.</p>
            <div class="map-legend" aria-label="Map legend"><span><i class="legend-pin"></i> Purok pin</span><span><i class="legend-subgroup-pin"></i> Subgroup pin</span><span><i class="legend-area"></i> Purok boundary</span><span><i class="legend-subgroup-area"></i> Subgroup boundary</span></div>
            <fieldset class="boundary-visibility"><legend>Boundary visibility</legend><div id="boundaryVisibilityOptions"></div></fieldset>
            <div id="demographicsMap" class="demographics-map" role="application" aria-label="Purok and subgroup map"></div>
            <p class="map-attribution-note">Click to place pins. In boundary mode, add vertices, then select Finish drawing; use Edit saved boundary to drag vertices or midpoints.</p>
        </div>
    </section>

    <script>window.demographicsMapData = <?php echo e(\Illuminate\Support\Js::from($demographicsMapData)); ?>;</script>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/admin/demographics.js')); ?>?v=<?php echo e(filemtime(public_path('js/admin/demographics.js'))); ?>"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(config('services.google_maps.key')); ?>&loading=async&callback=initDemographicsMap" async defer></script>
<script src="<?php echo e(asset('js/admin/demographics-inline-2.js')); ?>?v=<?php echo e(filemtime(public_path('js/admin/demographics-inline-2.js'))); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('templates.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/admin/demographics.blade.php ENDPATH**/ ?>