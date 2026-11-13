    (() => {
        const reportType = document.getElementById('reportType');
        const filterFields = document.querySelectorAll('.report-field[data-reports]');
        const graphicsToggles = document.querySelectorAll('.report-graphics-toggle');
        const reportTypeField = reportType.closest('.report-field');
        const reportTypeLabel = document.getElementById('reportTypeLabel');
        const picker = document.createElement('div');
        picker.className = 'report-type-picker';
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.id = 'reportTypeTrigger';
        trigger.className = 'report-type-trigger';
        trigger.setAttribute('role', 'combobox');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', 'reportTypeOptions');
        trigger.setAttribute('aria-labelledby', 'reportTypeLabel reportTypeCurrent');
        trigger.setAttribute('aria-required', reportType.required ? 'true' : 'false');

        const currentLabel = document.createElement('span');
        currentLabel.id = 'reportTypeCurrent';
        currentLabel.className = 'report-type-current';
        const chevron = document.createElement('span');
        chevron.className = 'report-type-chevron';
        chevron.setAttribute('aria-hidden', 'true');
        trigger.append(currentLabel, chevron);

        const listbox = document.createElement('div');
        listbox.id = 'reportTypeOptions';
        listbox.className = 'report-type-options';
        listbox.setAttribute('role', 'listbox');
        listbox.setAttribute('aria-labelledby', 'reportTypeLabel');
        listbox.hidden = true;

        const reportOptions = [];
        [...reportType.querySelectorAll('optgroup')].forEach((group, groupIndex) => {
            const groupElement = document.createElement('div');
            groupElement.className = 'report-type-group';
            groupElement.setAttribute('role', 'group');
            groupElement.setAttribute('aria-label', group.label);

            const groupLabel = document.createElement('div');
            groupLabel.className = 'report-type-group-label';
            groupLabel.setAttribute('role', 'presentation');
            groupLabel.textContent = group.label;
            groupElement.appendChild(groupLabel);

            [...group.querySelectorAll('option')].forEach((option, optionIndex) => {
                const optionElement = document.createElement('div');
                optionElement.id = `reportTypeOption-${groupIndex}-${optionIndex}`;
                optionElement.className = 'report-type-option';
                optionElement.setAttribute('role', 'option');
                optionElement.setAttribute('aria-selected', option.selected ? 'true' : 'false');
                optionElement.textContent = option.textContent.trim();
                optionElement.addEventListener('pointermove', () => setActiveOption(optionElement));
                optionElement.addEventListener('click', () => selectOption(option));
                groupElement.appendChild(optionElement);
                reportOptions.push({ option, element: optionElement });
            });

            listbox.appendChild(groupElement);
        });

        reportType.parentNode.insertBefore(picker, reportType.nextSibling);
        picker.append(trigger, listbox);
        reportType.classList.add('report-type-native');
        reportType.setAttribute('aria-hidden', 'true');
        reportType.tabIndex = -1;
        reportTypeField.classList.add('report-type-field');

        let activeOptionIndex = Math.max(0, reportOptions.findIndex(item => item.option.selected));
        let typeahead = '';
        let typeaheadTimer;

        function syncReportTypeControl() {
            const selectedItem = reportOptions.find(item => item.option.value === reportType.value);
            if (!selectedItem) return;

            currentLabel.textContent = selectedItem.option.textContent.trim();
            reportOptions.forEach(item => {
                item.element.setAttribute('aria-selected', item === selectedItem ? 'true' : 'false');
            });
        }

        function setActiveOption(optionElement) {
            const index = reportOptions.findIndex(item => item.element === optionElement);
            if (index < 0) return;

            activeOptionIndex = index;
            reportOptions.forEach(item => item.element.classList.toggle('is-active', item.element === optionElement));
            trigger.setAttribute('aria-activedescendant', optionElement.id);
            optionElement.scrollIntoView({ block: 'nearest' });
        }

        function positionListbox() {
            const bounds = trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - bounds.bottom - 8;
            const spaceAbove = bounds.top - 8;
            const estimatedHeight = Math.min(320, listbox.scrollHeight);
            const opensUp = spaceBelow < estimatedHeight && spaceAbove > spaceBelow;
            const availableHeight = Math.max(0, opensUp ? spaceAbove : spaceBelow);

            listbox.classList.toggle('opens-up', opensUp);
            listbox.style.maxHeight = `${Math.min(320, availableHeight)}px`;
        }

        function openListbox(index = activeOptionIndex) {
            if (!reportOptions.length) return;

            listbox.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            reportTypeField.classList.add('is-open');
            positionListbox();
            setActiveOption(reportOptions[Math.max(0, Math.min(reportOptions.length - 1, index))].element);
        }

        function closeListbox(restoreFocus = false) {
            listbox.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            trigger.removeAttribute('aria-activedescendant');
            reportTypeField.classList.remove('is-open');
            listbox.classList.remove('opens-up');
            listbox.style.maxHeight = '';
            reportOptions.forEach(item => item.element.classList.remove('is-active'));
            if (restoreFocus) trigger.focus();
        }

        function selectOption(option) {
            reportType.value = option.value;
            syncReportTypeControl();
            activeOptionIndex = reportOptions.findIndex(item => item.option === option);
            reportType.dispatchEvent(new Event('change', { bubbles: true }));
            closeListbox(true);
        }

        function findTypeaheadMatch(character) {
            typeahead += character.toLocaleLowerCase();
            window.clearTimeout(typeaheadTimer);
            typeaheadTimer = window.setTimeout(() => { typeahead = ''; }, 700);
            const startIndex = Math.max(0, activeOptionIndex + 1);
            for (let offset = 0; offset < reportOptions.length; offset += 1) {
                const index = (startIndex + offset) % reportOptions.length;
                if (reportOptions[index].option.textContent.trim().toLocaleLowerCase().startsWith(typeahead)) {
                    return index;
                }
            }
            return -1;
        }

        trigger.addEventListener('click', () => {
            if (listbox.hidden) openListbox();
            else closeListbox();
        });
        trigger.addEventListener('keydown', event => {
            const open = !listbox.hidden;
            if (event.key === 'Tab') {
                closeListbox();
                return;
            }

            if (event.key === 'Escape') {
                if (open) {
                    event.preventDefault();
                    closeListbox();
                }
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                if (!open) {
                    const selectedIndex = reportOptions.findIndex(item => item.option.selected);
                    openListbox(Math.max(0, selectedIndex + (event.key === 'ArrowDown' ? 1 : -1)));
                } else {
                    setActiveOption(reportOptions[Math.max(
                        0,
                        Math.min(reportOptions.length - 1, activeOptionIndex + direction)
                    )].element);
                }
                return;
            }

            if (event.key === 'Home' || event.key === 'End') {
                event.preventDefault();
                if (!open) openListbox(event.key === 'Home' ? 0 : reportOptions.length - 1);
                else setActiveOption(reportOptions[event.key === 'Home' ? 0 : reportOptions.length - 1].element);
                return;
            }

            if (event.key === 'PageDown' || event.key === 'PageUp') {
                event.preventDefault();
                const direction = event.key === 'PageDown' ? 5 : -5;
                if (!open) openListbox();
                else setActiveOption(reportOptions[Math.max(
                    0,
                    Math.min(reportOptions.length - 1, activeOptionIndex + direction)
                )].element);
                return;
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                if (!open) openListbox();
                else selectOption(reportOptions[activeOptionIndex].option);
                return;
            }

            if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
                const matchIndex = findTypeaheadMatch(event.key);
                if (matchIndex >= 0) {
                    event.preventDefault();
                    if (!open) openListbox(matchIndex);
                    else setActiveOption(reportOptions[matchIndex].element);
                }
            }
        });

        reportTypeLabel.addEventListener('click', event => {
            event.preventDefault();
            trigger.focus();
            openListbox();
        });
        document.addEventListener('pointerdown', event => {
            if (!picker.contains(event.target) && !reportTypeLabel.contains(event.target)) closeListbox();
        });
        window.addEventListener('resize', () => {
            if (!listbox.hidden) positionListbox();
        });
        reportType.addEventListener('change', syncReportTypeControl);
        syncReportTypeControl();

        const updateReportFields = () => {
            filterFields.forEach((field) => {
                const isRelevant = field.dataset.reports.split(',').includes(reportType.value);
                field.hidden = !isRelevant;
                field.querySelectorAll('input, select').forEach((input) => {
                    input.disabled = !isRelevant;
                });
            });
            graphicsToggles.forEach((toggle) => {
                const relevant = toggle.dataset.reports.split(',').includes(reportType.value);
                toggle.hidden = !relevant;
                toggle.querySelector('input').disabled = !relevant;
            });
        };

        reportType.addEventListener('change', () => {
            updateReportFields();
            document.querySelectorAll('.report-field[data-reports][hidden] input, .report-field[data-reports][hidden] select')
                .forEach((input) => {
                    if (input.type === 'checkbox') input.checked = false;
                    else input.value = '';
                });
            document.querySelectorAll('input[name="search"]').forEach((input) => {
                input.value = '';
            });
        });
        updateReportFields();
    })();
