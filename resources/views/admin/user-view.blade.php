@extends(auth()->user()->isSuperAdmin() ? 'templates.superadmin' : 'templates.admin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/admin/user_create.css') }}">
@endsection

@section('content')
<div class="page-top">
    <div class="page-title-group">
        <h1>User Details</h1>
        <p>Account information for {{ $user->name }}.</p>
    </div>
    <a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.users' : 'admin.users') }}" class="btn-secondary" data-ui-action="secondary">Back to Users</a>
</div>

<section class="form-container" aria-label="User account details">
    <dl class="form-grid">
        <div class="form-group"><dt>Name</dt><dd>{{ $user->name }}</dd></div>
        <div class="form-group"><dt>Username</dt><dd>{{ $user->username ?: 'Not set' }}</dd></div>
        <div class="form-group"><dt>Email</dt><dd>{{ $user->email ?: 'Not set' }}</dd></div>
        <div class="form-group"><dt>Role</dt><dd>{{ ['superadmin' => 'Super Admin', 'admin' => 'Admin', 'bhw' => 'Barangay Health Worker', 'citizen' => 'Citizen'][$user->role] ?? $user->role }}</dd></div>
        <div class="form-group"><dt>Account status</dt><dd>{{ $user->isLockedOut() ? 'Locked' : 'Active' }}</dd></div>
        <div class="form-group"><dt>Password change required</dt><dd>{{ $user->must_change_password ? 'Yes' : 'No' }}</dd></div>
        <div class="form-group"><dt>Created</dt><dd>{{ $user->created_at?->format('M d, Y h:i A') ?? 'Not recorded' }}</dd></div>
    </dl>
</section>
@endsection
