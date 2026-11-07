document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('citizen-photo-form');
    if (!form) return;

    const input = document.getElementById('photo');
    const preview = document.getElementById('selected-photo-preview');
    const previewImage = document.getElementById('selected-photo-image');
    const confirmImage = document.getElementById('confirm-photo-image');
    const error = document.getElementById('photo-preview-error');
    const dialog = document.getElementById('photo-confirm-dialog');
    const cancel = document.getElementById('photo-confirm-cancel');
    const confirm = document.getElementById('photo-confirm-save');
    let previewUrl = null;
    let confirmed = false;

    const autoConfirm = input.hasAttribute('data-auto-confirm');

    function clearPreview() {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        if (previewImage) previewImage.removeAttribute('src');
        confirmImage.removeAttribute('src');
        if (preview) preview.hidden = true;
    }

    function setError(message) {
        input.setCustomValidity(message);
        error.textContent = message;
        error.hidden = !message;
    }

    input.addEventListener('change', function () {
        clearPreview();
        setError('');
        const file = input.files[0];
        if (!file) return;

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            setError('Please choose a JPG, PNG, or WEBP image.');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            setError('The photo must be 2 MB or smaller.');
            return;
        }

        previewUrl = URL.createObjectURL(file);
        if (previewImage) previewImage.src = previewUrl;
        if (preview) preview.hidden = false;
        confirmImage.src = previewUrl;
        if (autoConfirm) dialog.showModal();
    });

    confirmImage.addEventListener('error', function () {
        if (!previewUrl) return;
        if (dialog.open) dialog.close();
        input.value = '';
        clearPreview();
        setError('The selected photo could not be displayed. Please choose another image.');
    });

    dialog.addEventListener('close', function () {
        if (autoConfirm && !confirmed) {
            input.value = '';
            clearPreview();
        }
    });

    form.addEventListener('submit', function (event) {
        if (confirmed) {
            confirmed = false;
            confirm.disabled = true;
            return;
        }
        event.preventDefault();
        dialog.showModal();
    });

    cancel.addEventListener('click', function () {
        dialog.close();
    });

    confirm.addEventListener('click', function () {
        confirmed = true;
        confirm.disabled = true;
        dialog.close();
        form.submit();
    });

});
