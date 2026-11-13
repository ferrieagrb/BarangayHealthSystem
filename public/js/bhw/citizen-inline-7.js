document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('citizenStatusFilterModal');
    const openButton = document.getElementById('openCitizenStatusFilter');
    const closeButton = document.getElementById('closeCitizenStatusFilter');
    const clearButton = document.getElementById('clearCitizenStatusFilters');
    const applyButton = document.getElementById('applyCitizenStatusFilters');

    if (!modal || !openButton || !closeButton || !clearButton || !applyButton) return;

    function openModal() {
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        openButton.classList.add('active');
        document.body.style.overflow = 'hidden';
        closeButton.focus();
    }

    function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        openButton.classList.remove('active');
        document.body.style.overflow = '';
        openButton.focus();
    }

    openButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });

    const params = new URLSearchParams(window.location.search);
    const savedSort = params.get('sort') || 'Alphabetical';
    const sortInput = Array.from(modal.querySelectorAll('input[name="sort"]'))
        .find(input => input.value === savedSort);
    if (sortInput) sortInput.checked = true;

    const savedSex = params.get('filter_sex') || 'all';
    const sexInput = Array.from(modal.querySelectorAll('input[name="filter_sex"]'))
        .find(input => input.value === savedSex);
    if (sexInput) sexInput.checked = true;

    [
        ['filter_housing', 'filter_housing[]'],
        ['filter_health', 'filter_health[]'],
        ['health_filter_vaccine', 'health_filter_vaccine']
    ].forEach(function ([key, inputName]) {
        const values = [...params.getAll(`${key}[]`), ...params.getAll(key)];
        values.forEach(function (value) {
            const input = Array.from(modal.querySelectorAll(`input[name="${inputName}"]`))
                .find(item => item.value === value);
            if (input) input.checked = true;
        });
    });

    const deceased = document.getElementById('filterIncludeDeceased');
    if (deceased) deceased.checked = params.get('filter_deceased') === '1';

    clearButton.addEventListener('click', function () {
        const defaultSort = modal.querySelector('input[name="sort"][value="Alphabetical"]');
        if (defaultSort) defaultSort.checked = true;

        const allSex = modal.querySelector('input[name="filter_sex"][value="all"]');
        if (allSex) allSex.checked = true;

        modal.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
            input.checked = false;
        });
    });

    applyButton.addEventListener('click', function () {
        const url = new URL(window.location.href);
        const query = url.searchParams;

        [
            'sort',
            'filter_sex',
            'filter_housing',
            'filter_housing[]',
            'filter_health',
            'filter_health[]',
            'health_filter_vaccine',
            'health_filter_vaccine[]',
            'filter_deceased',
            'page'
        ].forEach(key => query.delete(key));

        const selectedSort = modal.querySelector('input[name="sort"]:checked');
        if (selectedSort) {
            query.set('sort', selectedSort.value);
        }

        const selectedSex = modal.querySelector('input[name="filter_sex"]:checked');
        if (selectedSex && selectedSex.value !== 'all') {
            query.set('filter_sex', selectedSex.value);
        }

        modal.querySelectorAll('input[name="filter_housing[]"]:checked').forEach(function (input) {
            query.append('filter_housing[]', input.value);
        });

        modal.querySelectorAll('input[name="filter_health[]"]:checked').forEach(function (input) {
            query.append('filter_health[]', input.value);
        });

        modal.querySelectorAll('input[name="health_filter_vaccine"]:checked').forEach(function (input) {
            query.append('health_filter_vaccine[]', input.value);
        });

        if (deceased && deceased.checked) {
            query.set('filter_deceased', '1');
        }

        window.location.href = url.toString();
    });
});
