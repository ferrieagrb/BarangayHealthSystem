@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/referral_create.css') }}">
@endsection

@section('content')

<div class="main">

    <div class="page-top">
        <div class="page-title-group">
            <h1>Create Referral</h1>
            <p>Create and generate barangay health referral forms.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="referral-form-card">

        <form action="{{ route('referrals.store') }}" method="POST">
            @csrf

            <div class="form-grid">

                <div class="form-group">
                    <label>Date of Referral</label>
                    <input type="date" name="date_of_referral" required>
                </div>

                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" required>
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="form-group full">
                    <label>Address</label>
                    <textarea name="address" required></textarea>
                </div>

                <div class="form-group full">
                    <label>Request/s For</label>
                    <textarea name="requests_for" required></textarea>
                </div>

                <div class="form-group">
                    <label>Vital Signs</label>
                    <textarea name="vital_signs"></textarea>
                </div>

                <div class="form-group">
                    <label>Treatment / Intervention Given</label>
                    <textarea name="treatment_given"></textarea>
                </div>

                <div class="form-group">
                    <label>Name of Medication Given</label>
                    <textarea name="medication_given"></textarea>
                </div>

                <div class="form-group">
                    <label>If Self-Medication</label>
                    <textarea name="self_medication"></textarea>
                </div>

                <div class="form-group full">
                    <label>Maintenance Schedule/s</label>
                    <textarea name="maintenance_schedule"></textarea>
                </div>

                <div class="form-group">
                    <label>Referred By</label>
                    <input type="text" name="referred_by" required>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Generate Referral
                </button>
            </div>

        </form>

    </div>

</div>

@endsection