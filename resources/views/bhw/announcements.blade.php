@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/announcements.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
<style>
    .announcement-card { cursor: pointer; }
    .announcement-card[hidden] { display: none !important; }
    .announcement-card-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:14px; }
    #announcementSearchEmpty { padding: 20px; color: #64748b; text-align: center; }
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
    .announcement-image-item { display:flex; flex-direction:column; gap:8px; }
    .announcement-image-change { border:1px solid #cbd5e1; background:#f8fafc; color:#334155; border-radius:8px; padding:7px 10px; font-size:13px; cursor:pointer; }
    .announcement-image-change:hover { background:#e2e8f0; }
    .picture-confirm-dialog { width:min(720px,100%); max-height:90vh; overflow:auto; padding:24px; border-radius:14px; background:#fff; box-shadow:0 24px 70px rgba(15,23,42,.3); }
    .picture-confirm-dialog h3 { margin:0 0 6px; color:#172033; }
    .picture-confirm-compare { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px; margin:16px 0; }
    .picture-confirm-compare figure { margin:0; }
    .picture-confirm-compare figcaption { margin-bottom:6px; color:#64748b; font-size:13px; font-weight:600; }
    .picture-confirm-compare img { width:100%; aspect-ratio:4/3; object-fit:cover; border-radius:9px; border:1px solid #e2e8f0; }
    .picture-confirm-actions { display:flex; justify-content:flex-end; gap:10px; }
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
        <input type="search" id="announcementSearch" placeholder="Search announcement title" aria-label="Search announcement titles">
        <select id="announcementCategoryFilter" aria-label="Filter announcements by category">
            <option value="">All Categories</option>
            @foreach($announcements->pluck('category')->filter()->unique()->sort() as $category)
                <option value="{{ $category }}">{{ $category }}</option>
            @endforeach
        </select>
    </div>

    <div class="announcement-list">

@foreach($announcements as $a)

    <div class="announcement-card" data-announcement-title="{{ $a->title }}" data-announcement-category="{{ $a->category }}" data-announcement-date="{{ $a->created_at->format('M d, Y') }}" data-announcement-description="{{ $a->description }}" data-announcement-id="{{ $a->id }}" data-announcement-images='@json($a->images->map(fn ($image) => ["id" => $image->id, "url" => $image->url])->values())'>

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
                <form method="POST" action="{{ route('announcements.destroy', $a->id) }}" onclick="event.stopPropagation();" onsubmit="return confirm('Archive this announcement? You can restore it from the archive.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Archive</button>
                </form>
            </div>

        </div>

    </div>

@endforeach

</div>
<p id="announcementSearchEmpty" hidden>No announcements match these filters.</p>

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

<div id="pictureConfirmModal" class="announcement-modal" role="dialog" aria-modal="true" aria-labelledby="pictureConfirmTitle" style="z-index:10001;">
    <form id="pictureConfirmForm" class="picture-confirm-dialog" method="POST" enctype="multipart/form-data">
        @csrf
        <h3 id="pictureConfirmTitle">Change this picture?</h3>
        <p class="announcement-modal-meta">Review the new picture before saving. The current picture will be replaced.</p>
        <div class="picture-confirm-compare">
            <figure><figcaption>Current</figcaption><img id="pictureConfirmCurrent" alt="Current picture"></figure>
            <figure><figcaption>New</figcaption><img id="pictureConfirmNew" alt="New picture preview"></figure>
        </div>
        <p id="pictureConfirmError" class="announcement-modal-meta" style="color:#b91c1c;" hidden></p>
        <input type="file" name="image" id="pictureConfirmFile" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
        <div class="picture-confirm-actions">
            <button type="button" class="btn-secondary" id="pictureConfirmCancel">Cancel</button>
            <button type="submit" class="btn-primary" id="pictureConfirmSubmit">Confirm change</button>
        </div>
    </form>
</div>
<input type="file" id="pictureChooser" accept="image/jpeg,image/png,image/gif,image/webp" hidden>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('announcementDetailModal');
    const searchInput = document.getElementById('announcementSearch');
    const categoryFilter = document.getElementById('announcementCategoryFilter');
    const emptyMessage = document.getElementById('announcementSearchEmpty');
    const cards = Array.from(document.querySelectorAll('.announcement-card'));
    const title = document.getElementById('announcementModalTitle');
    const meta = document.getElementById('announcementModalMeta');
    const description = document.getElementById('announcementModalDescription');
    const images = document.getElementById('announcementModalImages');

    function closeModal() { modal.classList.remove('open'); }

    function filterAnnouncements() {
        const search = searchInput.value.trim().toLocaleLowerCase();
        const category = categoryFilter.value.toLocaleLowerCase();
        let visibleCount = 0;

        cards.forEach(card => {
            const matchesSearch = card.dataset.announcementTitle.toLocaleLowerCase().includes(search);
            const matchesCategory = !category || card.dataset.announcementCategory.toLocaleLowerCase() === category;
            card.hidden = !(matchesSearch && matchesCategory);
            if (!card.hidden) visibleCount++;
        });

        emptyMessage.hidden = visibleCount > 0;
    }

    searchInput.addEventListener('input', filterAnnouncements);
    categoryFilter.addEventListener('change', filterAnnouncements);

    cards.forEach(card => {
        card.addEventListener('click', function () {
            title.textContent = card.dataset.announcementTitle;
            meta.textContent = `${card.dataset.announcementCategory} · Posted ${card.dataset.announcementDate}`;
            description.textContent = card.dataset.announcementDescription;
            images.innerHTML = '';
            JSON.parse(card.dataset.announcementImages || '[]').forEach(item => {
                const wrapper = document.createElement('div');
                wrapper.className = 'announcement-image-item';
                const image = document.createElement('img');
                image.src = item.url;
                image.alt = card.dataset.announcementTitle;
                const change = document.createElement('button');
                change.type = 'button';
                change.className = 'announcement-image-change';
                change.textContent = 'Change picture';
                change.addEventListener('click', () => startPictureChange(card.dataset.announcementId, item));
                wrapper.append(image, change);
                images.appendChild(wrapper);
            });
            modal.classList.add('open');
        });
    });

    const confirmModal = document.getElementById('pictureConfirmModal');
    const confirmForm = document.getElementById('pictureConfirmForm');
    const chooser = document.getElementById('pictureChooser');
    const confirmFile = document.getElementById('pictureConfirmFile');
    const confirmCurrent = document.getElementById('pictureConfirmCurrent');
    const confirmNew = document.getElementById('pictureConfirmNew');
    const confirmError = document.getElementById('pictureConfirmError');
    const confirmSubmit = document.getElementById('pictureConfirmSubmit');
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const updateUrlTemplate = @json(route('announcements.images.update', ['announcement' => '__A__', 'image' => '__I__']));
    let pendingItem = null;
    let pendingAnnouncementId = null;
    let previewUrl = null;

    function startPictureChange(announcementId, item) {
        pendingAnnouncementId = announcementId;
        pendingItem = item;
        chooser.value = '';
        chooser.click();
    }

    function closeConfirm() {
        confirmModal.classList.remove('open');
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        confirmNew.removeAttribute('src');
        chooser.value = '';
    }

    chooser.addEventListener('change', function () {
        const file = chooser.files[0];
        if (!file) return;

        let error = '';
        if (!allowedTypes.includes(file.type)) error = 'Please choose a JPG, PNG, GIF or WEBP image.';
        else if (file.size > 5 * 1024 * 1024) error = 'The image must be 5 MB or smaller.';

        const transfer = new DataTransfer();
        transfer.items.add(file);
        confirmFile.files = transfer.files;

        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        confirmNew.src = previewUrl;
        confirmCurrent.src = pendingItem.url;
        confirmError.textContent = error;
        confirmError.hidden = !error;
        confirmSubmit.disabled = !!error;
        confirmForm.action = updateUrlTemplate.replace('__A__', pendingAnnouncementId).replace('__I__', pendingItem.id);
        confirmModal.classList.add('open');
    });

    document.getElementById('pictureConfirmCancel').addEventListener('click', closeConfirm);
    confirmModal.addEventListener('click', event => { if (event.target === confirmModal) closeConfirm(); });
    confirmForm.addEventListener('submit', () => { confirmSubmit.disabled = true; });

    modal.querySelector('.announcement-modal-close').addEventListener('click', closeModal);
    modal.addEventListener('click', event => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        if (confirmModal.classList.contains('open')) closeConfirm(); else closeModal();
    });
    filterAnnouncements();
});
</script>

@endsection