<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password Required</title>

    <!-- Existing CSS -->
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">

    <!-- Additional Password Reset UI Styles -->
    <style>
        :root {
            --primary-color: #173B7C;
            --primary-hover: #102D61;
            --background-color: #F3F6FC;
            --text-color: #1E293B;
            --muted-color: #64748B;
            --border-color: #DCE3EF;
            --white: #FFFFFF;
            --success-color: #15803D;
            --danger-color: #DC2626;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 16px;
            background:
                radial-gradient(
                    circle at top left,
                    rgba(23, 59, 124, 0.09),
                    transparent 35%
                ),
                linear-gradient(135deg, #F7F9FE 0%, #EAF0FA 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            color: var(--text-color);
        }

        .password-page {
            width: 100%;
            max-width: 470px;
        }

        .password-card {
            width: 100%;
            padding: 38px 36px 32px;
            background: var(--white);
            border: 1px solid rgba(220, 227, 239, 0.85);
            border-radius: 18px;
            box-shadow: 0 18px 55px rgba(23, 59, 124, 0.10);
            animation: cardFadeIn 0.5s ease;
        }

        .security-icon-wrapper {
            width: 76px;
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            border-radius: 22px;
            background: #EDF3FF;
            color: var(--primary-color);
        }

        .security-icon-wrapper svg {
            width: 40px;
            height: 40px;
        }

        .card-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            margin-bottom: 16px;
            border-radius: 50px;
            background: #EDF3FF;
            color: var(--primary-color);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .card-header h2 {
            margin-bottom: 12px;
            color: var(--text-color);
            font-size: 25px;
            font-weight: 750;
            line-height: 1.3;
        }

        .card-header p {
            max-width: 340px;
            margin: 0 auto;
            color: var(--muted-color);
            font-size: 13.5px;
            line-height: 1.8;
        }

        .password-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 21px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            color: #334155;
            font-size: 13px;
            font-weight: 650;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper input {
            display: block;
            width: 100%;
            height: 49px;
            padding: 0 48px 0 15px;
            border: 1px solid var(--border-color);
            border-radius: 9px;
            outline: none;
            background: #FFFFFF;
            color: var(--text-color);
            font-family: inherit;
            font-size: 13px;
            transition: border-color 0.2s ease,
                        box-shadow 0.2s ease;
        }

        .input-wrapper input::placeholder {
            color: #A0AEC0;
        }

        .input-wrapper input:hover {
            border-color: #AABBD6;
        }

        .input-wrapper input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(23, 59, 124, 0.10);
        }

        .input-wrapper input.is-invalid {
            border-color: var(--danger-color);
        }

        .toggle-password {
            position: absolute;
            right: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border: none;
            background: transparent;
            color: #8491A5;
            cursor: pointer;
            border-radius: 5px;
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        .toggle-password:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        .toggle-password svg {
            width: 19px;
            height: 19px;
        }

        .password-hint {
            display: block;
            margin-top: 8px;
            color: var(--muted-color);
            font-size: 11.5px;
            line-height: 1.6;
        }

        /* Live password suggestions shown below the New Password field */
        .password-suggestions {
            display: none;
            margin-top: 10px;
            padding: 12px 14px;
            border: 1px solid #E6EBF4;
            border-radius: 9px;
            background: #F8FAFE;
        }

        .password-suggestions.visible {
            display: block;
        }

        .password-suggestions-title {
            margin-bottom: 7px;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
        }

        .password-suggestions-list {
            margin: 0;
            padding-left: 18px;
            color: #64748B;
            font-size: 11.5px;
            line-height: 1.8;
        }

        .password-suggestions-list li {
            padding-left: 2px;
        }

        .password-suggestions.success {
            border-color: #BBF7D0;
            background: #F0FDF4;
        }

        .password-suggestions.success .password-suggestions-title {
            color: #15803D;
        }

        .password-suggestions.success .password-suggestions-list {
            color: #15803D;
            list-style: none;
            padding-left: 0;
        }

        .error-message {
            padding: 13px 15px;
            margin-bottom: 22px;
            border: 1px solid #FECACA;
            border-radius: 9px;
            background: #FEF2F2;
            color: #B91C1C;
            font-size: 12.5px;
            line-height: 1.7;
        }

        .error-heading {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .error-message ul {
            padding-left: 20px;
        }

        .password-mismatch {
            display: none;
            margin-top: 8px;
            color: var(--danger-color);
            font-size: 12px;
        }

        .password-match {
            display: none;
            margin-top: 8px;
            color: var(--success-color);
            font-size: 12px;
        }

        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 49px;
            padding: 13px 18px;
            margin-top: 7px;
            border: none;
            border-radius: 9px;
            background: var(--primary-color);
            color: #FFFFFF;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s ease,
                        transform 0.2s ease,
                        box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            box-shadow: 0 7px 18px rgba(23, 59, 124, 0.18);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:focus-visible {
            outline: 3px solid rgba(23, 59, 124, 0.25);
            outline-offset: 3px;
        }

        .btn-primary svg {
            width: 17px;
            height: 17px;
        }

        .security-note {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            gap: 8px;
            margin-top: 23px;
            padding-top: 20px;
            border-top: 1px solid #EDF0F6;
            color: #718096;
            font-size: 11.5px;
            line-height: 1.7;
            text-align: center;
        }

        .security-note svg {
            flex-shrink: 0;
            width: 16px;
            height: 16px;
            margin-top: 1px;
            color: var(--primary-color);
        }

        .footer-text {
            margin-top: 20px;
            color: #94A3B8;
            font-size: 11px;
            text-align: center;
        }

        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 20px 14px;
            }

            .password-card {
                padding: 30px 23px 25px;
                border-radius: 15px;
            }

            .security-icon-wrapper {
                width: 66px;
                height: 66px;
                margin-bottom: 18px;
                border-radius: 19px;
            }

            .security-icon-wrapper svg {
                width: 35px;
                height: 35px;
            }

            .card-header h2 {
                font-size: 22px;
            }

            .card-header p {
                font-size: 12.5px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>

    <main class="password-page">

        <div class="dashboard-card password-card">

            <!-- Security Icon -->
            <div class="security-icon-wrapper" aria-hidden="true">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <rect x="4" y="10" width="16" height="11" rx="2"></rect>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                    <path d="M12 14v3"></path>
                </svg>
            </div>

            <div class="card-header">

                <div class="security-badge">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                    Account Security
                </div>

                <!-- Original Heading -->
                <h2>Security Update Required</h2>

                <!-- Original Description -->
                <p>
                    Welcome! Since this is your temporary first login,
                    please update your password to proceed to the system.
                </p>

            </div>

            <!-- Original Laravel Validation -->
            @if ($errors->any())
                <div class="error-message" role="alert">

                    <div class="error-heading">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="17"
                            height="17"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 8v4"></path>
                            <path d="M12 16h.01"></path>
                        </svg>
                        Please check the following:
                    </div>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>
            @endif

            <!-- Original Laravel Form and Route -->
            <form
                action="{{ route('password.force-update') }}"
                method="POST"
                class="password-form"
                id="passwordUpdateForm"
            >
                @csrf

                <!-- New Password -->
                <div class="form-group">

                    <label for="password">New Password</label>

                    <div class="input-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your new password"
                            autocomplete="new-password"
                            required
                            aria-describedby="password-hint"
                            @if ($errors->has('password')) aria-invalid="true" @endif
                            class="@if ($errors->has('password')) is-invalid @endif"
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password"
                            aria-label="Show new password"
                            aria-pressed="false"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>

                    </div>

                    <small class="password-hint" id="password-hint">
                        Choose a strong password that you have not used before.
                    </small>

                    <!-- Real-time password suggestions -->
                    <div
                        class="password-suggestions"
                        id="password-suggestions"
                        aria-live="polite"
                        aria-atomic="true"
                    >
                        <p class="password-suggestions-title" id="password-suggestions-title">
                            To make your password stronger, add:
                        </p>
                        <ul class="password-suggestions-list" id="password-suggestions-list"></ul>
                    </div>

                </div>

                <!-- Confirm New Password -->
                <div class="form-group">

                    <label for="password_confirmation">
                        Confirm New Password
                    </label>

                    <div class="input-wrapper">

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Re-enter your new password"
                            autocomplete="new-password"
                            required
                            aria-describedby="password-match-message"
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password_confirmation"
                            aria-label="Show confirm password"
                            aria-pressed="false"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>

                    </div>

                    <small
                        class="password-mismatch"
                        id="password-match-message"
                        role="status"
                        aria-live="polite"
                    >
                        Passwords do not match.
                    </small>

                    <small
                        class="password-match"
                        id="password-match-success"
                        role="status"
                        aria-live="polite"
                    >
                        Passwords match.
                    </small>

                </div>

                <!-- Original Submit Button -->
                <button type="submit" class="btn-primary">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>

                    Update Password &amp; Continue

                </button>

            </form>

            <!-- Security Reminder -->
            <div class="security-note">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <rect x="4" y="10" width="16" height="11" rx="2"></rect>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                </svg>

                <span>
                    Your account security matters. Keep your password private
                    and never share it with anyone.
                </span>

            </div>

        </div>

        <p class="footer-text">
            Secure Access &bull; Authorized Users Only
        </p>

    </main>

    <!-- Password Visibility and Matching -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');

            const mismatchMessage = document.getElementById('password-match-message');
            const matchMessage = document.getElementById('password-match-success');

            const passwordSuggestions = document.getElementById('password-suggestions');
            const passwordSuggestionsTitle = document.getElementById('password-suggestions-title');
            const passwordSuggestionsList = document.getElementById('password-suggestions-list');

            // Give the user real-time suggestions based on what is missing.
            function updatePasswordRequirements() {
                const password = passwordInput.value;

                const missingRequirements = [];

                if (password.length < 8) {
                    missingRequirements.push('Add at least 8 characters.');
                }
                if (!/[A-Z]/.test(password)) {
                    missingRequirements.push('Add at least one uppercase letter (A–Z).');
                }
                if (!/[a-z]/.test(password)) {
                    missingRequirements.push('Add at least one lowercase letter (a–z).');
                }
                if (!/[0-9]/.test(password)) {
                    missingRequirements.push('Add at least one number (0–9).');
                }
                if (!/[^A-Za-z0-9\s]/.test(password)) {
                    missingRequirements.push('Add at least one special character (e.g. ! @ # $ %).');
                }

                passwordSuggestionsList.replaceChildren();

                if (password.length === 0) {
                    passwordSuggestions.classList.remove('visible', 'success');
                    passwordInput.setCustomValidity('');
                    passwordInput.classList.remove('is-invalid');
                    return false;
                }

                passwordSuggestions.classList.add('visible');

                if (missingRequirements.length > 0) {
                    passwordSuggestions.classList.remove('success');
                    passwordSuggestionsTitle.textContent = 'To make your password stronger, add:';

                    missingRequirements.forEach(function (suggestion) {
                        const item = document.createElement('li');
                        item.textContent = suggestion;
                        passwordSuggestionsList.appendChild(item);
                    });

                    passwordInput.setCustomValidity(
                        'Please follow the password suggestions shown below.'
                    );
                    passwordInput.classList.add('is-invalid');
                    return false;
                }

                passwordSuggestions.classList.add('success');
                passwordSuggestionsTitle.textContent = 'Strong password';

                const successItem = document.createElement('li');
                successItem.textContent = 'Your password meets all the requirements.';
                passwordSuggestionsList.appendChild(successItem);

                passwordInput.setCustomValidity('');
                passwordInput.classList.remove('is-invalid');
                return true;
            }

            // Show or hide passwords
            document.querySelectorAll('.toggle-password').forEach(function (button) {

                button.addEventListener('click', function () {

                    const targetId = button.getAttribute('data-target');
                    const input = document.getElementById(targetId);

                    if (!input) {
                        return;
                    }

                    const isPassword = input.type === 'password';

                    input.type = isPassword ? 'text' : 'password';

                    button.setAttribute(
                        'aria-label',
                        isPassword
                            ? 'Hide ' + (targetId === 'password'
                                ? 'new password'
                                : 'confirm password')
                            : 'Show ' + (targetId === 'password'
                                ? 'new password'
                                : 'confirm password')
                    );

                    button.setAttribute('aria-pressed', String(isPassword));

                });

            });

            // Check whether both passwords match
            function checkPasswordMatch() {

                const password = passwordInput.value;
                const confirmation = confirmInput.value;

                mismatchMessage.style.display = 'none';
                matchMessage.style.display = 'none';

                confirmInput.classList.remove('is-invalid');

                confirmInput.setCustomValidity('');

                if (confirmation.length === 0) {
                    return true;
                }

                if (password !== confirmation) {

                    mismatchMessage.style.display = 'block';

                    confirmInput.classList.add('is-invalid');

                    confirmInput.setCustomValidity(
                        'The confirmation password does not match.'
                    );

                    return false;

                }

                matchMessage.style.display = 'block';

                return true;

            }

            passwordInput.addEventListener('input', function () {
                updatePasswordRequirements();
                checkPasswordMatch();
            });

            confirmInput.addEventListener('input', checkPasswordMatch);

            // Prevent submission if the password rules are not met or passwords differ.
            document.getElementById('passwordUpdateForm')
                .addEventListener('submit', function (event) {

                    const requirementsMet = updatePasswordRequirements();
                    const passwordsMatch = checkPasswordMatch();

                    if (!requirementsMet) {
                        event.preventDefault();
                        passwordInput.reportValidity();
                        passwordInput.focus();
                        return;
                    }

                    if (!passwordsMatch) {
                        event.preventDefault();
                        confirmInput.reportValidity();
                        confirmInput.focus();
                    }

                });

        });
    </script>

</body>
</html>
