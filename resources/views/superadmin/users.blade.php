
@extends('templates.superadmin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/superadmin/users.css') }}">
@endsection

@section('content')
<div class="user-management-page">
    <div class="page-top">
        <div class="page-title-group">
            <h1>User Management</h1>
            <p>Manage system users, update profiles, and control role permissions.</p>
        </div>

        <div class="header-user">
            <span>Super Admin</span>
            <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
        </div>
    </div>

    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert-success" role="alert">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Error Message --}}
    @if (session('error'))
        <div class="alert-error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert-error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <div>
                <strong>Please check the following:</strong>
                <ul style="margin: 6px 0 0 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Registered Users Card --}}
    <section class="card">
        <div class="card-header">
            <div>
                <h3>Registered System Users</h3>
                <p style="margin: 4px 0 0; color: #64748b; font-size: 12px;">
                    View and manage registered accounts and their assigned roles.
                </p>
            </div>

            <button
                type="button"
                class="primary-btn"
                id="openAddUserModal"
            >
                <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                Add New User
            </button>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Name / Email</th>
                        <th scope="col">Username</th>
                        <th scope="col">Role</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="user-info-cell">
                                    <span class="user-name">
                                        {{ $user->name }}
                                    </span>

                                    <span class="user-email">
                                        {{ $user->email }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                {{ $user->username ?: '—' }}
                            </td>

                            <td>
                                <span class="badge badge-{{ strtolower($user->role) }}">
                                    {{ $user->role === 'bhw' ? 'Barangay Health Worker' : ucfirst($user->role) }}
                                </span>
                            </td>

                            <td>
                                <span class="badge badge-active">Active</span>
                            </td>

                            <td>
                                <div class="action-buttons">
                                    <button
                                        type="button"
                                        class="btn-icon edit js-edit-user"
                                        title="Edit User"
                                        aria-label="Edit {{ $user->name }}"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-username="{{ $user->username }}"
                                        data-role="{{ $user->role }}"
                                    >
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    </button>

                                    <form
                                        action="{{ route('superadmin.users.delete', $user->id) }}"
                                        method="POST"
                                        class="delete-user-form"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn-icon delete"
                                            title="Delete User"
                                            aria-label="Delete {{ $user->name }}"
                                        >
                                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 32px 14px; text-align: center;">
                                <i
                                    class="fa-solid fa-users"
                                    aria-hidden="true"
                                    style="display: block; margin-bottom: 10px; color: #94a3b8; font-size: 26px;"
                                ></i>

                                <strong style="display: block; color: #334155;">
                                    No users found
                                </strong>

                                <span style="display: block; margin-top: 5px; color: #64748b; font-size: 12px;">
                                    Add a new user to get started.
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- =====================================================
     EDIT USER MODAL
     ===================================================== --}}
<div
    id="editModal"
    class="user-management-modals modal-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editModalTitle"
    aria-hidden="true"
>
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="editModalTitle">Edit User Information</h3>

            <button
                type="button"
                class="close-btn"
                data-close-modal="editModal"
                aria-label="Close edit user modal"
            >
                &times;
            </button>
        </div>

        <form id="editUserForm" method="POST">
            @csrf
            @method('PUT')

            <div class="modal-body">
                <div class="input-group">
                    <label for="edit_name">Full Name</label>
                    <input
                        type="text"
                        id="edit_name"
                        name="name"
                        maxlength="255"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="input-group">
                    <label for="edit_email">Email Address</label>
                    <input
                        type="email"
                        id="edit_email"
                        name="email"
                        maxlength="255"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="input-group">
                    <label for="edit_username">Username</label>
                    <input
                        type="text"
                        id="edit_username"
                        name="username"
                        maxlength="255"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="input-group">
                    <label for="edit_role">User Role</label>
                    <select id="edit_role" name="role" required>
                        <option value="superadmin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="bhw">Barangay Health Worker</option>
                        <option value="citizen">Citizen</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="secondary-btn"
                    data-close-modal="editModal"
                >
                    Cancel
                </button>

                <button type="submit" class="primary-btn" id="saveEditUserButton">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================
     ADD USER MODAL
     ===================================================== --}}
<div
    id="addModal"
    class="user-management-modals modal-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="addModalTitle"
    aria-hidden="true"
>
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="addModalTitle">Add New User</h3>

            <button
                type="button"
                class="close-btn"
                data-close-modal="addModal"
                aria-label="Close add user modal"
            >
                &times;
            </button>
        </div>

        <form
            id="addUserForm"
            action="{{ route('superadmin.users.store') }}"
            method="POST"
        >
            @csrf

            <div class="modal-body">
                <div class="input-group">
                    <label for="add_name">Full Name</label>
                    <input
                        type="text"
                        id="add_name"
                        name="name"
                        maxlength="255"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="input-group">
                    <label for="add_email">Email Address</label>
                    <input
                        type="email"
                        id="add_email"
                        name="email"
                        maxlength="255"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="input-group">
                    <label for="add_username">Username</label>
                    <input
                        type="text"
                        id="add_username"
                        name="username"
                        maxlength="255"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="input-group">
                    <label for="add_password">Password</label>
                    <input
                        type="password"
                        id="add_password"
                        name="password"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="input-group">
                    <label for="add_role">User Role</label>
                    <select id="add_role" name="role" required>
                        <option value="superadmin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="bhw">Barangay Health Worker</option>
                        <option value="citizen">Citizen</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="secondary-btn"
                    data-close-modal="addModal"
                >
                    Cancel
                </button>

                <button type="submit" class="primary-btn" id="createUserButton">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editUserForm');
    const addForm = document.getElementById('addUserForm');
    const openAddButton = document.getElementById('openAddUserModal');

    let previouslyFocusedElement = null;

    function openModal(modal) {
        if (!modal) {
            return;
        }

        previouslyFocusedElement = document.activeElement;

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        const firstInput = modal.querySelector(
            'input:not([type="hidden"]), select, button'
        );

        if (firstInput) {
            window.setTimeout(function () {
                firstInput.focus();
            }, 50);
        }
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        const anotherModalIsOpen = document.querySelector(
            '.user-management-modals.modal-overlay.is-open'
        );

        if (!anotherModalIsOpen) {
            document.body.style.overflow = '';

            if (
                previouslyFocusedElement &&
                typeof previouslyFocusedElement.focus === 'function'
            ) {
                previouslyFocusedElement.focus();
            }
        }
    }

    function openAddModal() {
        if (addForm) {
            addForm.reset();
        }

        openModal(addModal);
    }

    function openEditModal(button) {
        if (!button || !editForm) {
            return;
        }

        const userId = button.dataset.id;

        if (!userId) {
            return;
        }

        // Preserve the existing Superadmin update URL pattern.
        editForm.action = `/superadmin/users/${encodeURIComponent(userId)}`;

        document.getElementById('edit_name').value =
            button.dataset.name || '';

        document.getElementById('edit_email').value =
            button.dataset.email || '';

        document.getElementById('edit_username').value =
            button.dataset.username || '';

        document.getElementById('edit_role').value =
            button.dataset.role || 'citizen';

        openModal(editModal);
    }

    if (openAddButton) {
        openAddButton.addEventListener('click', openAddModal);
    }

    document.querySelectorAll('.js-edit-user').forEach(function (button) {
        button.addEventListener('click', function () {
            openEditModal(button);
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = button.dataset.closeModal;
            closeModal(document.getElementById(modalId));
        });
    });

    // Close a modal when the user clicks its backdrop.
    [addModal, editModal].forEach(function (modal) {
        if (!modal) {
            return;
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    // Close the active modal with Escape.
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        if (editModal && editModal.classList.contains('is-open')) {
            closeModal(editModal);
        } else if (addModal && addModal.classList.contains('is-open')) {
            closeModal(addModal);
        }
    });

    // Confirm before deleting a user.
    document.querySelectorAll('.delete-user-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const confirmed = window.confirm(
                'Are you sure you want to delete this user? This action cannot be undone.'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    // Prevent accidental repeated submissions.
    if (addForm) {
        addForm.addEventListener('submit', function () {
            const button = document.getElementById('createUserButton');

            if (button && addForm.checkValidity()) {
                button.disabled = true;
                button.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Creating...';
            }
        });
    }

    if (editForm) {
        editForm.addEventListener('submit', function () {
            const button = document.getElementById('saveEditUserButton');

            if (button && editForm.checkValidity()) {
                button.disabled = true;
                button.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Saving...';
            }
        });
    }
});
</script>
@endsection
