    document.querySelectorAll('.js-reset-user').forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = document.getElementById('resetModal');
            document.getElementById('resetPasswordForm').action =
                `/superadmin/users/${encodeURIComponent(button.dataset.id)}/reset-password`;
            document.getElementById('reset_user_name').textContent = button.dataset.name || '';
            document.getElementById('reset_confirm_password').value = '';
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.getElementById('reset_confirm_password').focus();
        });
    });
