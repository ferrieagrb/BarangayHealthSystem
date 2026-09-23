@extends('templates.citizen')

@section('content')
<div style="max-width: 800px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <div style="margin-bottom: 20px;">
        <h2>💊 Available Health Center Supplies & Medicines</h2>
        <p style="color: #666; font-size: 14px;">Browse current stock availability at the health center.</p>
    </div>

    <!-- Search Bar -->
    <form method="GET" action="{{ route('citizen.supplies') }}" style="margin-bottom: 20px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search medicine or supply name..." style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px;">
    </form>

    <!-- Restricted Table (Name and Quantity Only) -->
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <thead>
            <tr style="background: #f1f1f1; text-align: left;">
                <th style="padding: 10px; border-bottom: 2px solid #ddd;">Medicine / Supply Name</th>
                <th style="padding: 10px; border-bottom: 2px solid #ddd;">Category</th>
                <th style="padding: 10px; border-bottom: 2px solid #ddd; text-align: right;">Available Quantity</th>
            </tr>
        </thead>
        <tbody>
            @forelse($availableSupplies as $item)
            <tr style="border-bottom: 1px solid #f9f9f9;">
                <td style="padding: 12px; font-weight: 500;">{{ $item->name }}</td>
                <td style="padding: 12px; color: #555;">{{ $item->category }}</td>
                <td style="padding: 12px; text-align: right;">
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
@endsection
