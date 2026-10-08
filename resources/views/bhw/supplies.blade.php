@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/supplies.css') }}">
@endsection

@section('content')

<!-- HEADER -->
<div class="page-top">
    <div class="page-title">
        <h1>Health Supplies Inventory</h1>
        <p>Monitor stock levels of medicines and health supplies.</p>
    </div>

    @if(auth()->user()->hasWriteAccess('supplies'))
        <a href="{{ route('supplies.create') }}" class="btn-primary">
            + Add New Item
        </a>
    @endif
</div>


<!-- SUMMARY -->
<div class="summary">

    <div class="summary-card">
        <span>Total Items</span>
        <strong>{{ $totalsupply }}</strong>
    </div>

    <div class="summary-card">
        <span>Low Stock</span>
        <strong>{{ $lowStock }}</strong>
    </div>

    <div class="summary-card">
        <span>Well Stocked</span>
        <strong>{{ $wellStocked }}</strong>
    </div>

</div>


<!-- TOOLBAR -->
<div class="toolbar">
    <form method="GET" action="{{ url('/supplies') }}">
        <select name="status" onchange="this.form.submit()">
            <option value="all">All</option>
            <option value="in_stock">In Stock</option>
            <option value="low_stock">Low Stock</option>
            <option value="out_of_stock">Out of Stock</option>
        </select>
    </form>
</div>


<!-- TABLE -->
<div class="table-container">

    <table>

        <thead>
            <tr>
                <th>Item</th>
                <th>Category</th>
                <th>Total Quantity</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Last Updated</th>
            </tr>
        </thead>

        <tbody>

            @php
                $groupedSupplies = $supplies->groupBy('name');
            @endphp

            @foreach ($groupedSupplies as $itemName => $itemsOfKind)

                @php
                    $actualPacks = $itemsOfKind->filter(function($item) {
                        return $item->quantity > 0 ||
                               !empty($item->item_number) ||
                               !empty($item->serial_number);
                    });

                    $totalQty = $itemsOfKind->sum('quantity');

                    $minStockThreshold =
                        $itemsOfKind->first()->min_stock ?? 5;

                    $category =
                        $itemsOfKind->first()->category;

                    $lastUpdated =
                        $itemsOfKind->max('updated_at');
                @endphp


                <!-- =====================================================
                     MASTER ROW / DROPDOWN TRIGGER
                     ===================================================== -->

                <tr
                    class="item-toggle-row"
                    onclick="togglePackRow('packs-{{ $loop->index }}', this)"
                >

                    <td>
                        <span class="arrow-icon">▶</span>

                        <strong>{{ $itemName }}</strong>

                        <small class="text-muted">
                            ({{ $actualPacks->count() }} packs)
                        </small>
                    </td>


                    <td>
                        {{ $category }}
                    </td>


                    <td>
                        {{ $totalQty }}
                    </td>


                    <td>

                        @if($totalQty <= 0)

                            <span class="low">
                                Out of Stock
                            </span>

                        @elseif($totalQty <= $minStockThreshold)

                            <span class="medium">
                                Low Stock
                            </span>

                        @else

                            <span class="high">
                                In Stock
                            </span>

                        @endif

                    </td>


                    <td>

                        @if(auth()->user()->hasWriteAccess('supplies'))

                            <div onclick="event.stopPropagation();">

                                <button
                                    type="button"
                                    class="btn-primary openDeposit"
                                    data-name="{{ $itemName }}"
                                >
                                    + Deposit Packs
                                </button>

                            </div>

                        @else

                            <span style="color: #6b7280; font-size: 0.85rem;">
                                Read Only
                            </span>

                        @endif

                    </td>


                    <td>
                        {{ \Carbon\Carbon::parse($lastUpdated)->format('M d, Y - h:i A') }}
                    </td>

                </tr>


                <!-- =====================================================
                     EXPANDABLE CHILD ROW
                     ===================================================== -->

                <tr
                    id="packs-{{ $loop->index }}"
                    class="pack-details-row"
                >

                    <td
                        colspan="6"
                        style="padding: 0;"
                    >

                        <div class="pack-details-wrapper">

                            <div>

                                <table class="table pack-table">

                                    <thead>

                                        <tr style="background: #efefef;">

                                            <th>
                                                Item # / Code
                                            </th>

                                            <th>
                                                Serial #
                                            </th>

                                            <th>
                                                Unit
                                            </th>

                                            <th>
                                                Qty
                                            </th>

                                            <th>
                                                Expiration Date
                                            </th>

                                            <th>
                                                Supplier
                                            </th>

                                            <th>
                                                Status
                                            </th>

                                            <th>
                                                Action
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        @foreach ($itemsOfKind as $supply)

                                            @if(
                                                $supply->quantity > 0 ||
                                                !empty($supply->item_number) ||
                                                !empty($supply->serial_number)
                                            )

                                                <tr>

                                                    <td>
                                                        {{ $supply->item_number ?? 'N/A' }}
                                                    </td>


                                                    <td>
                                                        {{ $supply->serial_number ?? 'N/A' }}
                                                    </td>


                                                    <td>
                                                        {{ $supply->unit ?? 'N/A' }}
                                                    </td>


                                                    <td>
                                                        <strong>
                                                            {{ $supply->quantity }}
                                                        </strong>
                                                    </td>


                                                    <td>

                                                        @if($supply->expiration_date)

                                                            {{ \Carbon\Carbon::parse($supply->expiration_date)->format('Y-m-d') }}

                                                        @else

                                                            N/A

                                                        @endif

                                                    </td>


                                                    <td>
                                                        {{ $supply->supplier ?? 'N/A' }}
                                                    </td>


                                                    <td>

                                                        @if(
                                                            $supply->expiration_date &&
                                                            \Carbon\Carbon::parse($supply->expiration_date)->isPast()
                                                        )

                                                            <span class="low">
                                                                Expired
                                                            </span>

                                                        @else

                                                            <span class="high">
                                                                Available
                                                            </span>

                                                        @endif

                                                    </td>


                                                    <td>

                                                        @if(auth()->user()->hasWriteAccess('supplies'))

                                                            @if(
                                                                $supply->expiration_date &&
                                                                \Carbon\Carbon::parse($supply->expiration_date)->isPast()
                                                            )

                                                                <!-- DELETE EXPIRED PACK -->

                                                                <form
                                                                    action="{{ route('supplies.destroy', $supply->id) }}"
                                                                    method="POST"
                                                                    onsubmit="return confirm('Remove this expired pack entirely?');"
                                                                    style="display:inline;"
                                                                >

                                                                    @csrf
                                                                    @method('DELETE')

                                                                    <button
                                                                        type="submit"
                                                                        class="btn-secondary"
                                                                        style="padding: 4px 8px; color: #d9534f; border-color: #d9534f;"
                                                                        title="Delete Expired Pack"
                                                                    >
                                                                        🗑️ Delete
                                                                    </button>

                                                                </form>

                                                            @else

                                                                <!-- WITHDRAW SPECIFIC PACK -->

                                                                <button
                                                                    type="button"
                                                                    class="btn-secondary openWithdrawModal"
                                                                    style="padding: 4px 8px;"
                                                                    data-id="{{ $supply->id }}"
                                                                    data-max="{{ $supply->quantity }}"
                                                                    data-code="{{ $supply->item_number ?? 'N/A' }}"
                                                                >
                                                                    Withdraw
                                                                </button>

                                                            @endif

                                                        @else

                                                            <span style="color: #6b7280; font-size: 0.85rem;">
                                                                -
                                                            </span>

                                                        @endif

                                                    </td>

                                                </tr>

                                            @endif

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</div>


<!-- ================================================================
     MULTI-DEPOSIT MODAL
     ================================================================ -->

<div
    id="depositModal"
    class="supplies-modal-overlay"
    style="display: none;"
>

    <div class="supplies-modal-content deposit-modal-content">

        <h3>
            Deposit New Packs
        </h3>

        <p
            id="depositItemName"
            class="deposit-item-name"
        ></p>


        <!-- DEPOSIT FORM -->

        <form
            id="depositForm"
            method="POST"
            action="{{ route('supplies.deposit') }}"
        >

            @csrf

            <input
                type="hidden"
                name="name"
                id="depositNameInput"
            >


            <!-- DEPOSIT TABLE -->

            <div class="deposit-table-wrapper">

                <table
                    class="multi-deposit-table"
                    id="depositRowsTable"
                >

                    <thead>

                        <tr>

                            <th>
                                Item # / Code
                            </th>

                            <th>
                                Serial #
                            </th>

                            <th>
                                Qty
                            </th>

                            <th>
                                Unit
                            </th>

                            <th>
                                Expiry Date
                            </th>

                            <th>
                                Supplier
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <tr>

                            <td>
                                <input
                                    type="text"
                                    name="packs[0][item_number]"
                                    placeholder="Item/Code"
                                    required
                                >
                            </td>


                            <td>
                                <input
                                    type="text"
                                    name="packs[0][serial_number]"
                                    placeholder="Serial #"
                                >
                            </td>


                            <td>
                                <input
                                    type="number"
                                    name="packs[0][quantity]"
                                    placeholder="Qty"
                                    required
                                    min="1"
                                >
                            </td>


                            <td>
                                <input
                                    type="text"
                                    name="packs[0][unit]"
                                    placeholder="e.g. box"
                                >
                            </td>


                            <td>
                                <input
                                    type="date"
                                    name="packs[0][expiration_date]"
                                    required
                                >
                            </td>


                            <td>
                                <input
                                    type="text"
                                    name="packs[0][supplier]"
                                    placeholder="Supplier"
                                >
                            </td>


                            <td>

                                <button
                                    type="button"
                                    class="btn-secondary removeRowBtn"
                                    style="padding: 4px 8px;"
                                    disabled
                                >
                                    X
                                </button>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <!-- ADD ROW -->

            <button
                type="button"
                id="addRowBtn"
                class="btn-secondary"
                style="margin-bottom: 15px;"
            >
                + Add Another Pack Row
            </button>


            <!-- ACTION BUTTONS -->

            <div class="modal-actions">

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Submit All Deposits
                </button>

                <button
                    type="button"
                    class="btn-secondary closeModal"
                >
                    Cancel
                </button>

            </div>

        </form>

    </div>

</div>


<!-- ================================================================
     DEPOSIT CONFIRMATION MODAL
     ================================================================ -->

<div
    id="depositConfirmModal"
    class="deposit-confirm-overlay"
>

    <div class="deposit-confirm-modal">

        <h2>
            Confirm Deposit
        </h2>


        <p>
            Are you sure you want to submit
            <strong id="confirmDepositCount">
                1 deposit
            </strong>
            for
            <strong id="confirmDepositItem">
                this item
            </strong>?
        </p>


        <div class="deposit-confirm-buttons">

            <button
                type="button"
                id="cancelDepositConfirmation"
                class="deposit-confirm-cancel"
            >
                Cancel
            </button>


            <button
                type="button"
                id="confirmDepositSubmission"
                class="deposit-confirm-save"
            >
                Yes, Submit
            </button>

        </div>

    </div>

</div>


<!-- ================================================================
     SPECIFIC PACK WITHDRAW MODAL
     ================================================================ -->

<div
    id="withdrawModal"
    class="supplies-modal-overlay"
    style="display: none;"
>

    <div class="supplies-modal-content supplies-withdraw-content">

        <h3>
            Withdraw Pack Quantity
        </h3>


        <p
            id="withdrawPackInfo"
            class="withdraw-pack-info"
        ></p>


        <form
            method="POST"
            action="{{ route('supplies.batch.withdraw') }}"
        >

            @csrf

            <input
                type="hidden"
                name="supply_id"
                id="withdrawSupplyId"
            >


            <!-- QUANTITY -->

            <div
                class="form-group"
                style="margin-bottom: 15px;"
            >

                <label
                    style="display:block; margin-bottom: 5px;"
                >
                    Quantity to Withdraw:
                </label>

                <input
                    type="number"
                    name="quantity"
                    id="withdrawQtyInput"
                    min="1"
                    required
                    style="width: 100%; padding: 6px; box-sizing: border-box;"
                >

            </div>


            <!-- NOTES -->

            <div
                class="form-group"
                style="margin-bottom: 15px;"
            >

                <label
                    style="display:block; margin-bottom: 5px;"
                >
                    Notes / Reason (Optional):
                </label>

                <input
                    type="text"
                    name="notes"
                    placeholder="e.g., Dispensed to patient / Damaged"
                    style="width: 100%; padding: 6px; box-sizing: border-box;"
                >

            </div>


            <!-- ACTIONS -->

            <div class="modal-actions">

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Confirm Withdrawal
                </button>

                <button
                    type="button"
                    class="btn-secondary closeWithdrawModalBtn"
                >
                    Cancel
                </button>

            </div>

        </form>

    </div>

</div>


@endsection


@section('scripts')

<script>

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
                        type="text"
                        name="packs[${rowIndex}][unit]"
                        placeholder="e.g. box"
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
                    >
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

</script>

@endsection