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
@foreach ($supplies as $supply)
<tr>
    <td>{{ $supply->name }}</td>
    <td>{{ $supply->category }}</td>
    <td>
    {{ $supply->quantity }}
    </td>

    <td>
    @if($supply->quantity <= 0)
        <span class="low">Out of Stock</span>
    @elseif($supply->quantity <= $supply->min_stock)
        <span class="medium">Low Stock</span>
    @else
        <span class="high">In Stock</span>
    @endif
</td>

    <td>
        <button 
            class="btn-primary openDeposit"
            data-id="{{ $supply->id }}"
            data-name="{{ $supply->name }}">
            Deposit
        </button>

        <button 
            class="btn-secondary openRelease"
            data-id="{{ $supply->id }}"
            data-name="{{ $supply->name }}">
            Release
        </button>
    </td>

    <td>{{ $supply->updated_at->format('M d, Y - h:i A') }}</td>
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
        btn.addEventListener("click", () => {
            depositModal.style.display = "flex";
            depositId.value = btn.dataset.id;
            depositItemName.innerText = btn.dataset.name;
        });
    });

    document.querySelectorAll(".openRelease").forEach(btn => {
        btn.addEventListener("click", () => {
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