@extends('templates.layout')

@section('CSSown')

@endsection

@section('content')

<h2>Create Referral</h2>

<form action="{{ route('referrals.store') }}" method="POST">
    @csrf

    <label>Date of Referral</label>
    <input type="date" name="date_of_referral" required>

    <label>Name</label>
    <input type="text" name="name" required>

    <label>Age</label>
    <input type="number" name="age" required>

    <label>Gender</label>
    <select name="gender" required>
        <option value="">Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
    </select>

    <label>Address</label>
    <textarea name="address" required></textarea>

    <label>Request/s For</label>
    <textarea name="requests_for" required></textarea>

    <label>Vital Signs</label>
    <textarea name="vital_signs"></textarea>

    <label>Treatment / Intervention Given</label>
    <textarea name="treatment_given"></textarea>

    <label>Name of Medication Given</label>
    <textarea name="medication_given"></textarea>

    <label>If Self-Medication</label>
    <textarea name="self_medication"></textarea>

    <label>Maintenance Schedule/s</label>
    <textarea name="maintenance_schedule"></textarea>

    <label>Referred By</label>
    <input type="text" name="referred_by" required>

    <button type="submit">Generate Referral</button>
</form>

@endsection