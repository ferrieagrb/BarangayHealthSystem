<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($announcement->title); ?> | Barangay Amuyong</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/citizenhome.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/citizenann.css')); ?>">
    <?php echo $__env->make('partials.button-action-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>
        .announcement-detail {
            max-width: 960px;
            margin: 48px auto 72px;
        }

        .announcement-detail-header {
            margin-bottom: 28px;
        }

        .announcement-detail-title {
            color: var(--secondary-color);
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            line-height: 1.15;
            margin: 16px 0 10px;
        }

        .announcement-detail-date {
            color: var(--text-muted);
            font-size: .9rem;
        }

        .announcement-detail-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr));
            gap: 14px;
            margin-bottom: 32px;
        }

        .announcement-detail-gallery button.gallery-trigger {
            display: block;
            width: 100%;
            height: auto;
            border: 0;
            padding: 0;
            cursor: zoom-in;
            background: transparent;
            border-radius: 8px;
            overflow: hidden;
        }

        .announcement-detail-gallery:has(> button:only-child) {
            grid-template-columns: minmax(0, 1fr);
        }

        .announcement-detail-gallery:has(> button:only-child) img {
            aspect-ratio: auto;
            max-height: 560px;
            object-fit: contain;
            background: #e2e8f0;
        }

        .announcement-detail-gallery img {
            display: block;
            width: 100%;
            height: auto;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            transition: transform .2s ease;
        }

        body.announcement-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
        }

        body.announcement-page > main {
            flex: 1 0 auto;
            width: 100%;
        }

        body.announcement-page > footer {
            flex-shrink: 0;
        }

        .announcement-detail-gallery button:hover img {
            transform: scale(1.03);
        }

        .announcement-detail-description {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.8;
            white-space: pre-line;
        }

        .lightbox[hidden] {
            display: none;
        }

        .lightbox {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: grid;
            place-items: center;
            padding: 24px;
            background: rgba(15, 23, 42, .92);
            overscroll-behavior: contain;
            overflow: hidden;
            isolation: isolate;
        }

        .lightbox-stage {
            position: fixed;
            inset: 0;
            z-index: 1;
            width: 100vw;
            height: 100vh;
            display: grid;
            place-items: center;
            overflow: hidden;
            touch-action: none;
        }

        .lightbox-image {
            display: block;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transform: scale(1);
            transition: transform .15s ease;
            user-select: none;
            cursor: grab;
            position: relative;
            z-index: 1;
        }

        .lightbox-image.is-dragging {
            cursor: grabbing;
            transition: none;
        }

        .lightbox-toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 8px;
            z-index: 999999 !important;
            pointer-events: auto !important;
        }

        .lightbox button {
            width: 42px;
            height: 42px;
            border: 1px solid rgba(255,255,255,.3);
            border-radius: 6px;
            background: rgba(15,23,42,.7);
            color: #fff;
            cursor: pointer;
            position: relative;
            z-index: 1000000 !important;
            pointer-events: auto !important;
        }

        .lightbox-nav {
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            z-index: 999999 !important;
            pointer-events: auto !important;
        }

        .lightbox-nav[hidden] {
            display: none !important;
        }

        .lightbox-prev { left: 20px; }
        .lightbox-next { right: 20px; }

        @media (max-width: 600px) {
            .announcement-detail { margin-top: 28px; }
            .announcement-detail-gallery { grid-template-columns: 1fr; }
            .lightbox { padding: 12px; }
                .lightbox-stage { width: 100vw; height: 100vh; }
            .lightbox-prev { left: 8px; }
            .lightbox-next { right: 8px; }
        }
    </style>
    <link rel="stylesheet" href="<?php echo e(asset('css/typography.css')); ?>">
<?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body class="announcement-page">
    <?php echo $__env->make('public.partials.header', ['activeNav' => 'announcements'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="container announcement-detail">
        <a href="<?php echo e(route('public.announcements')); ?>" class="view-all"><i class="fa-solid fa-arrow-left"></i> All Announcements</a>
        <div class="announcement-detail-header">
            <span class="category-badge"><?php echo e($announcement->category ?? 'General'); ?></span>
            <h1 class="announcement-detail-title"><?php echo e($announcement->title); ?></h1>
            <time class="announcement-detail-date"><i class="fa-regular fa-clock"></i> Posted <?php echo e($announcement->created_at->format('F d, Y')); ?></time>
        </div>

        <div class="announcement-detail-gallery">
            <?php $__currentLoopData = $announcement->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" class="gallery-trigger" data-index="<?php echo e($index); ?>" aria-label="Open image <?php echo e($index + 1); ?>">
                    <img src="<?php echo e($image->url); ?>" alt="<?php echo e($announcement->title); ?> image <?php echo e($index + 1); ?>">
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <p class="announcement-detail-description"><?php echo e($announcement->description); ?></p>
    </main>

    <?php echo $__env->make('public.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="lightbox" id="announcementLightbox" hidden role="dialog" aria-modal="true" aria-label="Announcement image viewer">
        <div class="lightbox-toolbar">
            <button type="button" id="zoomOut" title="Zoom out" aria-label="Zoom out"><i class="fa-solid fa-minus"></i></button>
            <button type="button" id="zoomIn" title="Zoom in" aria-label="Zoom in"><i class="fa-solid fa-plus"></i></button>
            <button type="button" id="zoomReset" title="Reset zoom" aria-label="Reset zoom"><i class="fa-solid fa-arrows-to-dot"></i></button>
            <button type="button" id="lightboxClose" title="Close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <button type="button" class="lightbox-nav lightbox-prev" id="lightboxPrev" title="Previous image" aria-label="Previous image"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="lightbox-stage">
            <img class="lightbox-image" id="lightboxImage" src="" alt="">
        </div>
        <button type="button" class="lightbox-nav lightbox-next" id="lightboxNext" title="Next image" aria-label="Next image"><i class="fa-solid fa-chevron-right"></i></button>
    </div>

    <script>
        const imageSources = <?php echo json_encode($announcement->images->map(fn ($image) => $image->url)->values(), 15, 512) ?>;
        const lightbox = document.getElementById('announcementLightbox');
        const lightboxImage = document.getElementById('lightboxImage');
        let currentIndex = 0;
        let zoom = 1;
        let lockedScrollY = 0;
        let panX = 0;
        let panY = 0;
        let dragStartX = 0;
        let dragStartY = 0;

        function updateImageTransform() {
            lightboxImage.style.transform = `translate(${panX}px, ${panY}px) scale(${zoom})`;
        }

        function showImage(index) {
            currentIndex = (index + imageSources.length) % imageSources.length;
            lightboxImage.src = imageSources[currentIndex];
            lightboxImage.alt = `Announcement image ${currentIndex + 1}`;
            zoom = 1;
            panX = 0;
            panY = 0;
            updateImageTransform();
        }

        function closeLightbox() {
            lightbox.hidden = true;
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';
            document.body.style.overflow = '';
            window.scrollTo(0, lockedScrollY);
        }

        function setZoom(nextZoom) {
            zoom = Math.min(Math.max(nextZoom, .5), 3);
            if (zoom <= 1) {
                panX = 0;
                panY = 0;
            }
            updateImageTransform();
        }

        document.querySelectorAll('.gallery-trigger').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                showImage(Number(trigger.dataset.index));
                lightbox.hidden = false;
                lockedScrollY = window.scrollY;
                document.body.style.position = 'fixed';
                document.body.style.top = `-${lockedScrollY}px`;
                document.body.style.width = '100%';
                document.body.style.overflow = 'hidden';
                document.getElementById('lightboxPrev').hidden = imageSources.length < 2;
                document.getElementById('lightboxNext').hidden = imageSources.length < 2;
            });
        });

        document.getElementById('lightboxClose').addEventListener('click', closeLightbox);
        document.getElementById('lightboxPrev').addEventListener('click', () => showImage(currentIndex - 1));
        document.getElementById('lightboxNext').addEventListener('click', () => showImage(currentIndex + 1));
        document.getElementById('zoomIn').addEventListener('click', () => {
            setZoom(zoom + .25);
        });
        document.getElementById('zoomOut').addEventListener('click', () => {
            setZoom(zoom - .25);
        });
        document.getElementById('zoomReset').addEventListener('click', () => showImage(currentIndex));
        lightbox.addEventListener('wheel', (event) => {
            event.preventDefault();
            setZoom(zoom + (event.deltaY < 0 ? .15 : -.15));
        }, { passive: false });
        lightboxImage.addEventListener('pointerdown', (event) => {
            if (zoom <= 1) return;
            lightboxImage.setPointerCapture(event.pointerId);
            lightboxImage.classList.add('is-dragging');
            dragStartX = event.clientX - panX;
            dragStartY = event.clientY - panY;
        });
        lightboxImage.addEventListener('pointermove', (event) => {
            if (!lightboxImage.classList.contains('is-dragging')) return;
            panX = event.clientX - dragStartX;
            panY = event.clientY - dragStartY;
            updateImageTransform();
        });
        lightboxImage.addEventListener('pointerup', () => lightboxImage.classList.remove('is-dragging'));
        lightboxImage.addEventListener('pointercancel', () => lightboxImage.classList.remove('is-dragging'));
        lightboxImage.addEventListener('dblclick', () => setZoom(zoom > 1 ? 1 : 2));
        lightbox.addEventListener('click', (event) => {
            if (event.target === lightbox) closeLightbox();
        });
        document.addEventListener('keydown', (event) => {
            if (lightbox.hidden) return;
            if (event.key === 'Escape') closeLightbox();
            if (event.key === 'ArrowLeft') showImage(currentIndex - 1);
            if (event.key === 'ArrowRight') showImage(currentIndex + 1);
        });
    </script>
</body>
</html><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/citizenannouncement_show.blade.php ENDPATH**/ ?>