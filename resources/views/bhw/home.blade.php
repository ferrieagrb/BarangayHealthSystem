@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/home.css') }}">
@endsection

@section('content')

<div class="main">

    <div class="page-top">
        <div class="page-title-group">
            <h1>BHW Dashboard</h1>
            <p>Overview of citizen records, health trends, events, inventory, and referrals.</p>
        </div>
    </div>

    <div class="summary-cards">

        <div class="summary-card">
            <span>Total Citizens</span>
            <strong>{{ $totalCitizens ?? 0 }}</strong>
        </div>

        <div class="summary-card">
            <span>Most Popular Disease</span>
            <strong>{{ $popularDisease ?? 'N/A' }}</strong>
        </div>

        <div class="summary-card">
            <span>Upcoming Events</span>
            <strong>{{ $upcomingEventsCount ?? 0 }}</strong>
        </div>

        <div class="summary-card">
            <span>Total Referrals</span>
            <strong>{{ $recentReferralsCount ?? 0 }}</strong>
        </div>

    </div>

    <div class="dashboard-grid">

        <div class="dashboard-card">
            <div class="card-header">
                <h2>Upcoming Events</h2>
                <p>Scheduled barangay health activities</p>
            </div>

            @forelse($upcomingEvents ?? [] as $event)
                <div class="list-item">
                    <div>
                        <strong>{{ $event->title ?? 'Untitled Event' }}</strong>
                        <span>{{ $event->start_date ?? $event->date ?? 'No date' }}</span>
                    </div>
                </div>
            @empty
                <p class="empty-text">No upcoming events.</p>
            @endforelse
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h2>Inventory</h2>
                <p>Medicine and supply stock monitoring</p>
            </div>

            @forelse($lowStockSupplies ?? [] as $supply)
                <div class="list-item">
                    <div>
                        <strong>{{ $supply->name ?? $supply->item_name ?? 'Unnamed Item' }}</strong>
                        <span>Stock: {{ $supply->quantity ?? 0 }}</span>
                    </div>

                    <span class="status-badge status-monitoring">Low Stock</span>
                </div>
            @empty
                <p class="empty-text">No low stock items.</p>
            @endforelse
        </div>

        <div class="dashboard-card full-card">
            <div class="card-header">
                <h2>Recent Referrals</h2>
                <p>Latest patient referral records</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Request For</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recentReferrals ?? [] as $referral)
                        <tr>
                            <td>{{ $referral->date_of_referral }}</td>
                            <td>{{ $referral->name }}</td>
                            <td>{{ $referral->requests_for }}</td>
                            <td>
                                <span class="status-badge status-active">
                                    {{ ucfirst($referral->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No recent referrals.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

@endsection