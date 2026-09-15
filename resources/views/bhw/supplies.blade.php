@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/supplies.css') }}">
<style>
    .item-toggle-row { cursor: pointer; background-color: #fafafa; font-weight: 600; }
    .item-toggle-row:hover { background-color: #f1f1f1; }
    .batch-details-row { background-color: #ffffff; }
    .batch-table { width: 95%; margin: 10px auto; border: 1px solid #e0e0e0; }
    .batch-table th, .batch-table td { font-size: 0.9rem; padding: 6px 10px !important; }
    .arrow-icon { display: inline-block; transition: transform 0.2s ease; margin-right: 8px; }
    .expanded .arrow-icon { transform: rotate(90deg); }
    
    /* Multi-row Deposit Table Styles */
    .multi-deposit-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
    .multi-deposit-table th, .multi-deposit-table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
    .multi-deposit-table input, .multi-deposit-table select { width: 100%; padding: 6px; box-sizing: border-box; }
</style>
@endsection

@section('content')

<!-- HEADER -->
<div class="page-top">
    <div class="page-title">
        <h1>Health Supplies Inventory</h1>
        <p>Monitor stock levels of medicines and health supplies.</p>
    </div>
    <a href="{{ route('supplies.create') }}" class="btn-primary">+ Add New Item</a>
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
        // Filter out empty placeholder rows so we only count real batches
        $actualBatches = $itemsOfKind->filter(function($item) {
            return $item->quantity > 0 || !empty($item->item_number) || !empty($item->serial_number);
        });

        $totalQty = $itemsOfKind->sum('quantity');
        $minStockThreshold = $itemsOfKind->first()->min_stock ?? 5;
        $category = $itemsOfKind->first()->category;
        $lastUpdated = $itemsOfKind->max('updated_at');
    @endphp

    <!-- MASTER ROW (DROPDOWN TRIGGER) -->
    <tr class="item-toggle-row">
        <td onclick="toggleBatchRow('batches-{{ $loop->index }}', this)">
            <span class="arrow-icon">▶</span> 
            <strong>{{ $itemName }}</strong> 
            <small class="text-muted">({{ $actualBatches->count() }} batches)</small>
        </td>
        <td onclick="toggleBatchRow('batches-{{ $loop->index }}', this)">{{ $category }}</td>
        <td onclick="toggleBatchRow('batches-{{ $loop->index }}', this)">{{ $totalQty }}</td>
        <td onclick="toggleBatchRow('batches-{{ $loop->index }}', this)">
            @if($totalQty <= 0)
                <span class="low">Out of Stock</span>
            @elseif($totalQty <= $minStockThreshold)
                <span class="medium">Low Stock</span>
            @else
                <span class="high">In Stock</span>
            @endif
        </td>
        <td>
            <!-- Triggers Multi-Batch Deposit Modal -->
            <button 
                type="button"
                class="btn-primary openDeposit"
                data-name="{{ $itemName }}">
                + Deposit Batches
            </button>
        </td>
        <td onclick="toggleBatchRow('batches-{{ $loop->index }}', this)">{{ \Carbon\Carbon::parse($lastUpdated)->format('M d, Y - h:i A') }}</td>
    </tr>

    <!-- EXPANDABLE CHILD ROW (LISTS REAL BATCHES ONLY) -->
<tr id="batches-{{ $loop->index }}" class="batch-details-row" style="display: none;">
    <td colspan="8" style="padding: 0; background: #f9f9f9;">
        <table class="table batch-table">
            <thead>
                <tr style="background: #efefef;">
                    <th>Item # / Code</th>
                    <th>Serial #</th>
                    <th>Unit</th>
                    <th>Qty</th>
                    <th>Expiration Date</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($itemsOfKind as $supply)
                    @if($supply->quantity > 0 || !empty($supply->item_number) || !empty($supply->serial_number))
                    <tr>
                        <td>{{ $supply->item_number ?? 'N/A' }}</td>
                        <td>{{ $supply->serial_number ?? 'N/A' }}</td>
                        <td>{{ $supply->unit ?? 'N/A' }}</td>
                        <td><strong>{{ $supply->quantity }}</strong></td>
                        <td>
                            @if($supply->expiration_date)
                                {{ \Carbon\Carbon::parse($supply->expiration_date)->format('Y-m-d') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $supply->supplier ?? 'N/A' }}</td>
                        <td>
                            @if($supply->expiration_date && \Carbon\Carbon::parse($supply->expiration_date)->isPast())
                                <span class="low">Expired</span>
                            @else
                                <span class="high">Available</span>
                            @endif
                        </td>
                        <td>
                            @if($supply->expiration_date && \Carbon\Carbon::parse($supply->expiration_date)->isPast())
                                <!-- Trash Button for Expired Items (Completely removes expired batch) -->
                                <form action="{{ route('supplies.destroy', $supply->id) }}" method="POST" onsubmit="return confirm('Remove this expired batch entirely?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary" style="padding: 4px 8px; color: #d9534f; border-color: #d9534f;" title="Delete Expired Batch">
                                        🗑️ Delete
                                    </button>
                                </form>
                            @else
                                <!-- Button to open Withdraw Quantity Modal for this specific batch -->
                                <button 
                                    type="button" 
                                    class="btn-secondary openWithdrawModal" 
                                    style="padding: 4px 8px;"
                                    data-id="{{ $supply->id }}"
                                    data-max="{{ $supply->quantity }}"
                                    data-code="{{ $supply->item_number ?? 'N/A' }}">
                                    Withdraw
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </td>
</tr>
@endforeach
</tbody>
</table>
</div>

<!-- ================= MULTI-DEPOSIT MODAL ================= -->
<div id="depositModal" class="modal" style="display: none; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background: white; padding: 20px; border-radius: 8px; width: 800px; max-width: 95%;">
        <h3>Deposit New Batches</h3>
        <p id="depositItemName" style="font-weight: bold; color: #555; margin-bottom: 15px;"></p>

        <form method="POST" action="{{ route('supplies.deposit') }}">
            @csrf
            <input type="hidden" name="name" id="depositNameInput">

            <table class="multi-deposit-table" id="depositRowsTable">
                <thead>
                    <tr>
                        <th>Item # / Code</th>
                        <th>Serial #</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Expiry Date</th>
                        <th>Supplier</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Initial Row -->
                    <tr>
                        <td><input type="text" name="batches[0][item_number]" placeholder="Item/Code" required></td>
                        <td><input type="text" name="batches[0][serial_number]" placeholder="Serial #"></td>
                        <td><input type="number" name="batches[0][quantity]" placeholder="Qty" required min="1"></td>
                        <td><input type="text" name="batches[0][unit]" placeholder="e.g. box"></td>
                        <td><input type="date" name="batches[0][expiration_date]" required></td>
                        <td><input type="text" name="batches[0][supplier]" placeholder="Supplier"></td>
                        <td><button type="button" class="btn-secondary removeRowBtn" style="padding: 4px 8px;" disabled>X</button></td>
                    </tr>
                </tbody>
            </table>

            <button type="button" id="addRowBtn" class="btn-secondary" style="margin-bottom: 15px;">+ Add Another Batch Row</button>

            <div class="modal-actions" style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="submit" class="btn-primary">Submit All Deposits</button>
                <button type="button" class="btn-secondary closeModal">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function toggleBatchRow(rowId, element) {
    const targetRow = document.getElementById(rowId);
    const masterRow = targetRow.previousElementSibling;
    
    if (targetRow.style.display === "none") {
        targetRow.style.display = "table-row";
        masterRow.classList.add("expanded");
    } else {
        targetRow.style.display = "none";
        masterRow.classList.remove("expanded");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const depositModal = document.getElementById("depositModal");
    const depositItemName = document.getElementById("depositItemName");
    const depositNameInput = document.getElementById("depositNameInput");
    const depositRowsTable = document.getElementById("depositRowsTable").getElementsByTagName('tbody')[0];
    const addRowBtn = document.getElementById("addRowBtn");

    let rowIndex = 1;

    // Add dynamic row functionality for multi-deposits
    addRowBtn.addEventListener("click", function() {
        let newRow = depositRowsTable.insertRow();
        newRow.innerHTML = `
            <td><input type="text" name="batches[${rowIndex}][item_number]" placeholder="Item/Code" required></td>
            <td><input type="text" name="batches[${rowIndex}][serial_number]" placeholder="Serial #"></td>
            <td><input type="number" name="batches[${rowIndex}][quantity]" placeholder="Qty" required min="1"></td>
            <td><input type="text" name="batches[${rowIndex}][unit]" placeholder="e.g. box"></td>
            <td><input type="date" name="batches[${rowIndex}][expiration_date]" required></td>
            <td><input type="text" name="batches[${rowIndex}][supplier]" placeholder="Supplier"></td>
            <td><button type="button" class="btn-secondary removeRowBtn" style="padding: 4px 8px;">X</button></td>
        `;
        rowIndex++;
        updateRemoveButtons();
    });

    // Handle row removal
    depositRowsTable.addEventListener("click", function(e) {
        if (e.target.classList.contains("removeRowBtn")) {
            e.target.closest("tr").remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        let rows = depositRowsTable.getElementsByTagName("tr");
        for (let i = 0; i < rows.length; i++) {
            let btn = rows[i].querySelector(".removeRowBtn");
            if (rows.length === 1) {
                btn.disabled = true;
            } else {
                btn.disabled = false;
            }
        }
    }

    function closeAll() {
        depositModal.style.display = "none";
    }

    document.querySelectorAll(".openDeposit").forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            depositModal.style.display = "flex";
            let itemName = btn.dataset.name;
            depositItemName.innerText = "Depositing batches for: " + itemName;
            depositNameInput.value = itemName;
        });
    });

    document.querySelectorAll(".closeModal").forEach(btn => {
        btn.addEventListener("click", closeAll);
    });

    window.addEventListener("click", e => {
        if (e.target === depositModal) {
            closeAll();
        }
    });
});
</script>
@endsection