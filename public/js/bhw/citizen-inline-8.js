document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.querySelector('.citizen-filter-form');
    const searchInput = searchForm?.querySelector('input[name="search"]');
    const ageInput = searchForm?.querySelector('input[name="age"]');

    if (!searchForm) return;

    let searchTimer = null;
    let requestController = null;

    function triggerLiveSearch(delay = 350) {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(async function () {
            if (requestController) requestController.abort();
            requestController = new AbortController();

            const params = new URLSearchParams(window.location.search);
            ['search', 'purok', 'subgroup', 'age', 'page'].forEach(key => params.delete(key));

            const formData = new FormData(searchForm);
            ['search', 'purok', 'subgroup', 'age'].forEach(function (key) {
                const value = formData.get(key);
                if (value !== null && String(value).trim() !== '') {
                    params.set(key, String(value).trim());
                }
            });

            const results = document.getElementById('citizen-results');
            if (!results) return;

            results.setAttribute('aria-busy', 'true');

            try {
                const requestUrl = new URL(searchForm.action, window.location.origin);
                params.forEach((value, key) => requestUrl.searchParams.append(key, value));

                const response = await fetch(requestUrl.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    signal: requestController.signal
                });

                if (!response.ok) {
                    throw new Error(`Search request failed (${response.status})`);
                }

                const html = await response.text();
                const parsed = new DOMParser().parseFromString(html, 'text/html');
                const updatedResults = parsed.getElementById('citizen-results');

                if (!updatedResults) {
                    throw new Error('Search response did not contain citizen results.');
                }

                results.replaceWith(updatedResults);
                window.history.replaceState({}, '', requestUrl.toString());
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Citizen search failed:', error);
                }
            } finally {
                document.getElementById('citizen-results')?.removeAttribute('aria-busy');
            }
        }, delay);
    }

    searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        triggerLiveSearch(0);
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => triggerLiveSearch());
        searchInput.addEventListener('search', () => triggerLiveSearch(0));
    }

    if (ageInput) {
        ageInput.addEventListener('input', () => triggerLiveSearch());
        ageInput.addEventListener('change', () => triggerLiveSearch(0));
    }
});
