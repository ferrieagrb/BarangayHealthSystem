<style>
    .citizen-announcement-modal[hidden],
    .citizen-announcement-viewer[hidden] { display: none; }
    .citizen-announcement-modal {
        position: fixed;
        inset: 0;
        z-index: 5000;
        display: grid;
        place-items: center;
        padding: 24px;
        background: rgba(15, 23, 42, .72);
    }
    .citizen-announcement-dialog {
        width: min(900px, 100%);
        max-height: min(760px, 92vh);
        overflow-y: auto;
        padding: clamp(20px, 3vw, 34px);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 20px 60px rgba(15, 23, 42, .3);
    }
    .citizen-announcement-dialog-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
    }
    .citizen-announcement-dialog h2 { margin: 0; color: #173b82; font-size: clamp(22px, 3vw, 32px); }
    .citizen-announcement-close,
    .citizen-announcement-control {
        width: 40px;
        height: 40px;
        border: 0;
        border-radius: 7px;
        color: #fff;
        background: #173b82;
        cursor: pointer;
        flex: 0 0 auto;
    }
    .citizen-announcement-meta { margin: 8px 0 22px; color: #64748b; font-size: 13px; }
    .citizen-announcement-gallery { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 9px; margin-bottom: 22px; }
    .citizen-announcement-gallery button { padding: 0; border: 2px solid transparent; border-radius: 7px; overflow: hidden; cursor: pointer; background: #f1f5f9; }
    .citizen-announcement-gallery button.active { border-color: #1d4ed8; }
    .citizen-announcement-gallery img { display: block; width: 100%; aspect-ratio: 4 / 3; object-fit: cover; }
    .citizen-announcement-description { color: #334155; line-height: 1.75; white-space: pre-line; }
    .citizen-announcement-viewer { position: fixed; inset: 0; z-index: 6000; display: grid; place-items: center; padding: 24px; background: rgba(15, 23, 42, .94); overflow: hidden; isolation: isolate; }
    .citizen-announcement-viewer-stage { position: fixed; inset: 0; z-index: 1; display: grid; place-items: center; overflow: hidden; touch-action: none; }
    .citizen-announcement-viewer img { position: relative; z-index: 1; max-width: min(92vw, 1200px); max-height: 86vh; object-fit: contain; transform: translate(0, 0) scale(1); transition: transform .15s ease; cursor: zoom-in; touch-action: none; user-select: none; }
    .citizen-announcement-viewer img.is-dragging { cursor: grabbing; transition: none; }
    .citizen-announcement-viewer-toolbar { position: fixed; top: 18px; right: 18px; z-index: 999999; display: flex; gap: 8px; pointer-events: auto; }
    .citizen-announcement-viewer .citizen-announcement-control { position: relative; z-index: 1000000; background: rgba(15, 23, 42, .8); border: 1px solid rgba(255,255,255,.35); pointer-events: auto; }
    .citizen-announcement-viewer-nav { position: fixed; top: 50%; transform: translateY(-50%); z-index: 999999; }
    .citizen-announcement-viewer-nav.is-hidden { display: none !important; }
    .citizen-announcement-viewer-prev { left: 18px; }
    .citizen-announcement-viewer-next { right: 18px; }
    @media (max-width: 600px) { .citizen-announcement-modal { padding: 12px; } .citizen-announcement-dialog { max-height: 94vh; } }
</style>

<div class="citizen-announcement-modal" id="citizenAnnouncementModal" hidden role="dialog" aria-modal="true" aria-label="Announcement details">
    <article class="citizen-announcement-dialog">
        <header class="citizen-announcement-dialog-header">
            <div>
                <span class="c-ann-label" id="citizenAnnouncementCategory">Health Advisory</span>
                <h2 id="citizenAnnouncementTitle"></h2>
            </div>
            <button type="button" class="citizen-announcement-close" id="citizenAnnouncementClose" aria-label="Close announcement"><i class="bx bx-x"></i></button>
        </header>
        <p class="citizen-announcement-meta" id="citizenAnnouncementDate"></p>
        <div class="citizen-announcement-gallery" id="citizenAnnouncementGallery"></div>
        <p class="citizen-announcement-description" id="citizenAnnouncementDescription"></p>
    </article>
</div>

<div class="citizen-announcement-viewer" id="citizenAnnouncementViewer" hidden role="dialog" aria-modal="true" aria-label="Announcement image viewer">
    <div class="citizen-announcement-viewer-toolbar">
        <button type="button" class="citizen-announcement-control" id="citizenAnnouncementZoomOut" aria-label="Zoom out"><i class="bx bx-minus"></i></button>
        <button type="button" class="citizen-announcement-control" id="citizenAnnouncementZoomIn" aria-label="Zoom in"><i class="bx bx-plus"></i></button>
        <button type="button" class="citizen-announcement-control" id="citizenAnnouncementZoomReset" aria-label="Reset zoom"><i class="bx bx-reset"></i></button>
        <button type="button" class="citizen-announcement-control" id="citizenAnnouncementViewerClose" aria-label="Close image viewer"><i class="bx bx-x"></i></button>
    </div>
    <button type="button" class="citizen-announcement-control citizen-announcement-viewer-nav citizen-announcement-viewer-prev" id="citizenAnnouncementPrevious" aria-label="Previous image"><i class="bx bx-chevron-left"></i></button>
    <div class="citizen-announcement-viewer-stage">
        <img id="citizenAnnouncementViewerImage" src="" alt="">
    </div>
    <button type="button" class="citizen-announcement-control citizen-announcement-viewer-nav citizen-announcement-viewer-next" id="citizenAnnouncementNext" aria-label="Next image"><i class="bx bx-chevron-right"></i></button>
</div>

@php
    $announcementPayload = $announcements->mapWithKeys(function ($announcement) {
        return [$announcement->id => [
            'title' => $announcement->title,
            'category' => $announcement->category ?? 'General',
            'date' => optional($announcement->created_at)->format('F d, Y'),
            'description' => $announcement->content ?? $announcement->description ?? '',
            'images' => $announcement->images->map(fn ($image) => asset('storage/' . $image->path))->values(),
        ]];
    })->toArray();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    const announcements = @json($announcementPayload);
    const modal = document.getElementById('citizenAnnouncementModal');
    const viewer = document.getElementById('citizenAnnouncementViewer');
    const gallery = document.getElementById('citizenAnnouncementGallery');
    const viewerImage = document.getElementById('citizenAnnouncementViewerImage');
    let currentImages = [];
    let currentImageIndex = 0;
    let zoom = 1;
    let panX = 0;
    let panY = 0;
    let dragStartX = 0;
    let dragStartY = 0;

    function updateImageTransform() {
        viewerImage.style.transform = `translate(${panX}px, ${panY}px) scale(${zoom})`;
    }

    function openAnnouncement(id) {
        const announcement = announcements[id];
        if (!announcement) return;
        document.getElementById('citizenAnnouncementTitle').textContent = announcement.title;
        document.getElementById('citizenAnnouncementCategory').textContent = announcement.category;
        document.getElementById('citizenAnnouncementDate').textContent = `Posted ${announcement.date}`;
        document.getElementById('citizenAnnouncementDescription').textContent = announcement.description;
        currentImages = announcement.images || [];
        gallery.innerHTML = '';
        currentImages.forEach((src, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = index === 0 ? 'active' : '';
            button.innerHTML = `<img src="${src}" alt="${announcement.title} image ${index + 1}">`;
            button.addEventListener('click', () => openViewer(index));
            gallery.appendChild(button);
        });
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function openViewer(index) {
        if (!currentImages[index]) return;
        currentImageIndex = index;
        viewerImage.src = currentImages[index];
        zoom = 1;
        panX = 0;
        panY = 0;
        updateImageTransform();
        viewer.hidden = false;
        document.getElementById('citizenAnnouncementPrevious').classList.toggle('is-hidden', currentImages.length < 2);
        document.getElementById('citizenAnnouncementNext').classList.toggle('is-hidden', currentImages.length < 2);
    }

    function closeViewer() { viewer.hidden = true; }
    function setZoom(nextZoom) {
        zoom = Math.min(Math.max(nextZoom, .5), 3);
        if (zoom <= 1) {
            panX = 0;
            panY = 0;
        }
        updateImageTransform();
    }
    function closeAnnouncement() {
        closeViewer();
        modal.hidden = true;
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.announcement-modal-trigger').forEach((card) => {
        card.addEventListener('click', () => openAnnouncement(card.dataset.announcementId));
    });
    document.getElementById('citizenAnnouncementClose').addEventListener('click', closeAnnouncement);
    document.getElementById('citizenAnnouncementViewerClose').addEventListener('click', closeViewer);
    document.getElementById('citizenAnnouncementPrevious').addEventListener('click', () => {
        openViewer((currentImageIndex - 1 + currentImages.length) % currentImages.length);
    });
    document.getElementById('citizenAnnouncementNext').addEventListener('click', () => {
        openViewer((currentImageIndex + 1) % currentImages.length);
    });
    document.getElementById('citizenAnnouncementZoomIn').addEventListener('click', () => {
        setZoom(zoom + .25);
    });
    document.getElementById('citizenAnnouncementZoomOut').addEventListener('click', () => {
        setZoom(zoom - .25);
    });
    document.getElementById('citizenAnnouncementZoomReset').addEventListener('click', () => {
        setZoom(1);
    });
    viewer.addEventListener('wheel', (event) => {
        event.preventDefault();
        setZoom(zoom + (event.deltaY < 0 ? .15 : -.15));
    }, { passive: false });
    viewerImage.addEventListener('dblclick', () => setZoom(zoom > 1 ? 1 : 2));
    viewerImage.addEventListener('pointerdown', (event) => {
        if (zoom <= 1) return;
        viewerImage.setPointerCapture(event.pointerId);
        viewerImage.classList.add('is-dragging');
        dragStartX = event.clientX - panX;
        dragStartY = event.clientY - panY;
    });
    viewerImage.addEventListener('pointermove', (event) => {
        if (!viewerImage.classList.contains('is-dragging')) return;
        panX = event.clientX - dragStartX;
        panY = event.clientY - dragStartY;
        updateImageTransform();
    });
    viewerImage.addEventListener('pointerup', () => viewerImage.classList.remove('is-dragging'));
    viewerImage.addEventListener('pointercancel', () => viewerImage.classList.remove('is-dragging'));
    modal.addEventListener('click', (event) => { if (event.target === modal) closeAnnouncement(); });
    viewer.addEventListener('click', (event) => { if (event.target === viewer) closeViewer(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (!viewer.hidden) closeViewer();
            else if (!modal.hidden) closeAnnouncement();
        }
        if (!viewer.hidden && event.key === 'ArrowLeft') {
            openViewer((currentImageIndex - 1 + currentImages.length) % currentImages.length);
        }
        if (!viewer.hidden && event.key === 'ArrowRight') {
            openViewer((currentImageIndex + 1) % currentImages.length);
        }
    });
});
</script>