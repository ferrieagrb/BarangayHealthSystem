@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/referral.css') }}">
@endsection

@section('content')

<div class="main">

    <div class="page-top">

        <div class="page-title-group">
            <h1>Referral Dashboard</h1>
            <p>Manage and monitor barangay health referrals.</p>
        </div>

        <a href="{{ route('referrals.create') }}" class="btn-primary">
            Create Referral
        </a>

    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="toolbar">

        <div class="toolbar-left">

            <form method="GET" class="search-box">
                <input
                    type="text"
                    name="search"
                    placeholder="Search patient..."
                    value="{{ request('search') }}"
                >
            </form>

        </div>

    </div>

    <div class="table-container">

        <table>

            <thead>
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Request For</th>
                    <th>Status</th>
                    <th>Update Status</th>
                    <th>File</th>
                </tr>
            </thead>

            <tbody>

                @forelse($referrals as $referral)

                    <tr>

                        <td>
                            {{ $referral->date_of_referral }}
                        </td>

                        <td>
                            <span class="citizen-name">
                                {{ $referral->name }}
                            </span>

                            <span class="citizen-sub">
                                {{ $referral->gender }},
                                {{ $referral->age }} years old
                            </span>
                        </td>

                        <td>
                            {{ $referral->requests_for }}
                        </td>

                        <td>

                            @if($referral->status == 'approved')
                                <span class="status-badge status-approved">
                                    Approved
                                </span>

                            @elseif($referral->status == 'released')
                                <span class="status-badge status-released">
                                    Released
                                </span>

                            @elseif($referral->status == 'returned')
                                <span class="status-badge status-returned">
                                    Returned
                                </span>
                            @endif

                        </td>

                        <td>

                            <form
                                action="{{ route('referrals.status', $referral->id) }}"
                                method="POST"
                                class="action-group"
                            >

                                @csrf
                                @method('PATCH')

                                <select
                                    name="status"
                                    class="status-select"
                                >

                                    <option value="approved"
                                        {{ $referral->status == 'approved' ? 'selected' : '' }}>
                                        Approved
                                    </option>

                                    <option value="released"
                                        {{ $referral->status == 'released' ? 'selected' : '' }}>
                                        Released
                                    </option>

                                    <option value="returned"
                                        {{ $referral->status == 'returned' ? 'selected' : '' }}>
                                        Returned
                                    </option>

                                </select>

                                <button type="submit" class="btn-primary">
                                    Update
                                </button>

                            </form>

                        </td>

                        <td>

                            @if($referral->file_path)

                                <a
                                    href="{{ route('referrals.download', $referral->id) }}"
                                    class="btn-primary"
                                >
                                    Download PDF
                                </a>

                            @else

                                No file

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6">
                            No referrals found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection