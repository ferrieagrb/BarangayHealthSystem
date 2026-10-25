@extends('templates.layout')

@section('CSSown')
<style>
    .batch-release-page {
        padding: 28px;
    }

    .batch-release-summary,
    .batch-release-assignments {
        margin-top: 20px;
        padding: 22px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
    }

    .batch-release-assignments table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }

    .batch-release-assignments th,
    .batch-release-assignments td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
    }

    .batch-status {
        display: inline-block;
        padding: 4px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .batch-status-scheduled {
        background: #fef3c7;
        color: #92400e;
    }

    .batch-status-claimed {
        background: #dcfce7;
        color: #166534;
    }
</style>
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
@endsection

@section('content')
<div class="batch-release-page">
    <div class="page-top">
        <div class="page-title">
            <h1>{{ $batchReleases->first()->event->title ?? $batchReleases->first()->batch_title }}</h1>
            <p>Dedicated batch release schedule and citizen claim status.</p>
        </div>
        <a href="{{ route('supplies.index') }}" class="btn-secondary">Back to Supplies</a>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="batch-release-summary">
        <p><strong>Claim date:</strong> {{ $batchReleases->first()->claim_date->format('M d, Y') }}</p>
        <p><strong>Details:</strong> {{ $batchReleases->first()->details ?? 'No additional details.' }}</p>
    </section>

    <section class="batch-release-assignments">
        <h2>Citizen Assignments</h2>
        <table>
            <thead>
                <tr>
                    <th>Citizen</th>
                    <th>Supply Batch</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batchReleases as $release)
                    <tr>
                        <td>{{ trim(($release->citizen->Citizen_FName ?? '') . ' ' . ($release->citizen->Citizen_LName ?? '')) }}</td>
                        <td>{{ $release->supply->name ?? 'Supply unavailable' }}<br><small>{{ $release->supply->item_number ?? $release->supply->serial_number ?? 'Batch #' . $release->supply_id }}</small></td>
                        <td>{{ $release->quantity }}</td>
                        <td><span class="batch-status batch-status-{{ $release->status }}">{{ ucfirst($release->status) }}</span></td>
                        <td>
                            @if($release->status === 'scheduled')
                                <form method="POST" action="{{ route('supplies.batch.release.claim', $release->id) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary" onclick="return confirm('Confirm that this citizen claimed the assigned supply?');">Mark Claimed</button>
                                </form>
                            @else
                                <small>Claimed {{ $release->claimed_at?->format('M d, Y h:i A') }}</small>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
@endsection