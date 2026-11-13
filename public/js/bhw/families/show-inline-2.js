document.addEventListener('DOMContentLoaded', function () {
    initializeFamilyRegistration();

    const search = document.getElementById('familySearchInput');
    if (search) {
        let searchTimer;
        search.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => document.getElementById('familySearchForm').submit(), 500);
        });
    }

    // Flash success messages as a brief popup toast instead of a page banner.
    const toast = document.getElementById('familyPageAlert');
    if (toast) {
        window.setTimeout(function () {
            if (!toast.isConnected) return;
            toast.classList.add('is-hiding');
            window.setTimeout(function () {
                if (toast.isConnected) toast.remove();
            }, 220);
        }, 4500);
    }
});
