@extends('templates.superadmin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
@endsection
    
@section('content')

    <div class="admin-container" style="margin-left: 0; width: 100%;">
        <!-- Main Content Area -->
        <main class="main-content" style="margin-left: 0; width: 100%;">
            <header class="content-header">
                <div class="header-title">
                    <h1>User Management</h1>
                    <p>Manage system users, update profiles, and control role permissions.</p>
                </div>
                <div class="header-user">
                    <span>Super Admin</span>
                    <i class="fa-solid fa-circle-user"></i>
                </div>
            </header>

            @if (session('success'))
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Users Table Card -->
            <div class="card">
                <div class="card-header">
                    <h3>Registered System Users</h3>
                    <button class="primary-btn" onclick="openAddModal()">
                        <i class="fa-solid fa-user-plus"></i> Add New User
                    </button>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Name / Email</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="user-info-cell">
                                        <span class="user-name">{{ $user->name }}</span>
                                        <span class="user-email">{{ $user->email }}</span>
                                    </div>
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($user->role) }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-active">Active</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon edit" onclick="openEditModal('{{ $user->id }}', '{{ $user->name }}', '{{ $user->email }}', '{{ $user->username }}', '{{ $user->role }}')" title="Edit User">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('superadmin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon delete" title="Delete User">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Edit User Information</h3>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="input-group">
                        <label for="edit_name">Full Name</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                    <div class="input-group">
                        <label for="edit_email">Email Address</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label for="edit_username">Username</label>
                        <input type="text" id="edit_username" name="username" required>
                    </div>
                    <div class="input-group">
                        <label for="edit_role">User Role</label>
                        <select id="edit_role" name="role" required>
                            <option value="superadmin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="worker">Community Health Worker</option>
                            <option value="volunteer">Volunteer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="secondary-btn" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="primary-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, email, username, role) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editUserForm');
            
            // Set form action route dynamically to match superadmin prefix
            form.action = `/superadmin/users/${id}`;
            
            // Populate fields
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_role').value = role;
            
            modal.style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside of card content
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
    </script>

@endsection