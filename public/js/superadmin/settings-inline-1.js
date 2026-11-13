document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('systemSettingsForm');
    const input = document.getElementById('logoUpload');
    const modal = document.getElementById('logoPreviewModal');
    const preview = document.getElementById('newLogoPreview');
    const cancel = document.getElementById('cancelLogoUpload');
    const confirm = document.getElementById('confirmLogoUpload');

    if (!form || !input || !modal || !preview || !cancel || !confirm) {
        return;
    }

    let previewObjectUrl = null;

    function closePreview() {
        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
        input.value = '';

        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }

        preview.removeAttribute('src');
        input.focus();
    }

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];

        if (!file) {
            return;
        }

        const allowedTypes = [
            'image/png',
            'image/jpeg',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {
            alert('Please select a PNG, JPG, or WEBP image.');
            input.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            alert('The selected image must not exceed 2 MB.');
            input.value = '';
            return;
        }

        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
        }

        previewObjectUrl = URL.createObjectURL(file);
        preview.src = previewObjectUrl;

        modal.classList.add('is-visible');
        modal.setAttribute('aria-hidden', 'false');
        cancel.focus();
    });

    cancel.addEventListener('click', closePreview);

    confirm.addEventListener('click', function () {
        if (!input.files || !input.files[0]) {
            closePreview();
            return;
        }

        confirm.disabled = true;
        confirm.textContent = 'Saving...';

        form.requestSubmit();
    });

    form.addEventListener('submit', function () {
        confirm.disabled = true;
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closePreview();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-visible')) {
            closePreview();
        }
    });
});
