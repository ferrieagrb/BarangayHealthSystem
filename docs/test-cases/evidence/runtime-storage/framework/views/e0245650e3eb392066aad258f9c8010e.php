<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Amuyong | Health & Community Portal</title>
    
    <!-- Google Fonts & FontAwesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo e(asset('css/citizenhome.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/typography.css')); ?>">
    <?php echo $__env->make('partials.button-action-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>

    <?php echo $__env->make('public.partials.header', ['activeNav' => 'home'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Hero Section -->
    <section class="hero" style="background-image: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(30, 41, 59, 0.85) 100%), url('<?php echo e(asset('images/mission.jpg')); ?>');">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <span class="badge">Community Health Care</span>
            <h1 class="hero-title">Aksyon sa <br><span class="text-accent">Iyong Kalusugan</span></h1>
            <p class="hero-description">Connecting residents to accessible health services, real-time announcements, and community updates.</p>
            
            <div class="hero-actions">
                <a href="<?php echo e(url('/contactdirectory')); ?>" class="btn btn-secondary"><i class="fa-solid fa-address-book"></i> Contact Directory</a>
            </div>
        </div>
    </section>

    <!-- Main Content Area -->
    <main class="container main-content">

        <!-- Recent Events Section -->
        <section class="content-section">
            <div class="section-header">
                <h2>Recent Events</h2>
                <a href="<?php echo e(route('publiccalendar')); ?>" class="view-all">View Calendar <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="cards-grid">
                <?php $__empty_1 = true; $__currentLoopData = $recentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="card event-list-item">
                        <div class="card-body">
                            <span class="card-date-badge">
                                <i class="fa-regular fa-calendar"></i> <?php echo e(\Carbon\Carbon::parse($event->start)->format('M d, Y')); ?>

                            </span>
                            <h3 class="card-title"><?php echo e($event->title); ?></h3>
                            <p class="card-text"><?php echo e(\Illuminate\Support\Str::limit($event->description ?? 'No description available', 120)); ?></p>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        <p>No recent events available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Announcements Section -->
        <section class="content-section">
            <div class="section-header">
                <h2>Announcements</h2>
                <a href="<?php echo e(route('public.announcements')); ?>" class="view-all">All Announcements <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="announcements-list">
                <?php $__empty_1 = true; $__currentLoopData = $recentAnnouncements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('public.announcements.show', $announcement)); ?>" class="announcement-item announcement-link">
                        <?php $logoUrl = \App\Models\SystemSetting::logoUrl(); ?>
                        <img src="<?php echo e($announcement->images->isNotEmpty() ? $announcement->images->first()->url : $logoUrl); ?>" alt="<?php echo e($announcement->title); ?>" class="announcement-image" onerror="this.onerror=null;this.src='<?php echo e($logoUrl); ?>';this.classList.add('is-logo');">
                        <div class="announcement-icon">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <div class="announcement-details">
                            <div class="announcement-meta">
                                <h3 class="announcement-title"><?php echo e($announcement->title); ?></h3>
                                <time class="announcement-date">
                                    <i class="fa-regular fa-clock"></i> <?php echo e(\Carbon\Carbon::parse($announcement->created_at)->format('M d, Y')); ?>

                                </time>
                            </div>
                            <p class="announcement-text"><?php echo e(\Illuminate\Support\Str::limit($announcement->description, 150)); ?></p>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <p>No announcements posted right now.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <?php echo $__env->make('public.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/citizenhome.blade.php ENDPATH**/ ?>