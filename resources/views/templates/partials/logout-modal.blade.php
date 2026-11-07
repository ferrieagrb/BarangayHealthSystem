<style>
    .app-confirm-modal {
        width: 100%;
        max-width: none;
        height: 100%;
        max-height: none;
        margin: 0;
        padding: 20px;
        box-sizing: border-box;
        border: 0;
        overflow: auto;
        background: transparent;
        color: inherit;
    }

    .app-confirm-modal[open] {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .app-confirm-modal::backdrop {
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(3px);
    }

    .app-confirm-dialog {
        width: min(400px, 100%);
        padding: 24px;
        border-radius: 12px;
        background: #fff;
        color: #1e293b;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
    }

    .app-confirm-dialog h2 {
        margin: 0 0 8px;
        font-size: 20px;
        font-weight: 600;
    }

    .app-confirm-dialog p {
        margin: 0;
        color: #64748b;
        font-size: 14px;
    }

    .app-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .app-confirm-actions button {
        border: 0;
        border-radius: 7px;
        padding: 9px 14px;
        cursor: pointer;
        font-size: 14px;
    }

    .app-confirm-cancel {
        background: #e2e8f0;
        color: #334155;
    }

    .app-confirm-submit {
        background: #dc2626;
        color: #fff;
    }
</style>

<dialog id="appConfirmModal" class="app-confirm-modal" aria-labelledby="appConfirmTitle">
    <div class="app-confirm-dialog">
        <h2 id="appConfirmTitle">Please confirm</h2>
        <p id="appConfirmMessage"></p>
        <div class="app-confirm-actions">
            <button type="button" class="app-confirm-cancel" data-ui-action="secondary">Cancel</button>
            <button type="button" class="app-confirm-submit" data-ui-action="danger">Confirm</button>
        </div>
    </div>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('appConfirmModal');
    const title = document.getElementById('appConfirmTitle');
    const message = document.getElementById('appConfirmMessage');
    const cancelButton = modal?.querySelector('.app-confirm-cancel');
    const confirmButton = modal?.querySelector('.app-confirm-submit');
    const confirmedForms = new WeakSet();
    let resolveConfirmation = null;

    if (!modal || !cancelButton || !confirmButton) return;

    function closeConfirmation(confirmed) {
        if (modal.open) modal.close();
        if (resolveConfirmation) {
            const resolve = resolveConfirmation;
            resolveConfirmation = null;
            resolve(confirmed);
        }
    }

    window.showConfirmation = function (prompt, options = {}) {
        if (resolveConfirmation) closeConfirmation(false);
        title.textContent = options.title || 'Please confirm';
        message.textContent = prompt;
        confirmButton.textContent = options.confirmText || 'Confirm';
        confirmButton.dataset.uiAction = options.danger === false ? 'primary' : 'danger';
        modal.showModal();
        cancelButton.focus();

        return new Promise(function (resolve) {
            resolveConfirmation = resolve;
        });
    };

    cancelButton.addEventListener('click', function () {
        closeConfirmation(false);
    });

    confirmButton.addEventListener('click', function () {
        closeConfirmation(true);
    });

    modal.addEventListener('cancel', function (event) {
        event.preventDefault();
        closeConfirmation(false);
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeConfirmation(false);
    });

    document.addEventListener('submit', function (event) {
        const form = event.target;
        const prompt = form instanceof HTMLFormElement
            ? (form.dataset.confirmMessage || event.submitter?.dataset.confirmMessage)
            : null;
        if (!prompt) return;
        if (confirmedForms.has(form)) {
            confirmedForms.delete(form);
            return;
        }

        event.preventDefault();
        const submitter = event.submitter;
        window.showConfirmation(prompt, {
            title: form.dataset.confirmTitle,
            confirmText: form.dataset.confirmText,
            danger: form.dataset.confirmDanger !== 'false'
        }).then(function (confirmed) {
            if (!confirmed || !form.isConnected) return;
            confirmedForms.add(form);
            if (submitter && submitter.form === form) {
                form.requestSubmit(submitter);
            } else {
                form.requestSubmit();
            }
            confirmedForms.delete(form);
        });
    });

    document.querySelectorAll('.logout-form, .citizen-sidebar-logout').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            window.showConfirmation('Are you sure you want to log out of your account?', {
                title: 'Log out?',
                confirmText: 'Log out'
            }).then(function (confirmed) {
                if (confirmed) form.submit();
            });
        });
    });
});
</script>
