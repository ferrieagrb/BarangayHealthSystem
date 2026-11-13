(function () {
    function updateDiagnosisBMI() {
        const weightInput = document.getElementById('weightInput');
        const heightInput = document.getElementById('heightInput');
        const bmiDisplay = document.getElementById('bmiDisplay');
        const bmiStatus = document.getElementById('bmiStatus');

        if (!weightInput || !heightInput || !bmiDisplay || !bmiStatus) return;

        const weight = Number.parseFloat(weightInput.value);
        const height = Number.parseFloat(heightInput.value);
        bmiStatus.className = 'diagnosis-bmi-status';

        if (Number.isFinite(weight) && Number.isFinite(height) && weight > 0 && height > 0) {
            const bmi = weight / Math.pow(height / 100, 2);
            bmiDisplay.textContent = bmi.toFixed(1);

            if (bmi < 18.5) {
                bmiStatus.textContent = 'Underweight';
                bmiStatus.classList.add('diagnosis-bmi-underweight');
            } else if (bmi < 25) {
                bmiStatus.textContent = 'Normal Weight';
                bmiStatus.classList.add('diagnosis-bmi-normal');
            } else if (bmi < 30) {
                bmiStatus.textContent = 'Overweight';
                bmiStatus.classList.add('diagnosis-bmi-overweight');
            } else if (bmi < 35) {
                bmiStatus.textContent = 'Obesity Class I';
                bmiStatus.classList.add('diagnosis-bmi-obese');
            } else if (bmi < 40) {
                bmiStatus.textContent = 'Obesity Class II';
                bmiStatus.classList.add('diagnosis-bmi-obese');
            } else {
                bmiStatus.textContent = 'Obesity Class III';
                bmiStatus.classList.add('diagnosis-bmi-obese');
            }
        } else {
            bmiDisplay.textContent = '—';
            bmiStatus.textContent = 'Enter Height & Weight';
            bmiStatus.classList.add('diagnosis-bmi-empty');
        }
    }

    // Delegate input events so BMI still updates even if other page scripts fail.
    document.addEventListener('input', function (event) {
        if (event.target && (event.target.id === 'weightInput' || event.target.id === 'heightInput')) {
            updateDiagnosisBMI();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        updateDiagnosisBMI();

        const diagnosisForm = document.getElementById('diagnosisForm');
        const clearButton = document.getElementById('clearDiagnosisBtn');

        const vaccinationForm = document.getElementById('vaccinationForm');
        const clearVaccinationButton = document.getElementById('clearVaccinationBtn');
        if (clearVaccinationButton && vaccinationForm) {
            clearVaccinationButton.addEventListener('click', function () {
                vaccinationForm.reset();
                const vaccineSelect = document.getElementById('vaccine_name');
                const doseSelect = document.getElementById('dose_number');
                const administeredDate = document.getElementById('date_administered');
                if (vaccineSelect) vaccineSelect.selectedIndex = 0;
                if (doseSelect) doseSelect.selectedIndex = 0;
                if (administeredDate) administeredDate.value = new Date().toISOString().slice(0, 10);
            });
        }

        const maintenanceForm = document.getElementById('maintenanceForm');
        const clearMaintenanceButton = document.getElementById('clearMaintenanceBtn');
        if (clearMaintenanceButton && maintenanceForm) {
            clearMaintenanceButton.addEventListener('click', function () {
                maintenanceForm.reset();
                const quantityInput = document.getElementById('maintenance_quantity');
                const startDateInput = document.getElementById('maintenance_start_date');
                if (quantityInput) quantityInput.value = '1';
                if (startDateInput) startDateInput.value = new Date().toISOString().slice(0, 10);
            });
        }

        if (clearButton && diagnosisForm) {
            clearButton.addEventListener('click', function () {
                diagnosisForm.reset();
                const dateInput = document.getElementById('recordDateInput');
                if (dateInput) dateInput.value = new Date().toISOString().slice(0, 10);
                updateDiagnosisBMI();
            });
        }

        if (diagnosisForm) {
            diagnosisForm.addEventListener('submit', function () {
                const saveButton = document.getElementById('saveDiagnosisBtn');
                if (saveButton) {
                    saveButton.disabled = true;
                    saveButton.textContent = 'Saving...';
                }
            });
        }
    });

    // In case this script is loaded after DOMContentLoaded.
    if (document.readyState !== 'loading') updateDiagnosisBMI();
})();
