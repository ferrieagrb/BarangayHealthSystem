@extends('templates.admin')

@section('CSSown')
<style>
    /* Full-screen fluid container */
    .demographics-container {
        width: 100%;
        min-height: 100vh;
        margin: 0;
        padding: 24px;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
        color: #333;
    }

    .demographics-container h1 {
        font-size: clamp(1.25rem, 2vw, 1.75rem); /* Scales dynamically with screen size */
        margin-bottom: 20px;
        color: #2c3e50;
    }

    /* Dynamic CSS Grid for full width layout */
    .grid-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
        width: 100%;
    }

    .card {
        background: #ffffff;
        padding: clamp(16px, 2vw, 24px); /* Scales padding based on viewport */
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: flex;
        flex-direction: column;
        height: 100%;
        box-sizing: border-box;
    }

    .card h2 {
        font-size: clamp(1rem, 1.5vw, 1.15rem);
        margin-bottom: 16px;
        color: #34495e;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
        flex: 1;
    }

    /* Responsive Form Row that wraps on small screens if needed */
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .form-control {
        flex: 1 1 200px; /* Allows inputs to wrap and scale gracefully */
        padding: 10px 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        width: 100%;
        box-sizing: border-box;
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
        white-space: nowrap;
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

    /* List item styles wrapped securely for responsiveness */
    .purok-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        padding: 14px 0;
        border-bottom: 1px solid #eee;
    }

    .purok-update-form {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        flex: 1 1 250px;
        align-items: center;
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

    <!-- Row 1: Add Purok Form & Add Subgroup Form -->
    <div class="grid-row">
        <!-- Add Purok Form Card -->
        <div class="card">
            <h2>Add New Purok & Assign BHW</h2>
            <form action="{{ route('admin.puroks.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1;">
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
                    <button type="submit" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Add Purok</button>
                </div>
            </form>
        </div>

        <!-- Add Purok Subgroup Form Card -->
        <div class="card">
            <h2>Add Purok Sub-group (e.g., P1A, P1B)</h2>
            <form action="{{ route('admin.subgroups.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1;">
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
                    <button type="submit" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Add Subgroup</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Row 2: Existing Puroks List & Existing Subgroups List -->
    <div class="grid-row">
        <div class="card">
            <h2>Existing Puroks & Assigned BHWs</h2>
            <ul style="list-style: none; padding:0; margin:0; flex: 1;">
                @forelse($puroks as $purok)
                    <li class="purok-item">
                        <form action="{{ route('admin.puroks.update', $purok->id) }}" method="POST" class="purok-update-form">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $purok->name }}" class="form-control" style="flex: 1 1 100px;" required>
                            <select name="user_id" class="form-control" style="flex: 2 1 140px;">
                                <option value="">-- No BHW --</option>
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
            <ul style="list-style: none; padding:0; margin:0; flex: 1;">
                @forelse($subgroups as $sub)
                    <li class="purok-item">
                        <span style="flex: 1; word-break: break-word;"><strong>{{ $sub->purok->name ?? 'Unknown' }}</strong> ➔ {{ $sub->name }}</span>
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
</div>
@endsection