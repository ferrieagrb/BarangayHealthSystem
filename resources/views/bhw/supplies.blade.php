@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/supplies.css') }}">
<style>
    .batch-release-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .52);
        opacity: 0;
        visibility: hidden;
        transition: opacity .2s ease, visibility .2s ease;
    }

    .batch-release-modal.is-open {
        opacity: 1;
        visibility: visible;
    }

    .batch-release-dialog {
        width: min(900px, 100%);
        max-height: 92vh;
        overflow-y: auto;
        padding: 24px;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
        transform: translateY(16px) scale(.98);
        transition: transform .25s ease;
    }

    .batch-release-modal.is-open .batch-release-dialog {
        transform: translateY(0) scale(1);
    }

    .batch-release-header,
    .batch-release-actions,
    .batch-release-row-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .batch-release-row-grid label {
        display: flex;
        min-width: 0;
        flex-direction: column;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }
    .batch-picker-trigger {
        width: 100%;
        min-height: 42px;
        overflow: hidden;
        margin-top: 6px;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #334155;
        cursor: pointer;
        text-align: left;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .batch-picker-trigger:hover,
    .batch-picker-trigger:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
        outline: none;
    }

    .batch-release-close,
    .batch-release-remove {
        border: 0;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        font-size: 22px;
    }

    .batch-release-field,
    .batch-release-row select,
    .batch-release-row input,
    .batch-release-details {
        width: 100%;
        margin-top: 6px;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #fff;
        color: #1e293b;
    }

    .batch-release-field:focus,
    .batch-release-row select:focus,
    .batch-release-row input:focus,
    .batch-release-details:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
        outline: none;
    }

    .batch-release-row {
        margin-top: 14px;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
    }

    .batch-release-row-grid {
        display: grid;
        grid-template-columns: 1fr 1.4fr 120px;
        gap: 12px;
        width: 100%;
    }

    .batch-release-add {
        margin-top: 14px;
        border: 1px dashed #93c5fd;
        border-radius: 9px;
        padding: 10px 14px;
        background: #eff6ff;
        color: #1d4ed8;
        cursor: pointer;
    }

    .batch-picker-modal {
        position: fixed;
        inset: 0;
        z-index: 10100;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .52);
        opacity: 0;
        visibility: hidden;
        transition: opacity .2s ease, visibility .2s ease;
    }

    .batch-picker-modal.is-open {
        opacity: 1;
        visibility: visible;
    }

    .batch-picker-dialog {
        width: min(560px, 100%);
        max-height: 88vh;
        overflow-y: auto;
        padding: 22px;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
        transform: translateY(14px) scale(.98);
        transition: transform .25s ease;
    }

    .batch-picker-modal.is-open .batch-picker-dialog {
        transform: translateY(0) scale(1);
    }

    .batch-picker-search {
        width: 100%;
        margin: 14px 0 10px;
        padding: 11px 13px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
    }

    .batch-picker-option {
        display: block;
        width: 100%;
        margin-top: 8px;
        padding: 12px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #f8fafc;
        color: #1e293b;
        cursor: pointer;
        text-align: left;
    }

    .batch-picker-option:hover {
        border-color: #93c5fd;
        background: #eff6ff;
    }

    .batch-picker-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 16px;
    }

    @media (max-width: 700px) {
        .batch-release-row-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')

<!-- HEADER -->
<div class="page-top">
    <div class="page-title">
        <h1>Health Supplies Inventory</h1>
<<<<<<< HEAD
        <p>Monitor stock levels and manage supply packs.</p>
=======
        <p>Monitor stock levels and manage health supply inventory.</p>
>>>>>>> main
    </div>

    @if(auth()->user()->hasWriteAccess('supplies'))
        <a href="{{ route('supplies.create') }}" class="btn-primary">
            + Add New Item
        </a>
<<<<<<< HEAD
        <button type="button" id="openBatchReleaseModal" class="btn-primary" style="margin-left: 10px;">
            Schedule Batch Release
        </button>
=======
>>>>>>> main
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

<<<<<<< HEAD

<!-- BATCH RELEASES -->
<div class="table-container" style="margin-bottom: 24px;">
    <div class="page-title" style="margin-bottom: 16px;">
        <h2>Batch Releases</h2>
        <p>Review scheduled distributions and confirm citizen claims.</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Batch</th>
                <th>Claim Date</th>
                <th>Assignments</th>
                <th>Progress</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batchReleases as $eventId => $releaseGroup)
                @php
                    $firstRelease = $releaseGroup->first();
                    $claimedCount = $releaseGroup->where('status', 'claimed')->count();
                @endphp
                <tr>
                    <td><strong>{{ $firstRelease->batch_title }}</strong></td>
                    <td>{{ $firstRelease->claim_date->format('M d, Y') }}</td>
                    <td>{{ $releaseGroup->count() }}</td>
                    <td>{{ $claimedCount }} / {{ $releaseGroup->count() }} claimed</td>
                    <td><a href="{{ route('supplies.batch.release.show', $eventId) }}" class="btn-secondary">View Batch</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #64748b;">No batch releases scheduled.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

=======
>>>>>>> main
<!-- TABLE -->
<div class="table-container supplies-main-table-wrapper">

    <table class="supplies-main-table">

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
                            <div class="pack-table-wrapper">
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
                class="supplies-add-row-btn"
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

@php
    $batchReleaseCitizens = $citizens->map(function ($citizen) {
        return [
            'id' => $citizen->id,
            'name' => trim(($citizen->Citizen_FName ?? '') . ' ' . ($citizen->Citizen_LName ?? '')),
        ];
    })->values();

    $batchReleaseSupplies = $supplies->filter(function ($supply) {
        return $supply->quantity > 0;
    })->map(function ($supply) {
        return [
            'id' => $supply->id,
            'name' => $supply->name,
            'code' => $supply->item_number ?: ($supply->serial_number ?: 'Batch #' . $supply->id),
            'quantity' => $supply->quantity,
        ];
    })->values();
@endphp

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

<<<<<<< HEAD
<div id="batchReleaseModal" class="batch-release-modal" role="dialog" aria-modal="true" aria-labelledby="batchReleaseTitle" aria-hidden="true">
    <div class="batch-release-dialog">
        <div class="batch-release-header">
            <div>
                <h2 id="batchReleaseTitle">Schedule Batch Release</h2>
                <p>Assign specific supply batches to citizens and add the claiming schedule.</p>
            </div>
            <button type="button" id="closeBatchReleaseModal" class="batch-release-close" aria-label="Close batch release modal">&times;</button>
        </div>

        <form method="POST" action="{{ route('supplies.batch.release') }}" id="batchReleaseForm">
            @csrf

            <label for="batch_release_title">Batch Release Title</label>
            <input type="text" name="batch_title" id="batch_release_title" class="batch-release-field" placeholder="e.g. October Medicine Distribution" required>

            <label for="batch_release_date">Claiming / Release Date</label>
            <input type="date" name="claim_date" id="batch_release_date" class="batch-release-field" value="{{ now()->format('Y-m-d') }}" required>

            <label for="batch_release_details">Details</label>
            <textarea name="details" id="batch_release_details" class="batch-release-details" rows="3" placeholder="Add distribution instructions or location details..."></textarea>

            <div class="batch-release-row-header" style="margin-top: 20px;">
                <h3>Citizen Assignments</h3>
                <span style="color: #64748b; font-size: 13px;">Each row uses one specific supply batch.</span>
            </div>

            <div id="batchReleaseAssignments"></div>

            <button type="button" id="addBatchReleaseAssignment" class="batch-release-add">+ Add Citizen Assignment</button>

            <div class="batch-release-actions" style="margin-top: 22px; justify-content: flex-end;">
                <button type="button" id="cancelBatchRelease" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Release &amp; Schedule Batch</button>
            </div>
        </form>
    </div>
</div>

<div id="batchPickerModal" class="batch-picker-modal" role="dialog" aria-modal="true" aria-labelledby="batchPickerTitle" aria-hidden="true">
    <div class="batch-picker-dialog">
        <div class="batch-release-header">
            <h2 id="batchPickerTitle">Select Citizen</h2>
            <button type="button" id="closeBatchPicker" class="batch-release-close" aria-label="Close picker">&times;</button>
        </div>
        <input type="search" id="batchPickerSearch" class="batch-picker-search" placeholder="Search...">
        <div id="batchPickerOptions"></div>
        <div class="batch-picker-footer">
            <button type="button" id="batchPickerPrevious" class="btn-secondary">Previous</button>
            <span id="batchPickerPage">Page 1</span>
            <button type="button" id="batchPickerNext" class="btn-secondary">Next</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const batchReleaseModal = document.getElementById('batchReleaseModal');
    const openBatchReleaseModal = document.getElementById('openBatchReleaseModal');
    const closeBatchReleaseModal = document.getElementById('closeBatchReleaseModal');
    const cancelBatchRelease = document.getElementById('cancelBatchRelease');
    const batchReleaseForm = document.getElementById('batchReleaseForm');
    const addAssignmentButton = document.getElementById('addBatchReleaseAssignment');
    const assignmentsContainer = document.getElementById('batchReleaseAssignments');
    const batchPickerModal = document.getElementById('batchPickerModal');
    const batchPickerTitle = document.getElementById('batchPickerTitle');
    const batchPickerSearch = document.getElementById('batchPickerSearch');
    const batchPickerOptions = document.getElementById('batchPickerOptions');
    const batchPickerPage = document.getElementById('batchPickerPage');
    const batchPickerPrevious = document.getElementById('batchPickerPrevious');
    const batchPickerNext = document.getElementById('batchPickerNext');
    const citizens = @json($batchReleaseCitizens);
    const supplies = @json($batchReleaseSupplies);
    let assignmentIndex = 0;
    let activePickerType = 'citizen';
    let activeAssignmentRow = null;
    let pickerPage = 1;

    function pickerItems() {
        const items = activePickerType === 'citizen' ? citizens : supplies;
        const search = batchPickerSearch.value.trim().toLowerCase();

        return items.filter(function (item) {
            const label = activePickerType === 'citizen'
                ? item.name
                : `${item.name} ${item.code}`;

            return label.toLowerCase().includes(search);
        });
    }

    function renderPicker() {
        const items = pickerItems();
        const pageSize = 10;
        const totalPages = Math.max(1, Math.ceil(items.length / pageSize));
        pickerPage = Math.min(pickerPage, totalPages);
        const pageItems = items.slice((pickerPage - 1) * pageSize, pickerPage * pageSize);

        batchPickerOptions.innerHTML = '';

        if (!pageItems.length) {
            batchPickerOptions.innerHTML = '<p>No matching records found.</p>';
        }

        pageItems.forEach(function (item) {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'batch-picker-option';
            option.textContent = activePickerType === 'citizen'
                ? item.name || 'Unnamed citizen'
                : `${item.name} | ${item.code} | Available: ${item.quantity}`;
            option.addEventListener('click', function () {
                const valueInput = activeAssignmentRow.querySelector(
                    activePickerType === 'citizen' ? '.assignment-citizen-id' : '.assignment-supply-id'
                );
                const displayButton = activeAssignmentRow.querySelector(
                    activePickerType === 'citizen' ? '.select-citizen-button' : '.select-supply-button'
                );

                valueInput.value = item.id;
                displayButton.textContent = activePickerType === 'citizen' ? item.name : `${item.name} | ${item.code}`;
                closePicker();
            });
            batchPickerOptions.appendChild(option);
        });

        batchPickerPage.textContent = `Page ${pickerPage} of ${totalPages}`;
        batchPickerPrevious.disabled = pickerPage === 1;
        batchPickerNext.disabled = pickerPage === totalPages;
    }

    function openPicker(type, row) {
        activePickerType = type;
        activeAssignmentRow = row;
        pickerPage = 1;
        batchPickerSearch.value = '';
        batchPickerTitle.textContent = type === 'citizen' ? 'Select Citizen' : 'Select Supply Batch';
        batchPickerSearch.placeholder = type === 'citizen' ? 'Search citizen...' : 'Search supply or batch code...';
        batchPickerModal.classList.add('is-open');
        batchPickerModal.setAttribute('aria-hidden', 'false');
        renderPicker();
    }

    function closePicker() {
        batchPickerModal.classList.remove('is-open');
        batchPickerModal.setAttribute('aria-hidden', 'true');
        activeAssignmentRow = null;
    }

    function addOption(select, value, label) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = label;
        select.appendChild(option);
    }

    function addAssignmentRow() {
        const row = document.createElement('div');
        row.className = 'batch-release-row';
        row.innerHTML = `
            <div class="batch-release-row-header">
                <strong>Assignment ${assignmentIndex + 1}</strong>
                <button type="button" class="batch-release-remove" aria-label="Remove assignment">&times;</button>
            </div>
            <div class="batch-release-row-grid">
                <label>Citizen
                    <input type="hidden" name="assignments[${assignmentIndex}][citizen_id]" class="assignment-citizen-id" required>
                    <button type="button" class="batch-picker-trigger select-citizen-button">Select citizen</button>
                </label>
                <label>Specific Supply Batch
                    <input type="hidden" name="assignments[${assignmentIndex}][supply_id]" class="assignment-supply-id" required>
                    <button type="button" class="batch-picker-trigger select-supply-button">Select supply batch</button>
                </label>
                <label>Quantity
                    <input type="number" name="assignments[${assignmentIndex}][quantity]" min="1" value="1" required>
                </label>
            </div>`;

        row.querySelector('.select-citizen-button').addEventListener('click', function () {
            openPicker('citizen', row);
        });

        row.querySelector('.select-supply-button').addEventListener('click', function () {
            openPicker('supply', row);
        });

        row.querySelector('.batch-release-remove').addEventListener('click', function () {
            row.remove();
        });

        assignmentsContainer.appendChild(row);
        assignmentIndex++;
    }

    function openModal() {
        if (!assignmentsContainer.children.length) {
            addAssignmentRow();
        }
        batchReleaseModal.classList.add('is-open');
        batchReleaseModal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        closePicker();
        batchReleaseModal.classList.remove('is-open');
        batchReleaseModal.setAttribute('aria-hidden', 'true');
        batchReleaseForm.reset();
        assignmentsContainer.replaceChildren();
        assignmentIndex = 0;
    }

    openBatchReleaseModal?.addEventListener('click', openModal);
    closeBatchReleaseModal.addEventListener('click', closeModal);
    cancelBatchRelease.addEventListener('click', closeModal);
    addAssignmentButton.addEventListener('click', addAssignmentRow);
    batchReleaseModal.addEventListener('click', function (event) {
        if (event.target === batchReleaseModal) {
            closeModal();
        }
    });
    batchPickerSearch.addEventListener('input', function () {
        pickerPage = 1;
        renderPicker();
    });
    batchPickerPrevious.addEventListener('click', function () {
        pickerPage--;
        renderPicker();
    });
    batchPickerNext.addEventListener('click', function () {
        pickerPage++;
        renderPicker();
    });
    document.getElementById('closeBatchPicker').addEventListener('click', closePicker);
    batchPickerModal.addEventListener('click', function (event) {
        if (event.target === batchPickerModal) {
            closePicker();
        }
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
            closePicker();
        }
    });
});
</script>

=======
>>>>>>> main
@endsection