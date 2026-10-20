<style>
    .logout-confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 100000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .55);
    }

    .logout-confirm-modal.is-open {
        display: flex;
    }

    .logout-confirm-dialog {
        width: min(400px, 100%);
        padding: 24px;
        border-radius: 12px;
        background: #fff;
        color: #1e293b;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
    }

    .logout-confirm-dialog h2 {
        margin: 0 0 8px;
        font-size: 20px;
        font-weight: 600;
    }

    .logout-confirm-dialog p {
        margin: 0;
        color: #64748b;
        font-size: 14px;
    }

    .logout-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .logout-confirm-actions button {
        border: 0;
        border-radius: 7px;
        padding: 9px 14px;
        cursor: pointer;
        font-size: 14px;
    }

    .logout-confirm-cancel {
        background: #e2e8f0;
        color: #334155;
    }

    .logout-confirm-submit {
        background: #dc2626;
        color: #fff;
    }
</style>

<div id="logoutConfirmModal" class="logout-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="logoutConfirmTitle" aria-hidden="true">
    <div class="logout-confirm-dialog">
        <h2 id="logoutConfirmTitle">Log out?</h2>
        <p>Are you sure you want to log out of your account?</p>
        <div class="logout-confirm-actions">
            <button type="button" class="logout-confirm-cancel">Cancel</button>
            <button type="button" class="logout-confirm-submit">Log out</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('logoutConfirmModal');
    const cancelButton = modal?.querySelector('.logout-confirm-cancel');
    const confirmButton = modal?.querySelector('.logout-confirm-submit');
    let pendingForm = null;

    if (!modal || !cancelButton || !confirmButton) return;

    function closeLogoutModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        pendingForm = null;
    }

    document.querySelectorAll('.logout-form, .citizen-sidebar-logout').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            pendingForm = form;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            cancelButton.focus();
        });
    });

    cancelButton.addEventListener('click', closeLogoutModal);
    confirmButton.addEventListener('click', function () {
        if (!pendingForm) return;
        confirmButton.disabled = true;
        pendingForm.submit();
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeLogoutModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeLogoutModal();
        }
    });
});
</script>
