@extends('templates.admin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/admin/admin_users.css') }}">
@endsection

@section('content')

<div class="page-top">
    <div class="page-title-group">
        <h1>Admin Dashboard</h1>
        <p>Manage system users, roles, and access permissions.</p>
    </div>

    <a href="{{ route('admin.users.create') }}" class="btn-primary">
        + Add User
    </a>
</div>

<div class="summary-cards">
    <div class="summary-card">
        <span>Total Users</span>
        <strong>{{ $totalUsers }}</strong>
    </div>

    <div class="summary-card">
        <span>Admins</span>
        <strong>{{ $admins }}</strong>
    </div>

    <div class="summary-card">
        <span>Staff</span>
        <strong>{{ $staff }}</strong>
    </div>
</div>

<div class="toolbar">
    <div class="toolbar-left">

        <div class="search-box">
            <form method="GET" action="{{ route('admin.users') }}">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search user name or email"
                       oninput="this.form.submit()">
            </form>
        </div>

        <div class="filter-box">
            <form method="GET" action="{{ route('admin.users') }}">
                <select name="role" onchange="this.form.submit()">
                    <option value="all">All Roles</option>
                    <option value="admin" {{ request('role')=='admin'?'selected':'' }}>Admin</option>
                    <option value="staff" {{ request('role')=='staff'?'selected':'' }}>Nurse</option>
                    <option value="user" {{ request('role')=='user'?'selected':'' }}>BHW</option>
                </select>
            </form>
        </div>

    </div>

    <button class="btn-secondary">Export Users</button>
</div>

<div class="table-container">

    <div class="table-header">
        <div>
            <h2>User Accounts</h2>
            <p>Manage all registered system users and their roles.</p>
        </div>
    </div>

    <table>

        @foreach($users as $user)
        <tr>

            <td>
                <span class="citizen-name">{{ $user->name }}</span>
                <span class="citizen-sub">{{ $user->email }}</span>
            </td>

            <td>{{ ucfirst($user->role ?? 'user') }}</td>

            <td>{{ $user->created_at->format('M d, Y') }}</td>

            <td>{{ ucfirst($user->status ?? 'active') }}</td>

            <td>
                <div class="action-group">

                    <a href="{{ route('admin.users.view', $user->id) }}" class="btn-primary">
                        View
                    </a>

                    <form action="{{ route('admin.users.delete', $user->id) }}"
                          method="POST"
                          onsubmit="return confirm('Delete this user?')">

                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn-danger">
                            Delete
                        </button>

                    </form>

                </div>
            </td>

        </tr>
        @endforeach

    </table>

    <div style="margin-top: 15px;">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>

</div>

@endsection