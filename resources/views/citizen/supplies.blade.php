@extends('templates.citizen')

@section('content')
<div style="max-width: 900px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <div style="margin-bottom: 20px;">
        <h2>💊 Available Health Center Supplies & Medicines</h2>
        <p style="color: #666; font-size: 14px;">Browse current stock availability and click an item to submit a request.</p>
    </div>

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

    <!-- Search Bar -->
    <form method="GET" action="{{ route('citizen.supplies') }}" style="margin-bottom: 20px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search medicine or supply name..." style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px;">
    </form>

    <!-- Available Supplies Table -->
    <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 40px;">
        <thead>
            <tr style="background: #f1f1f1; text-align: left;">
                <th style="padding: 10px; border-bottom: 2px solid #ddd;">Medicine / Supply Name</th>
                <th style="padding: 10px; border-bottom: 2px solid #ddd;">Category</th>
                <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: center;">Available Quantity</th>
                <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($availableSupplies as $item)
            <tr style="border-bottom: 1px solid #f9f9f9;">
                <td style="padding: 12px; font-weight: 500;">{{ $item->name }}</td>
                <td style="padding: 12px; color: #555;">{{ $item->category }}</td>
                <td style="padding: 12px; text-align: center;">
                    <span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 4px; font-weight: bold;">
                        {{ $item->total_quantity }} available
                    </span>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <button 
                        type="button" 
                        class="openRequestModal"
                        data-id="{{ $item->id }}"
                        data-name="{{ $item->name }}"
                        data-max="{{ $item->total_quantity }}"
                        style="padding: 6px 14px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"
                    >
                        Request Item
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: #6c757d; padding: 30px;">No supplies currently available.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ================================================================
         MY SUPPLY REQUESTS STATUS SECTION
         ================================================================ -->
    <div style="border-top: 2px solid #e5e7eb; padding-top: 25px;">
        <h3 style="margin-bottom: 15px; color: #1f2937;">📋 My Submitted Requests Status</h3>
        
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: #f3f4f6; text-align: left;">
                    <th style="padding: 10px; border-bottom: 2px solid #ddd;">Item Requested</th>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: center;">Quantity</th>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd;">Remarks</th>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: center;">Status</th>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: center;">Claim Date</th>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: center;">Date Submitted</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myRequests as $req)
                <tr style="border-bottom: 1px solid #f9f9f9;">
                    <td style="padding: 12px; font-weight: 500;">{{ $req->supply->name ?? 'Unknown Item' }}</td>
                    <td style="padding: 12px; text-align: center;">{{ $req->quantity }}</td>
                    <td style="padding: 12px; color: #555;">{{ $req->remarks ?? 'None' }}</td>
                    <td style="padding: 12px; text-align: center;">
                        @if($req->status === 'approved')
                            <span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px;">Approved</span>
                        @elseif($req->status === 'rejected')
                            <span style="background: #f8d7da; color: #721c24; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px;">Rejected</span>
                        @elseif($req->status === 'fulfilled')
                            <span style="background: #cce5ff; color: #004085; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px;">Fulfilled</span>
                        @else
                            <span style="background: #fff3cd; color: #856404; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px;">Pending Review</span>
                        @endif
                    </td>
                    <td>
                        @php
                            preg_match('/\[Scheduled Claim: (.*?)\]/', $req->bhw_remarks, $matches);
                            $claimDate = $matches[1] ?? null;
                        @endphp

                        @if($claimDate)
                            <strong style="color: #0369a1;">📅 {{ \Carbon\Carbon::parse($claimDate)->format('M d, Y') }}</strong>
                        @else
                            <span style="color: #6b7280; font-style: italic;">Not yet scheduled</span>
                        @endif
                    </td>
                    <td style="padding: 12px; text-align: center; color: #6b7280; font-size: 13px;">
                        {{ $req->created_at->format('M d, Y h:i A') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #6c757d; padding: 25px;">You have not made any supply requests yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================
     REQUEST POPUP MODAL
     ================================================================ -->
<div id="requestModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 400px; max-width: 90%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3 id="modalItemName" style="margin-top: 0; color: #1f2937; margin-bottom: 10px;">Request Supply</h3>
        <p id="modalItemStock" style="font-size: 13px; color: #6b7280; margin-bottom: 20px;"></p>

        <form action="{{ route('citizen.supplies.request') }}" method="POST">
            @csrf
            <input type="hidden" name="supply_id" id="modalSupplyId">

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: #374151;">Quantity:</label>
                <input type="number" name="quantity" id="modalQtyInput" min="1" value="1" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: #374151;">Remarks / Reason (Optional):</label>
                <textarea name="remarks" placeholder="Add any details or instructions for the BHW..." rows="3" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-family: inherit;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" id="closeModalBtn" style="padding: 8px 14px; background: #e5e7eb; color: #374151; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">Cancel</button>
                <button type="submit" style="padding: 8px 14px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("requestModal");
        const closeModalBtn = document.getElementById("closeModalBtn");
        const modalSupplyId = document.getElementById("modalSupplyId");
        const modalItemName = document.getElementById("modalItemName");
        const modalItemStock = document.getElementById("modalItemStock");
        const modalQtyInput = document.getElementById("modalQtyInput");

        document.querySelectorAll(".openRequestModal").forEach(button => {
            button.addEventListener("click", function () {
                const id = this.getAttribute("data-id");
                const name = this.getAttribute("data-name");
                const max = this.getAttribute("data-max");

                modalSupplyId.value = id;
                modalItemName.innerText = "Request: " + name;
                modalItemStock.innerText = "Available Stock: " + max;
                modalQtyInput.max = max;
                modalQtyInput.value = 1;

                modal.style.display = "flex";
            });
        });

        function closeModal() {
            modal.style.display = "none";
        }

        closeModalBtn.addEventListener("click", closeModal);
        window.addEventListener("click", function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    });
</script>
@endsection