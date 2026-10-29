@extends('templates.citizen')

@section('CSSown')
<style>
.announcement-detail-page {
    max-width: 1050px;
    margin: 0 auto;
    padding: clamp(18px, 3vw, 38px);
    color: #172033;
}
.announcement-detail-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 22px;
    padding: 10px 16px;
    border-radius: 9px;
    background: #173b82;
    color: #fff !important;
    font-weight: 700;
    text-decoration: none !important;
}
.announcement-detail-back:hover {
    background: #102b5c;
}
.announcement-detail-card {
    overflow: hidden;
    border: 1px solid #d7e0ec;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(23, 59, 130, .08);
}
.announcement-detail-header {
    padding: clamp(22px, 4vw, 40px);
    background: linear-gradient(120deg, #102b5c, #1d4c9c);
    color: #fff;
}
.announcement-detail-label {
    display: inline-block;
    margin-bottom: 12px;
    padding: 6px 11px;
    border: 1px solid rgba(255,255,255,.32);
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    font-size: 13px;
    font-weight: 700;
}
.announcement-detail-header h1 {
    margin: 0;
    color: #fff;
    font-size: clamp(24px, 3vw, 36px);
    line-height: 1.3;
    overflow-wrap: anywhere;
}
.announcement-detail-meta {
    margin-top: 14px;
    color: #e2eaf8;
    font-size: 14px;
}
.announcement-detail-body {
    padding: clamp(22px, 4vw, 40px);
    font-size: 16px;
    line-height: 1.85;
    overflow-wrap: anywhere;
}
.announcement-detail-content {
    white-space: pre-line;
}
@media (max-width: 600px) {
    .announcement-detail-page { padding: 16px; }
    .announcement-detail-body { font-size: 15px; }
}
</style>
@endsection

@section('content')
@php
    $eventDateValue = $announcement->event_date
        ?? $announcement->event_start_date
        ?? $announcement->schedule_date
        ?? $announcement->date_of_event
        ?? $announcement->start_date
        ?? $announcement->event_datetime
        ?? $announcement->date
        ?? null;

    try {
        $displayEventDate = $eventDateValue
            ? \Carbon\Carbon::parse($eventDateValue)->format('F j, Y')
            : null;
    } catch (\Throwable $e) {
        $displayEventDate = null;
    }

    $fullAnnouncement = $announcement->content
        ?? $announcement->description
        ?? 'No additional details are available for this announcement.';
@endphp

<div class="announcement-detail-page">
    <a class="announcement-detail-back"
       href="{{ route('citizen.announcements') }}">
        ← Back to Announcements
    </a>

    <article class="announcement-detail-card">
        <header class="announcement-detail-header">
            <span class="announcement-detail-label">📢 Health Advisory</span>
            <h1>{{ $announcement->title }}</h1>
            <div class="announcement-detail-meta">
                @if($displayEventDate)
                    Event date: {{ $displayEventDate }}
                @else
                    Posted: {{ optional($announcement->created_at)->format('F j, Y') ?? 'Date unavailable' }}
                @endif
                <span> · Barangay Amuyong</span>
            </div>
        </header>

        <div class="announcement-detail-body">
            <div class="announcement-detail-content">{{ $fullAnnouncement }}</div>
        </div>
    </article>
</div>
@endsection
