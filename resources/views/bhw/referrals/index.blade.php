@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/referral.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
@endsection

@section('content')

<div class="main bhw-referrals-page">

    <div class="page-top">

        <div class="page-title-group">
            <h1>Referral Dashboard</h1>
            <p>Manage and monitor barangay health referrals.</p>
        </div>

        <a href="{{ route('referrals.create') }}" class="btn-primary" data-ui-action="primary">
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

        <table class="bhw-referrals-table" role="table" aria-label="Referral records">

            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col">Date</th>
                    <th scope="col">Patient</th>
                    <th scope="col">Request For</th>
                    <th scope="col">Status</th>
                    <th scope="col">Update Status</th>
                    <th scope="col">File</th>
                </tr>
            </thead>

            <tbody role="rowgroup">

                @forelse($referrals as $referral)

                    <tr role="row">

                        <td role="cell" data-label="Date">
                            {{ $referral->date_of_referral }}
                        </td>

                        <td role="cell" data-label="Patient">
                            <span class="citizen-name">
                                {{ $referral->name }}
                            </span>

                            <span class="citizen-sub">
                                {{ $referral->gender }},
                                {{ $referral->age }} years old
                            </span>
                        </td>

                        <td role="cell" data-label="Request For">
                            {{ $referral->requests_for }}
                        </td>

                        <td role="cell" data-label="Status">

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

                        <td role="cell" data-label="Update Status">

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
                                    aria-label="Update referral status for {{ $referral->name }}"
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

                                <button type="submit" class="btn-primary" data-ui-action="primary">
                                    Update
                                </button>

                            </form>

                        </td>

                        <td role="cell" data-label="File">

                            @if($referral->file_path)

                                <a
                                    href="{{ route('referrals.download', $referral->id) }}"
                                    class="btn-primary"
                                 data-ui-action="secondary">
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
        {{ $referrals->links('partials.pagination') }}

    </div>

</div>

@endsection
