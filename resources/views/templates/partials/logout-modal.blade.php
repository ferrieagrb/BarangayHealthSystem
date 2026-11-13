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

<script src="{{ asset('js/templates/partials/logout-modal-inline-1.js') }}?v={{ filemtime(public_path('js/templates/partials/logout-modal-inline-1.js')) }}"></script>
