@extends('templates.admin')

@section('content')

<style>

    .permissions-page,
    .permissions-page *,
    .permission-modal,
    .permission-modal * {
        box-sizing: border-box;
    }

    .permissions-page {
        width: 100%;
        padding: 24px;
        color: #1f2937;
    }

    .permissions-page .page-top {
        margin-bottom: 24px;
    }

    .permissions-page .page-top h1 {
        margin: 0 0 8px;
        color: #173b7c;
        font-size: 26px;
        font-weight: 700;
    }

    .permissions-page .page-top p {
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }

    .permissions-page .alert-success,
    .permissions-page .alert-error {
        margin-bottom: 20px;
        padding: 13px 16px;
        border-radius: 6px;
        font-size: 14px;
    }

    .permissions-page .alert-success {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #bbf7d0;
    }

    .permissions-page .alert-error {
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fecaca;
    }

    .permissions-page .alert-error ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .permissions-page .permissions-table-wrapper {
        width: 100%;
        overflow-x: auto;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    }

    .permissions-page .permissions-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .permissions-page .permissions-table thead {
        background: #f8fafc;
    }

    .permissions-page .permissions-table th {
        padding: 15px 20px;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid #e5e7eb;
    }

    .permissions-page .permissions-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .permissions-page .permissions-table tbody tr:last-child td {
        border-bottom: none;
    }

    .permissions-page .permissions-table tbody tr:hover {
        background: #f8fafc;
    }

    .permissions-page .bhw-name {
        color: #1f2937;
        font-size: 14px;
        font-weight: 600;
    }

    .permissions-page .bhw-email {
        color: #64748b;
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .permissions-page .action-cell {
        text-align: right;
        white-space: nowrap;
    }

    .permissions-page .edit-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 38px;
        padding: 9px 15px;
        color: #fff;
        background: #2563eb;
        border: 1px solid #2563eb;
        border-radius: 6px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .permissions-page .edit-button:hover {
        background: #1d4ed8;
    }

    .permissions-page .empty-state {
        padding: 35px 20px !important;
        color: #64748b;
        text-align: center;
    }

    .permission-modal {
        display: none !important;
        position: fixed !important;
        inset: 0 !important;
        z-index: 100000 !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 20px !important;
        overflow-y: auto !important;
        background: rgba(15, 23, 42, 0.60) !important;
    }

    .permission-modal.is-open {
        display: flex !important;
    }

    .permission-modal .permission-modal-content {
        position: relative;
        width: 100%;
        max-width: 650px;
        max-height: calc(100vh - 40px);
        margin: auto;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.22);
        animation: permissionModalFade 0.18s ease-out;
    }

    @keyframes permissionModalFade {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .permission-modal .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 22px 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .permission-modal .modal-header h2 {
        margin: 0;
        color: #173b7c;
        font-size: 21px;
        font-weight: 700;
    }

    .permission-modal .modal-description {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.6;
    }

    .permission-modal .close-modal-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        padding: 0;
        color: #64748b;
        background: transparent;
        border: none;
        border-radius: 6px;
        font-size: 26px;
        line-height: 1;
        cursor: pointer;
    }

    .permission-modal .close-modal-button:hover {
        color: #111827;
        background: #f1f5f9;
    }

    .permission-modal .bhw-info {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 20px 24px;
        padding: 14px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .permission-modal .bhw-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 42px;
        height: 42px;
        color: #1d4ed8;
        background: #dbeafe;
        border-radius: 50%;
        font-size: 17px;
        font-weight: 700;
    }

    .permission-modal .bhw-info strong,
    .permission-modal .bhw-info span {
        display: block;
        overflow-wrap: anywhere;
    }

    .permission-modal .bhw-info strong {
        margin-bottom: 4px;
        color: #1f2937;
        font-size: 14px;
    }

    .permission-modal .bhw-info span {
        color: #64748b;
        font-size: 12px;
    }

    .permission-modal .permissions-list {
        padding: 0 24px;
    }

    .permission-modal .permission-category {
        padding: 17px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .permission-modal .permission-category:last-child {
        border-bottom: none;
    }

    .permission-modal .permission-category-title {
        margin: 0 0 13px;
        color: #1f2937;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.5;
    }

    .permission-modal .permission-options {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .permission-modal .permission-option {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-width: 0;
        min-height: 43px;
        padding: 9px 7px;
        color: #475569;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        font-size: 12px;
        line-height: 1.4;
        text-align: center;
        cursor: pointer;
        transition: background 0.2s ease, border-color 0.2s ease;
    }

    .permission-modal .permission-option:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    .permission-modal .permission-option:has(input:checked) {
        color: #1d4ed8;
        background: #eff6ff;
        border-color: #2563eb;
        font-weight: 600;
    }

    .permission-modal .permission-option input[type="radio"] {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
        margin: 0;
        accent-color: #2563eb;
        cursor: pointer;
    }

    .permission-modal .permission-option:focus-within {
        outline: 2px solid #93c5fd;
        outline-offset: 2px;
    }

    .permission-modal .permission-note {
        margin: 20px 24px 0;
        padding: 12px 14px;
        color: #475569;
        background: #f8fafc;
        border-radius: 6px;
        font-size: 12px;
        line-height: 1.7;
    }

    .permission-modal .permission-note strong {
        color: #334155;
    }

    .permission-modal .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 22px;
        padding: 18px 24px;
        border-top: 1px solid #e5e7eb;
    }

    .permission-modal .save-button,
    .permission-modal .cancel-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 39px;
        padding: 9px 16px;
        border-radius: 6px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .permission-modal .save-button {
        color: #fff;
        background: #2563eb;
        border: 1px solid #2563eb;
    }

    .permission-modal .save-button:hover {
        background: #1d4ed8;
    }

    .permission-modal .cancel-button {
        color: #475569;
        background: #fff;
        border: 1px solid #cbd5e1;
    }

    .permission-modal .cancel-button:hover {
        background: #f1f5f9;
    }

    /* ADDED: Permission confirmation modal */

    .permission-confirmation-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 100002;
        align-items: center;
        justify-content: center;
        padding: 16px;
        overflow-y: auto;
        background: rgba(15, 23, 42, 0.72);
    }

    .permission-confirmation-modal.is-open {
        display: flex;
    }

    .permission-confirmation-content {
        width: 100%;
        max-width: 580px;
        max-height: calc(100vh - 32px);
        overflow-y: auto;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.30);
        animation: permissionModalFade 0.18s ease-out;
    }

    .permission-confirmation-header {
        padding: 22px 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .permission-confirmation-header h2 {
        margin: 0 0 8px;
        color: #173b7c;
        font-size: 21px;
        font-weight: 700;
    }

    .permission-confirmation-header p {
        margin: 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.7;
    }

    .permission-confirmation-user {
        margin: 18px 24px;
        padding: 14px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .permission-confirmation-user strong,
    .permission-confirmation-user span {
        display: block;
        overflow-wrap: anywhere;
    }

    .permission-confirmation-user strong {
        margin-bottom: 5px;
        color: #1f2937;
        font-size: 14px;
    }

    .permission-confirmation-user span {
        color: #64748b;
        font-size: 12px;
    }

    .permission-confirmation-summary {
        padding: 0 24px;
    }

    .permission-confirmation-summary h3 {
        margin: 0 0 12px;
        color: #1f2937;
        font-size: 14px;
        font-weight: 700;
    }

    .permission-summary-table-wrapper {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .permission-summary-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .permission-summary-table th {
        padding: 11px 12px;
        color: #475569;
        background: #f8fafc;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid #e5e7eb;
    }

    .permission-summary-table td {
        padding: 12px;
        color: #334155;
        font-size: 12px;
        line-height: 1.5;
        border-bottom: 1px solid #f1f5f9;
        overflow-wrap: anywhere;
    }

    .permission-summary-table tbody tr:last-child td {
        border-bottom: none;
    }

    .permission-access-badge {
        display: inline-block;
        padding: 5px 8px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .permission-access-none {
        color: #991b1b;
        background: #fee2e2;
    }

    .permission-access-read {
        color: #92400e;
        background: #fef3c7;
    }

    .permission-access-write {
        color: #166534;
        background: #dcfce7;
    }

    .permission-confirmation-note {
        margin: 18px 24px 0;
        padding: 12px 14px;
        color: #854d0e;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 6px;
        font-size: 12px;
        line-height: 1.7;
    }

    .permission-confirmation-footer {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
        padding: 18px 24px;
        border-top: 1px solid #e5e7eb;
    }

    .permission-confirmation-footer button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 10px 15px;
        border-radius: 6px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .permission-back-button {
        color: #475569;
        background: #fff;
        border: 1px solid #cbd5e1;
    }

    .permission-back-button:hover {
        background: #f1f5f9;
    }

    .permission-confirm-button {
        color: #fff;
        background: #166534;
        border: 1px solid #166534;
    }

    .permission-confirm-button:hover {
        background: #14532d;
    }

    .permission-confirmation-footer button:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .permissions-page button:focus-visible,
    .permission-modal button:focus-visible,
    .permission-confirmation-modal button:focus-visible {
        outline: 3px solid #93c5fd;
        outline-offset: 3px;
    }

    body.permission-modal-open {
        overflow: hidden !important;
    }

    @media (max-width: 640px) {
        .permissions-page {
            padding: 16px;
        }

        .permissions-page .page-top h1 {
            font-size: 22px;
        }

        .permissions-page .permissions-table th,
        .permissions-page .permissions-table td {
            padding: 13px 12px;
        }

        .permissions-page .edit-button {
            padding: 9px 11px;
            white-space: nowrap;
        }

        .permission-modal {
            padding: 10px !important;
        }

        .permission-modal .permission-modal-content {
            max-height: calc(100vh - 20px);
        }

        .permission-modal .modal-header {
            padding: 18px;
        }

        .permission-modal .bhw-info {
            margin: 16px 18px;
        }

        .permission-modal .permissions-list {
            padding: 0 18px;
        }

        .permission-modal .permission-note {
            margin: 18px 18px 0;
        }

        .permission-modal .modal-footer {
            padding: 16px 18px;
        }

        /* ADDED: Responsive confirmation dialog */

        .permission-confirmation-header {
            padding: 18px;
        }

        .permission-confirmation-user {
            margin: 16px 18px;
        }

        .permission-confirmation-summary {
            padding: 0 18px;
        }

        .permission-confirmation-note {
            margin: 16px 18px 0;
        }

        .permission-confirmation-footer {
            padding: 16px 18px;
        }
    }

    @media (max-width: 480px) {
        .permission-modal .permission-options {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .permission-modal .permission-option {
            justify-content: flex-start;
            padding: 11px 12px;
            font-size: 13px;
        }

        .permission-confirmation-footer {
            flex-direction: column-reverse;
        }

        .permission-confirmation-footer button {
            width: 100%;
        }

        .permission-summary-table th,
        .permission-summary-table td {
            padding: 10px 8px;
        }
    }
</style>

<div class="permissions-page">

    <div class="page-top">
        <h1>BHW User Permissions Management</h1>
        <p>
            Manage access to each feature for Barangay Health Workers.
        </p>
    </div>

    @if (session('success'))
        <div class="alert-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-error" role="alert">
            <strong>Please check the following:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    
    <div class="permissions-table-wrapper">
        <table class="permissions-table">
            <thead>
                <tr>
                    <th>BHW User</th>
                    <th>Email Address</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($bhwUsers as $bhw)
                    <tr>
                        <td>
                            <div class="bhw-name">
                                {{ $bhw->name }}
                            </div>
                        </td>

                        <td>
                            <span class="bhw-email">
                                {{ $bhw->email }}
                            </span>
                        </td>

                        <td class="action-cell">
                            <button
                                type="button"
                                class="edit-button"
                                onclick="openPermissionModal('permission-modal-{{ $bhw->id }}')"
                                aria-haspopup="dialog"
                                aria-controls="permission-modal-{{ $bhw->id }}"
                            >
                                <span aria-hidden="true">&#9998;</span>
                                Edit Permissions
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty-state">
                            No BHW users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>


@foreach ($bhwUsers as $bhw)
    <div
        class="permission-modal"
        id="permission-modal-{{ $bhw->id }}"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title-{{ $bhw->id }}"
        aria-hidden="true"
        onclick="handleModalBackdrop(event, 'permission-modal-{{ $bhw->id }}')"
    >

        <div class="permission-modal-content">

            <div class="modal-header">
                <div>
                    <h2 id="modal-title-{{ $bhw->id }}">
                        Edit Permissions
                    </h2>

                    <p class="modal-description">
                        Select one permission level for each category.
                    </p>
                </div>

                <button
                    type="button"
                    class="close-modal-button"
                    onclick="closePermissionModal('permission-modal-{{ $bhw->id }}')"
                    aria-label="Close permissions dialog"
                >
                    &times;
                </button>
            </div>

            <div class="bhw-info">
                <div class="bhw-avatar" aria-hidden="true">
                    {{ strtoupper(substr($bhw->name ?? 'B', 0, 1)) }}
                </div>

                <div>
                    <strong>{{ $bhw->name }}</strong>
                    <span>{{ $bhw->email }}</span>
                </div>
            </div>

            <form
                action="{{ route('admin.permissions.update', $bhw->id) }}"
                method="POST"
                class="permissions-form"
            >
                @csrf
                @method('PUT')

                <div class="permissions-list">

                    @foreach ($features as $featureKey => $label)
                        @php
                            $userPerm = $bhw->permissions
                                ->firstWhere('feature', $featureKey);

                            $currentAccess = $userPerm
                                ? $userPerm->access
                                : 'none';

                            $validAccessLevels = [
                                'none',
                                'read',
                                'write',
                            ];

                            if (!in_array(
                                $currentAccess,
                                $validAccessLevels,
                                true
                            )) {
                                $currentAccess = 'none';
                            }
                        @endphp

                        <div class="permission-category">

                            <h3 class="permission-category-title">
                                {{ $label }}
                            </h3>

                            <div class="permission-options">

                                <label class="permission-option">
                                    <input
                                        type="radio"
                                        name="permissions[{{ $featureKey }}]"
                                        value="none"
                                        {{ $currentAccess === 'none' ? 'checked' : '' }}
                                        required
                                    >

                                    <span>No Access</span>
                                </label>

                                <label class="permission-option">
                                    <input
                                        type="radio"
                                        name="permissions[{{ $featureKey }}]"
                                        value="read"
                                        {{ $currentAccess === 'read' ? 'checked' : '' }}
                                        required
                                    >

                                    <span>Read Only</span>
                                </label>

                                <label class="permission-option">
                                    <input
                                        type="radio"
                                        name="permissions[{{ $featureKey }}]"
                                        value="write"
                                        {{ $currentAccess === 'write' ? 'checked' : '' }}
                                        required
                                    >

                                    <span>Read / Write</span>
                                </label>

                            </div>
                        </div>
                    @endforeach

                </div>

                <div class="permission-note">
                    <strong>No Access:</strong> Cannot access the category.
                    <br>

                    <strong>Read Only:</strong> Can view information but
                    cannot modify it.
                    <br>

                    <strong>Read / Write:</strong> Can view and modify
                    information, subject to the system's implemented features.
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="cancel-button"
                        onclick="closePermissionModal('permission-modal-{{ $bhw->id }}')"
                    >
                        Cancel
                    </button>

                    <button type="submit" class="save-button">
                        Save Changes
                    </button>
                </div>

            </form>

        </div>
    </div>
@endforeach



<div
    class="permission-confirmation-modal"
    id="permission-confirmation-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="permission-confirmation-title"
    aria-describedby="permission-confirmation-description"
    aria-hidden="true"
>
    <div class="permission-confirmation-content">

        <div class="permission-confirmation-header">
            <h2 id="permission-confirmation-title">
                Confirm Permission Changes
            </h2>

            <p id="permission-confirmation-description">
                Please review the permissions below before saving.
                Your changes will not be submitted until you confirm.
            </p>
        </div>

        <div class="permission-confirmation-user">
            <strong id="confirmation-bhw-name"></strong>
            <span id="confirmation-bhw-email"></span>
        </div>

        <div class="permission-confirmation-summary">
            <h3>Permission Summary</h3>

            <div class="permission-summary-table-wrapper">
                <table class="permission-summary-table">
                    <thead>
                        <tr>
                            <th>Feature / Category</th>
                            <th>Selected Permission</th>
                        </tr>
                    </thead>

                    <tbody id="permission-confirmation-list">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="permission-confirmation-note">
            <strong>Important:</strong>
            Confirming will submit these permission settings for this BHW
            user. Please ensure that each access level is correct.
        </div>

        <div class="permission-confirmation-footer">
            <button
                type="button"
                class="permission-back-button"
                id="permission-back-button"
            >
                Back to Editing
            </button>

            <button
                type="button"
                class="permission-confirm-button"
                id="permission-confirm-button"
            >
                Confirm &amp; Save
            </button>
        </div>

    </div>
</div>


<script>
    (function () {
        let activeModal = null;
        let previousFocus = null;

       
        let pendingPermissionForm = null;
        let confirmationPreviousFocus = null;

        const confirmationModal = document.getElementById(
            'permission-confirmation-modal'
        );

        const confirmationBhwName = document.getElementById(
            'confirmation-bhw-name'
        );

        const confirmationBhwEmail = document.getElementById(
            'confirmation-bhw-email'
        );

        const confirmationList = document.getElementById(
            'permission-confirmation-list'
        );

        const confirmButton = document.getElementById(
            'permission-confirm-button'
        );

        const backButton = document.getElementById(
            'permission-back-button'
        );

        const accessLabels = {
            none: 'No Access',
            read: 'Read Only',
            write: 'Read / Write'
        };

       
        function openPermissionConfirmation(form) {
            if (!form || !confirmationModal) {
                return;
            }

            const parentModal = form.closest('.permission-modal');

            if (!parentModal) {
                return;
            }

            const nameElement = parentModal.querySelector(
                '.bhw-info strong'
            );

            const emailElement = parentModal.querySelector(
                '.bhw-info span'
            );

            confirmationBhwName.textContent = nameElement
                ? nameElement.textContent.trim()
                : 'BHW User';

            confirmationBhwEmail.textContent = emailElement
                ? emailElement.textContent.trim()
                : '';

           
            confirmationList.replaceChildren();

            const categories = form.querySelectorAll(
                '.permission-category'
            );

            categories.forEach(function (category) {
                const titleElement = category.querySelector(
                    '.permission-category-title'
                );

                const selectedRadio = category.querySelector(
                    'input[type="radio"]:checked'
                );

                const featureName = titleElement
                    ? titleElement.textContent.trim()
                    : 'Unnamed Feature';

                const accessValue = selectedRadio
                    ? selectedRadio.value
                    : 'none';

                const accessLabel = accessLabels[accessValue]
                    || 'No Access';

                const row = document.createElement('tr');

                const featureCell = document.createElement('td');
                featureCell.textContent = featureName;

                const accessCell = document.createElement('td');

                const accessBadge = document.createElement('span');

                accessBadge.className = 'permission-access-badge';

                if (accessValue === 'read') {
                    accessBadge.classList.add(
                        'permission-access-read'
                    );
                } else if (accessValue === 'write') {
                    accessBadge.classList.add(
                        'permission-access-write'
                    );
                } else {
                    accessBadge.classList.add(
                        'permission-access-none'
                    );
                }

                accessBadge.textContent = accessLabel;

                accessCell.appendChild(accessBadge);

                row.appendChild(featureCell);
                row.appendChild(accessCell);

                confirmationList.appendChild(row);
            });

            pendingPermissionForm = form;

            confirmationPreviousFocus = document.activeElement;

            confirmationModal.classList.add('is-open');

            confirmationModal.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.classList.add(
                'permission-modal-open'
            );

            confirmButton.disabled = false;
            confirmButton.textContent = 'Confirm & Save';

            confirmButton.focus();
        }

       
        function closePermissionConfirmation() {
            if (!confirmationModal) {
                return;
            }

            confirmationModal.classList.remove('is-open');

            confirmationModal.setAttribute(
                'aria-hidden',
                'true'
            );

            pendingPermissionForm = null;

            
            if (activeModal) {
                document.body.classList.add(
                    'permission-modal-open'
                );
            } else {
                document.body.classList.remove(
                    'permission-modal-open'
                );
            }

            if (
                confirmationPreviousFocus &&
                document.contains(confirmationPreviousFocus)
            ) {
                confirmationPreviousFocus.focus();
            }

            confirmationPreviousFocus = null;
        }

       
        document.querySelectorAll('.permissions-form').forEach(
            function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    openPermissionConfirmation(form);
                });
            }
        );

        
        backButton.addEventListener('click', function () {
            closePermissionConfirmation();
        });

        
        confirmButton.addEventListener('click', function () {
            if (!pendingPermissionForm) {
                return;
            }

            const formToSubmit = pendingPermissionForm;

            
            confirmButton.disabled = true;
            confirmButton.textContent = 'Saving...';

            
            confirmationModal.classList.remove('is-open');

            confirmationModal.setAttribute(
                'aria-hidden',
                'true'
            );

            pendingPermissionForm = null;

            
            formToSubmit.submit();
        });

        
        window.openPermissionModal = function (modalId) {
            const modal = document.getElementById(modalId);

            if (!modal) {
                return;
            }

            if (activeModal) {
                window.closePermissionModal(activeModal.id);
            }

            previousFocus = document.activeElement;
            activeModal = modal;

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');

            document.body.classList.add('permission-modal-open');

            const firstRadio = modal.querySelector(
                '.permission-option input[type="radio"]:checked'
            );

            const firstAvailableRadio = modal.querySelector(
                '.permission-option input[type="radio"]'
            );

            const closeButton = modal.querySelector(
                '.close-modal-button'
            );

            if (firstRadio) {
                firstRadio.focus();
            } else if (firstAvailableRadio) {
                firstAvailableRadio.focus();
            } else if (closeButton) {
                closeButton.focus();
            }
        };

        
        window.closePermissionModal = function (modalId) {
            const modal = document.getElementById(modalId);

            if (!modal) {
                return;
            }

           
            if (
                confirmationModal &&
                confirmationModal.classList.contains('is-open')
            ) {
                return;
            }

            modal.classList.remove('is-open');

            modal.setAttribute('aria-hidden', 'true');

            if (activeModal === modal) {
                activeModal = null;

                document.body.classList.remove(
                    'permission-modal-open'
                );

                if (
                    previousFocus &&
                    document.contains(previousFocus)
                ) {
                    previousFocus.focus();
                }

                previousFocus = null;
            }
        };

        
        window.handleModalBackdrop = function (event, modalId) {
            if (event.target === event.currentTarget) {
                window.closePermissionModal(modalId);
            }
        };

        
        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            if (
                confirmationModal &&
                confirmationModal.classList.contains('is-open')
            ) {
                closePermissionConfirmation();
                return;
            }

            if (activeModal) {
                window.closePermissionModal(activeModal.id);
            }
        });

    })();
</script>

@endsection