@extends('templates.layout')

@section('CSSown')

@endsection

@section('content')

<h2 style="text-align:center;">Barangay Health Referral Form</h2>

<p><strong>Date of Referral:</strong> {{ $referral->date_of_referral }}</p>

<hr>

<p><strong>Name:</strong> {{ $referral->name }}</p>
<p><strong>Age:</strong> {{ $referral->age }}</p>
<p><strong>Gender:</strong> {{ $referral->gender }}</p>
<p><strong>Address:</strong> {{ $referral->address }}</p>

<hr>

<p><strong>Request/s For:</strong></p>
<p>{{ $referral->requests_for }}</p>

<p><strong>Vital Signs:</strong></p>
<p>{{ $referral->vital_signs ?? 'N/A' }}</p>

<p><strong>Treatment / Intervention Given:</strong></p>
<p>{{ $referral->treatment_given ?? 'N/A' }}</p>

<p><strong>Name of Medication Given:</strong></p>
<p>{{ $referral->medication_given ?? 'N/A' }}</p>

<p><strong>If Self-Medication:</strong></p>
<p>{{ $referral->self_medication ?? 'N/A' }}</p>

<p><strong>Maintenance Schedule/s:</strong></p>
<p>{{ $referral->maintenance_schedule ?? 'N/A' }}</p>

<hr>

<p><strong>Status:</strong> {{ ucfirst($referral->status) }}</p>
<p><strong>Referred By:</strong> {{ $referral->referred_by }}</p>

@endsection