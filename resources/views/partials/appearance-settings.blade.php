<section class="settings-card appearance-card" aria-labelledby="appearanceHeading">
    <div class="settings-card-header">
        <h2 id="appearanceHeading">Appearance &amp; Accessibility</h2>
        <p>Preview your personal display preferences. Changes apply only to your account when saved.</p>
    </div>
    @if(session('success') && !($appearanceFeedbackHandled ?? false))
        <p role="status" class="appearance-feedback">{{ session('success') }}</p>
    @endif
    @if($errors->has('theme') || $errors->has('font_size'))
        <p role="alert">{{ $errors->first('theme') ?: $errors->first('font_size') }}</p>
    @endif
    <form action="{{ route('user.preferences.update') }}" method="POST" data-appearance-form>
        @csrf
        @method('PUT')
        <div class="appearance-fields">
            <label class="field" for="theme"><span>Appearance theme</span>
                <select name="theme" id="theme">
                    <option value="light" @selected(auth()->user()->theme !== 'dark')>Light Mode</option>
                    <option value="dark" @selected(auth()->user()->theme === 'dark')>Dark Mode</option>
                </select>
            </label>
            <label class="field" for="font_size"><span>Text size</span>
                <select name="font_size" id="font_size">
                    <option value="normal" @selected(!in_array(auth()->user()->font_size, ['large', 'xl']))>Normal (Default)</option>
                    <option value="large" @selected(auth()->user()->font_size === 'large')>Large</option>
                    <option value="xl" @selected(auth()->user()->font_size === 'xl')>Extra Large (XL)</option>
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
