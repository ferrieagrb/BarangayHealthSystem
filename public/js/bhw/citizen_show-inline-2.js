
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

        if (!medicinePickerModal) return;

        medicinePickerModal.classList.remove("active");

        medicinePickerModal.setAttribute("aria-hidden", "true");

    }

    openMedicinePicker?.addEventListener("click", function () {

        medicinePickerModal.classList.add("active");

        medicinePickerModal.setAttribute("aria-hidden", "false");

    });

    closeMedicinePicker?.addEventListener("click", closeMedicinePickerModal);

    medicinePickerModal?.addEventListener("click", function (event) {

        if (event.target === medicinePickerModal) {

            closeMedicinePickerModal();

        }

    });

    document.querySelectorAll(".medicine-option").forEach(function (option) {

        option.addEventListener("click", function () {

            if (!existingMedicine || !selectedMedicineLabel) return;
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

    /* ============================================================

       DIAGNOSIS FORM ELEMENTS

       ============================================================ */

    const diagnosisInput =

        document.getElementById("diagnosisInput");

    const recordDateInput =

        document.getElementById("recordDateInput");

    const commentsInput =

        document.getElementById("commentsInput");

    const saveDiagnosisBtn =

        document.getElementById("saveDiagnosisBtn");

});
