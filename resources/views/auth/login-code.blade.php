<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Sign In - BHEAMS</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    @include('partials.favicon')
</head>
<body>
    <main class="login-card">
        <div class="welcome-header">
            <h2>Check your email</h2>
            <p>We sent a sign-in confirmation and a 6-digit code to <strong>{{ $email }}</strong>.</p>
            <p>Open the email link and choose <strong>Yes, this is me</strong>. Then return here and enter your code within 10 minutes.</p>
        </div>
        @if ($approved)
            <div class="alert-success" role="status">Email confirmation received. Enter your code to finish signing in.</div>
        @endif
        @if (session('status'))
            <div class="alert-success" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert-error" role="alert">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('login.confirmation.verify') }}" method="POST">
            @csrf
            <div class="input-group">
                <label for="code">Verification code</label>
                <div class="input-field-wrapper">
                    <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="6-digit code" required autofocus>
                </div>
            </div>
            <button class="submit-btn" type="submit">Verify and Sign In</button>
        </form>
        <p>Check your spam folder if the email has not arrived. You can request a new email after 60 seconds.</p>
        <form action="{{ route('login.confirmation.resend') }}" method="POST">
            @csrf
            <button class="submit-btn" type="submit">Resend Email and Code</button>
        </form>
        <form action="{{ route('login.confirmation.cancel') }}" method="POST">
            @csrf
            <button class="submit-btn" type="submit">Cancel Sign In</button>
        </form>
    </main>
</body>
</html>
