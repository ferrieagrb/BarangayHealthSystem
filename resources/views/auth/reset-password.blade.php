<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Barangay Amuyong</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@include('partials.favicon')
@include('partials.button-action-assets')
</head>
<body id="appActionControls">
@include('partials.global-loader')

    <div class="login-card">
        <div class="top-nav">
            <a href="{{ route('login') }}" class="back-home">
                <i class="fa-solid fa-arrow-left"></i> Back to Sign In
            </a>
        </div>

        <div class="welcome-header">
            <img src="{{ \App\Models\SystemSetting::logoUrl() }}" alt="Barangay Amuyong Logo" class="logo" height="100" width="100">
            <h2>Barangay Amuyong<br><span style="font-size: 1.1rem; font-weight: 500; color: #64748b;">Health Information System</span></h2>
            <p>Choose a new password for your account</p>
        </div>

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            @if ($errors->any())
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="input-group">
                <label for="email">Email</label>
                <div class="input-field-wrapper">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
                </div>
            </div>

            <div class="input-group">
                <label for="password">New Password</label>
                <div class="input-field-wrapper">
                    <i class="fa-lock fa-solid"></i>
                    <input type="password" id="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required>
                </div>
            </div>

            <div class="input-group">
                <label for="password_confirmation">Confirm New Password</label>
                <div class="input-field-wrapper">
                    <i class="fa-lock fa-solid"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Re-enter your new password" autocomplete="new-password" minlength="8" required>
                </div>
            </div>

            <button type="submit" class="submit-btn" data-ui-action="primary">
                <span>Reset Password</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="portal-footer">
            <p>&copy; 2026 Barangay Amuyong. All rights reserved.</p>
        </div>
    </div>

<script src="{{ asset('js/password-toggle.js') . '?v=1793722773' }}"></script>
</body>
</html>
