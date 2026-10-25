@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/announcements.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
<style>
    .announcement-card { cursor: pointer; }
    .announcement-card-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:14px; }
    .announcement-modal { position:fixed; inset:0; z-index:10000; display:none; align-items:center; justify-content:center; padding:20px; background:rgba(15,23,42,.62); }
    .announcement-modal.open { display:flex; }
    .announcement-modal-dialog { width:min(820px,100%); max-height:90vh; overflow:auto; padding:26px; border-radius:14px; background:#fff; box-shadow:0 24px 70px rgba(15,23,42,.3); }
    .announcement-modal-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; }
    .announcement-modal-header h2 { margin:0; color:#172033; font-size:24px; }
    .announcement-modal-close { border:0; background:transparent; color:#64748b; font-size:28px; cursor:pointer; }
    .announcement-modal-meta { margin-top:6px; color:#64748b; font-size:13px; }
    .announcement-modal-description { margin-top:20px; color:#334155; line-height:1.7; white-space:pre-wrap; }
    .announcement-modal-images { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-top:20px; }
    .announcement-modal-images img { width:100%; aspect-ratio:4/3; object-fit:cover; border-radius:9px; border:1px solid #e2e8f0; }
</style>
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

    <div class="announcement-card" data-announcement-title="{{ $a->title }}" data-announcement-category="{{ $a->category }}" data-announcement-date="{{ $a->created_at->format('M d, Y') }}" data-announcement-description="{{ $a->description }}" data-announcement-images='@json($a->images->map(fn ($image) => route("announcement.image", ["path" => $image->path]))->values())'>

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

            <div class="announcement-card-actions">
                <button type="button" class="btn-secondary announcement-view-button">View Details</button>
                <form method="POST" action="{{ route('announcements.destroy', $a->id) }}" onclick="event.stopPropagation();" onsubmit="return confirm('Delete this announcement from the system and Facebook?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Delete</button>
                </form>
            </div>

        </div>

    </div>

@endforeach

</div>

<div id="announcementDetailModal" class="announcement-modal" role="dialog" aria-modal="true" aria-labelledby="announcementModalTitle">
    <div class="announcement-modal-dialog">
        <div class="announcement-modal-header">
            <div>
                <h2 id="announcementModalTitle"></h2>
                <div id="announcementModalMeta" class="announcement-modal-meta"></div>
            </div>
            <button type="button" class="announcement-modal-close" aria-label="Close">&times;</button>
        </div>
        <div id="announcementModalDescription" class="announcement-modal-description"></div>
        <div id="announcementModalImages" class="announcement-modal-images"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('announcementDetailModal');
    const title = document.getElementById('announcementModalTitle');
    const meta = document.getElementById('announcementModalMeta');
    const description = document.getElementById('announcementModalDescription');
    const images = document.getElementById('announcementModalImages');

    function closeModal() { modal.classList.remove('open'); }

    document.querySelectorAll('.announcement-card').forEach(card => {
        card.addEventListener('click', function () {
            title.textContent = card.dataset.announcementTitle;
            meta.textContent = `${card.dataset.announcementCategory} · Posted ${card.dataset.announcementDate}`;
            description.textContent = card.dataset.announcementDescription;
            images.innerHTML = '';
            JSON.parse(card.dataset.announcementImages || '[]').forEach(url => {
                const image = document.createElement('img');
                image.src = url;
                image.alt = card.dataset.announcementTitle;
                images.appendChild(image);
            });
            modal.classList.add('open');
        });
    });

    modal.querySelector('.announcement-modal-close').addEventListener('click', closeModal);
    modal.addEventListener('click', event => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeModal(); });
});
</script>

@endsection