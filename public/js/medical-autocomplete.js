(() => {
    if (window.MedicalAutocomplete) return;
    const endpoint = document.querySelector('meta[name="medical-terminology-search-url"]')?.content;
    if (!endpoint) return;

    const selector = '[data-medical-autocomplete="medicine"], [data-medical-autocomplete="vaccine"], [data-medical-autocomplete="diagnosis"], [data-medical-autocomplete="supply"]';
    const initialized = new WeakSet();
    const activeRequests = new WeakMap();
    const pendingTimers = new WeakMap();
    let generatedId = 0;

    function normalize(value) {
        return String(value || '').toLocaleLowerCase().replace(/[^\p{L}\p{N}]+/gu, ' ').trim();
    }

    function localVaccineMatches(select, query) {
        const normalizedQuery = normalize(query);
        return Array.from(select.options || [])
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
        list.id = `${input.id || 'medical-field'}-suggestions-${++generatedId}`;
        list.setAttribute('role', 'listbox');
        list.hidden = true;

        const status = document.createElement('div');
        status.className = 'medical-autocomplete-status';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        status.hidden = true;
        status.id = `${list.id}-status`;
        input.setAttribute('aria-describedby', [input.getAttribute('aria-describedby'), status.id].filter(Boolean).join(' '));

        wrapper.append(list, status);
        input.setAttribute('aria-controls', list.id);

        if (control instanceof HTMLSelectElement) {
            wrapper.append(control);
        }

        let currentItems = [];
        let activeIndex = -1;
        let requestVersion = 0;
        let terminologyType = type;
        let requestedTerm = '';
        let renderedTerm = '';
        const categoryControl = type === 'supply' ? input.form?.querySelector('[name="category"]') : null;

        function termRange() {
            const value = input.value;
            if (!input.hasAttribute('data-medical-multiple')) {
                return { start: 0, end: value.length, query: value.trim() };
            }
            const caret = input.selectionStart ?? value.length;
            const before = value.slice(0, caret);
            const lastSeparator = Math.max(before.lastIndexOf(','), before.lastIndexOf(';'), before.lastIndexOf('\n'));
            let start = lastSeparator + 1;
            const nextSeparator = value.slice(caret).search(/[,;\n]/);
            let end = nextSeparator < 0 ? value.length : caret + nextSeparator;
            const segment = value.slice(start, end);
            start += segment.length - segment.trimStart().length;
            end -= segment.length - segment.trimEnd().length;
            return { start, end: Math.max(start, end), query: segment.trim() };
        }

        function searchTerm() {
            return termRange().query;
        }

        function spellingMessage(query, available = true) {
            if (terminologyType !== 'medicine') return '';
            if (!available) return 'Online spelling check is unavailable. Please check the medicine name before saving.';
            if (currentItems.some(item => normalize(item.name) === normalize(query))) return '';
            if (currentItems.some(item => item.match_type === 'approximate')) {
                return 'Possible spelling error. Choose the correct suggestion or review the name before saving.';
            }
            return currentItems.length
                ? 'Choose a suggestion to use its spelling, or check your entry before saving.'
                : 'Medicine spelling could not be verified. Check the name; local brands and non-medicine supplies may be entered manually.';
        }

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
            renderedTerm = query;
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
                if (item.match_type === 'approximate') name.append('Did you mean: ');
                const start = item.name.toLocaleLowerCase().indexOf(query.toLocaleLowerCase());
                if (start >= 0) {
                    name.append(item.name.slice(0, start));
                    const match = document.createElement('mark');
                    match.textContent = item.name.slice(start, start + query.length);
                    name.append(match, item.name.slice(start + query.length));
                } else name.append(item.name);
                if (item.match_type === 'approximate') name.append('?');
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
                    item.generic_name ? `Generic: ${item.generic_name}` : null,
                    item.classification_code ? `${item.code_system || (terminologyType === 'vaccine' ? 'CVX' : 'ICD-11')} ${item.classification_code}` : null,
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
                const range = termRange();
                if (range.query !== renderedTerm) {
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    return;
                }
                // Correct the medicine at the caret, preserving every other entry.
                input.value = input.value.slice(0, range.start) + item.name + input.value.slice(range.end);
                if (input.hasAttribute('data-medical-multiple')) {
                    input.setSelectionRange(range.start + item.name.length, range.start + item.name.length);
                }
                requestVersion++;
                activeRequests.get(input)?.abort();
                window.clearTimeout(pendingTimers.get(input));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }

            closeList();
            setStatus('');
        }

        async function search(query, version) {
            if (!input.isConnected) return;
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
                    body: JSON.stringify({ type, q: query, ...(categoryControl ? { category: categoryControl.value } : {}) }),
                    signal: controller.signal
                });
                if (!response.ok) throw new Error('Terminology search failed.');
                const payload = await response.json();
                if (version !== requestVersion) return;
                terminologyType = payload.terminology_type || type;

                const suggestions = Array.isArray(payload.suggestions) ? payload.suggestions : [];
                render([...suggestions, ...localMatches], query);
                setStatus(spellingMessage(query, payload.online_available || payload.reference_available) || payload.availability_message || (!currentItems.length
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
            const query = searchTerm();
            requestedTerm = query;
            setStatus('');
            closeList();

            activeRequests.get(input)?.abort();
            window.clearTimeout(pendingTimers.get(input));
            if (categoryControl && !categoryControl.value) {
                setStatus('Select the supply category first to see relevant suggestions.');
                return;
            }
            if (query.length < 2) return;
            if (query.length > 100) {
                setStatus('Search one medicine or diagnosis at a time (up to 100 characters).');
                return;
            }

            setStatus('Searching official medical terminology…');
            pendingTimers.set(input, window.setTimeout(() => search(query, version), 300));
        });

        categoryControl?.addEventListener('change', () => {
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });

        function searchAtCaret() {
            if (input.hasAttribute('data-medical-multiple') && searchTerm() !== requestedTerm) {
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
        input.addEventListener('click', searchAtCaret);
        input.addEventListener('keyup', event => {
            if (['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) searchAtCaret();
        });

        input.addEventListener('focus', () => {
            if (searchTerm().length >= 2 && list.hidden) {
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        (input.form || control.form)?.addEventListener('reset', () => {
            requestVersion++;
            activeRequests.get(input)?.abort();
            window.clearTimeout(pendingTimers.get(input));
            closeList();
            setStatus('');
            if (control instanceof HTMLSelectElement) window.setTimeout(syncSelect, 0);
        });

        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                requestVersion++;
                activeRequests.get(input)?.abort();
                window.clearTimeout(pendingTimers.get(input));
                closeList();
                setStatus('');
                return;
            }
            if (list.hidden || !currentItems.length) {
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                const options = Array.from(list.children);
                let candidate = activeIndex;
                for (let tried = 0; tried < options.length; tried++) {
                    candidate = candidate < 0
                        ? (direction > 0 ? 0 : options.length - 1)
                        : (candidate + direction + options.length) % options.length;
                    if (!options[candidate].disabled) { activeIndex = candidate; break; }
                }
                if (activeIndex < 0) return;
                Array.from(list.children).forEach((option, index) => {
                    option.setAttribute('aria-selected', String(index === activeIndex));
                });
                input.setAttribute('aria-activedescendant', `${list.id}-option-${activeIndex}`);
                options[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (event.key === 'Enter' && activeIndex >= 0) {
                event.preventDefault();
                selectSuggestion(currentItems[activeIndex]);
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
