@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/supplies.css') }}">
<style>
    /* Accordion Custom Styling */
    .item-toggle-row {
        cursor: pointer;
        background-color: #fafafa;
        font-weight: 600;
    }
    .item-toggle-row:hover {
        background-color: #f1f1f1;
    }
    .batch-details-row {
        background-color: #ffffff;
    }
    .batch-table {
        width: 95%;
        margin: 10px auto;
        border: 1px solid #e0e0e0;
    }
    .batch-table th, .batch-table td {
        font-size: 0.9rem;
        padding: 6px 10px !important;
    }
    .arrow-icon {
        display: inline-block;
        transition: transform 0.2s ease;
        margin-right: 8px;
    }
    .expanded .arrow-icon {
        transform: rotate(90deg);
    }
</style>
@endsection

@section('content')

<!-- HEADER -->
<div class="page-top">
    <div class="page-title">
        <h1>Health Supplies Inventory</h1>
        <p>Monitor stock levels of medicines and health supplies.</p>
    </div>
    <a href="{{ route('supplies.create') }}" class="btn-primary">+ Add Item</a>
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
    <th>Quantity</th>
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
        $totalQty = $itemsOfKind->sum('quantity');
        $minStockThreshold = $itemsOfKind->first()->min_stock ?? 5;
        $category = $itemsOfKind->first()->category;
        $lastUpdated = $itemsOfKind->max('updated_at');
        // Default to the first batch ID for quick actions on the main row
        $defaultSupply = $itemsOfKind->first();
    @endphp

    <!-- MASTER ROW (DROPDOWN TRIGGER) -->
    <tr class="item-toggle-row">
        <td onclick="toggleBatchRow('batches-{{ $loop->index }}', this)" style="cursor: pointer;">
            <span class="arrow-icon">▶</span> 
            <strong>{{ $itemName }}</strong> 
            <small class="text-muted">({{ $itemsOfKind->count() }} batches)</small>
        </td>
        <td onclick="toggleBatchRow('batches-{{ $loop->index }}', this)" style="cursor: pointer;">{{ $category }}</td>
        <td onclick="toggleBatchRow('batches-{{ $loop->index }}', this)" style="cursor: pointer;">{{ $totalQty }}</td>
        <td onclick="toggleBatchRow('batches-{{ $loop->index }}', this)" style="cursor: pointer;">
            @if($totalQty <= 0)
                <span class="low">Out of Stock</span>
            @elseif($totalQty <= $minStockThreshold)
                <span class="medium">Low Stock</span>
            @else
                <span class="high">In Stock</span>
            @endif
        </td>
        <td>
            <button 
                class="btn-primary openDeposit"
                data-id="{{ $defaultSupply->id }}"
                data-name="{{ $itemName }}">
                Deposit
            </button>

            <button 
                class="btn-secondary openRelease"
                data-id="{{ $defaultSupply->id }}"
                data-name="{{ $itemName }}">
                Release
            </button>
        </td>
        <td onclick="toggleBatchRow('batches-{{ $loop->index }}', this)" style="cursor: pointer;">{{ \Carbon\Carbon::parse($lastUpdated)->format('M d, Y - h:i A') }}</td>
    </tr>

    <!-- EXPANDABLE CHILD ROW (LISTS ALL BATCHES OF THAT KIND) -->
    <tr id="batches-{{ $loop->index }}" class="batch-details-row" style="display: none;">
        <td colspan="6" style="padding: 0; background: #f9f9f9;">
            <table class="table batch-table">
                <thead>
                    <tr style="background: #efefef;">
                        <th>Item #</th>
                        <th>Serial #</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Expiration Date</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Batch Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemsOfKind as $supply)
                    <tr>
                        <td>{{ $supply->item_number ?? 'N/A' }}</td>
                        <td>{{ $supply->serial_number ?? 'N/A' }}</td>
                        <td>{{ $supply->unit ?? 'N/A' }}</td>
                        <td>{{ $supply->quantity }}</td>
                        <td>
                            @if($supply->expiration_date)
                                {{ \Carbon\Carbon::parse($supply->expiration_date)->format('Y-m-d') }}
                                @if(\Carbon\Carbon::parse($supply->expiration_date)->isPast())
                                    <span class="low" style="font-size:0.75rem;">(Expired)</span>
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $supply->supplier ?? 'N/A' }}</td>
                        <td>
                            @if($supply->quantity <= 0)
                                <span class="low">Out of Stock</span>
                            @elseif($supply->quantity <= $supply->min_stock)
                                <span class="medium">Low Stock</span>
                            @else
                                <span class="high">{{ $supply->status ?? 'Available' }}</span>
                            @endif
                        </td>
                        <td>
                            <button 
                                class="btn-primary openDeposit"
                                data-id="{{ $supply->id }}"
                                data-name="{{ $supply->name }} (Item #: {{ $supply->item_number ?? 'N/A' }})">
                                Deposit
                            </button>

                            <button 
                                class="btn-secondary openRelease"
                                data-id="{{ $supply->id }}"
                                data-name="{{ $supply->name }} (Item #: {{ $supply->item_number ?? 'N/A' }})">
                                Release
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </td>
    </tr>
@endforeach
</tbody>
</table>
</div>

<!-- ================= MODALS ================= -->

<!-- DEPOSIT -->
<div id="depositModal" class="modal">
    <div class="modal-content">
        <h3>Deposit Stock</h3>
        <p id="depositItemName"></p>

        <form method="POST" action="{{ route('supplies.deposit') }}">
            @csrf
            <input type="hidden" name="id" id="depositId">
            <input type="number" name="quantity" placeholder="Enter quantity" required>

            <div class="modal-actions">
                <button type="submit" class="btn-primary">Confirm</button>
                <button type="button" class="btn-secondary closeModal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- RELEASE -->
<div id="releaseModal" class="modal">
    <div class="modal-content">

        <h3>Release Stock</h3>
        <p id="releaseItemName"></p>

        <form method="POST" action="{{ route('supplies.release') }}">
            @csrf

            <input type="hidden" name="id" id="releaseId">

            <input type="number" name="quantity" required>

            <select name="citizen_id" required>
                <option value="">Select Citizen</option>
                @foreach($citizens as $citizen)
                    <option value="{{ $citizen->id }}">
                        {{ $citizen->name }}
                    </option>
                @endforeach
            </select>

            <textarea name="notes" placeholder="Notes / Diagnosis"></textarea>

            <button type="submit">Confirm</button>
        </form>

    </div>
</div>

@endsection

@section('scripts')
<script>
function toggleBatchRow(rowId, element) {
    const targetRow = document.getElementById(rowId);
    // Find the master row to toggle the arrow class
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
    const releaseModal = document.getElementById("releaseModal");

    const depositId = document.getElementById("depositId");
    const releaseId = document.getElementById("releaseId");

    const depositItemName = document.getElementById("depositItemName");
    const releaseItemName = document.getElementById("releaseItemName");

    function closeAll() {
        depositModal.style.display = "none";
        releaseModal.style.display = "none";
    }

    document.querySelectorAll(".openDeposit").forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation(); 
            depositModal.style.display = "flex";
            depositId.value = btn.dataset.id;
            depositItemName.innerText = btn.dataset.name;
        });
    });

    document.querySelectorAll(".openRelease").forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation(); 
            releaseModal.style.display = "flex";
            releaseId.value = btn.dataset.id;
            releaseItemName.innerText = btn.dataset.name;
            releaseModal.querySelector("form").reset();
            releaseId.value = btn.dataset.id;
        });
    });

    document.querySelectorAll(".closeModal").forEach(btn => {
        btn.addEventListener("click", closeAll);
    });

    window.addEventListener("click", e => {
        if (e.target === depositModal || e.target === releaseModal) {
            closeAll();
        }
    });

});
</script>
@endsection