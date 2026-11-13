document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('citizenStatusFilterModal');
    const openButton = document.getElementById('openCitizenStatusFilter');
    const closeButton = document.getElementById('closeCitizenStatusFilter');
    const clearButton = document.getElementById('clearCitizenStatusFilters');
    const applyButton = document.getElementById('applyCitizenStatusFilters');
    if (!modal || !openButton || !closeButton || !clearButton || !applyButton) return;

    const sexInputs = Array.from(modal.querySelectorAll('input[name="health_filter_sex"]'));
    const housingInputs = Array.from(modal.querySelectorAll('input[name="health_filter_housing"]'));
    const healthInputs = Array.from(modal.querySelectorAll('input[name="health_filter_health"]'));
    const vaccineInputs = Array.from(modal.querySelectorAll('input[name="health_filter_vaccine"]'));
    const deceasedInput = document.getElementById('healthFilterIncludeDeceased');
    const params = new URLSearchParams(window.location.search);
    const selectedSex = params.get('health_filter_sex') || 'all';
    sexInputs.forEach(input => { input.checked = input.value === selectedSex; });
    [
        ['health_filter_housing', housingInputs],
        ['health_filter_health', healthInputs],
        ['health_filter_vaccine', vaccineInputs]
    ].forEach(([key, inputs]) => {
        const selected = [...params.getAll(`${key}[]`), ...params.getAll(key)];
        inputs.forEach(input => { input.checked = selected.includes(input.value); });
    });
    deceasedInput.checked = params.get('filter_deceased') === '1';

    function openModal() {
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('citizen-status-modal-open');
        openButton.classList.add('active');
    }
    function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('citizen-status-modal-open');
        openButton.classList.remove('active');
    }
    function applyFilters() {
        const url = new URL(window.location.href);
        const query = url.searchParams;
        [
            'health_filter_sex',
            'health_filter_housing',
            'health_filter_housing[]',
            'health_filter_health',
            'health_filter_health[]',
            'health_filter_vaccine',
            'health_filter_vaccine[]',
            'filter_deceased',
            'page'
        ].forEach(key => query.delete(key));

        const sex = sexInputs.find(input => input.checked);
        if (sex && sex.value !== 'all') query.set('health_filter_sex', sex.value);

        [
            ['health_filter_housing', housingInputs],
            ['health_filter_health', healthInputs],
            ['health_filter_vaccine', vaccineInputs]
        ].forEach(([key, inputs]) => {
            inputs.filter(input => input.checked).forEach(input => {
                query.append(`${key}[]`, input.value);
            });
        });

        if (deceasedInput.checked) query.set('filter_deceased', '1');
        window.location.href = url.toString();
    }

    openButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);
    modal.addEventListener('click', event => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && modal.classList.contains('active')) closeModal();
    });
    clearButton.addEventListener('click', function () {
        sexInputs.forEach(input => { input.checked = input.value === 'all'; });
        housingInputs.forEach(input => { input.checked = false; });
        healthInputs.forEach(input => { input.checked = false; });
        vaccineInputs.forEach(input => { input.checked = false; });
        deceasedInput.checked = false;
    });
    applyButton.addEventListener('click', applyFilters);
});
