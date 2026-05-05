@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/announcements.css') }}">
@endsection

@section('content')

@if(session('success'))
    <div class="success-message">
        {{ session('success') }}
    </div>
@endif

<div class="page-top">
        <div class="page-title">
            <h1>Announcements</h1>
            <p>Post, manage, and publish important health-related announcements for citizens.</p>
        </div>
        <a href="{{ route('announcements.create') }}" class="btn-primary">
    + Post Announcement
</a>
    </div>

    <div class="summary">
    <div class="summary-card">
        <span>Total Posts</span>
        <strong>{{ $totalPosts }}</strong>
    </div>

    <div class="summary-card">
        <span>Published This Month</span>
        <strong>{{ $thisMonth }}</strong>
    </div>

    <div class="summary-card">
        <span>Urgent Alerts</span>
        <strong>{{ $urgentAlerts }}</strong>
    </div>
</div>

    <div class="toolbar">
        <input type="text" placeholder="Search announcement title">
        <select>
            <option>All Categories</option>
            <option>Health Advisory</option>
            <option>Urgent Alert</option>
            <option>Community Program</option>
        </select>
    </div>

    <div class="announcement-list">

@foreach($announcements as $a)

    <div class="announcement-card">

        <div class="announcement-content">

            <span class="announcement-tag">
                {{ $a->category }}
            </span>

            <div class="announcement-title">
                {{ $a->title }}
            </div>

            <div class="announcement-date">
                Posted on {{ $a->created_at->format('M d, Y') }}
            </div>

            <div class="announcement-desc">
                {{ $a->description }}
            </div>

        </div>

    </div>

@endforeach

</div>

@endsection