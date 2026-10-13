<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplyRequest;
use App\Models\Supply;          
use App\Models\citizens;
use App\Models\Event;

class BhwSupplyRequestController extends Controller
{
    /**
     * Display a listing of citizen supply requests for the BHW.
     */
    public function index()
    {
        // Fetch requests ordered by priority (urgent -> high -> normal) and latest date
        $requests = SupplyRequest::with(['citizen', 'supply'])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal')")
            ->latest()
            ->paginate(10);

        return view('bhw.supply_requests', compact('requests'));
    }

    /**
     * Update request priority, status, and remarks (approve/reject/fulfill).
     */
    public function updateStatus(Request $request, $id)
{
    $supplyRequest = SupplyRequest::findOrFail($id);

    $request->validate([
        'status' => 'required|in:pending,approved,rejected,fulfilled',
        'priority' => 'required|in:normal,high,urgent',
        'bhw_remarks' => 'nullable|string|max:500',
    ]);

    $supply = $supplyRequest->supply;
    $oldStatus = $supplyRequest->status;
    $newStatus = $request->status;

    // 1. If newly approved, verify and deduct stock
    if ($newStatus === 'approved' && $oldStatus !== 'approved') {
        if ($supply->quantity < $supplyRequest->quantity) {
            return back()->with('error', 'Insufficient stock available to approve this request.');
        }

        $supply->decrement('quantity', $supplyRequest->quantity);
    }
    // 2. If it was previously approved and is now changed back to pending or rejected, restore the stock
    elseif ($oldStatus === 'approved' && in_array($newStatus, ['pending', 'rejected'])) {
        $supply->increment('quantity', $supplyRequest->quantity);
    }

    // Update the request details
    $supplyRequest->update([
        'status' => $newStatus,
        'priority' => $request->priority,
        'bhw_remarks' => $request->bhw_remarks,
    ]);

    return back()->with('success', 'Supply request updated successfully.');
}

    public function storeReservation(Request $request)
{
    $request->validate([
        'citizen_id' => 'required|exists:citizens,id',
        'supply_id' => 'required|exists:supplies,id',
        'quantity' => 'required|integer|min:1',
        'priority' => 'required|in:normal,high,urgent',
        'bhw_remarks' => 'nullable|string|max:500',
    ]);

    $supply = Supply::findOrFail($request->supply_id);

    // Check if enough stock is available
    if ($supply->quantity < $request->quantity) {
        return back()->with('error', 'Insufficient stock available to reserve this quantity.');
    }

    // Immediately deduct stock upon BHW direct assignment/reservation
    $supply->decrement('quantity', $request->quantity);

    // Create the record as automatically approved/reserved
    SupplyRequest::create([
        'citizen_id' => $request->citizen_id,
        'supply_id' => $request->supply_id,
        'quantity' => $request->quantity,
        'priority' => $request->priority,
        'status' => 'approved', // Automatically approved because it's a BHW direct reservation
        'bhw_remarks' => $request->bhw_remarks ?? 'Directly assigned and reserved by BHW.',
    ]);

    return back()->with('success', 'Supply successfully reserved and assigned to the citizen. Stock has been deducted.');
}

    public function storeBatchRelease(Request $request)
{
    $request->validate([
        'supply_request_ids' => 'required|array',
        'supply_request_ids.*' => 'exists:supply_requests,id',
        'release_date' => 'required|date',
        'batch_title' => 'required|string|max:255',
        'notes' => 'nullable|string|max:500',
    ]);

    $releasedCount = 0;
    $summaryDetails = [];

    foreach ($request->supply_request_ids as $reqId) {
        $supplyReq = SupplyRequest::with(['citizen', 'supply'])->findOrFail($reqId);

        if ($supplyReq->status !== 'approved') {
            continue; 
        }

        // Store the schedule flag directly inside the remarks string
        $scheduleNote = "[Scheduled Claim: {$request->release_date}]";
        $existingRemarks = preg_replace('/\[Scheduled Claim:.*?\]/', '', $supplyReq->bhw_remarks ?? '');

        // Mark request as fulfilled and embed the schedule date tag
        $supplyReq->update([
            'status' => 'fulfilled',
            'bhw_remarks' => trim($scheduleNote . ' ' . $existingRemarks)
        ]);

        $citizenName = ($supplyReq->citizen->Citizen_FName ?? '') . ' ' . ($supplyReq->citizen->Citizen_LName ?? '');
        $summaryDetails[] = "{$supplyReq->quantity}x {$supplyReq->supply->name} for {$citizenName}";
        $releasedCount++;
    }

    if ($releasedCount > 0) {
        Event::create([
            'title' => $request->batch_title,
            'description' => "Disbursed items: " . implode(', ', $summaryDetails) . ($request->notes ? " | Notes: {$request->notes}" : ''),
            'start' => $request->release_date,
            'end' => $request->release_date,
            'type' => 'supply_release',
        ]);

        return back()->with('success', "Successfully scheduled batch release for {$releasedCount} recipient(s)!");
    }

    return back()->with('error', 'No valid approved requests selected for release.');
}
}