@extends('templates.layout')

@section('CSSown')
<style>
    .settings-container {
        padding: 30px;
        max-width: 800px;
        margin: 0 auto;
    }
    .settings-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 25px;
        transition: background 0.3s, border-color 0.3s;
    }
    .settings-header {
        background: #2563eb;
        color: white;
        padding: 18px 25px;
        font-size: 1.15rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .settings-body {
        padding: 25px 30px;
    }
    .form-group-custom {
        margin-bottom: 20px;
    }
    .form-group-custom label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        transition: color 0.3s;
    }
    .form-group-custom select,
    .form-group-custom input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.95rem;
        background-color: #f9fafb;
        color: #1f2937;
        transition: all 0.2s ease;
    }
    .form-group-custom select:focus,
    .form-group-custom input:focus {
        border-color: #2563eb;
        background-color: #fff;
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    .form-hint {
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 6px;
        transition: color 0.3s;
    }
    .btn-save {
        background: #2563eb;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-save:hover {
        background: #1d4ed8;
    }
    .alert-custom {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }
    .alert-success-custom {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .alert-danger-custom {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    /* ==========================================
       DARK MODE OVERRIDES FOR SETTINGS
       ========================================== */
    body.theme-dark .settings-card {
        background: #1e1e1e !important;
        border-color: #333333 !important;
    }
    body.theme-dark .form-group-custom label {
        color: #e5e7eb !important;
    }
    body.theme-dark .form-group-custom select,
    body.theme-dark .form-group-custom input {
        background-color: #2d2d2d !important;
        border-color: #404040 !important;
        color: #ffffff !important;
    }
    body.theme-dark .form-hint {
        color: #9ca3af !important;
    }

    body.theme-dark .security-row {
        border-color: #333333 !important;
    }
    body.theme-dark .security-label {
        color: #e5e7eb !important;
    }
    body.theme-dark .security-hint {
        color: #9ca3af !important;
    }
</style>
@endsection

@section('content')
<div class="settings-container">
    
    {{-- CARD 1: PREFERENCES SETTINGS --}}
    <div class="settings-card">
        <div class="settings-header">
            <i class='bx bx-slider-alt'></i> Appearance & Accessibility Preferences
        </div>
        
        <div class="settings-body">
            @if(session('success'))
                <div class="alert-custom alert-success-custom">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('user.preferences.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group-custom">
                    <label for="theme">Appearance Theme</label>
                    <select name="theme" id="theme">
                        <option value="light" {{ auth()->user()->theme === 'light' ? 'selected' : '' }}>Light Mode</option>
                        <option value="dark" {{ auth()->user()->theme === 'dark' ? 'selected' : '' }}>Dark Mode</option>
                    </select>
                    <div class="form-hint">Choose your preferred visual theme for the system interface.</div>
                </div>

                <div class="form-group-custom">
                    <label for="font_size">Font Size Accessibility</label>
                    <select name="font_size" id="font_size">
                        <option value="normal" {{ auth()->user()->font_size === 'normal' ? 'selected' : '' }}>Normal (Default)</option>
                        <option value="large" {{ auth()->user()->font_size === 'large' ? 'selected' : '' }}>Large</option>
                        <option value="xl" {{ auth()->user()->font_size === 'xl' ? 'selected' : '' }}>Extra Large (XL)</option>
                    </select>
                    <div class="form-hint">Adjust text scaling across your dashboard for improved readability.</div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 25px;">
                    <button type="submit" class="btn-save">Save Preferences</button>
                </div>
            </form>
        </div>
    </div>

    {{-- CARD 2: SECURITY & PASSWORD SETTINGS --}}
    <div class="settings-card">
        <div class="settings-header" style="background: #0f172a;">
            <i class='bx bx-lock-alt'></i> Security & Password Management
        </div>
        
        <div class="settings-body">
            @if(session('password_success'))
                <div class="alert-custom alert-success-custom">
                    {{ session('password_success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-custom alert-danger-custom">
                    <ul class="mb-0" style="padding-left: 15px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('user.password.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group-custom">
                    <label for="current_password">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required placeholder="Enter current password">
                </div>

                <div class="form-group-custom">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" required placeholder="At least 8 characters">
                </div>

                <div class="form-group-custom">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Re-enter new password">
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 25px;">
                    <button type="submit" class="btn-save" style="background: #0f172a;">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <div class="settings-card">
        <div class="settings-header" style="background: #047857;">
            <i class='bx bx-shield-quarter'></i> Account Status & Two-Factor Authentication
        </div>
        
        <div class="settings-body">
            <div style="display: flex; flex-direction: column; gap: 16px;">
                
                {{-- Email Verification Status --}}
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;" class="security-row">
                    <div>
                        <strong style="display: block; color: #374151;" class="security-label">Email Address Verification</strong>
                        <span style="font-size: 0.85rem; color: #6b7280;" class="security-hint">{{ auth()->user()->email }}</span>
                    </div>
                    <div>
                        @if(auth()->user()->hasVerifiedEmail())
                            <span style="background: #ecfdf5; color: #065f46; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #a7f3d0;">
                                <i class='bx bx-check-circle'></i> Verified
                            </span>
                        @else
                            <span style="background: #fef2f2; color: #991b1b; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #fecaca;">
                                <i class='bx bx-error-circle'></i> Unverified
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Google Authenticator / 2FA Status --}}
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="display: block; color: #374151;" class="security-label">Google Authenticator (2FA)</strong>
                        <span style="font-size: 0.85rem; color: #6b7280;" class="security-hint">Protects your account with time-based verification codes.</span>
                    </div>
                    <div>
                        {{-- Change 'google2fa_secret' or your specific 2FA column name depending on your database schema --}}
                        @if(!empty(auth()->user()->google2fa_secret))
                            <span style="background: #ecfdf5; color: #065f46; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #a7f3d0;">
                                <i class='bx bx-shield-alt-2'></i> Active
                            </span>
                        @else
                            <span style="background: #f3f4f6; color: #4b5563; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #d1d5db;">
                                <i class='bx bx-shield-x'></i> Inactive
                            </span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection