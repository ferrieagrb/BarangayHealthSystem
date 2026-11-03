(() => {
    const endpoint = document.querySelector('meta[name="medical-terminology-search-url"]')?.content;
    if (!endpoint) return;

    const selector = '[data-medical-autocomplete="medicine"], [data-medical-autocomplete="vaccine"], [data-medical-autocomplete="diagnosis"]';
    const initialized = new WeakSet();
    const activeRequests = new WeakMap();
    const pendingTimers = new WeakMap();
    let generatedId = 0;

    function normalize(value) {
        return String(value || '').toLocaleLowerCase().replace(/[^\p{L}\p{N}]+/gu, ' ').trim();
    }

    function localVaccineMatches(select, query) {
        const normalizedQuery = normalize(query);
        return Array.from(select.options)
            .filter(option => option.value && (option.dataset.code || option.dataset.external))
            .filter(option => {
                const searchable = normalize(`${option.value} ${option.dataset.code || ''} ${option.textContent}`);
                return searchable.includes(normalizedQuery);
            })
            .map(option => ({
                name: option.value,
                code: option.dataset.code || null,
                classification_code: option.dataset.cvxCode || null,
                official_name: option.dataset.formulation || null,
                formulation: option.dataset.formulation || null,
                formulation_notes: null,
                selection_token: option.dataset.selectionToken || null,
                source: 'BHEAMS vaccination options',
                recognition_status: 'recognized',
                eligibility_status: option.dataset.eligibilityStatus || (option.disabled ? 'unavailable' : 'available'),
                match_type: 'local'
            }));
    }

    function historicalModeEnabled() {
        return Boolean(document.getElementById('historical_recording')?.checked);
    }

    function availabilityLabel(availability) {
        return ({
            available: 'Available',
            unavailable: 'Unavailable',
            conditional: 'Conditional',
            unknown: 'Age eligibility not established'
        })[availability] || 'Age eligibility not established';
    }

    function makeSearchable(control, type) {
        const wrapper = document.createElement('div');
        wrapper.className = type === 'vaccine'
            ? 'medical-autocomplete medical-autocomplete-vaccine'
            : 'medical-autocomplete';
        control.parentNode.insertBefore(wrapper, control);

        let input = control;
        if (control instanceof HTMLSelectElement) {
            input = document.createElement('input');
            input.type = 'search';
            input.id = `${control.id || 'medical-vaccine'}-search-${++generatedId}`;
            input.autocomplete = 'off';
            input.spellcheck = false;
            input.placeholder = 'Search vaccines...';
            input.setAttribute('aria-label', 'Search vaccines');
            input.setAttribute('role', 'combobox');
            input.setAttribute('aria-autocomplete', 'list');
            input.setAttribute('aria-expanded', 'false');
            input.dataset.medicalAutocompleteSearch = 'true';
            wrapper.append(input);
            const selectedOption = control.selectedOptions[0];
            if (selectedOption?.value && !selectedOption.disabled) {
                input.value = selectedOption.value;
            }
        } else {
            wrapper.append(control);
            input.autocomplete = 'off';
            input.spellcheck = false;
            input.setAttribute('role', 'combobox');
            input.setAttribute('aria-autocomplete', 'list');
            input.setAttribute('aria-expanded', 'false');
        }

        const list = document.createElement('div');
        list.className = 'medical-autocomplete-results';
        list.id = `${input.id || 'medical-field'}-suggestions`;
        list.setAttribute('role', 'listbox');
        list.hidden = true;

        const status = document.createElement('div');
        status.className = 'medical-autocomplete-status';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        status.hidden = true;

        wrapper.append(list, status);
        input.setAttribute('aria-controls', list.id);

        if (control instanceof HTMLSelectElement) {
            wrapper.append(control);
        }

        let currentItems = [];
        let activeIndex = -1;
        let requestVersion = 0;

        function closeList() {
            list.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            activeIndex = -1;
        }

        function setStatus(message) {
            status.textContent = message || '';
            status.hidden = !message;
        }

        function syncSelect() {
            if (!(control instanceof HTMLSelectElement) || document.activeElement === input) return;
            const selected = control.selectedOptions[0];
            input.value = selected && !selected.disabled ? selected.value : '';
        }

        function vaccineOptionFor(item) {
            if (!(control instanceof HTMLSelectElement)) return null;
            if (item.code) {
                return Array.from(control.options).find(option =>
                    option.value && (
                        option.dataset.code === String(item.code)
                        || (!option.dataset.external && normalize(option.value) === normalize(item.name))
                    )
                ) || null;
            }

            if (item.selection_token && item.classification_code) {
                return Array.from(control.options).find(option =>
                    option.dataset.external && option.dataset.cvxCode === String(item.classification_code)
                ) || null;
            }

            return null;
        }

        function vaccineAvailability(item) {
            if (type !== 'vaccine') return null;
            const option = vaccineOptionFor(item);
            if (option) return option.dataset.eligibilityStatus || (option.disabled ? 'unavailable' : 'available');
            return item.eligibility_status || 'unknown';
        }

        function render(items, query) {
            const seen = new Set();
            currentItems = items.map(item => {
                if (!item || type !== 'vaccine') return item;
                return { ...item, eligibility_status: vaccineAvailability(item) };
            }).filter(item => {
                if (!item || !item.name) return false;
                const key = item.selection_token && !item.code
                    ? `${normalize(item.name)}|cvx:${item.classification_code || ''}`
                    : normalize(item.name);
                if (!key || seen.has(key)) return false;
                seen.add(key);
                return true;
            }).slice(0, 8);

            list.replaceChildren();
            activeIndex = -1;

            currentItems.forEach((item, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'medical-autocomplete-option';
                button.id = `${list.id}-option-${index}`;
                button.setAttribute('role', 'option');
                button.setAttribute('aria-selected', 'false');
                const availability = type === 'vaccine' ? item.eligibility_status : null;
                if (availability && availability !== 'available' && !historicalModeEnabled()) {
                    button.disabled = true;
                    button.setAttribute('aria-disabled', 'true');
                }

                const heading = document.createElement('span');
                heading.className = 'medical-autocomplete-option-heading';
                const name = document.createElement('span');
                name.className = 'medical-autocomplete-name';
                name.textContent = item.match_type === 'approximate' ? `Did you mean: ${item.name}?` : item.name;
                heading.append(name);
                if (type === 'vaccine' && item.recognition_status === 'recognized') {
                    const recognized = document.createElement('span');
                    recognized.className = 'medical-autocomplete-recognition';
                    recognized.textContent = 'Recognized';
                    heading.append(recognized);
                }
                if (availability) {
                    const badge = document.createElement('span');
                    badge.className = `medical-autocomplete-availability is-${availability}`;
                    badge.textContent = availabilityLabel(availability);
                    heading.append(badge);
                }
                button.append(heading);

                const details = [
                    item.classification_code ? `CVX ${item.classification_code}` : null,
                    item.formulation || item.official_name,
                    item.formulation_notes,
                    item.source
                ].filter(Boolean);
                if (details.length) {
                    const meta = document.createElement('small');
                    meta.className = 'medical-autocomplete-meta';
                    meta.textContent = details.join(' · ');
                    button.append(meta);
                }

                button.addEventListener('pointerdown', event => event.preventDefault());
                button.addEventListener('click', () => selectSuggestion(item, button));
                list.append(button);
            });

            if (currentItems.length) {
                list.hidden = false;
                input.setAttribute('aria-expanded', 'true');
                setStatus('');
            } else {
                closeList();
                if (!status.textContent) setStatus(`No matching suggestions for "${query}".`);
            }
        }

        function selectSuggestion(item) {
            if (control instanceof HTMLSelectElement) {
                let option = vaccineOptionFor(item);
                const availability = vaccineAvailability(item);
                if (availability !== 'available' && !historicalModeEnabled()) return;

                if (!option && item.selection_token && item.recognition_status === 'recognized') {
                    option = new Option(item.name, item.name);
                    option.dataset.external = 'true';
                    option.dataset.cvxCode = item.classification_code || '';
                    option.dataset.formulation = item.formulation || item.official_name || '';
                    option.dataset.selectionToken = item.selection_token;
                    option.dataset.eligibilityStatus = availability;
                    option.dataset.reason = item.eligibility_reason || '';
                    option.dataset.label = `${item.name} — ${availabilityLabel(availability)}`;
                    option.title = item.formulation || item.official_name || '';
                    control.append(option);
                }
                if (!option) return;

                if (item.selection_token) option.dataset.selectionToken = item.selection_token;
                if (item.classification_code) option.dataset.cvxCode = item.classification_code;
                if (item.formulation || item.official_name) {
                    option.dataset.formulation = item.formulation || item.official_name;
                }
                control.value = option.value;
                input.value = option.value;
                control.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                input.value = item.name;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }

            closeList();
            setStatus('');
        }

        async function search(query, version) {
            const localMatches = type === 'vaccine' ? localVaccineMatches(control, query) : [];
            const controller = new AbortController();
            activeRequests.get(input)?.abort();
            activeRequests.set(input, controller);

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ type, q: query }),
                    signal: controller.signal
                });
                if (!response.ok) throw new Error('Terminology search failed.');
                const payload = await response.json();
                if (version !== requestVersion) return;

                const suggestions = Array.isArray(payload.suggestions) ? payload.suggestions : [];
                render([...suggestions, ...localMatches], query);
                setStatus(payload.availability_message || (!currentItems.length
                    ? `No matching suggestions for "${query}".`
                    : ''));
            } catch (error) {
                if (error.name === 'AbortError' || version !== requestVersion) return;
                if (type === 'vaccine') {
                    render(localMatches, query);
                } else {
                    render([], query);
                }
                setStatus(type === 'vaccine'
                    ? 'Online vaccine suggestions are unavailable. Use an available vaccine option.'
                    : 'Online terminology suggestions are unavailable. You can continue with manual entry.');
            }
        }

        input.addEventListener('input', () => {
            requestVersion++;
            const version = requestVersion;
            const query = input.value.trim();
            setStatus('');
            closeList();

            activeRequests.get(input)?.abort();
            window.clearTimeout(pendingTimers.get(input));
            if (query.length < 2) return;

            pendingTimers.set(input, window.setTimeout(() => search(query, version), 300));
        });

        input.addEventListener('keydown', event => {
            if (list.hidden || !currentItems.length) {
                if (event.key === 'Escape') closeList();
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                activeIndex = (activeIndex + direction + currentItems.length) % currentItems.length;
                Array.from(list.children).forEach((option, index) => {
                    option.setAttribute('aria-selected', String(index === activeIndex));
                });
                input.setAttribute('aria-activedescendant', `${list.id}-option-${activeIndex}`);
            } else if (event.key === 'Enter' && activeIndex >= 0) {
                event.preventDefault();
                selectSuggestion(currentItems[activeIndex]);
            } else if (event.key === 'Escape') {
                closeList();
            }
        });

        input.addEventListener('blur', () => {
            requestVersion++;
            activeRequests.get(input)?.abort();
            window.clearTimeout(pendingTimers.get(input));
            window.setTimeout(closeList, 120);
        });

        if (control instanceof HTMLSelectElement) {
            control.addEventListener('change', syncSelect);

            const observer = new MutationObserver(() => {
                if (control.value && control.selectedOptions[0]?.disabled) {
                    control.value = '';
                }
                if (document.activeElement !== input) syncSelect();
                if (!list.hidden) render(currentItems, input.value.trim());
            });
            observer.observe(control, { attributes: true, childList: true, subtree: true });

            document.getElementById('historical_recording')?.addEventListener('change', () => {
                if (!list.hidden) render(currentItems, input.value.trim());
            });
        }
    }

    function initialize(root) {
        if (root instanceof Element && root.matches(selector) && !initialized.has(root)) {
            initialized.add(root);
            makeSearchable(root, root.dataset.medicalAutocomplete);
        }
        if (root.querySelectorAll) {
            root.querySelectorAll(selector).forEach(control => {
                if (initialized.has(control)) return;
                initialized.add(control);
                makeSearchable(control, control.dataset.medicalAutocomplete);
            });
        }
    }

    initialize(document);

    const observer = new MutationObserver(records => {
        records.forEach(record => record.addedNodes.forEach(node => {
            if (node instanceof Element) initialize(node);
        }));
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });

    window.MedicalAutocomplete = {
        refresh(control) {
            if (!(control instanceof HTMLSelectElement)) return;
            const input = control.parentElement?.querySelector('[data-medical-autocomplete-search="true"]');
            if (!input || document.activeElement === input) return;
            const selected = control.selectedOptions[0];
            input.value = selected && !selected.disabled ? selected.value : '';
        }
    };
})();
