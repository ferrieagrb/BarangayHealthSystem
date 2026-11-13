
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

            <div style="display:flex; gap:10px; align-items:center;">
            @if($showArchived)
                <a href="{{ route('superadmin.users') }}" class="primary-btn" style="text-decoration:none;" data-ui-action="secondary">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    Active Users
                </a>
            @else
                <a href="{{ route('superadmin.users', ['archived' => 1]) }}" class="primary-btn" style="text-decoration:none;" data-ui-action="secondary">
                    <i class="fa-solid fa-box-archive" aria-hidden="true"></i>
                    Archive ({{ $archivedCount }})
                </a>
            @endif
            <button
                type="button"
                class="primary-btn"
                id="openAddUserModal"
             data-ui-action="primary">
                <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                Add New User
            </button>
            </div>
        </div>

        <form method="GET" action="{{ route('superadmin.users') }}" class="users-search-form">
            @if($showArchived)
                <input type="hidden" name="archived" value="1">
            @endif
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email or username..." aria-label="Search users">
            <select name="role" aria-label="Filter by role">
                @foreach(['all' => 'All roles', 'superadmin' => 'Super Admin', 'admin' => 'Admin', 'bhw' => 'Barangay Health Worker', 'citizen' => 'Citizen'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('role', 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="primary-btn" data-ui-action="primary">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                Search
            </button>
            @if(request()->filled('search') || (request()->filled('role') && request('role') !== 'all'))
                <a href="{{ route('superadmin.users', $showArchived ? ['archived' => 1] : []) }}" class="primary-btn" style="text-decoration:none;" data-ui-action="secondary">Clear</a>
            @endif
        </form>

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
                                @if($showArchived)
                                    <span class="badge badge-inactive">Inactive</span>
                                @elseif($user->isLockedOut())
                                    <span class="badge badge-inactive">Locked{{ $user->locked_until && $user->locked_until->isFuture() ? ' until ' . $user->locked_until->format('h:i A') : '' }}</span>
                                    <form action="{{ route('superadmin.users.unlock', $user->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-action btn-edit" title="Unlock Account" aria-label="Unlock Account">
                                            <i class="fa-solid fa-lock-open" aria-hidden="true"></i>
                                            <span>Unlock</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="badge badge-active">Active</span>
                                @endif
                            </td>

                            <td>
                                    <div class="action-buttons">
                                        @if($showArchived)
                                        <form
                                            action="{{ route('superadmin.users.restore', $user->id) }}"
                                            method="POST"
                                            data-confirm-message="Reactivate this user? They will be required to change their password at next login."
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-action btn-edit" title="Set Active" aria-label="Set Active" data-ui-action="primary">
                                                <i class="fa-solid fa-user-check" aria-hidden="true"></i>
                                                <span>Set Active</span>
                                            </button>
                                        </form>
                                        @else
                                        <button
                                            type="button"
                                            class="btn-action btn-edit js-edit-user"
                                            title="Edit User"
                                            aria-label="Edit User"
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-username="{{ $user->username }}"
                                            data-role="{{ $user->role }}"
                                         data-ui-action="secondary">
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                            <span>Edit</span>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn-action btn-edit js-reset-user"
                                            title="Reset Password"
                                            aria-label="Reset Password"
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $user->name }}"
                                        >
                                            <i class="fa-solid fa-key" aria-hidden="true"></i>
                                            <span>Reset Password</span>
                                        </button>

                                        <form
                                            action="{{ route('superadmin.users.delete', $user->id) }}"
                                            method="POST"
                                            class="delete-user-form"
                                            data-confirm-message="Mark this user as inactive? The account will be archived and cannot sign in until reactivated."
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn-action btn-delete"
                                                title="Set Inactive"
                                                aria-label="Set Inactive"
                                             data-ui-action="danger">
                                                <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
                                                <span>Set Inactive</span>
                                            </button>
                                        </form>
                                        @endif
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

        @if ($users->hasPages())
            <div class="users-pagination">
                {{ $users->links('partials.pagination') }}
            </div>
        @endif
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
             data-ui-action="secondary">
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
                        autocomplete="off"
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
                        autocomplete="off"
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
                        autocomplete="off"
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

                <div class="input-group" @hidden(! \App\Models\SystemSetting::passwordReentryRequired())>
                    <label for="edit_confirm_password">Your password <small>(required only when changing the role)</small></label>

                    <input
                        type="password"
                        id="edit_confirm_password"
                        name="confirm_password"
                        autocomplete="current-password"
                    >
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="secondary-btn"
                    data-close-modal="editModal"
                 data-ui-action="secondary">
                    Cancel
                </button>

                <button
                    type="submit"
                    class="primary-btn"
                    id="saveEditUserButton"
                 data-ui-action="primary">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================
     RESET PASSWORD MODAL
     ===================================================== --}}
<div
    id="resetModal"
    class="user-management-modals modal-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="resetModalTitle"
    aria-hidden="true"
>
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="resetModalTitle">Reset Password</h3>

            <button type="button" class="close-btn" data-close-modal="resetModal" aria-label="Close reset password modal">
                &times;
            </button>
        </div>

        <form id="resetPasswordForm" method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body">
                <p>The password of <strong id="reset_user_name"></strong> will be reset to the default (TemporaryPassword123! for staff, Barangay123! for citizens). They must change it at next login.</p>

                <div class="input-group" @hidden(! \App\Models\SystemSetting::passwordReentryRequired())>
                    <label for="reset_confirm_password">Your password</label>
                    <input type="password" id="reset_confirm_password" name="confirm_password" @required(\App\Models\SystemSetting::passwordReentryRequired()) autocomplete="current-password">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="secondary-btn" data-close-modal="resetModal">Cancel</button>
                <button type="submit" class="primary-btn">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/superadmin/users-inline-1.js') }}?v={{ filemtime(public_path('js/superadmin/users-inline-1.js')) }}"></script>

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
             data-ui-action="secondary">
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
                        autocomplete="off"
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
                        autocomplete="off"
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
                        autocomplete="off"
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
                 data-ui-action="secondary">
                    Cancel
                </button>

                <button
                    type="submit"
                    class="primary-btn"
                    id="createUserButton"
                 data-ui-action="primary">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/superadmin/users-inline-2.js') }}?v={{ filemtime(public_path('js/superadmin/users-inline-2.js')) }}"></script>
@endsection
