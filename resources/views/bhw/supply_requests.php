@extends('templates.layout') {{-- Or your BHW template layout extension --}}

@section('content')
<div class="page-top" style="margin-bottom: 20px;">
    <h1>Citizen Supply Requests</h1>
    <p>Manage citizen supply requests, adjust priority levels, and handle approvals.</p>
</div>

@if(session('success'))
    <div style="background: #d4edda; color: #155724; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background: #f8d7da; color: #721c24; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px;">
        {{ session('error') }}
    </div>
@endif

<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #e5e7eb; color: #374151;">
                <th style="padding: 12px;">Citizen Name</th>
                <th style="padding: 12px;">Requested Supply</th>
                <th style="padding: 12px; text-align: center;">Quantity</th>
                <th style="padding: 12px; text-align: center;">Priority</th>
                <th style="padding: 12px; text-align: center;">Status</th>
                <th style="padding: 12px;">Remarks / Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <form action="{{ route('bhw.supply.requests.update', $req->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <td style="padding: 12px;">
                            <strong>{{ $req->citizen->Citizen_FName ?? 'N/A' }} {{ $req->citizen->Citizen_LName ?? '' }}</strong>
                        </td>

                        <td style="padding: 12px;">
                            {{ $req->supply->name ?? 'Unknown Item' }}
                            <br><small style="color: #6b7280;">Category: {{ $req->supply->category ?? 'N/A' }}</small>
                        </td>

                        <td style="padding: 12px; text-align: center;">
                            {{ $req->quantity }}
                        </td>

                        {{-- BHW Priority Management --}}
                        <td style="padding: 12px; text-align: center;">
                            <select name="priority" style="padding: 6px; border-radius: 4px; border: 1px solid #d1d5db; font-size: 13px;">
                                <option value="normal" {{ $req->priority === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ $req->priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ $req->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </td>

                        {{-- BHW Status Management --}}
                        <td style="padding: 12px; text-align: center;">
                            <select name="status" style="padding: 6px; border-radius: 4px; border: 1px solid #d1d5db; font-size: 13px;">
                                <option value="pending" {{ $req->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $req->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $req->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="fulfilled" {{ $req->status === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                            </select>
                        </td>

                        <td style="padding: 12px;">
                            <input type="text" name="bhw_remarks" value="{{ $req->bhw_remarks }}" placeholder="Optional remarks..." style="width: 100%; padding: 6px; margin-bottom: 6px; border-radius: 4px; border: 1px solid #d1d5db; font-size: 12px;">
                            <button type="submit" style="padding: 6px 12px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">Update</button>
                        </td>
                    </form>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #6b7280;">
                        No supply requests found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $requests->links() }}
    </div>
</div>
@endsection