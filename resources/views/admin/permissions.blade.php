@extends('templates.admin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/admin/permissions.css') }}">
@endsection

@section('content')
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
                    <th>Action</th>
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
                             data-ui-action="secondary">
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
                 data-ui-action="secondary">
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
                     data-ui-action="secondary">
                        Cancel
                    </button>

                    <button type="submit" class="save-button" data-ui-action="primary">
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
             data-ui-action="secondary">
                Back to Editing
            </button>

            <button
                type="button"
                class="permission-confirm-button"
                id="permission-confirm-button"
             data-ui-action="primary">
                Confirm &amp; Save
            </button>
        </div>

    </div>
</div>


<script src="{{ asset('js/admin/permissions-inline-1.js') }}?v={{ filemtime(public_path('js/admin/permissions-inline-1.js')) }}"></script>

@endsection
