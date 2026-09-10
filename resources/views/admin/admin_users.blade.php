@extends('templates.admin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/admin/admin_users.css') }}">
@endsection

@section('content')

@if ($errors->any())
    <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        {{ session('success') }}
    </div>
@endif

<div class="page-top">
    <div class="page-title-group">
        <h1>Admin Dashboard</h1>
        <p>Manage system users, roles, and access permissions.</p>
    </div>

    <button type="button" class="btn-primary" onclick="openAddModal()">
        + Add User
    </button>
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
        <span>BHW</span>
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
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="bhw" {{ request('role') == 'bhw' ? 'selected' : '' }}>Barangay Health Worker</option>
                </select>
            </form>
        </div>
    </div>
</div>

<div class="table-container">

    <div class="table-header">
        <div>
            <h2>User Accounts</h2>
            <p>Manage all registered system users and their roles.</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name / Email</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
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
                        <button type="button" class="btn-primary" onclick="openEditModal('{{ $user->id }}', '{{ $user->name }}', '{{ $user->email }}', '{{ $user->username ?? '' }}', '{{ $user->role }}')">
                            Edit
                        </button>

                        <form action="{{ route('admin.users.delete', $user->id) }}"
                            method="POST"
                            onsubmit="return confirm('Delete this user?')" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger">Delete</button>
                        </form>
                    </div>
                </td>

            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 15px;">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>

</div>

<!-- Add User Modal -->
<div id="addModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
    <div class="modal-card" style="background: white; padding: 20px; border-radius: 8px; width: 400px; max-width: 90%;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3>Add New User</h3>
            <button type="button" class="close-btn" onclick="closeAddModal()" style="background:none; border:none; font-size: 20px; cursor:pointer;">&times;</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="input-group" style="margin-bottom: 10px;">
                    <label for="add_name">Full Name</label>
                    <input type="text" id="add_name" name="name" required style="width: 100%;">
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label for="add_email">Email Address</label>
                    <input type="email" id="add_email" name="email" required style="width: 100%;">
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label for="add_username">Username</label>
                    <input type="text" id="add_username" name="username" style="width: 100%;">
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label for="add_password">Password</label>
                    <input type="password" id="add_password" name="password" required style="width: 100%;">
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label for="add_role">User Role</label>
                    <select id="add_role" name="role" required style="width: 100%;">
                        <option value="admin">Admin</option>
                        <option value="bhw">Barangay Health Worker</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                <button type="button" class="secondary-btn" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="primary-btn">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
    <div class="modal-card" style="background: white; padding: 20px; border-radius: 8px; width: 400px; max-width: 90%;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3>Edit User Information</h3>
            <button type="button" class="close-btn" onclick="closeEditModal()" style="background:none; border:none; font-size: 20px; cursor:pointer;">&times;</button>
        </div>
        <form id="editUserForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="input-group" style="margin-bottom: 10px;">
                    <label for="edit_name">Full Name</label>
                    <input type="text" id="edit_name" name="name" required style="width: 100%;">
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label for="edit_email">Email Address</label>
                    <input type="email" id="edit_email" name="email" required style="width: 100%;">
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label for="edit_username">Username</label>
                    <input type="text" id="edit_username" name="username" style="width: 100%;">
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label for="edit_role">User Role</label>
                    <select id="edit_role" name="role" required style="width: 100%;">
                        <option value="admin">Admin</option>
                        <option value="bhw">Barangay Health Worker</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                <button type="button" class="secondary-btn" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="primary-btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addModal').style.display = 'flex';
    }

    function closeAddModal() {
        document.getElementById('addModal').style.display = 'none';
    }

    function openEditModal(id, name, email, username, role) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editUserForm');
        
        // Target standard admin route update path
        form.action = `/admin/users/${id}`;
        
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_role').value = role;
        
        modal.style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const editModal = document.getElementById('editModal');
        const addModal = document.getElementById('addModal');
        if (event.target === editModal) {
            closeEditModal();
        }
        if (event.target === addModal) {
            closeAddModal();
        }
    }
</script>

@endsection