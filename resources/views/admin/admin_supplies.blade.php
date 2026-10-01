@extends('templates.admin')

@section('content')

@if ($errors->any())
    <div style="background: #f8d7da; color: #721c24; padding: 12px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #f5c6cb; font-size: 14px;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div style="background: #d4edda; color: #155724; padding: 12px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #c3e6cb; font-size: 14px;">
        {{ session('success') }}
    </div>
@endif

<!-- Page Header -->
<div class="page-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid #e5e7eb; padding-bottom: 16px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 600; color: #111827; margin: 0 0 4px 0;">Manage Inventory</h1>
        <p style="font-size: 14px; color: #6b7280; margin: 0;">Monitor health center medical supplies, stock levels, and hierarchical batch distributions.</p>
    </div>
    <div style="display: flex; gap: 12px;">
        @canWrite('supplies')
        <button type="button" onclick="openCategoryModal()" style="background: #ffffff; color: #374151; border: 1px solid #d1d5db; padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer;">
            Manage Categories
        </button>
        @if(auth()->user()->hasWriteAccess('supplies'))
            <button type="button" onclick="openCatalogModal()" class="btn-primary">+ New Catalog Item</button>
        @endif
        @endcanWrite
    </div>
</div>

<!-- --- 1. METRICS & VISUAL INDICATORS --- -->
<div class="summary-cards" style="display: flex; gap: 16px; margin-bottom: 24px;">
    <div class="summary-card" style="background: white; padding: 20px; border-radius: 6px; flex: 1; border: 1px solid #e5e7eb;">
        <span style="color: #6b7280; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Total Stock Qty</span>
        <strong style="display: block; font-size: 28px; font-weight: 700; color: #111827; margin-top: 4px;">{{ $totalsupply }}</strong>
    </div>

    <div class="summary-card" style="background: white; padding: 20px; border-radius: 6px; flex: 1; border: 1px solid #e5e7eb;">
        <span style="color: #6b7280; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Well-Stocked Items</span>
        <strong style="display: block; font-size: 28px; font-weight: 700; color: #059669; margin-top: 4px;">{{ $wellStocked }}</strong>
    </div>

    <div class="summary-card" style="background: white; padding: 20px; border-radius: 6px; flex: 1; border: 1px solid #e5e7eb;">
        <span style="color: #6b7280; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Low Stock Alerts</span>
        <strong style="display: block; font-size: 28px; font-weight: 700; color: #dc2626; margin-top: 4px;">{{ $lowStock }}</strong>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.supplies') }}">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item name or code..." style="padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; width: 320px; font-size: 14px; outline: none;">
    </form>
</div>

<!-- --- 2. HIERARCHICAL INVENTORY VIEW --- -->
<div class="table-container" style="background: white; padding: 24px; border-radius: 6px; border: 1px solid #e5e7eb;">
    <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin-top: 0; margin-bottom: 20px;">📋 Master Inventory Hierarchy</h3>

    @php 
        $groupedSupplies =$supplies->groupBy('name'); 
    @endphp

    @forelse($groupedSupplies as $name =>$group)
        @php
            $firstItem =$group->first();
            $totalQty =$group->sum('quantity');
            $category =$firstItem ? $firstItem->category : 'N/A';$minThreshold = $firstItem ? ($firstItem->min_stock ?? 10) : 10;

            if ($totalQty <= 0) {$statusBadge = '<span style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">Out of Stock</span>';
            } elseif ($totalQty <= $minThreshold) {$statusBadge = '<span style="background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">Low Stock</span>';
            } else {
                $statusBadge = '<span style="background: #d1fae5; color: #065f46; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">In Stock</span>';
            }
        @endphp

        <!-- Flat Expander Row -->
        <details style="border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 12px; background: #ffffff;">
            <summary style="cursor: pointer; font-weight: 600; display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; font-size: 14px; color: #1f2937; background: #f9fafb; border-radius: 6px;">
                <span>{{ $name }} &vert; <span style="color: #6b7280; font-weight: normal;">Category: {{ $category }}</span> &vert; <span style="color: #374151;">Total Qty: {{ $totalQty }}</span></span>
                <div>
                    {!! $statusBadge !!}
                </div>
            </summary>

            <div style="padding: 16px 18px; border-top: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <p style="font-size: 13px; color: #4b5563; margin: 0;"><strong>Active Batch Records ({{ count($group) }} packs):</strong></p>
                    <button type="button" onclick="openDepositModalForItem('{{ $name }}')" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">
                        + Deposit Stock For This Item
                    </button>
                </div>
                
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 16px;">
                    <thead>
                        <tr style="background: #f3f4f6; text-align: left; color: #374151;">
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">Item #</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">Serial #</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">Quantity</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">Expiration Date</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group as $batch)
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 8px 10px; color: #1f2937;">{{ $batch->item_number ?? 'N/A' }}</td>
                            <td style="padding: 8px 10px; color: #1f2937;">{{ $batch->serial_number ?? 'N/A' }}</td>
                            <td style="padding: 8px 10px; color: #1f2937;">{{ $batch->quantity }} {{$batch->unit }}</td>
                            <td style="padding: 8px 10px; color: #1f2937;">{{ $batch->expiration_date ? \Carbon\Carbon::parse($batch->expiration_date)->format('Y-m-d') : 'N/A' }}</td>
                            <td style="padding: 8px 10px; color: #1f2937;">{{ $batch->supplier ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @empty
        <p style="text-align: center; color: #6b7280; padding: 24px; font-size: 14px;">No supplies found.</p>
    @endforelse
</div>

<!-- --- MODALS --- -->

<!-- Modal for Managing Categories (Add, Update, Delete) -->
<div id="categoryModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background: white; padding: 24px; border-radius: 6px; width: 500px; max-width: 90%; border: 1px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px; color: #111827;">⚙️ Manage Inventory Categories</h3>
            <button type="button" onclick="closeCategoryModal()" style="background:none; border:none; font-size: 20px; cursor:pointer; color: #6b7280;">&times;</button>
        </div>

        <!-- Add / Edit Category Form -->
        <form id="categoryForm" action="{{ route('admin.categories.store') }}" method="POST" style="margin-bottom: 20px;">
            @csrf
            <input type="hidden" name="_method" id="category_method_field" value="POST">
            <input type="hidden" id="edit_category_id" value="">
            
            <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;" id="category_form_label">Add New Category</label>
            <div style="display: flex; gap: 8px;">
                <input type="text" name="name" id="category_name_input" placeholder="Category name..." required style="flex: 1; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;">
                <button type="submit" id="category_submit_btn" style="padding: 8px 14px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;">Add</button>
                <button type="button" id="category_cancel_edit_btn" onclick="resetCategoryForm()" style="display: none; padding: 8px 14px; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 4px; cursor: pointer; font-size: 14px;">Cancel</button>
            </div>
        </form>

        <!-- Existing Categories Table List -->
        <p style="font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 8px;">Existing Categories:</p>
        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px; background: #f9fafb;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <tbody>
                    @isset($categories)
                        @foreach($categories as $cat)
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 8px; color: #1f2937; font-weight: 500;">{{ $cat->name }}</td>
                            <td style="padding: 8px; text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                <!-- Trigger Edit Mode -->
                                <button type="button" onclick="editCategory('{{ $cat->id }}', '{{$cat->name }}')" style="background: none; border: none; color: #2563eb; cursor: pointer; font-size: 13px;">Edit</button>
                                
                                <!-- Delete Form -->
                                <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this category?')" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 13px;">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td style="padding: 8px; color: #6b7280; text-align: center;">No categories available.</td>
                        </tr>
                    @endisset
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button type="button" onclick="closeCategoryModal()" style="padding: 8px 14px; background: #ffffff; color: #374151; border: 1px solid #d1d5db; border-radius: 4px; cursor: pointer; font-size: 14px;">Close</button>
        </div>
    </div>
</div>

<!-- Modal for Catalog Creation -->
<div id="catalogModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background: white; padding: 24px; border-radius: 6px; width: 450px; max-width: 90%; border: 1px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px; color: #111827;">Create New Catalog Item</h3>
            <button type="button" onclick="closeCatalogModal()" style="background:none; border:none; font-size: 20px; cursor:pointer; color: #6b7280;">&times;</button>
        </div>
        <form action="{{ route('supplies.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Item Name</label>
                <input type="text" name="name" required style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Category</label>
                <select name="category" required style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; background: white; font-size: 14px; box-sizing: border-box;">
                    <option value="" disabled selected>Select Category</option>
                    @isset($categories)
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
                <button type="button" onclick="closeCatalogModal()" style="padding: 8px 14px; background:#ffffff; color:#374151; border:1px solid #d1d5db; border-radius:4px; cursor: pointer; font-size: 14px;">Cancel</button>
                <button type="submit" style="padding: 8px 14px; background:#2563eb; color:white; border:none; border-radius:4px; cursor: pointer; font-size: 14px;">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Deposit Stock Batch (Multi-Row Dynamic) -->
<div id="depositModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background: white; padding: 24px; border-radius: 6px; width: 850px; max-width: 95%; max-height: 90vh; overflow-y: auto; border: 1px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px; color: #111827;">Deposit Stock Batches</h3>
            <button type="button" onclick="closeDepositModal()" style="background:none; border:none; font-size: 20px; cursor:pointer; color: #6b7280;">&times;</button>
        </div>
        
        <form action="{{ route('admin.supplies.deposit') }}" method="POST">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Main Item Name / Target</label>
                <input type="text" name="name" id="deposit_modal_item_name" required placeholder="Enter item name" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 16px; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;" id="depositRowsTable">
                    <thead>
                        <tr style="background: #f3f4f6; text-align: left; font-size: 13px; color: #374151;">
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">Item/Code</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">Serial #</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">Qty</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">Unit</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">Expiration</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">Supplier</th>
                            <th style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Initial Row -->
                        <tr>
                            <td style="padding: 6px 4px;"><input type="text" name="packs[0][item_number]" placeholder="Item/Code" required style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
                            <td style="padding: 6px 4px;"><input type="text" name="packs[0][serial_number]" placeholder="Serial #" style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
                            <td style="padding: 6px 4px;"><input type="number" name="packs[0][quantity]" placeholder="Qty" required min="1" style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
                            <td style="padding: 6px 4px;"><input type="text" name="packs[0][unit]" placeholder="e.g. box" style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
                            <td style="padding: 6px 4px;"><input type="date" name="packs[0][expiration_date]" required style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
                            <td style="padding: 6px 4px;"><input type="text" name="packs[0][supplier]" placeholder="Supplier" style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
                            <td style="padding: 6px 4px;"><button type="button" class="btn-secondary removeRowBtn" style="padding: 6px 10px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-radius: 4px; cursor: pointer; font-size: 13px;" disabled>✕</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" id="addRowBtn" style="margin-top: 12px; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">+ Add Another Row</button>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
                <button type="button" onclick="closeDepositModal()" style="padding: 8px 14px; background: #ffffff; color: #374151; border: 1px solid #d1d5db; border-radius: 4px; cursor: pointer; font-size: 14px;">Cancel</button>
                <button type="submit" style="padding: 8px 14px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">Confirm Deposit</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Category Modal Controls & Dynamic Edit Switcher
    function openCategoryModal() { document.getElementById('categoryModal').style.display = 'flex'; }
    function closeCategoryModal() { document.getElementById('categoryModal').style.display = 'none'; resetCategoryForm(); }

    function editCategory(id, name) {
        let form = document.getElementById('categoryForm');
        form.action = "/admin/categories/" + id; // Adjust based on your update route naming convention
        document.getElementById('category_method_field').value = "PUT";
        document.getElementById('category_form_label').innerText = "Edit Category: " + name;
        document.getElementById('category_name_input').value = name;
        document.getElementById('category_submit_btn').innerText = "Update";
        document.getElementById('category_cancel_edit_btn').style.display = 'inline-block';
    }

    function resetCategoryForm() {
        let form = document.getElementById('categoryForm');
        form.action = "{{ route('admin.categories.store') }}";
        document.getElementById('category_method_field').value = "POST";
        document.getElementById('category_form_label').innerText = "Add New Category";
        document.getElementById('category_name_input').value = '';
        document.getElementById('category_submit_btn').innerText = "Add";
        document.getElementById('category_cancel_edit_btn').style.display = 'none';
    }

    // Catalog Creation Modal
    function openCatalogModal() { document.getElementById('catalogModal').style.display = 'flex'; }
    function closeCatalogModal() { document.getElementById('catalogModal').style.display = 'none'; }

    // Deposit Stock Batch Modals
    function openDepositModal() { 
        document.getElementById('deposit_modal_item_name').value = '';
        document.getElementById('depositModal').style.display = 'flex'; 
    }
    function openDepositModalForItem(itemName) {
        document.getElementById('deposit_modal_item_name').value = itemName;
        document.getElementById('depositModal').style.display = 'flex';
    }
    function closeDepositModal() { document.getElementById('depositModal').style.display = 'none'; }

    // Dynamic Multi-Row Script for Deposits
    let rowIndex = 1;
    const addRowBtn = document.getElementById("addRowBtn");
    const depositRowsTable = document.getElementById("depositRowsTable").getElementsByTagName('tbody')[0];

    addRowBtn.addEventListener("click", function() {
        let newRow = depositRowsTable.insertRow();
        newRow.innerHTML = `
            <td style="padding: 6px 4px;"><input type="text" name="packs[${rowIndex}][item_number]" placeholder="Item/Code" required style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
            <td style="padding: 6px 4px;"><input type="text" name="packs[${rowIndex}][serial_number]" placeholder="Serial #" style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
            <td style="padding: 6px 4px;"><input type="number" name="packs[${rowIndex}][quantity]" placeholder="Qty" required min="1" style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
            <td style="padding: 6px 4px;"><input type="text" name="packs[${rowIndex}][unit]" placeholder="e.g. box" style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
            <td style="padding: 6px 4px;"><input type="date" name="packs[${rowIndex}][expiration_date]" required style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
            <td style="padding: 6px 4px;"><input type="text" name="packs[${rowIndex}][supplier]" placeholder="Supplier" style="width: 100%; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px;"></td>
            <td style="padding: 6px 4px;"><button type="button" class="btn-secondary removeRowBtn" style="padding: 6px 10px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-radius: 4px; cursor: pointer; font-size: 13px;">✕</button></td>
        `;
        rowIndex++;
        updateRemoveButtons();
    });

    document.addEventListener("click", function(e) {
        if (e.target && e.target.classList.contains("removeRowBtn")) {
            let row = e.target.closest("tr");
            row.remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        let removeButtons = depositRowsTable.getElementsByClassName("removeRowBtn");
        for (let btn of removeButtons) {
            btn.disabled = depositRowsTable.rows.length <= 1;
            btn.style.opacity = btn.disabled ? "0.4" : "1";
            btn.style.cursor = btn.disabled ? "not-allowed" : "pointer";
        }
    }
</script>

@endsection