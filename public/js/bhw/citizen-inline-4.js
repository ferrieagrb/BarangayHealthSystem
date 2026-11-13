document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('citizenDeleteConfirmModal');
    const nameElement = document.getElementById('confirmCitizenName');
    const cancelButton = document.getElementById('cancelCitizenDelete');
    const confirmButton = document.getElementById('confirmCitizenDelete');

    if (!modal || !nameElement || !cancelButton || !confirmButton) return;

    let selectedForm = null;

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('.deleteCitizenForm');
        if (!form) return;

        event.preventDefault();
        selectedForm = form;
        nameElement.textContent = form.dataset.citizenName || 'this citizen';
        modal.classList.add('active');
    });

    cancelButton.addEventListener('click', function () {
        selectedForm = null;
        modal.classList.remove('active');
    });

    confirmButton.addEventListener('click', function () {
        if (!selectedForm) return;

        const formToSubmit = selectedForm;
        selectedForm = null;
        modal.classList.remove('active');
        if (typeof window.showLoader === 'function') window.showLoader();
        formToSubmit.submit();
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            selectedForm = null;
            modal.classList.remove('active');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('active')) {
            selectedForm = null;
            modal.classList.remove('active');
        }
    });
});
