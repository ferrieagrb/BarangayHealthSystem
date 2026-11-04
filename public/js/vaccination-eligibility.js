(() => {
    function initialize() {
        const vaccineSelect = document.getElementById('vaccine_name');
        const doseSelect = document.getElementById('dose_number');
        const administeredDate = document.getElementById('date_administered');
        const historicalRecording = document.getElementById('historical_recording');
        const selectionToken = document.getElementById('vaccineSelectionToken');
        const message = document.getElementById('vaccinationEligibilityMessage');
        const form = document.getElementById('vaccinationForm');
        if (!vaccineSelect || !doseSelect || !administeredDate || !historicalRecording || !message || !form) return;

        function ageAt(birth, date) {
            const birthParts = birth.split('-').map(Number);
            const dateParts = date.split('-').map(Number);
            const birthValue = new Date(birthParts[0], birthParts[1] - 1, birthParts[2]);
            const dateValue = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
            if (Number.isNaN(birthValue.getTime()) || Number.isNaN(dateValue.getTime()) || dateValue < birthValue) return null;

            const days = Math.floor((dateValue - birthValue) / 86400000);
            let months = (dateParts[0] - birthParts[0]) * 12 + dateParts[1] - birthParts[1];
            if (dateParts[2] < birthParts[2]) months--;
            let years = dateParts[0] - birthParts[0];
            if (dateParts[1] < birthParts[1] || (dateParts[1] === birthParts[1] && dateParts[2] < birthParts[2])) years--;
            return { days, months, years };
        }

        function matchesDose(dose, age) {
            if (!dose || !age) return false;
            return ['days', 'months', 'years'].every(unit => {
                const minimum = dose['min_' + unit];
                const maximum = dose['max_' + unit];
                return (minimum === undefined || age[unit] >= minimum)
                    && (maximum === undefined || age[unit] <= maximum);
            });
        }

        function statusLabel(status) {
            return ({
                available: 'Available',
                unavailable: 'Unavailable',
                conditional: 'Conditional',
                unknown: 'Age eligibility not established'
            })[status] || 'Age eligibility not established';
        }

        function updateEligibility() {
            const birthDate = form.dataset.citizenBirthDate;
            const dateIsValid = administeredDate.value
                && (!administeredDate.max || administeredDate.value <= administeredDate.max);
            const age = birthDate && dateIsValid
                ? ageAt(birthDate, administeredDate.value)
                : null;

            const selectedValue = vaccineSelect.value;
            for (const option of vaccineSelect.options) {
                if (!option.value || (!option.dataset.code && !option.dataset.external)) continue;

                let status;
                let reason;
                if (option.dataset.code) {
                    const doses = JSON.parse(option.dataset.doses || '[]');
                    const doseName = historicalRecording.checked && option.value === selectedValue
                        ? (doseSelect.value || option.dataset.nextDose)
                        : option.dataset.nextDose;
                    const dose = doses.find(item => item.name === doseName);
                    if (!doseName) {
                        status = 'unavailable';
                        reason = option.dataset.reason || 'All configured routine doses are already recorded or scheduled.';
                    } else if (!age) {
                        status = 'unknown';
                        reason = 'Enter a valid birthdate and administration date.';
                    } else {
                        status = matchesDose(dose, age) ? 'available' : 'unavailable';
                        reason = status === 'available'
                            ? option.dataset.guideline
                            : 'The selected dose is outside its configured age range.';
                    }
                } else {
                    status = option.dataset.eligibilityStatus || 'unknown';
                    reason = option.dataset.reason || 'No validated BHEAMS age rule is configured for this CVX vaccine.';
                }

                option.dataset.eligibilityStatus = status;
                option.dataset.label ||= option.dataset.external
                    ? `${option.value} — ${statusLabel(status)}`
                    : option.value;
                option.disabled = status !== 'available' && !historicalRecording.checked;
                option.textContent = status === 'available'
                    ? option.dataset.label.replace(/ — (Available|Unavailable|Conditional|Age eligibility not established)$/, '')
                    : `${option.value} — ${statusLabel(status)}`;
                option.title = status === 'available' ? (option.dataset.guideline || '') : (reason || '');
            }

            const selectedOption = vaccineSelect.selectedOptions[0];
            if (!selectedOption || !selectedOption.value || selectedOption.disabled) {
                vaccineSelect.value = '';
                doseSelect.value = '';
                if (selectionToken) selectionToken.value = '';
                message.textContent = age
                    ? 'Select an Available vaccine, or enable historical recording for a vaccine that was already administered.'
                    : 'Enter a valid birthdate and administration date. Historical recording remains available for an actual prior vaccination.';
                return;
            }

            if (selectionToken) selectionToken.value = selectedOption.dataset.selectionToken || '';
            if (selectedOption.dataset.code && !historicalRecording.checked) {
                doseSelect.value = selectedOption.dataset.nextDose || '';
                for (const option of doseSelect.options) {
                    option.disabled = option.value !== '' && option.value !== selectedOption.dataset.nextDose;
                }
            } else {
                for (const option of doseSelect.options) {
                    option.disabled = false;
                }
            }

            const status = selectedOption.dataset.eligibilityStatus || 'unknown';
            const reason = selectedOption.dataset.reason || 'No validated BHEAMS age rule is configured for this CVX vaccine.';
            if (status === 'available') {
                message.textContent = `Available — ${selectedOption.dataset.guideline || 'Meets the configured age requirement on the administration date.'}`;
            } else if (status === 'unavailable') {
                message.textContent = `Unavailable — ${reason} Enable historical recording only if this vaccine was actually administered.`;
            } else if (status === 'conditional') {
                message.textContent = `Conditional — ${reason} Enable historical recording to document an actual prior vaccination.`;
            } else {
                message.textContent = `${reason} Enable historical recording to document an actual prior vaccination without marking it age-eligible.`;
            }
        }

        vaccineSelect.addEventListener('change', updateEligibility);
        doseSelect.addEventListener('change', updateEligibility);
        administeredDate.addEventListener('input', updateEligibility);
        administeredDate.addEventListener('change', updateEligibility);
        historicalRecording.addEventListener('change', updateEligibility);
        form.addEventListener('reset', () => window.setTimeout(updateEligibility));
        form.addEventListener('submit', event => {
            const selected = vaccineSelect.selectedOptions[0];
            const eligible = selected?.dataset.eligibilityStatus === 'available';
            if (!selected || !selected.value || (!eligible && !historicalRecording.checked)) {
                event.preventDefault();
                message.textContent = 'Select an Available vaccine or explicitly enable historical recording for a vaccination that was already administered.';
            } else if (selected.dataset.external && !selectionToken?.value) {
                event.preventDefault();
                message.textContent = 'Select this vaccine directly from the CDC CVX suggestions before recording it.';
            }
        });

        window.updateVaccinationEligibility = updateEligibility;
        updateEligibility();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
})();
