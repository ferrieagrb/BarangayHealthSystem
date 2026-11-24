<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Amuyong | Announcements</title>
    
    <!-- Google Fonts & FontAwesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo e(asset('css/citizenhome.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/citizenann.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/typography.css')); ?>">
    <?php echo $__env->make('partials.button-action-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body>

    <?php echo $__env->make('public.partials.header', ['activeNav' => 'announcements'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Hero Section with Background Image -->
    <section class="hero page-hero" style="background-image: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(30, 41, 59, 0.85) 100%), url('<?php echo e(asset('images/mission.jpg')); ?>');">
        <div class="container hero-content">
            <span class="badge">Official Updates</span>
            <h1 class="hero-title">Barangay <br><span class="text-accent">Announcements</span></h1>
            <p class="hero-description">Stay informed with official notices, health advisories, and community news from Barangay Amuyong.</p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container main-content">
        <div class="section-header">
            <h2><i class="fa-solid fa-bullhorn"></i> Public Bulletins</h2>
        </div>

        <?php if($announcements->isEmpty()): ?>
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <p>No announcements available at the moment.</p>
            </div>
        <?php else: ?>
            <div class="announcements-grid">
                <?php $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('public.announcements.show', $announcement)); ?>" class="announcement-card">
                        <div class="announcement-gallery">
                            <?php $logoUrl = \App\Models\SystemSetting::logoUrl(); ?>
                            <?php $__empty_1 = true; $__currentLoopData = $announcement->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <img src="<?php echo e($image->url); ?>" alt="<?php echo e($announcement->title); ?>" onerror="this.onerror=null;this.src='<?php echo e($logoUrl); ?>';this.classList.add('is-logo');">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e($announcement->title); ?>" class="is-logo">
                            <?php endif; ?>
                        </div>
                        <div class="announcement-header">
                            <span class="category-badge"><?php echo e($announcement->category ?? 'General'); ?></span>
                            <time class="post-date"><i class="fa-regular fa-clock"></i> <?php echo e($announcement->created_at->format('M d, Y')); ?></time>
                        </div>
                        <h3 class="announcement-title"><?php echo e($announcement->title); ?></h3>
                        <p class="announcement-body"><?php echo e($announcement->description); ?></p>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </main>

    <?php echo $__env->make('public.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html><?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/citizenannouncements.blade.php ENDPATH**/ ?>