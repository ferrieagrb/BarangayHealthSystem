<section class="bheams-id-card" aria-label="Citizen Health ID">
    <div class="bheams-id-decoration"></div>

    <div class="bheams-id-header">
        <div class="bheams-seal">
            <img src="<?php echo e(asset('images/amuyong.png')); ?>" alt="BHEAMS Logo" class="bheams-logo">
        </div>
        <div class="bheams-brand">
            <h2>BHEAMS</h2>
            <p>BARANGAY AMUYONG</p>
            <span>BARANGAY HEALTH AND EMERGENCY MANAGEMENT SYSTEM</span>
        </div>
    </div>

    <div class="bheams-id-body">
        <div class="bheams-photo">
            <?php if($citizen->photo_path): ?>
                <img src="<?php echo e(route('citizen.photo', $citizen->id)); ?>" alt="Citizen Photo">
            <?php else: ?>
                <div class="bheams-no-photo">
                    <span><?php echo e(strtoupper(substr($citizen->Citizen_FName, 0, 1) . substr($citizen->Citizen_LName, 0, 1))); ?></span>
                    <small>NO PHOTO</small>
                </div>
            <?php endif; ?>
        </div>

        <div class="bheams-id-details">
            <div class="bheams-id-title">CITIZEN HEALTH ID</div>
            <div class="bheams-id-field">
                <span>FULL NAME</span>
                <strong><?php echo e(strtoupper($citizen->Citizen_LName)); ?>, <?php echo e(strtoupper($citizen->Citizen_FName)); ?></strong>
            </div>
            <div class="bheams-id-field">
                <span>CITIZEN IDENTIFICATION NUMBER</span>
                <strong class="bheams-citizen-number">C-<?php echo e(str_pad($citizen->id, 4, '0', STR_PAD_LEFT)); ?></strong>
            </div>
            <div class="bheams-id-two-columns">
                <div class="bheams-id-field">
                    <span>DATE OF BIRTH</span>
                    <strong><?php echo e($citizen->Citizen_BirthDate ? \Carbon\Carbon::parse($citizen->Citizen_BirthDate)->format('M d, Y') : 'N/A'); ?></strong>
                </div>
            </div>
            <div class="bheams-id-field">
                <span>ADDRESS / PUROK</span>
                <strong><?php echo e($citizen->Citizen_Purok ?: 'N/A'); ?></strong>
            </div>
            <div class="bheams-id-field">
                <span>SEX</span>
                <strong><?php echo e($citizen->Citizen_Sex ?: 'N/A'); ?></strong>
            </div>
            <div class="bheams-id-field">
                <span>AGE</span>
                <strong><?php echo e($citizen->Citizen_Age !== null && $citizen->Citizen_Age !== '' ? $citizen->Citizen_Age : 'N/A'); ?></strong>
            </div>
            <div class="bheams-id-field">
                <span>CONTACT NUMBER</span>
                <strong><?php echo e($citizen->Citizen_ContactNo ?: 'N/A'); ?></strong>
            </div>
        </div>

        <div class="bheams-id-qr bheams-id-qr-centered">
            <div class="bheams-qr-frame">
                <?php if(!empty($qrImage)): ?>
                    <img src="<?php echo e($qrImage); ?>" alt="Citizen Verification QR Code">
                <?php else: ?>
                    <span>QR code unavailable</span>
                <?php endif; ?>
            </div>
            <strong>SCAN TO VERIFY</strong>
            <small>Citizen Record Verification</small>
        </div>
    </div>

    <div class="bheams-id-footer">
        <span>BARANGAY AMUYONG</span>
        <span>OFFICIAL CITIZEN IDENTIFICATION</span>
    </div>
</section>
<?php /**PATH C:\Users\HUAWEI\Desktop\BarangayHealthSystem\resources\views/partials/health-card.blade.php ENDPATH**/ ?>