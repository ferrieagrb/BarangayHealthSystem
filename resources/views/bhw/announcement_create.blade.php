@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/announce_create.css') }}">
@endsection

@section('content')

<!-- HEADER (same as supplies) -->
<div class="page-top">
    <div class="page-title">
        <h1>Post Announcement</h1>
        <p>Create a new barangay announcement</p>
    </div>

    <a href="{{ route('announcements') }}" class="btn-secondary">
        ← Back
    </a>
</div>

<!-- FORM CONTAINER (same structure as supplies) -->
<div class="form-container">

    <form method="POST" action="{{ route('announcements.store') }}">
        @csrf

        @if ($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- GRID -->
        <div class="form-grid">

            <!-- TITLE -->
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" placeholder="Enter title" required>
            </div>

            <!-- CATEGORY -->
            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="Health Advisory">Health Advisory</option>
                    <option value="Urgent Alert">Urgent Alert</option>
                    <option value="Community Program">Community Program</option>
                </select>
            </div>

            <!-- DESCRIPTION (full width like supplies notes style) -->
            <div class="form-group" style="grid-column: span 2;">
                <label>Description</label>
                <textarea name="description" placeholder="Write announcement details..." required></textarea>
            </div>

        </div>

        <!-- ACTIONS -->
        <div class="form-actions">
            <button type="submit" class="btn-primary">Post Announcement</button>
            <a href="{{ route('announcements') }}" class="btn-secondary">Cancel</a>
        </div>

    </form>

</div>

@endsection