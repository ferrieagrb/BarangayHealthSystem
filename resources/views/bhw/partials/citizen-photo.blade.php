@php
    $photoEditable = $canEdit && !(bool) $citizen->Citizen_IsDeceased;
    $photoVisible = $citizen->photo_path && ($canEdit || app(\App\Services\BhwCitizenAccess::class)->canViewAll(auth()->user()));
@endphp
<div class="citizen-photo-section">
    @if($photoEditable)
        <input id="photo" class="citizen-photo-input" name="photo" type="file" form="citizen-photo-form" accept="image/jpeg,image/png,image/webp" required disabled data-auto-confirm aria-label="Choose a citizen photo">
    @endif
    <label class="citizen-photo-frame" for="photo">
        @if($photoVisible)
            <img class="citizen-photo-image" src="{{ route('citizen.photo', $citizen->id) }}" alt="Citizen photo">
        @else
            <span class="citizen-photo-empty">{{ $citizen->photo_path ? 'Photo unavailable' : 'No photo' }}</span>
        @endif
        @if($photoEditable)
            <span class="citizen-photo-overlay">{{ $citizen->photo_path ? 'Change photo' : 'Add photo' }}</span>
        @endif
    </label>
    <button type="button" class="btn-secondary" id="openEditManualPicker" style="display:none;" data-ui-action="secondary">Assign without family</button>

    @if(session('photo_success'))
        <p class="citizen-photo-success" role="status">{{ session('photo_success') }}</p>
    @endif
    @error('photo')
        <p class="citizen-photo-error" role="alert">{{ $message }}</p>
    @enderror

    @if($photoEditable)
        <p id="photo-preview-error" class="citizen-photo-error" role="alert" hidden></p>

        <dialog class="citizen-photo-dialog" id="photo-confirm-dialog" aria-labelledby="photo-confirm-title" aria-describedby="photo-confirm-description">
            <h2 id="photo-confirm-title">Confirm photo change</h2>
            <p id="photo-confirm-description">Review the new photo before saving it to the citizen's health ID.</p>
            <div class="citizen-photo-previews">
                <figure>
                    <figcaption>Current photo</figcaption>
                    @if($citizen->photo_path)
                        <img class="citizen-photo-image" src="{{ route('citizen.photo', $citizen->id) }}" alt="Current citizen photo">
                    @else
                        <p>No current photo.</p>
                    @endif
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
        <script src="{{ asset('js/citizen-photo-settings.js') }}"></script>
    @endif
</div>
