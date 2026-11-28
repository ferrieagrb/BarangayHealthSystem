<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Is This You? - BHEAMS</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    @include('partials.favicon')
</head>
<body>
    <main class="login-card">
        <div class="welcome-header"><h2>BHEAMS sign-in confirmation</h2></div>
        @if ($result === 'denied' || $challenge->denied_at)
            <div class="alert-success" role="status">This sign-in attempt has been blocked.</div>
            <p>If you did not try to sign in, reset your password now because someone entered your correct password.</p>
            <a class="back-home" href="{{ route('password.request') }}">Reset my password</a>
        @elseif ($result === 'unavailable' || ! $challenge->isPending())
            <div class="alert-error" role="alert">This request has expired, was replaced, or has already been used. Start a new sign-in if needed.</div>
        @elseif ($result === 'approved')
            <div class="alert-success" role="status">You confirmed this sign-in.</div>
            <p>Return to the browser where you entered your password and enter the 6-digit code from your email to finish.</p>
        @else
            <p>Is this you trying to sign in?</p>
            <p><strong>Time:</strong> {{ $challenge->created_at->copy()->timezone('Asia/Manila')->format('M j, Y g:i A') }} (Philippine time)</p>
            <p><strong>IP address:</strong> {{ $challenge->ip_address }}</p>
            <p><strong>Browser/device:</strong> {{ $challenge->user_agent }}</p>
            <form action="{{ request()->fullUrl() }}" method="POST">
                @csrf
                <button class="submit-btn" type="submit" name="decision" value="yes">Yes, this is me</button>
                <button class="submit-btn" type="submit" name="decision" value="no">No, this is not me</button>
            </form>
            <p>Choosing Yes still requires the code in your original sign-in browser. Choosing No blocks this attempt.</p>
        @endif
    </main>
</body>
</html>
