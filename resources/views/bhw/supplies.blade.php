@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/supplies.css') }}">
@endsection

@section('content')

<!-- HEADER -->
<div class="page-top">
    <div class="page-title">
        <h1>Health Supplies Inventory & Requests</h1>
        <p>Monitor stock levels, manage packs, and review citizen supply requests.</p>
    </div>

    @if(auth()->user()->hasWriteAccess('supplies'))
        <a href="{{ route('supplies.create') }}" class="btn-primary">
            + Add New Item
        </a>
        <button type="button" class="btn-primary" id="openReservationModal" style="background: #10b981; margin-left: 10px;">
            + Direct Citizen Reservation
        </button>

        <button type="button" class="btn-primary" id="openBatchReleaseModal" style="background: #2563eb; margin-left: 10px;">
        📦 Batch Release Supplies
    </button>
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


<!-- ================================================================
     CITIZEN SUPPLY REQUESTS MANAGEMENT SECTION
     ================================================================ -->
<div class="section-container" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 30px;">
    <h2 style="margin-bottom: 15px; font-size: 1.25rem; color: #1f2937;">Incoming Citizen Supply Requests</h2>

    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 10px 15px; border-radius: 4px; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 10px 15px; border-radius: 4px; margin-bottom: 15px;">
            {{ session('error') }}
        </div>
    @endif

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

                    $minStockThreshold = $itemsOfKind->first()->min_stock ?? 5;

                    $category = $itemsOfKind->first()->category;

                    $lastUpdated = $itemsOfKind->max('updated_at');
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

                            <!-- Inventory Packs Table -->
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

                            <!-- Citizens with Direct Reservations Sub-Table -->
                            @php
                                $itemIds = $itemsOfKind->pluck('id');
                                $reservationsForThisItem = collect();
                                if(isset($bhwReservations)) {
                                    foreach($itemIds as $id) {
                                        if(isset($bhwReservations[$id])) {
                                            $reservationsForThisItem = $reservationsForThisItem->merge($bhwReservations[$id]);
                                        }
                                    }
                                }
                            @endphp

                            <div style="padding: 10px 15px; background: #f8fafc; border-top: 1px solid #e5e7eb;">
                                <h4 style="font-size: 13px; color: #1f2937; margin-bottom: 8px;">👤 Citizens with Direct Reservations</h4>
                                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px; background: white; border: 1px solid #e5e7eb; border-radius: 4px;">
                                    <thead>
                                        <tr style="background: #f3f4f6; text-align: left;">
                                            <th style="padding: 8px; border-bottom: 1px solid #ddd;">Citizen Name</th>
                                            <th style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">Reserved Qty</th>
                                            <th style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">Priority</th>
                                            <th style="padding: 8px; border-bottom: 1px solid #ddd;">BHW Notes</th>
                                            <th style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">Date Reserved</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reservationsForThisItem as $res)
                                            <tr style="border-bottom: 1px solid #f9f9f9;">
                                                <td style="padding: 8px; font-weight: 500;">
                                                    {{ $res->citizen->Citizen_FName ?? 'N/A' }} {{ $res->citizen->Citizen_LName ?? '' }}
                                                </td>
                                                <td style="padding: 8px; text-align: center;">
                                                    <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: bold;">
                                                        {{ $res->quantity }}
                                                    </span>
                                                </td>
                                                <td style="padding: 8px; text-align: center; text-transform: capitalize;">
                                                    {{ $res->priority }}
                                                </td>
                                                <td style="padding: 8px; color: #555;">
                                                    {{ $res->bhw_remarks }}
                                                </td>
                                                <td style="padding: 8px; text-align: center; color: #6b7280; font-size: 12px;">
                                                    {{ $res->created_at->format('M d, Y h:i A') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" style="padding: 10px; text-align: center; color: #6b7280;">
                                                    No active BHW citizen reservations for this item.
                                                </td>
                                            </tr>
                                        @endforelse
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

<!-- ================================================================
     DIRECT BHW RESERVATION MODAL
     ================================================================ -->
<div id="reservationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 450px; max-width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; color: #1f2937; margin-bottom: 15px;">Direct Citizen Supply Reservation</h3>

        <form action="{{ route('bhw.supply.reserve') }}" method="POST">
            @csrf

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: #374151;">Select Citizen:</label>
                <select name="citizen_id" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
                    <option value="">-- Choose Citizen --</option>
                    @foreach(\App\Models\citizens::all() as $citizen)
                        <option value="{{ $citizen->id }}">{{ $citizen->Citizen_FName ?? '' }} {{ $citizen->Citizen_LName ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: #374151;">Select Supply Item:</label>
                <select name="supply_id" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
                    <option value="">-- Choose Supply --</option>
                    @foreach($supplies->where('quantity', '>', 0) as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->name }} (Available: {{$sup->quantity }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: #374151;">Quantity to Reserve:</label>
                <input type="number" name="quantity" min="1" value="1" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: #374151;">Priority Level:</label>
                <select name="priority" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: #374151;">BHW Remarks / Notes:</label>
                <textarea name="bhw_remarks" placeholder="Optional notes regarding this reservation..." rows="2" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-family: inherit;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" id="closeReservationModalBtn" style="padding: 8px 14px; background: #e5e7eb; color: #374151; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">Cancel</button>
                <button type="submit" style="padding: 8px 14px; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">Confirm Reservation</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const resModal = document.getElementById("reservationModal");
        const openBtn = document.getElementById("openReservationModal");
        const closeBtn = document.getElementById("closeReservationModalBtn");

        if(openBtn) {
            openBtn.addEventListener("click", () => { resModal.style.display = "flex"; });
        }
        if(closeBtn) {
            closeBtn.addEventListener("click", () => { resModal.style.display = "none"; });
        }
        window.addEventListener("click", (e) => {
            if(e.target === resModal) { resModal.style.display = "none"; }
        });
    });
</script>

<!-- Batch Release Modal Overlay -->
<div id="batchReleaseModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1050;">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 600px; max-width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; color: #1f2937; margin-bottom: 15px;">Batch Release Supplies to Reserved Citizens</h3>

        <form action="{{ route('bhw.supplies.batch.release') }}" method="POST">
            @csrf

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: #374151;">Select Approved Reservations to Release:</label>
                
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px;">
                    @php
                        // Fetch all currently approved requests across items
                        $approvedReservations = \App\Models\SupplyRequest::with(['citizen', 'supply'])
                            ->where('status', 'approved')
                            ->get();
                    @endphp

                    @forelse($approvedReservations as $res)
                        <label style="display: flex; align-items: center; gap: 10px; padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="supply_request_ids[]" value="{{ $res->id }}" data-supply="{{ $res->supply->name ?? 'Item' }}" data-qty="{{ $res->quantity }}" data-citizen="{{ $res->citizen->Citizen_FName ?? '' }} {{ $res->citizen->Citizen_LName ?? '' }}" class="release-checkbox">
                            <div>
                                <strong>{{ $res->citizen->Citizen_FName ?? '' }} {{ $res->citizen->Citizen_LName ?? '' }}</strong> — 
                                <span style="color: #0369a1;">{{ $res->quantity }}x {{ $res->supply->name ?? 'N/A' }}</span>
                                <br><small style="color: #6b7280;">Priority: {{ ucfirst($res->priority) }} | Reserved on: {{ $res->created_at->format('M d, Y') }}</small>
                            </div>
                        </label>
                    @empty
                        <p style="text-align: center; color: #6b7280; font-size: 13px; margin: 10px 0;">No approved citizen reservations available for release.</p>
                    @endforelse
                </div>
            </div>

            <!-- Dynamic Preview Box when items are checked -->
            <div id="releasePreviewBox" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 13px; color: #166534;">
                <strong>Selected Summary:</strong> <span id="releasePreviewText"></span>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="batch_title" style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: #374151;">Batch Release Title / Event Name:</label>
                <input 
                    type="text" 
                    name="batch_title" 
                    id="batch_title" 
                    class="form-control" 
                    placeholder="e.g., Q3 Barangay Health Supply Distribution" 
                    required
                    style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;"
                >
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: #374151;">Scheduled Release Date (Reflected on Calendar):</label>
                <input type="date" name="release_date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: #374151;">Release Notes / Remarks:</label>
                <textarea name="notes" placeholder="Optional notes for distribution event..." rows="2" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-family: inherit;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" id="closeBatchReleaseModalBtn" style="padding: 8px 14px; background: #e5e7eb; color: #374151; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">Cancel</button>
                <button type="submit" style="padding: 8px 14px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">Confirm Batch Release & Log Schedule</button>
            </div>
        </form>
    </div>
</div>  
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("batchReleaseModal");
        const openBtn = document.getElementById("openBatchReleaseModal");
        const closeBtn = document.getElementById("closeBatchReleaseModalBtn");
        const checkboxes = document.querySelectorAll(".release-checkbox");
        const previewBox = document.getElementById("releasePreviewBox");
        const previewText = document.getElementById("releasePreviewText");

        if(openBtn) {
            openBtn.addEventListener("click", () => { modal.style.display = "flex"; });
        }
        if(closeBtn) {
            closeBtn.addEventListener("click", () => { modal.style.display = "none"; });
        }
        window.addEventListener("click", (e) => {
            if(e.target === modal) { modal.style.display = "none"; }
        });

        // Update preview dynamically when checkboxes are toggled
        checkboxes.forEach(chk => {
            chk.addEventListener("change", function() {
                let selected = [];
                checkboxes.forEach(c => {
                    if(c.checked) {
                        selected.push(`${c.dataset.qty}x ${c.dataset.supply} (${c.dataset.citizen})`);
                    }
                });

                if(selected.length > 0) {
                    previewBox.style.display = "block";
                    previewText.innerText = selected.join(", ");
                } else {
                    previewBox.style.display = "none";
                }
            });
        });
    });
</script>

@endsection