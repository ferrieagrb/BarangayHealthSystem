
document.addEventListener("DOMContentLoaded", function () {

    const medicineSource = document.getElementById("medicine_source");

    const existingMedicineGroup = document.getElementById("existingMedicineGroup");

    const customMedicineGroup = document.getElementById("customMedicineGroup");

    const existingMedicine = document.getElementById("existing_medicine");

    const customMedicine = document.getElementById("custom_medicine");

    const medicinePickerModal = document.getElementById("medicinePickerModal");

    const openMedicinePicker = document.getElementById("openMedicinePicker");

    const closeMedicinePicker = document.getElementById("closeMedicinePicker");

    const selectedMedicineLabel = document.getElementById("selectedMedicineLabel");

    function closeMedicinePickerModal() {

        medicinePickerModal.classList.remove("active");

        medicinePickerModal.setAttribute("aria-hidden", "true");

    }

    if (openMedicinePicker) {
        openMedicinePicker.addEventListener("click", function () {

            medicinePickerModal.classList.add("active");

            medicinePickerModal.setAttribute("aria-hidden", "false");

        });
    }

    if (closeMedicinePicker) {
        closeMedicinePicker.addEventListener("click", closeMedicinePickerModal);
    }

    if (medicinePickerModal) {
        medicinePickerModal.addEventListener("click", function (event) {

            if (event.target === medicinePickerModal) {

                closeMedicinePickerModal();

            }

        });
    }

    document.querySelectorAll(".medicine-option").forEach(function (option) {

        option.addEventListener("click", function () {

            existingMedicine.value = option.dataset.medicine;

            selectedMedicineLabel.textContent = option.dataset.medicine;

            closeMedicinePickerModal();

        });

    });

    document.addEventListener("keydown", function (event) {

        if (event.key === "Escape") {

            closeMedicinePickerModal();

        }

    });

    function updateMedicineSourceFields() {
        if (!medicineSource) return;
        const isCustom = medicineSource.value === "custom";

        existingMedicineGroup.hidden = isCustom;

        customMedicineGroup.hidden = !isCustom;

        existingMedicine.required = !isCustom;

        customMedicine.required = isCustom;

        existingMedicine.disabled = isCustom;

        customMedicine.disabled = !isCustom;

    }

    if (medicineSource) {
        medicineSource.addEventListener("change", updateMedicineSourceFields);
        updateMedicineSourceFields();
    }
    setEditGatedState(false);

    const diagnosisInput =

        document.getElementById("diagnosisInput");

    const recordDateInput =

        document.getElementById("recordDateInput");

    const commentsInput =

        document.getElementById("commentsInput");

    const saveDiagnosisBtn =

        document.getElementById("saveDiagnosisBtn");


    /* CONFIRMATION MODAL ELEMENTS

       ============================================================ */

    const diagnosisConfirmModal =

        document.getElementById("diagnosisConfirmModal");

    const confirmDiagnosis =

        document.getElementById("confirmDiagnosis");

    const confirmDiagnosisDate =

        document.getElementById("confirmDiagnosisDate");

    const confirmDiagnosisComments =

        document.getElementById("confirmDiagnosisComments");

    const cancelDiagnosisConfirmation =

        document.getElementById(

            "cancelDiagnosisConfirmation"

        );

    const confirmDiagnosisSubmission =

        document.getElementById(

            "confirmDiagnosisSubmission"

        );

    // This page uses direct submission when the old confirmation dialog is absent.
    if (!diagnosisForm || !diagnosisConfirmModal || !cancelDiagnosisConfirmation || !confirmDiagnosisSubmission) return;

    /* ============================================================

       FORM SUBMISSION

       ============================================================ */

    diagnosisForm.addEventListener("submit", function (event) {

        event.preventDefault();

        const diagnosis =

            diagnosisInput.value.trim();

        const recordDate =

            recordDateInput.value;

        const comments =

            commentsInput.value.trim();

        if (!diagnosis || !recordDate) {

            diagnosisForm.reportValidity();

            return;

        }

        confirmDiagnosis.textContent =

            diagnosis;

        confirmDiagnosisDate.textContent =

            recordDate;

        confirmDiagnosisComments.textContent =

            comments || "No notes";

        diagnosisConfirmModal.classList.add("active");

    });

    cancelDiagnosisConfirmation.addEventListener(

        "click",

        function () {

            diagnosisConfirmModal.classList.remove(

                "active"

            );

        }

    );

    confirmDiagnosisSubmission.addEventListener(

        "click",

        function () {

            confirmDiagnosisSubmission.disabled = true;

            confirmDiagnosisSubmission.textContent =

                "Saving...";

            diagnosisConfirmModal.classList.remove(

                "active"

            );

            saveDiagnosisBtn.disabled = true;

            diagnosisForm.submit();

        }

    );

    window.addEventListener(

        "click",

        function (event) {

            if (

                event.target === diagnosisConfirmModal

            ) {

                diagnosisConfirmModal.classList.remove(

                    "active"

                );

            }

        }

    );

    document.addEventListener(

        "keydown",

        function (event) {

            if (event.key === "Escape") {

                diagnosisConfirmModal.classList.remove(

                    "active"

                );

            }

        }

    );

});
