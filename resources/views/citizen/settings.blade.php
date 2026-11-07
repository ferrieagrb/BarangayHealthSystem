@extends('templates.citizen')

@section('CSSown')
<style>
    .citizen-settings {
        max-width: 900px;
        margin: 0 auto;
        padding: clamp(18px, 3vw, 36px);
        color: #172033;
    }

    .cs-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .cs-head h1 {
        margin: 0;
        color: #173b82;
        font-size: clamp(25px, 3vw, 32px);
        font-weight: 600;
    }

    .cs-head p {
        margin: 7px 0 0;
        color: #475569;
        font-size: 16px;
        line-height: 1.55;
    }

    .cs-back,
    .cs-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 11px 17px;
        border: 0;
        border-radius: 9px;
        background: #1d4ed8;
        color: #fff;
        text-decoration: none;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
    }

    .cs-card {
        margin-bottom: 20px;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 5px 18px #0f172a0a;
        overflow: hidden;
    }

    .cs-card h2 {
        margin: 0;
        padding: 18px 22px;
        background: #f1f5f9;
        color: #173b82;
        font-size: 19px;
        font-weight: 600;
    }

    .cs-body {
        padding: clamp(18px, 2.5vw, 26px);
    }

    .cs-field {
        margin-bottom: 20px;
    }

    .cs-field label {
        display: block;
        margin-bottom: 8px;
        color: #263449;
        font-size: 16px;
        font-weight: 500;
    }

    .cs-field input,
    .cs-field select {
        width: 100%;
        min-height: 48px;
        padding: 11px 13px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #172033;
        font-size: 16px;
        box-sizing: border-box;
    }

    .cs-hint {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.5;
    }

    .cs-photo {
        display: block;
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 16px;
    }

    .cs-photo-previews {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .cs-photo-previews figure,
    .cs-photo-compare figure {
        margin: 0;
    }

    .cs-photo-previews figcaption,
    .cs-photo-compare figcaption {
        margin-bottom: 8px;
        color: #475569;
        font-weight: 600;
    }

    .cs-photo-dialog {
        position: fixed;
        inset: 0;
        margin: auto;
        height: fit-content;
        width: min(520px, calc(100% - 32px));
        max-height: 90vh;
        overflow: auto;
        box-sizing: border-box;
        border: 0;
        border-radius: 14px;
        padding: 24px;
        color: #172033;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .3);
    }

    .cs-photo-dialog::backdrop {
        background: rgba(15, 23, 42, .65);
    }

    .cs-photo-dialog h2 {
        margin: 0;
        color: #173b82;
    }

    .cs-photo-compare {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: flex-start;
        gap: 32px;
        margin: 20px 0;
    }

    .cs-photo-compare figure {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 130px;
        text-align: center;
    }

    .cs-photo-compare img {
        margin: 0 auto;
    }

    .cs-photo-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .cs-photo-cancel {
        background: #e2e8f0;
        color: #263449;
    }

    .cs-alert {
        padding: 13px 16px;
        margin-bottom: 18px;
        border-radius: 9px;
        font-size: 15px;
        line-height: 1.5;
    }

    .cs-success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .cs-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .cs-security {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .cs-status {
        font-weight: 600;
        font-size: 15px;
    }

    .cs-status.ok {
        color: #047857;
    }

    .cs-status.pending {
        color: #b45309;
    }

    @media (max-width: 600px) {
        .citizen-settings {
            padding: 18px 13px;
        }

        .cs-head {
            align-items: stretch;
        }

        .cs-back,
        .cs-submit {
            width: 100%;
            box-sizing: border-box;
        }

        .cs-card h2 {
            padding: 16px;
            font-size: 18px;
        }
    }
</style>
@endsection

@section('content')
<main class="citizen-settings">

    {{-- Page Header --}}
    <header class="cs-head">
        <div>
            <h1>My Settings</h1>

            <p>
                Manage your photo, accessibility, account security, and email verification.
            </p>
        </div>

        <a
            class="cs-back"
            href="{{ route('citizen.dashboard') }}"
        >
            <i class="bx bx-arrow-back"></i>
            &nbsp; Return to Homepage
        </a>
    </header>

    {{-- Success Message --}}
    @if (session('success'))
        <div class="cs-alert cs-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    {{-- Password Success Message --}}
    @if (session('password_success'))
        <div class="cs-alert cs-success" role="status">
            {{ session('password_success') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="cs-alert cs-error" role="alert">
            <ul style="margin: 0; padding-left: 20px">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="cs-card">
        <h2><i class="bx bx-image"></i> My Photo</h2>
        <div class="cs-body">
            @if ($citizen)
                <div class="cs-photo-previews">
                    <figure>
                        <figcaption>Current photo</figcaption>
                        @if ($citizen->photo_path)
                            <img class="cs-photo" src="{{ route('citizen.photo', $citizen->id) }}" alt="Your current citizen photo">
                        @else
                            <p class="cs-hint">You do not have a photo yet.</p>
                        @endif
                    </figure>
                    <figure id="selected-photo-preview" hidden>
                        <figcaption>New photo preview</figcaption>
                        <img class="cs-photo" id="selected-photo-image" alt="Preview of your selected photo">
                    </figure>
                </div>
                <form id="citizen-photo-form" action="{{ route('citizen.photo.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="cs-field">
                        <label for="photo">Choose a new photo</label>
                        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" required aria-describedby="photo-hint">
                        <p class="cs-hint" id="photo-hint">JPG, PNG, or WEBP; maximum 2 MB. This photo appears on your health ID.</p>
                        <p class="cs-hint cs-error" id="photo-preview-error" role="alert" hidden></p>
                    </div>
                    <button class="cs-submit" type="submit">Update Photo</button>
                    <dialog class="cs-photo-dialog" id="photo-confirm-dialog" aria-labelledby="photo-confirm-title" aria-describedby="photo-confirm-description">
                        <h2 id="photo-confirm-title">Confirm photo change</h2>
                        <p class="cs-hint" id="photo-confirm-description">Review your new photo before saving. It will appear on your health ID.</p>
                        <div class="cs-photo-compare">
                            <figure>
                                <figcaption>Current photo</figcaption>
                                @if ($citizen->photo_path)
                                    <img class="cs-photo" src="{{ route('citizen.photo', $citizen->id) }}" alt="Your current citizen photo">
                                @else
                                    <p class="cs-hint">No current photo.</p>
                                @endif
                            </figure>
                            <figure>
                                <figcaption>New photo</figcaption>
                                <img class="cs-photo" id="confirm-photo-image" alt="Preview of your new citizen photo">
                            </figure>
                        </div>
                        <div class="cs-photo-actions">
                            <button class="cs-submit cs-photo-cancel" id="photo-confirm-cancel" type="button" autofocus>Cancel</button>
                            <button class="cs-submit" id="photo-confirm-save" type="button">Confirm change</button>
                        </div>
                    </dialog>
                </form>
            @else
                <p class="cs-hint">No citizen record is linked to your account. Please contact your barangay health worker.</p>
            @endif
        </div>
    </section>

    {{-- Appearance and Accessibility --}}
    <section class="cs-card">
        <h2>
            <i class="bx bx-universal-access"></i>
            Appearance &amp; Accessibility
        </h2>

        <div class="cs-body">
            <form
                data-appearance-form
                action="{{ route('user.preferences.update') }}"
                method="POST"
            >
                @csrf
                @method('PUT')

                <div class="cs-field">
                    <label for="theme">Appearance theme</label>

                    <select name="theme" id="theme">
                        <option
                            value="light"
                            @selected($user->theme === 'light')
                        >
                            Light mode
                        </option>

                        <option
                            value="dark"
                            @selected($user->theme === 'dark')
                        >
                            Dark mode
                        </option>
                    </select>

                    <p class="cs-hint">
                        Choose the display theme that is easiest for you to read.
                    </p>
                </div>

                <div class="cs-field">
                    <label for="font_size">Text size</label>

                    <select name="font_size" id="font_size">
                        <option
                            value="normal"
                            @selected($user->font_size === 'normal')
                        >
                            Small
                        </option>

                        <option
                            value="large"
                            @selected($user->font_size === 'large')
                        >
                            Medium
                        </option>

                        <option
                            value="xl"
                            @selected($user->font_size === 'xl')
                        >
                            Large
                        </option>
                    </select>

                    <p class="cs-hint">
                        Choose a text size that is comfortable to read.
                    </p>
                </div>

                <p class="cs-hint" data-appearance-status role="status" aria-live="polite">Your saved preferences are applied.</p>
                <button class="cs-submit appearance-cancel" type="reset">Cancel Preview</button>
                <button class="cs-submit" type="submit">
                    Save Preferences
                </button>
            </form>
        </div>
    </section>

    @include('partials.push-notification-settings')

    {{-- Change Password --}}
    <section class="cs-card">
        <h2>
            <i class="bx bx-lock-alt"></i>
            Change Password
        </h2>

        <div class="cs-body">
            <form
                action="{{ route('user.password.update') }}"
                method="POST"
            >
                @csrf
                @method('PUT')

                <div class="cs-field">
                    <label for="current_password">
                        Current password
                    </label>

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="cs-field">
                    <label for="password">New password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <p class="cs-hint">
                        Use at least 8 characters.
                    </p>
                </div>

                <div class="cs-field">
                    <label for="password_confirmation">
                        Confirm new password
                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button class="cs-submit" type="submit">
                    Update Password
                </button>
            </form>
        </div>
    </section>

    {{-- Email Account Verification --}}
    <section class="cs-card">
        <h2>
            <i class="bx bx-envelope"></i>
            Email Account Verification
        </h2>

        <div class="cs-body">
            <div class="cs-security">
                <div>
                    <strong style="font-size: 17px">
                        {{ $user->email }}
                    </strong>

                    <p class="cs-hint">
                        Verify that you own this email address to help protect
                        your account.
                    </p>
                </div>

                <span
                    class="cs-status {{ $user->hasVerifiedEmail() ? 'ok' : 'pending' }}"
                >
                    {{ $user->hasVerifiedEmail() ? '✓ Verified' : 'Not verified' }}
                </span>
            </div>

            @unless ($user->hasVerifiedEmail())
                <form
                    action="{{ route('verification.send') }}"
                    method="POST"
                    style="margin-top: 20px"
                >
                    @csrf

                    <button class="cs-submit" type="submit">
                        Send Verification Email
                    </button>
                </form>
            @endunless

            <p class="cs-hint" style="margin-top: 16px">
                This sends a signed verification link. It is email ownership
                verification, not a second-factor login code (2FA).
            </p>
        </div>
    </section>

</main>

<script src="{{ asset('js/citizen-photo-settings.js') }}"></script>
@endsection