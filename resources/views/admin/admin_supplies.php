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
        <h1>Admin Supply Inventory</h1>
        <p>Monitor health center medical supplies, stock levels, and batch distributions.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn-primary" onclick="openCatalogModal()" style="background: #6c757d; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">
            + New Catalog Item
        </button>
        <button type="button" class="btn-primary" onclick="openDepositModal()" style="background: #0d6efd; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">
            + Deposit Stock Batch
        </button>
    </div>
</div>

<!-- Phase 1: Summary Analytics Metrics Cards -->
<div class="summary-cards" style="display: flex; gap: 20px; margin-bottom: 20px;">
    <div class="summary-card" style="background: white; padding: 15px 20px; border-radius: 8px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <span style="color: #6c757d; font-size: 14px;">Total Stock Quantity</span>
        <strong style="display: block; font-size: 24px; color: #333;">{{ $totalsupply }}</strong>
    </div>

    <div class="summary-card" style="background: white; padding: 15px 20px; border-radius: 8px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <span style="color: #6c757d; font-size: 14px;">Well-Stocked Items</span>
        <strong style="display: block; font-size: 24px; color: #198754;">{{ $wellStocked }}</strong>
    </div>

    <div class="summary-card" style="background: white; padding: 15px 20px; border-radius: 8px; flex: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <span style="color: #6c757d; font-size: 14px;">Low Stock Alerts</span>
        <strong style="display: block; font-size: 24px; color: #dc3545;">{{ $lowStock }}</strong>
    </div>
</div>

<!-- Toolbar: Search & Filters -->
<div class="toolbar" style="display: flex; justify-content: space-between; margin-bottom: 15px;">
    <div class="toolbar-left" style="display: flex; gap: 10px;">
        <form method="GET" action="{{ route('admin.supplies') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search supply or serial #..." style="padding: 8px; border: 1px solid #ced4da; border-radius: 4px; width: 250px;">
        </form>

        <form method="GET" action="{{ route('admin.supplies') }}">
            <select name="status" onchange="this.form.submit()" style="padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
                <option value="">All Stock Statuses</option>
                <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
            </select>
        </form>
    </div>
</div>

<!-- Supplies Data Table -->
<div class="table-container" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #dee2e6;">
                <th style="padding: 10px;">Item Name / Category</th>
                <th style="padding: 10px;">Batch Identifiers</th>
                <th style="padding: 10px;">Quantity</th>
                <th style="padding: 10px;">Expiration Date</th>
                <th style="padding: 10px;">Status</th>
                <th style="padding: 10px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($supplies as $supply)
            <tr style="border-bottom: 1px solid #f1f1f1;">
                <td style="padding: 10px;">
                    <strong>{{ $supply->name }}</strong><br>
                    <small style="color: #6c757d;">{{ $supply->category }}</small>
                </td>
                <td style="padding: 10px;">
                    <small>Item #: {{ $supply->item_number ?? 'N/A' }}</small><br>
                    <small>Serial #: {{ $supply->serial_number ?? 'N/A' }}</small>
                </td>
                <td style="padding: 10px;">
                    {{ $supply->quantity }} {{ $supply->unit }}
                </td>
                <td style="padding: 10px;">
                    {{ $supply->expiration_date ? \Carbon\Carbon::parse($supply->expiration_date)->format('M d, Y') : 'N/A' }}
                </td>
                <td style="padding: 10px;">
                    @if($supply->quantity <= 0)
                        <span style="background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-size: 12px;">Out of Stock</span>
                    @elseif($supply->quantity <= $supply->min_stock)
                        <span style="background: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 4px; font-size: 12px;">Low Stock</span>
                    @else
                        <span style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-size: 12px;">Well Stocked</span>
                    @endif
                </td>
                <td style="padding: 10px;">
                    <div style="display: flex; gap: 5px;">
                        <button type="button" onclick="openWithdrawModal('{{ $supply->id }}', '{{ $supply->name }}', '{{ $supply->quantity }}')" style="background: #ffc107; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">Withdraw</button>
                        
                        <form action="{{ route('supplies.destroy', $supply->id) }}" method="POST" onsubmit="return confirm('Remove this batch record?')" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #6c757d;">No supplies found in inventory matching your filters.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Phase 2: Create Catalog Item Modal -->
<div id="catalogModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 450px; max-width: 90%;">
        <h3>Create New Supply Catalog Item</h3>
        <form action="{{ route('supplies.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 12px;">
                <label>Item Name</label>
                <input type="text" name="name" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label>Category</label>
                <input type="text" name="category" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label>Minimum Stock Threshold</label>
                <input type="number" name="min_stock" min="0" value="5" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Description (Optional)</label>
                <textarea name="description" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeCatalogModal()" style="padding: 8px 12px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 8px 12px; background: #0d6efd; color: white; border: none; border-radius: 4px; cursor: pointer;">Save Catalog</button>
            </div>
        </form>
    </div>
</div>

<!-- Phase 2: Deposit Batch Modal -->
<div id="depositModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 500px; max-width: 90%;">
        <h3>Deposit Stock Batch</h3>
        <form action="{{ route('supplies.deposit') }}" method="POST">
            @csrf
            <div style="margin-bottom: 12px;">
                <label>Item Name</label>
                <input type="text" name="name" placeholder="Exact catalog item name" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 12px;">
                <label>Quantity</label>
                <input type="number" name="batches[0][quantity]" min="1" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label>Expiration Date</label>
                <input type="date" name="batches[0][expiration_date]" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 12px;">
                <label>Supplier (Optional)</label>
                <input type="text" name="batches[0][supplier]" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px;">
                <button type="button" onclick="closeDepositModal()" style="padding: 8px 12px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 8px 12px; background: #0d6efd; color: white; border: none; border-radius: 4px; cursor: pointer;">Confirm Deposit</button>
            </div>
        </form>
    </div>
</div>

<!-- Phase 2: Withdraw Stock Modal -->
<div id="withdrawModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 400px; max-width: 90%;">
        <h3 id="withdrawTitle">Withdraw Batch Stock</h3>
        <form action="{{ route('supplies.batch.withdraw') }}" method="POST">
            @csrf
            <input type="hidden" name="supply_id" id="withdraw_supply_id">
            
            <div style="margin-bottom: 12px;">
                <label>Quantity to Withdraw</label>
                <input type="number" name="quantity" id="withdraw_quantity_input" min="1" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Notes / Reason</label>
                <textarea name="notes" placeholder="Optional remarks..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeWithdrawModal()" style="padding: 8px 12px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 8px 12px; background: #ffc107; border: none; border-radius: 4px; cursor: pointer;">Confirm Withdrawal</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Catalog Modal Functions
    function openCatalogModal() { document.getElementById('catalogModal').style.display = 'flex'; }
    function closeCatalogModal() { document.getElementById('catalogModal').style.display = 'none'; }

    // Deposit Modal Functions
    function openDepositModal() { document.getElementById('depositModal').style.display = 'flex'; }
    function closeDepositModal() { document.getElementById('depositModal').style.display = 'none'; }

    // Withdraw Modal Functions
    function openWithdrawModal(id, name, maxQty) {
        document.getElementById('withdraw_supply_id').value = id;
        document.getElementById('withdrawTitle').innerText = `Withdraw Stock: ${name}`;
        document.getElementById('withdraw_quantity_input').max = maxQty;
        document.getElementById('withdrawModal').style.display = 'flex';
    }
    function closeWithdrawModal() { document.getElementById('withdrawModal').style.display = 'none'; }
</script>

@endsection