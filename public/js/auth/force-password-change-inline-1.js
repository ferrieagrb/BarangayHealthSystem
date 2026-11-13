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
