@extends('templates.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin/permissions.css') }}">

<div class="permissions-page">

    <div class="page-top">
        <div class="page-title-group">
            <h1>BHW User Permissions Management</h1>
            <p>Manage access to each feature for Barangay Health Workers.</p>
        </div>
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
