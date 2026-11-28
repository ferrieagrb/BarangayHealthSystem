@extends(auth()->user()->isSuperAdmin() ? 'templates.superadmin' : (auth()->user()->role === 'admin' ? 'templates.admin' : 'templates.layout'))
@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/account-settings.css') }}?v={{ filemtime(public_path('css/account-settings.css')) }}">
@endsection
@section('content')
@php($account = auth()->user())
<main class="account-settings" aria-labelledby="settingsTitle">
    <header class="account-settings-heading">
        <div><p class="settings-eyebrow">YOUR WORKSPACE</p><h1 id="settingsTitle">Account settings</h1><p>Make yourself at home. Manage your display, notifications, and account security.</p></div>
        <span class="settings-private">@include('partials.settings-icon', ['name' => 'user-circle']) Personal preferences</span>
    </header>
    <section class="settings-profile" aria-label="Your account">
        <div class="settings-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($account->name, 0, 1)) }}</div>
        <div class="settings-profile-info"><h2>{{ $account->name }}</h2><p>{{ $account->email ?: 'No email address on file' }}</p></div>
        <span class="settings-badge settings-role">{{ $account->isBhw() ? 'Barangay Health Worker' : ($account->isSuperAdmin() ? 'Super Administrator' : 'Administrator') }}</span>
    </section>
    <nav class="settings-navigation" aria-label="Settings sections">
        <a href="#display-settings">@include('partials.settings-icon', ['name' => 'palette']) Appearance</a>
        <a href="#notification-settings">@include('partials.settings-icon', ['name' => 'bell']) Notifications &amp; devices</a>
        <a href="#security-settings">@include('partials.settings-icon', ['name' => 'shield-quarter']) Security</a>
    </nav>
    @if(session('success') || session('password_success'))
        <div class="settings-feedback settings-feedback-success" role="status">{{ session('password_success') ?: session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="settings-feedback settings-feedback-error" role="alert">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="settings-feedback settings-feedback-error" role="alert"><strong>Please check your changes.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="settings-columns">
        <div class="settings-column">
            <div id="display-settings" class="settings-anchor">@include('partials.appearance-settings', ['appearanceFeedbackHandled' => true])</div>
            <div id="notification-settings" class="settings-anchor">@include('partials.push-notification-settings')</div>
        </div>
        <div class="settings-column settings-anchor" id="security-settings">
            <section class="settings-card password-card" aria-labelledby="passwordHeading">
                <div class="settings-card-header"><span class="settings-section-icon" aria-hidden="true">@include('partials.settings-icon', ['name' => 'lock-alt'])</span><div><h2 id="passwordHeading">Password &amp; security</h2><p>Keep your account protected with a strong password.</p></div></div>
                <form action="{{ route('user.password.update') }}" method="POST" class="settings-password-form">
                    @csrf
                    @method('PUT')
                    <div class="settings-field">
                        <label for="current_password">Current password</label>
                        <input type="password" name="current_password" id="current_password" autocomplete="current-password" required placeholder="Enter your current password" @error('current_password') aria-invalid="true" aria-describedby="current-password-error" @enderror>
                        @error('current_password')<p id="current-password-error" class="settings-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="settings-field">
                        <label for="password">New password</label>
                        <input type="password" name="password" id="password" autocomplete="new-password" minlength="8" required placeholder="Create a new password" aria-describedby="password-hint @error('password') password-error @enderror" @error('password') aria-invalid="true" @enderror>
                        <p class="settings-hint" id="password-hint">Use at least 8 characters. A mix of words, numbers, and symbols is best.</p>
                        @error('password')<p id="password-error" class="settings-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="settings-field"><label for="password_confirmation">Confirm new password</label><input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" minlength="8" required placeholder="Re-enter your new password"></div>
                    <div class="settings-form-footer"><button type="submit" class="settings-primary">Update Password</button></div>
                </form>
            </section>
            <section class="settings-card" aria-labelledby="accountSecurityHeading">
                <div class="settings-card-header"><span class="settings-section-icon" aria-hidden="true">@include('partials.settings-icon', ['name' => 'shield-quarter'])</span><div><h2 id="accountSecurityHeading">Sign-in protection</h2><p>Review how your account is verified.</p></div></div>
                <div class="settings-security-item">
                    <div class="settings-security-label"><h3>Email address</h3><span class="settings-badge {{ $account->hasVerifiedEmail() ? 'settings-badge-success' : 'settings-badge-warning' }}">{{ $account->hasVerifiedEmail() ? 'Verified' : 'Unverified' }}</span></div>
                    <p class="settings-email">{{ $account->email ?: 'Ask an administrator to add your email address.' }}</p>
                    @if(!$account->hasVerifiedEmail() && $account->email)
                        <form method="POST" action="{{ route('verification.send') }}">@csrf<button type="submit" class="settings-secondary">Send Verification Email</button></form>
                    @endif
                </div>
                <div class="settings-security-item">
                    <div class="settings-security-label"><h3>Email sign-in confirmation</h3><span class="settings-badge {{ $account->email ? 'settings-badge-success' : 'settings-badge-warning' }}">{{ $account->email ? 'Required' : 'Email needed' }}</span></div>
                    <p>Every sign-in requires your password, a Yes confirmation from your email, and a 6-digit code.</p>
                </div>
            </section>
            <p class="settings-footnote">@include('partials.settings-icon', ['name' => 'info-circle']) Changes here apply to your account. Contact an administrator to update your name or email.</p>
        </div>
    </div>
</main>
@endsection
