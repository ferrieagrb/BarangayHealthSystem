<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password Required</title>
    <!-- Links to your compiled home.css or layout styles -->
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background: #f2f3f8;">

    <div class="dashboard-card" style="width: 100%; max-width: 420px;">
        <div class="card-header">
            <h2>Security Update Required</h2>
            <p>Welcome! Since this is your temporary first login, please update your password to proceed to the system.</p>
        </div>

        @if ($errors->any())
            <div style="background: #fef2f2; color: #dc2626; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.force-update') }}" method="POST">
    @csrf
    <div style="margin-bottom: 15px;">
        <label>New Password</label>
        <input type="password" name="password" required>
    </div>

    <div style="margin-bottom: 20px;">
        <label>Confirm New Password</label>
        <input type="password" name="password_confirmation" required>
    </div>

    <button type="submit" class="btn-primary">Update Password & Continue</button>
</form>
    </div>

</body>
</html>