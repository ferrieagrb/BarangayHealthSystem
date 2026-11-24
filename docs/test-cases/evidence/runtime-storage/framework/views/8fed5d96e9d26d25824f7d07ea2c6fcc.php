<section class="settings-card appearance-card" aria-labelledby="appearanceHeading">
    <div class="settings-card-header">
        <h2 id="appearanceHeading">Appearance &amp; Accessibility</h2>
        <p>Preview your personal display preferences. Changes apply only to your account when saved.</p>
    </div>
    <?php if(session('success') && !($appearanceFeedbackHandled ?? false)): ?>
        <p role="status" class="appearance-feedback"><?php echo e(session('success')); ?></p>
    <?php endif; ?>
    <?php if($errors->has('theme') || $errors->has('font_size')): ?>
        <p role="alert"><?php echo e($errors->first('theme') ?: $errors->first('font_size')); ?></p>
    <?php endif; ?>
    <form action="<?php echo e(route('user.preferences.update')); ?>" method="POST" data-appearance-form>
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="appearance-fields">
            <label class="field" for="theme"><span>Appearance theme</span>
                <select name="theme" id="theme">
                    <option value="light" <?php if(auth()->user()->theme !== 'dark'): echo 'selected'; endif; ?>>Light Mode</option>
                    <option value="dark" <?php if(auth()->user()->theme === 'dark'): echo 'selected'; endif; ?>>Dark Mode</option>
                </select>
            </label>
            <label class="field" for="font_size"><span>Text size</span>
                <select name="font_size" id="font_size">
                    <option value="normal" <?php if(!in_array(auth()->user()->font_size, ['large', 'xl'])): echo 'selected'; endif; ?>>Normal (Default)</option>
                    <option value="large" <?php if(auth()->user()->font_size === 'large'): echo 'selected'; endif; ?>>Large</option>
                    <option value="xl" <?php if(auth()->user()->font_size === 'xl'): echo 'selected'; endif; ?>>Extra Large (XL)</option>
                </select>
            </label>
        </div>
        <p class="appearance-preview-status" data-appearance-status role="status" aria-live="polite">Your saved preferences are applied.</p>
        <div class="appearance-actions">
            <button type="reset" class="btn appearance-cancel">Cancel Preview</button>
            <button type="submit" class="btn btn-primary">Save Preferences</button>
        </div>
    </form>
</section>
<?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/partials/appearance-settings.blade.php ENDPATH**/ ?>