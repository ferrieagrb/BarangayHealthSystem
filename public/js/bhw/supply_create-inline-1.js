document.addEventListener('DOMContentLoaded', function () {

    // Get the original Laravel form
    const supplyForm = document.getElementById('supplyForm');

    // Get confirmation modal elements
    const supplyConfirmModal =
        document.getElementById('supplyConfirmModal');

    const confirmSupplyName =
        document.getElementById('confirmSupplyName');

    const confirmSupplyCategory =
        document.getElementById('confirmSupplyCategory');

    const cancelSupplyConfirm =
        document.getElementById('cancelSupplyConfirm');

    const saveSupplyConfirm =
        document.getElementById('saveSupplyConfirm');


    /*  WHEN USER CLICKS "SAVE ITEM" */

    supplyForm.addEventListener('submit', function (event) {

        // Stop the form from submitting immediately
        event.preventDefault();


        // Check Laravel/HTML required fields first
        if (!supplyForm.checkValidity()) {

            supplyForm.reportValidity();

            return;
        }


        // Get Item Name
        const itemName =
            supplyForm.querySelector('input[name="name"]').value.trim();


        // Get Category
        const categorySelect =
            supplyForm.querySelector('select[name="category"]');

        const category =
            categorySelect.options[
                categorySelect.selectedIndex
            ].text;


        // Put the values into the confirmation message
        confirmSupplyName.textContent = itemName;

        confirmSupplyCategory.textContent = category;


        // Show confirmation modal
        supplyConfirmModal.classList.add('active');

    });


    /* CANCEL CONFIRMATION */

    cancelSupplyConfirm.addEventListener('click', function () {

        // Close modal
        supplyConfirmModal.classList.remove('active');

    });


    /* YES, SAVE */

    saveSupplyConfirm.addEventListener('click', function () {

        // Close modal
        supplyConfirmModal.classList.remove('active');


        // Submit the ORIGINAL Laravel form
        // This preserves:
        // - POST method
        // - route('supplies.store')
        // - @csrf
        // - name
        // - category
        // - min_stock
        // - description

        supplyForm.submit();

    });


    /* CLICKING OUTSIDE THE MODAL */

    supplyConfirmModal.addEventListener('click', function (event) {

        // Only close when clicking the dark background,
        // not when clicking the white modal itself.

        if (event.target === supplyConfirmModal) {

            supplyConfirmModal.classList.remove('active');

        }

    });


    /* ESC KEY */

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {

            supplyConfirmModal.classList.remove('active');

        }

    });

});
