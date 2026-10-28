@extends('templates.citizen')

@section('content')
<div class="citizen-supplies-page" style="max-width: 900px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <div style="margin-bottom: 20px;">
        <h2>💊 Available Health Center Supplies & Medicines</h2>
        <p style="color: #666; font-size: 14px;">Browse current health center stock availability.</p>
    </div>

    <!-- Search Bar -->
    <form method="GET" action="{{ route('citizen.supplies') }}" style="margin-bottom: 20px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search medicine or supply name..." style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px;">
    </form>

    <!-- Available Supplies Table -->
    <div class="citizen-table-scroll" role="region" aria-label="Available supplies" tabindex="0">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 40px;">
        <thead>
            <tr style="background: #f1f1f1; text-align: left;">
                <th style="padding: 10px; border-bottom: 2px solid #ddd;">Medicine / Supply Name</th>
                <th style="padding: 10px; border-bottom: 2px solid #ddd;">Category</th>
                <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: center;">Available Quantity</th>
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
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #6c757d; padding: 30px;">No supplies currently available.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="border-top: 2px solid #e5e7eb; padding-top: 25px;">
        <h3 style="margin-bottom: 8px; color: #1f2937;">Scheduled Supply Releases</h3>
        <p style="color: #666; font-size: 14px;">Supplies assigned to you by the BHW and their claiming dates.</p>

        <div class="citizen-table-scroll" role="region" aria-label="Scheduled supply releases" tabindex="0">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 15px;">
            <thead>
                <tr style="background: #f3f4f6; text-align: left;">
                    <th style="padding: 10px; border-bottom: 2px solid #ddd;">Batch</th>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd;">Supply</th>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: center;">Quantity</th>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: center;">Claim Date</th>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: center;">Status</th>
                    <th style="padding: 10px; border-bottom: 2px solid #ddd;">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scheduledReleases as $release)
                    <tr style="border-bottom: 1px solid #f9f9f9;">
                        <td style="padding: 12px; font-weight: 500;">{{ $release->batch_title }}</td>
                        <td style="padding: 12px;">{{ $release->supply->name ?? 'Supply unavailable' }}</td>
                        <td style="padding: 12px; text-align: center;">{{ $release->quantity }}</td>
                        <td style="padding: 12px; text-align: center; color: #0369a1; font-weight: 600;">
                            {{ $release->claim_date->format('M d, Y') }}
                        </td>
                        <td class="supply-release-status {{ $release->status === 'claimed' ? 'status-claimed' : 'status-pending' }}" style="padding: 12px; text-align: center; font-weight: 600; color: {{ $release->status === 'claimed' ? '#166534' : '#92400e' }};">
                            {{ ucfirst($release->status) }}
                        </td>
                        <td style="padding: 12px; color: #555;">{{ $release->details ?? 'No additional details.' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6c757d; padding: 25px;">No scheduled supply releases yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection