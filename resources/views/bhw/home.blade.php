@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/home.css') }}">
@endsection

@section('content')

<div class="main">

    <div class="page-top">

        <div class="page-title-group">
            <h1>BHW Dashboard</h1>
            <p>
                Overview of citizen records, health trends, events,
                inventory, and referrals.
            </p>
        </div>

    </div>

    {{-- =========================
        SUMMARY CARDS
    ========================= --}}

    <div class="summary-cards">

        <div class="summary-card">
            <span>Total Citizens</span>
            <strong>{{ $totalCitizens ?? 0 }}</strong>
        </div>

        <div class="summary-card">
            <span>Most Common Disease</span>
            <strong>{{ $popularDisease ?? 'N/A' }}</strong>
        </div>

        <div class="summary-card">
            <span>Upcoming Events</span>
            <strong>{{ $upcomingEventsCount ?? 0 }}</strong>
        </div>

        <div class="summary-card">
            <span>Inventory Items</span>
            <strong>{{ $totalInventory ?? 0 }}</strong>
        </div>

    </div>

    {{-- =========================
        DASHBOARD GRID
    ========================= --}}

    <div class="dashboard-grid">

        {{-- =========================
            UPCOMING EVENTS
        ========================= --}}

        <div class="dashboard-card">

            <div class="card-header">
                <h2>Upcoming Events</h2>
                <p>Scheduled barangay health activities</p>
            </div>

            @forelse($upcomingEvents as $event)

                <div class="list-item">

                    <div>
                        <strong>{{ $event->title }}</strong>

                        <span>
                            {{ \Carbon\Carbon::parse($event->start)->format('F d, Y') }}
                        </span>
                    </div>

                </div>

            @empty

                <p class="empty-text">
                    No upcoming events.
                </p>

            @endforelse

        </div>

        {{-- =========================
            INVENTORY
        ========================= --}}

        <div class="dashboard-card">

            <div class="card-header">
                <h2>Low Stock Inventory</h2>
                <p>Medicine and supply monitoring</p>
            </div>

            @forelse($lowStockSupplies as $supply)

                <div class="list-item">

                    <div>
                        <strong>{{ $supply->name }}</strong>

                        <span>
                            Remaining Stock:
                            {{ $supply->quantity }}
                        </span>
                    </div>

                    <span class="status-badge status-monitoring">
                        Low Stock
                    </span>

                </div>

            @empty

                <p class="empty-text">
                    No low stock items.
                </p>

            @endforelse

        </div>

        {{-- =========================
            RECENT REFERRALS
        ========================= --}}

        <div class="dashboard-card full-card">

            <div class="card-header">
                <h2>Recent Referrals</h2>
                <p>
                    Latest patient referral records
                </p>
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

                    @forelse($recentReferrals as $referral)

                        <tr>

                            <td>
                                {{ \Carbon\Carbon::parse($referral->date_of_referral)->format('M d, Y') }}
                            </td>

                            <td>
                                {{ $referral->name }}
                            </td>

                            <td>
                                {{ $referral->requests_for }}
                            </td>

                            <td>

                                @if($referral->status == 'approved')

                                    <span class="status-badge status-active">
                                        Approved
                                    </span>

                                @elseif($referral->status == 'released')

                                    <span class="status-badge status-monitoring">
                                        Released
                                    </span>

                                @elseif($referral->status == 'returned')

                                    <span class="status-badge status-returned">
                                        Returned
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4">
                                No recent referrals.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection