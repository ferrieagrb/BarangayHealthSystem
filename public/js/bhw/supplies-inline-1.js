
    /* ============================================================
       ORIGINAL ACCORDION / DROPDOWN
       DO NOT CHANGE
       ============================================================ */

    function togglePackRow(rowId, masterRow) {

        const targetRow = document.getElementById(rowId);

        if (!masterRow.classList.contains("expanded")) {

            masterRow.classList.add("expanded");
            targetRow.classList.add("expanded");

        } else {

            masterRow.classList.remove("expanded");
            targetRow.classList.remove("expanded");

        }

    }

    /* ============================================================
       PAGE JAVASCRIPT
       ============================================================ */

    document.addEventListener("DOMContentLoaded", function () {

        /* ========================================================
           DEPOSIT MODAL ELEMENTS
           ======================================================== */

        const depositModal =
            document.getElementById("depositModal");

        const depositItemName =
            document.getElementById("depositItemName");

        const depositNameInput =
            document.getElementById("depositNameInput");

        const depositForm =
            document.getElementById("depositForm");

        const depositRowsTable =
            document
                .getElementById("depositRowsTable")
                .getElementsByTagName("tbody")[0];

        const addRowBtn =
            document.getElementById("addRowBtn");

        /* ========================================================
           DEPOSIT CONFIRMATION MODAL
           ======================================================== */

        const depositConfirmModal =
            document.getElementById("depositConfirmModal");

        const confirmDepositItem =
            document.getElementById("confirmDepositItem");

        const confirmDepositCount =
            document.getElementById("confirmDepositCount");

        const cancelDepositConfirmation =
            document.getElementById("cancelDepositConfirmation");

        const confirmDepositSubmission =
            document.getElementById("confirmDepositSubmission");

        /* ========================================================
           WITHDRAW MODAL ELEMENTS
           ======================================================== */

        const withdrawModal =
            document.getElementById("withdrawModal");

        const withdrawSupplyId =
            document.getElementById("withdrawSupplyId");

        const withdrawPackInfo =
            document.getElementById("withdrawPackInfo");

        const withdrawQtyInput =
            document.getElementById("withdrawQtyInput");

        /* ========================================================
           ROW INDEX
           ======================================================== */

        let rowIndex = 1;

        /* ========================================================
           FIX:
           MOVE MODALS TO BODY

           This prevents the modal from being clipped by a parent
           container with overflow/transform styling.
           ======================================================== */

        document.body.appendChild(depositModal);
        document.body.appendChild(depositConfirmModal);
        document.body.appendChild(withdrawModal);

        /* ========================================================
           ADD DYNAMIC DEPOSIT ROW
           ======================================================== */

        addRowBtn.addEventListener("click", function () {

            let newRow =
                depositRowsTable.insertRow();

            newRow.innerHTML = `

                <td>
                    <input
                        type="text"
                        name="packs[${rowIndex}][item_number]"
                        placeholder="Item/Code"
                        required
                    >
                </td>

                <td>
                    <input
                        type="text"
                        name="packs[${rowIndex}][serial_number]"
                        placeholder="Serial #"
                    >
                </td>

                <td>
                    <input
                        type="number"
                        name="packs[${rowIndex}][quantity]"
                        placeholder="Qty"
                        required
                        min="1"
                    >
                </td>

                <td>
                    <input
                        type="date"
                        name="packs[${rowIndex}][expiration_date]"
                        required
                    >
                </td>

                <td>
                    <input
                        type="text"
                        name="packs[${rowIndex}][supplier]"
                        placeholder="Supplier"
                    >
                </td>

                <td>

                    <button
                        type="button"
                        class="btn-secondary removeRowBtn"
                        style="padding: 4px 8px;"
                     data-ui-action="secondary">
                        X
                    </button>

                </td>

            `;

            rowIndex++;

            updateRemoveButtons();

        });

        /* ========================================================
           REMOVE DEPOSIT ROW
           ======================================================== */

        depositRowsTable.addEventListener("click", function (e) {

            if (
                e.target.classList.contains("removeRowBtn")
            ) {

                e.target.closest("tr").remove();

                updateRemoveButtons();

            }

        });

        /* ========================================================
           ENABLE / DISABLE REMOVE BUTTONS
           ======================================================== */

        function updateRemoveButtons() {

            let rows =
                depositRowsTable.getElementsByTagName("tr");

            for (
                let i = 0;
                i < rows.length;
                i++
            ) {

                let btn =
                    rows[i].querySelector(".removeRowBtn");

                if (rows.length === 1) {

                    btn.disabled = true;

                } else {

                    btn.disabled = false;

                }

            }

        }

        /* ========================================================
           CLOSE ALL MODALS
           ======================================================== */

        function closeAllModals() {

            depositModal.style.display = "none";

            withdrawModal.style.display = "none";

            depositConfirmModal.classList.remove("active");

        }

        /* ========================================================
           OPEN DEPOSIT MODAL
           ======================================================== */

        document
            .querySelectorAll(".openDeposit")
            .forEach(function (btn) {

                btn.addEventListener("click", function (e) {

                    e.stopPropagation();

                    const itemName =
                        btn.dataset.name;

                    depositItemName.innerText =
                        "Depositing packs for: " + itemName;

                    depositNameInput.value =
                        itemName;

                    depositModal.style.display =
                        "flex";

                });

            });

        /* ========================================================
           DEPOSIT FORM SUBMISSION
           
           IMPORTANT:
           Do NOT submit immediately.
           Show confirmation first.
           ======================================================== */

        depositForm.addEventListener("submit", function (event) {

            event.preventDefault();

            const itemName =
                depositNameInput.value;

            const rows =
                depositRowsTable.querySelectorAll("tr");

            const depositCount =
                rows.length;

            /* Display number of deposits */

            confirmDepositCount.textContent =
                depositCount === 1
                    ? "1 deposit"
                    : depositCount + " deposits";

            /* Display item name */

            confirmDepositItem.textContent =
                itemName;

            /* Show confirmation */

            depositConfirmModal.classList.add("active");

        });

        /* ========================================================
           CANCEL DEPOSIT CONFIRMATION
           ======================================================== */

        cancelDepositConfirmation.addEventListener(
            "click",
            function () {

                depositConfirmModal.classList.remove(
                    "active"
                );

            }
        );

        /* ========================================================
           CONFIRM DEPOSIT
           ======================================================== */

        confirmDepositSubmission.addEventListener(
            "click",
            function () {

                depositConfirmModal.classList.remove(
                    "active"
                );

                depositModal.style.display =
                    "none";

                /*
                 * Native form.submit() bypasses the submit
                 * event, so the confirmation will NOT appear
                 * again.
                 */

                depositForm.submit();

            }
        );

        /* ========================================================
           OPEN SPECIFIC PACK WITHDRAW MODAL
           ======================================================== */

        document
            .querySelectorAll(".openWithdrawModal")
            .forEach(function (btn) {

                btn.addEventListener(
                    "click",
                    function (e) {

                        e.stopPropagation();

                        const supplyId =
                            this.dataset.id;

                        const maxQty =
                            this.dataset.max;

                        const itemCode =
                            this.dataset.code;

                        withdrawSupplyId.value =
                            supplyId;

                        withdrawPackInfo.innerText =
                            `Pack Code: ${itemCode} | Available Stock: ${maxQty}`;

                        withdrawQtyInput.max =
                            maxQty;

                        withdrawQtyInput.value =
                            1;

                        withdrawModal.style.display =
                            "flex";

                    }
                );

            });

        /* ========================================================
           CLOSE BUTTONS
           ======================================================== */

        document
            .querySelectorAll(
                ".closeModal, .closeWithdrawModalBtn"
            )
            .forEach(function (btn) {

                btn.addEventListener(
                    "click",
                    closeAllModals
                );

            });

        /* ========================================================
           CLICK OUTSIDE MODAL
           ======================================================== */

        window.addEventListener(
            "click",
            function (e) {

                if (
                    e.target === depositModal ||
                    e.target === withdrawModal
                ) {

                    closeAllModals();

                }

                if (
                    e.target === depositConfirmModal
                ) {

                    depositConfirmModal.classList.remove(
                        "active"
                    );

                }

            }
        );

        /* ========================================================
           ESC KEY
           ======================================================== */

        document.addEventListener(
            "keydown",
            function (e) {

                if (e.key === "Escape") {

                    closeAllModals();

                }

            }
        );

    });
