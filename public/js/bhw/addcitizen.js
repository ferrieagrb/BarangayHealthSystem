document.addEventListener('DOMContentLoaded', function() {
    const citizenForm = document.getElementById('citizen-form');
    const familyPickerModal = document.getElementById('familyPickerModal');
    const newFamilyModal = document.getElementById('newFamilyModal');
    const familyIdInput = document.getElementById('citizenFamilyId');
    const familyDisplay = document.getElementById('citizenFamilyDisplay');
    const purokInput = document.getElementById('citizenPurok');
    const subgroupInput = document.getElementById('citizenSubgroup');
    const purokDisplay = document.getElementById('citizenPurokDisplay');
    const subgroupDisplay = document.getElementById('citizenSubgroupDisplay');
    const manualAssignmentButton = document.getElementById('openManualAssignment');
    const manualAssignmentModal = document.getElementById('manualAssignmentModal');
    const assignmentStatus = document.getElementById('assignmentStatus');

    function useManualAssignment() {
        familyIdInput.value = '';
        manualAssignmentButton.disabled = false;
        familyIdInput.removeAttribute('required');
        familyDisplay.value = '';
        familyDisplay.removeAttribute('required');
        familyIdInput.value = '';
        purokInput.value = '';
        subgroupInput.value = '';
        purokDisplay.value = '';
        subgroupDisplay.value = '';
        assignmentStatus.textContent = 'Select the citizen\'s Purok and subgroup manually.';
    }

    function useFamilyAssignment() {
        familyIdInput.removeAttribute('required');
        manualAssignmentButton.disabled = false;
        assignmentStatus.textContent = 'The citizen\'s Purok and subgroup will come from the selected family.';
    }

    function openModal(modal) { modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); }
    function closeModal(modal) { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }

    document.getElementById('openFamilyPicker').addEventListener('click', () => {
        openModal(familyPickerModal);
        document.getElementById('familySearch').focus();
    });
    familyPickerModal.addEventListener('click', event => {
        if (event.target === familyPickerModal) closeModal(familyPickerModal);
    });
    familyPickerModal.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeModal(familyPickerModal);
            document.getElementById('openFamilyPicker').focus();
        }
    });
    document.getElementById('openManualAssignment').addEventListener('click', () => { useManualAssignment(); openModal(manualAssignmentModal); });
    document.getElementById('closeManualAssignment').addEventListener('click', () => closeModal(manualAssignmentModal));
    document.getElementById('closeFamilyPicker').addEventListener('click', () => closeModal(familyPickerModal));
    document.getElementById('openNewFamilyModal').addEventListener('click', () => { closeModal(familyPickerModal); openModal(newFamilyModal); });
    document.querySelectorAll('.close-new-family').forEach(button => button.addEventListener('click', () => closeModal(newFamilyModal)));

    document.querySelectorAll('#familyPickerList .family-option').forEach(option => option.addEventListener('click', () => {
        familyIdInput.value = option.dataset.familyId;
        familyDisplay.value = option.dataset.familyLabel;
        purokInput.value = option.dataset.purok;
        subgroupInput.value = option.dataset.subgroup;
        purokDisplay.value = option.dataset.purok;
        subgroupDisplay.value = option.dataset.subgroupName;
        useFamilyAssignment();
        closeModal(familyPickerModal);
    }));

    document.getElementById('familySearch').addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#familyPickerList .family-option').forEach(option => {
            option.hidden = !option.textContent.toLowerCase().includes(term);
        });
        document.getElementById('familyPickerEmpty').hidden = !!document.querySelector('#familyPickerList .family-option:not([hidden])');
    });

    document.getElementById('quickFamilyForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        const error = document.getElementById('quickFamilyError');
        const submitButton = this.querySelector('button[type="submit"]');
        error.textContent = '';
        submitButton.disabled = true;
        submitButton.textContent = 'Creating...';

        try {
            const response = await fetch(citizenForm.dataset.quickFamilyUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json' },
                body: new FormData(this)
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok || !payload.id || !payload.purok || !payload.subgroup) {
                error.textContent = payload.message || 'Unable to create family. Check the selected subgroup and try again.';
                return;
            }

            familyIdInput.value = payload.id;
            familyDisplay.value = `${payload.family_name} - ${payload.purok.name} / ${payload.subgroup.name}`;
            purokInput.value = payload.purok.name;
            subgroupInput.value = payload.subgroup.id;
            purokDisplay.value = payload.purok.name;
            subgroupDisplay.value = payload.subgroup.name;
            useFamilyAssignment();
            closeModal(newFamilyModal);
            this.reset();
        } catch (requestError) {
            error.textContent = 'Unable to connect while creating the family. Please try again.';
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Create and Select Family';
        }
    });

    document.querySelectorAll('.manual-assignment-option').forEach(option => option.addEventListener('click', function () {
        purokInput.value = option.dataset.purok;
        subgroupInput.value = option.dataset.subgroupId;
        purokDisplay.value = option.dataset.purok;
        subgroupDisplay.value = option.dataset.subgroupName;
        closeModal(manualAssignmentModal);
    }));

    if (familyIdInput.value) useFamilyAssignment();
    else useManualAssignment();

    // After a validation error the page reloads, so re-apply the previous Purok/subgroup/family selection.
    const oldFamilyId = citizenForm.dataset.oldFamilyId;
    const oldSubgroupId = citizenForm.dataset.oldSubgroupId;
    if (oldFamilyId) {
        const familyOption = document.querySelector(`#familyPickerList .family-option[data-family-id="${oldFamilyId}"]`);
        if (familyOption) familyOption.click();
    } else if (oldSubgroupId) {
        const manualOption = document.querySelector(`.manual-assignment-option[data-subgroup-id="${oldSubgroupId}"]`);
        if (manualOption) manualOption.click();
    }

    const ageInput = document.getElementById('age');
    const birthdateInput = document.getElementById('birthdate');
    const sexInput = document.getElementById('Citizen_Sex');
    const familyPlanningSection = document.getElementById('familyPlanningSection');
    const pregnancyInput = document.getElementById('Citizen_Pregnant');
    const pillsField = document.getElementById('pillsField');
    const pillsInput = document.getElementById('Citizen_InPills');
    const saveError = document.getElementById('citizenSaveError');
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    const photoPreviewEmpty = document.getElementById('photoPreviewEmpty');
    const photoCropModal = document.getElementById('photoCropModal');
    const photoCropImage = document.getElementById('photoCropImage');
    const cancelPhotoCrop = document.getElementById('cancelPhotoCrop');
    const confirmPhotoCrop = document.getElementById('confirmPhotoCrop');
    let photoCropper = null;

    function updateFamilyPlanningEligibility() {
        const female = sexInput.value === 'Female';
        const birthDate = birthdateInput.value ? new Date(`${birthdateInput.value}T00:00:00`) : null;
        const today = new Date();
        let age = null;

        if (birthDate && !Number.isNaN(birthDate.getTime())) {
            age = today.getFullYear() - birthDate.getFullYear();
            if (today.getMonth() < birthDate.getMonth()
                || (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) {
                age--;
            }
            ageInput.value = age;
        }

        const pillsEligible = female && Number.isFinite(age) && age >= 16;

        familyPlanningSection.hidden = !female;
        pregnancyInput.disabled = !female;
        pillsField.hidden = !pillsEligible;
        pillsInput.disabled = !pillsEligible;

        if (!female) pregnancyInput.value = '0';
        if (!pillsEligible) pillsInput.value = '0';
    }

    sexInput.addEventListener('change', updateFamilyPlanningEligibility);
    birthdateInput.addEventListener('input', updateFamilyPlanningEligibility);
    updateFamilyPlanningEligibility();

    photoInput.addEventListener('change', function () {
        const file = this.files?.[0];
        if (!file) {
            photoPreview.hidden = true;
            photoPreview.removeAttribute('src');
            photoPreviewEmpty.hidden = false;
            return;
        }

        const reader = new FileReader();
        reader.onload = event => {
            photoCropImage.src = event.target.result;
            photoCropModal.classList.add('open');
            photoCropper?.destroy();
            photoCropper = new Cropper(photoCropImage, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: .9,
                responsive: true,
            });
        };
        reader.readAsDataURL(file);
    });

    cancelPhotoCrop.addEventListener('click', function () {
        photoCropper?.destroy();
        photoCropper = null;
        photoCropModal.classList.remove('open');
        photoInput.value = '';
    });

    confirmPhotoCrop.addEventListener('click', function () {
        if (!photoCropper) return;

        photoCropper.getCroppedCanvas({ width: 600, height: 600 }).toBlob(function (blob) {
            const croppedFile = new File([blob], 'citizen-photo.jpg', { type: 'image/jpeg' });
            const transfer = new DataTransfer();
            transfer.items.add(croppedFile);
            photoInput.files = transfer.files;
            photoPreview.src = URL.createObjectURL(blob);
            photoPreview.hidden = false;
            photoPreviewEmpty.hidden = true;
            photoCropper.destroy();
            photoCropper = null;
            photoCropModal.classList.remove('open');
        }, 'image/jpeg', .9);
    });
    
    const modal = document.getElementById('confirmation-modal');
    const modalMessage = document.getElementById('modal-message');
    const confirmBtn = document.getElementById('modal-confirm-btn');
    const cancelBtn = document.getElementById('modal-cancel-btn');

    let isFormVerified = false;

    // When Birth Date changes → update Age dynamically
    birthdateInput.addEventListener('input', function() {
        const birth = new Date(this.value);
        const today = new Date();
        if (!isNaN(birth.getTime())) {
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            ageInput.value = age;
            updateFamilyPlanningEligibility();
            return true;
        }
    });

    // Intercepts form submission to open custom centered modal
    citizenForm.addEventListener('submit', function(e) {
        saveError.hidden = true;
        if (!citizenForm.checkValidity()) {
            e.preventDefault();
            const invalid = citizenForm.querySelector(':invalid');
            const label = invalid?.labels?.[0]?.textContent.trim() || 'Required field';
            saveError.textContent = `${label}: ${invalid?.validationMessage || 'Please check this field.'}`;
            saveError.hidden = false;
            citizenForm.reportValidity();
            return;
        }
        if (!isFormVerified) {
            e.preventDefault();

            const firstName = document.querySelector('input[name="Citizen_FName"]').value;
            const lastName = document.querySelector('input[name="Citizen_LName"]').value;
            const family = familyDisplay.value;
            const manualAssignment = purokInput.value && subgroupInput.value;

            if (!familyIdInput.value && !manualAssignment) {
                saveError.textContent = 'Select a family, or click Assign without family and choose a Purok and subgroup before saving.';
                saveError.hidden = false;
                assignmentStatus.textContent = saveError.textContent;
                document.getElementById('openManualAssignment').focus();
                saveError.scrollIntoView({ block: 'center', behavior: 'smooth' });
                return;
            }

            modalMessage.innerText = family
                ? `Are you sure you want to save the record for ${firstName} ${lastName} under ${family}?`
                : `Are you sure you want to save the record for ${firstName} ${lastName} in ${purokInput.value} / ${document.getElementById('citizenSubgroupDisplay').value} (no family assigned)?`;
            modal.style.display = 'flex';
        }
    });

    // If user clicks "Yes, Save" inside the modal, proceed with real form submission
    confirmBtn.addEventListener('click', function() {
        isFormVerified = true;
        modal.style.display = 'none';
        if (window.showLoader) showLoader();
        citizenForm.requestSubmit();
    });

    // If user clicks "Cancel" inside the modal, hide the modal and stay on page
    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
});
