<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Barangay Amuyong</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@include('partials.favicon')
@include('partials.button-action-assets')
</head>
<body id="appActionControls">

    <div class="login-card">
        <div class="top-nav">
            <a href="{{ route('login') }}" class="back-home">
                <i class="fa-solid fa-arrow-left"></i> Back to Sign In
            </a>
        </div>

        <div class="welcome-header">
            <img src="{{ \App\Models\SystemSetting::logoUrl() }}" alt="Barangay Amuyong Logo" class="logo" height="100" width="100">
            <h2>Barangay Amuyong<br><span style="font-size: 1.1rem; font-weight: 500; color: #64748b;">Health Information System</span></h2>
            <p>Enter your email and we'll send you a link to reset your password</p>
        </div>

        <form action="{{ route('password.email') }}" method="POST">
            @csrf

            @if (session('status'))
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

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
                    <input type="email" id="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </div>
            </div>

            <button type="submit" class="submit-btn" data-ui-action="primary">
                <span>Send Reset Link</span>
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>

        <div class="portal-footer">
            <p>&copy; 2026 Barangay Amuyong. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
