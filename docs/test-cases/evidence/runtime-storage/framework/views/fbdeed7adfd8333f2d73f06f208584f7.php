<?php
    $photoEditable = $canEdit && !(bool) $citizen->Citizen_IsDeceased;
    $photoVisible = $citizen->photo_path && ($canEdit || app(\App\Services\BhwCitizenAccess::class)->canViewAll(auth()->user()));
?>
<div class="citizen-photo-section">
    <?php if($photoEditable): ?>
        <input id="photo" class="citizen-photo-input" name="photo" type="file" form="citizen-photo-form" accept="image/jpeg,image/png,image/webp" required disabled data-auto-confirm aria-label="Choose a citizen photo">
    <?php endif; ?>
    <label class="citizen-photo-frame" for="photo">
        <?php if($photoVisible): ?>
            <img class="citizen-photo-image" src="<?php echo e(route('citizen.photo', $citizen->id)); ?>" alt="Citizen photo">
        <?php else: ?>
            <span class="citizen-photo-empty"><?php echo e($citizen->photo_path ? 'Photo unavailable' : 'No photo'); ?></span>
        <?php endif; ?>
        <?php if($photoEditable): ?>
            <span class="citizen-photo-overlay"><?php echo e($citizen->photo_path ? 'Change photo' : 'Add photo'); ?></span>
        <?php endif; ?>
    </label>
    <button type="button" class="btn-secondary" id="openEditManualPicker" style="display:none;" data-ui-action="secondary">Assign without family</button>

    <?php if(session('photo_success')): ?>
        <p class="citizen-photo-success" role="status"><?php echo e(session('photo_success')); ?></p>
    <?php endif; ?>
    <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="citizen-photo-error" role="alert"><?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

    <?php if($photoEditable): ?>
        <p id="photo-preview-error" class="citizen-photo-error" role="alert" hidden></p>

        <dialog class="citizen-photo-dialog" id="photo-confirm-dialog" aria-labelledby="photo-confirm-title" aria-describedby="photo-confirm-description">
            <h2 id="photo-confirm-title">Confirm photo change</h2>
            <p id="photo-confirm-description">Review the new photo before saving it to the citizen's health ID.</p>
            <div class="citizen-photo-previews">
                <figure>
                    <figcaption>Current photo</figcaption>
                    <?php if($citizen->photo_path): ?>
                        <img class="citizen-photo-image" src="<?php echo e(route('citizen.photo', $citizen->id)); ?>" alt="Current citizen photo">
                    <?php else: ?>
                        <p>No current photo.</p>
                    <?php endif; ?>
                </figure>
                <figure>
                    <figcaption>New photo</figcaption>
                    <img class="citizen-photo-image" id="confirm-photo-image" alt="Preview of new citizen photo">
                </figure>
            </div>
            <div class="citizen-photo-actions">
                <button class="btn-secondary" id="photo-confirm-cancel" type="button" autofocus>Cancel</button>
                <button class="btn-primary" id="photo-confirm-save" type="button">Confirm change</button>
            </div>
        </dialog>
        <script src="<?php echo e(asset('js/citizen-photo-settings.js')); ?>"></script>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/bhw/partials/citizen-photo.blade.php ENDPATH**/ ?>