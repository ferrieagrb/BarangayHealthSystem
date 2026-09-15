@extends('templates.admin')

@section('content')

@if ($errors->any())
    <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        {{ session('success') }}
    </div>
@endif

<div class="page-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div class="page-title-group">
        <h1>🏥 BHW Health Supplies Analytics</h1>
        <p>Monitor health center medical supplies, stock levels, and hierarchical batch distributions.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" onclick="openCatalogModal()" style="background: #6c757d; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">
            + New Catalog Item
        </button>
        <button type="button" onclick="openDepositModal()" style="background: #0d6efd; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">
            + Deposit Stock Batch
        </button>
    </div>
</div>

<!-- --- 1. METRICS & VISUAL INDICATORS --- -->
<div class="summary-cards" style="display: flex; gap: 20px; margin-bottom: 20px;">
    <div class="summary-card" style="background: white; padding: 15px 20px; border-radius: 8px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <span style="color: #6c757d; font-size: 14px;">📦 Total Stock Qty</span>
        <strong style="display: block; font-size: 24px; color: #333;">{{ $totalsupply }}</strong>
    </div>

    <div class="summary-card" style="background: white; padding: 15px 20px; border-radius: 8px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <span style="color: #6c757d; font-size: 14px;">🟢 Well-Stocked Items</span>
        <strong style="display: block; font-size: 24px; color: #198754;">{{ $wellStocked }}</strong>
    </div>

    <div class="summary-card" style="background: white; padding: 15px 20px; border-radius: 8px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <span style="color: #6c757d; font-size: 14px;">⚠️ Low Stock Alerts</span>
        <strong style="display: block; font-size: 24px; color: #dc3545;">{{ $lowStock }}</strong>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.supplies') }}">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item name or code..." style="padding: 8px; border: 1px solid #ced4da; border-radius: 4px; width: 300px;">
    </form>
</div>

<!-- --- 2. HIERARCHICAL INVENTORY VIEW --- -->
<div class="table-container" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <h3 style="margin-bottom: 15px;">📋 Master Inventory Hierarchy</h3>

    @php 
        $groupedSupplies = $supplies->groupBy('name'); 
    @endphp

    @forelse($groupedSupplies as $name => $group)
        @php
            $totalQty = $group->sum('quantity');
            $category = $group->first()->category;
            $minThreshold = $group->first()->min_stock ?? 10;

            if ($totalQty <= 0) {
                $statusBadge = '<span style="background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-size: 12px;">🔴 Out of Stock</span>';
            } elseif ($totalQty <= $minThreshold) {
                $statusBadge = '<span style="background: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 4px; font-size: 12px;">🟡 Low Stock</span>';
            } else {
                $statusBadge = '<span style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-size: 12px;">🟢 In Stock</span>';
            }
        @php

        <!-- Expander Row matching Streamlit concept -->
        <details style="border: 1px solid #e0e0e0; border-radius: 6px; margin-bottom: 10px; padding: 10px 15px; background: #fafafa;">
            <summary style="cursor: pointer; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
                <span>{{ $name }} | <small style="color: #666;">Category: {{ $category }}</small> | Total Qty: {{ $totalQty }}</span>
                <div>
                    {!! $statusBadge !!}
                </div>
            </summary>

            <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;">
                <p style="font-size: 13px; color: #555; margin-bottom: 8px;"><strong>Active Batch Records ({{ count($group) }} packs):</strong></p>
                
                <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 15px; background: white;">
                    <thead>
                        <tr style="background: #f1f1f1; text-align: left;">
                            <th style="padding: 8px;">Item #</th>
                            <th style="padding: 8px;">Serial #</th>
                            <th style="padding: 8px;">Quantity</th>
                            <th style="padding: 8px;">Expiration Date</th>
                            <th style="padding: 8px;">Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group as $batch)
                        <tr style="border-bottom: 1px solid #f9f9f9;">
                            <td style="padding: 8px;">{{ $batch->item_number ?? 'N/A' }}</td>
                            <td style="padding: 8px;">{{ $batch->serial_number ?? 'N/A' }}</td>
                            <td style="padding: 8px;">{{ $batch->quantity }} {{ $batch->unit }}</td>
                            <td style="padding: 8px;">{{ $batch->expiration_date ? \Carbon\Carbon::parse($batch->expiration_date)->format('Y-m-d') : 'N/A' }}</td>
                            <td style="padding: 8px;">{{ $batch->supplier ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Action Trigger Inside Hierarchy -->
                <button type="button" onclick="openAdminModal('{{ $name }}')" style="background: #ffc107; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">
                    ⚙️ Manage Admin Controls: {{ $name }}
                </button>
            </div>
        </details>
    @empty
        <p style="text-align: center; color: #6c757d; padding: 20px;">No supplies found.</p>
    @endforelse
</div>

<!-- --- 3. ACTIONABLE ADMINISTRATIVE MODAL --- -->
<div id="adminModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 500px; max-width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 id="modalTitle">⚙️ Administrative Control Panel</h3>
            <button type="button" onclick="closeAdminModal()" style="background:none; border:none; font-size: 20px; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('supplies.deposit') }}" method="POST">
            @csrf
            <div style="margin-bottom: 12px;">
                <label>Target Item Name</label>
                <input type="text" name="name" id="modal_item_name" readonly style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; background: #e9ecef;">
            </div>
            <div style="margin-bottom: 12px;">
                <label>Deposit Quantity</label>
                <input type="number" name="batches[0][quantity]" min="1" value="10" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label>Expiration Date</label>
                <input type="date" name="batches[0][expiration_date]" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                <button type="button" onclick="closeAdminModal()" style="padding: 8px 12px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 8px 12px; background: #0d6efd; color: white; border: none; border-radius: 4px; cursor: pointer;">Confirm Action</button>
            </div>
        </form>
    </div>
</div>

<!-- Extra Modals for Catalog Creation -->
<div id="catalogModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 400px; max-width: 90%;">
        <h3>Create New Catalog Item</h3>
        <form action="{{ route('supplies.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 12px;"><label>Item Name</label><input type="text" name="name" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></div>
            <div style="margin-bottom: 12px;"><label>Category</label><input type="text" name="category" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;"><button type="button" onclick="closeCatalogModal()" style="padding: 8px; background:#6c757d; color:white; border:none; border-radius:4px;">Cancel</button><button type="submit" style="padding: 8px; background:#0d6efd; color:white; border:none; border-radius:4px;">Save</button></div>
        </form>
    </div>
</div>

<script>
    function openAdminModal(itemName) {
        document.getElementById('modal_item_name').value = itemName;
        document.getElementById('adminModal').style.display = 'flex';
    }
    function closeAdminModal() { document.getElementById('adminModal').style.display = 'none'; }

    function openCatalogModal() { document.getElementById('catalogModal').style.display = 'flex'; }
    function closeCatalogModal() { document.getElementById('catalogModal').style.display = 'none'; }
</script>

@endsection