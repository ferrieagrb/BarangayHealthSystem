<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirm Password</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f1f5f9; font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; color: #0f172a; }
        .card { width: 100%; max-width: 420px; margin: 16px; padding: 28px; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(15, 23, 42, .12); }
        h1 { margin: 0 0 8px; font-size: 1.25rem; }
        p { margin: 0 0 18px; color: #475569; font-size: .92rem; line-height: 1.5; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: .9rem; }
        input { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; }
        .error { margin-top: 8px; color: #b91c1c; font-size: .88rem; }
        .actions { display: flex; gap: 10px; margin-top: 20px; }
        button, .cancel { flex: 1; padding: 10px 14px; border-radius: 8px; border: 0; font-size: .95rem; font-weight: 600; text-align: center; text-decoration: none; cursor: pointer; }
        button { background: #2563eb; color: #fff; }
        .cancel { background: #e2e8f0; color: #0f172a; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Confirm your password</h1>
        <p>This action involves sensitive citizen or system data. Please re-enter your own password to continue.</p>

        <form method="POST" action="{{ route('password.confirm.store') }}">
            @csrf
            <label for="password">Your password</label>
            <input id="password" type="password" name="password" required autofocus autocomplete="current-password">
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror

            <div class="actions">
                <a href="{{ route('landing') }}" class="cancel">Cancel</a>
                <button type="submit">Confirm</button>
            </div>
        </form>
    </main>
<script src="{{ asset('js/password-toggle.js') . '?v=1793722773' }}"></script>
</body>
</html>
