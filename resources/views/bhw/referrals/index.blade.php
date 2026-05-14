@extends('templates.layout')

@section('CSSown')

@endsection

@section('content')

<h2>Referral Dashboard</h2>

@if(session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

<a href="{{ route('referrals.create') }}">Create Referral</a>

<table border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>Date</th>
            <th>Name</th>
            <th>Request For</th>
            <th>Status</th>
            <th>Update Status</th>
            <th>File</th>
        </tr>
    </thead>

    <tbody>
        @foreach($referrals as $referral)
            <tr>
                <td>{{ $referral->date_of_referral }}</td>
                <td>{{ $referral->name }}</td>
                <td>{{ $referral->requests_for }}</td>
                <td>{{ ucfirst($referral->status) }}</td>

                <td>
                    <form action="{{ route('referrals.status', $referral->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <select name="status">
                            <option value="approved" {{ $referral->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="released" {{ $referral->status == 'released' ? 'selected' : '' }}>Released</option>
                            <option value="returned" {{ $referral->status == 'returned' ? 'selected' : '' }}>Returned</option>
                        </select>

                        <button type="submit">Update</button>
                    </form>
                </td>

                <td>
                    @if($referral->file_path)
                        <a href="{{ route('referrals.download', $referral->id) }}">Download PDF</a>
                    @else
                        No file
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection