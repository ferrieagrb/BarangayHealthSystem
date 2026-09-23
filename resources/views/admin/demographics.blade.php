@extends('templates.admin')

@section('CSSown')
<style>
    .demographics-container {
        max-width: 650px;
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

    .btn-danger {
        background: transparent;
        color: #e74c3c;
        padding: 6px 12px;
    }

    .btn-danger:hover {
        background-color: #fdf2f2;
        border-radius: 4px;
    }

    .purok-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .purok-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }

    .purok-item:last-child {
        border-bottom: none;
    }

    .empty-text {
        color: #7f8c8d;
        font-style: italic;
        text-align: center;
        padding: 16px 0;
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
    <h1>Demographics & Purok Management</h1>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <!-- Add Purok Form Card -->
    <div class="card">
        <h2>Add New Purok</h2>
        <form action="{{ route('admin.puroks.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <input type="text" name="name" placeholder="Enter new Purok name (e.g., Purok 8)" class="form-control" required>
                <button type="submit" class="btn btn-primary">Add Purok</button>
            </div>
        </form>
    </div>

    <!-- Existing Puroks List Card -->
    <div class="card">
        <h2>Existing Puroks</h2>
        <ul class="purok-list">
            @forelse($puroks as $purok)
                <li class="purok-item">
                    <span>{{ $purok->name }}</span>
                    <form action="{{ route('admin.puroks.destroy', $purok->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this purok?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </li>
            @empty
                <li class="empty-text">No puroks added yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
