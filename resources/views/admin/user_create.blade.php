@extends('templates.admin')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/admin/user_create.css') }}">
@endsection

@section('content')

<h1>Create User</h1>

@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif

<form method="POST" action="{{ route('admin.users.store') }}" class="form-container">
    @csrf

    <div class="form-grid">

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="bhw">BHW</option>
            </select>
        </div>

        <button type="submit">Create User</button>

    </div>
</form>

@endsection