@extends('templates.admin')

@section('CSSown')
<style>
    .demographics-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: Arial, sans-serif;
        color: #333;
    }

    .demographics-container h1 {
        font-size: 24px;
        margin-bottom: 24px;
        color: #2c3e50;
    }

    .card {
        background: #ffffff;
        padding: 24px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .card h2 {
        font-size: 18px;
        margin-bottom: 16px;
        color: #34495e;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 12px;
    }

    .form-row {
        display: flex;
        gap: 12px;
    }

    .form-control {
        flex: 1;
        padding: 10px 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
    }

    .form-control:focus {
        border-color: #3498db;
        outline: none;
    }

    .btn {
        padding: 10px 18px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        transition: background 0.2s;
    }

    .btn-primary {
        background-color: #3498db;
        color: white;
    }

    .btn-primary:hover {
        background-color: #2980b9;
    }

    .btn-success {
        background-color: #2ecc71;
        color: white;
        padding: 6px 12px;
        font-size: 12px;
    }

    .btn-danger {
        background: transparent;
        color: #e74c3c;
        padding: 6px 12px;
    }

    .btn-danger:hover {
        background-color: #fdf2f2;
        border-radius: 4px;
    }

    .purok-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid #eee;
    }

    .empty-text {
        color: #7f8c8d;
        font-style: italic;
        text-align: center;
        padding: 12px 0;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        padding: 12px 16px;
        border-radius: 4px;
        margin-bottom: 20px;
        border: 1px solid #c3e6cb;
    }
</style>
@endsection

@section('content')
<div class="demographics-container">
    <h1>Demographics, Puroks & Subgroups Management</h1>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <!-- Add Purok Form Card -->
    <div class="card">
        <h2>Add New Purok & Assign BHW</h2>
        <form action="{{ route('admin.puroks.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <div class="form-row">
                    <input type="text" name="name" placeholder="Enter Purok name (e.g., Purok 1)" class="form-control" required>
                    <select name="user_id" class="form-control">
                        <option value="">-- Assign BHW (Optional) --</option>
                        @foreach($bhws as $bhw)
                            <option value="{{ $bhw->id }}">{{ $bhw->name ?? $bhw->email }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="align-self: flex-start;">Add Purok</button>
            </div>
        </form>
    </div>

    <!-- Add Purok Subgroup Form Card -->
    <div class="card">
        <h2>Add Purok Sub-group (e.g., P1A, P1B)</h2>
        <form action="{{ route('admin.subgroups.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <div class="form-row">
                    <select name="purok_id" class="form-control" required>
                        <option value="">-- Select Parent Purok --</option>
                        @foreach($puroks as $purok)
                            <option value="{{ $purok->id }}">{{ $purok->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="name" placeholder="Subgroup name (e.g., P1A)" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary" style="align-self: flex-start;">Add Subgroup</button>
            </div>
        </form>
    </div>

    <!-- Existing Puroks & Subgroups Lists -->
    <div class="card">
        <h2>Existing Puroks & Assigned BHWs</h2>
        <ul style="list-style: none; padding:0; margin:0;">
            @forelse($puroks as $purok)
                <li class="purok-item">
                    <form action="{{ route('admin.puroks.update', $purok->id) }}" method="POST" style="display: flex; gap: 10px; flex: 1; align-items: center;">
                        @csrf
                        @method('PUT')
                        <input type="text" name="name" value="{{ $purok->name }}" class="form-control" style="max-width: 150px;" required>
                        <select name="user_id" class="form-control" style="max-width: 200px;">
                            <option value="">-- No BHW Assigned --</option>
                            @foreach($bhws as $bhw)
                                <option value="{{ $bhw->id }}" {{ $purok->user_id == $bhw->id ? 'selected' : '' }}>
                                    {{ $bhw->name ?? $bhw->email }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-success">Save</button>
                    </form>
                    <form action="{{ route('admin.puroks.destroy', $purok->id) }}" method="POST" onsubmit="return confirm('Delete purok and its subgroups?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </li>
            @empty
                <li class="empty-text">No puroks added yet.</li>
            @endforelse
        </ul>
    </div>

    <div class="card">
        <h2>Existing Subgroups</h2>
        <ul style="list-style: none; padding:0; margin:0;">
            @forelse($subgroups as $sub)
                <li class="purok-item">
                    <span><strong>{{ $sub->purok->name ?? 'Unknown' }}</strong> ➔ {{ $sub->name }}</span>
                    <form action="{{ route('admin.subgroups.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Delete this subgroup?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </li>
            @empty
                <li class="empty-text">No subgroups added yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection