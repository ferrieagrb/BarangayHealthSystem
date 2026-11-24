<?php
    $faviconUrl = route('site.favicon');
    $faviconVersion = substr(md5((string) \App\Models\SystemSetting::value('site_logo', 'images/amuyong.png')), 0, 8);
    $faviconUrl .= '?v=' . $faviconVersion;
?>
<link rel="icon" type="image/png" sizes="any" href="<?php echo e($faviconUrl); ?>">
<link rel="shortcut icon" type="image/png" href="<?php echo e($faviconUrl); ?>">
<link rel="apple-touch-icon" href="<?php echo e($faviconUrl); ?>">
<?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/partials/favicon.blade.php ENDPATH**/ ?>