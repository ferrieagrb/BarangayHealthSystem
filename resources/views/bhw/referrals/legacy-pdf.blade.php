<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barangay Health Referral Form</title>
    <style>
        @page { margin: 14mm; }
        body { color: #20382e; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.4; }
        h1 { color: #07573e; font-size: 20px; }
        h2 { margin: 14px 0 5px; color: #07573e; font-size: 12px; }
        p { margin: 4px 0; white-space: pre-line; overflow-wrap: break-word; }
        .clinical-field { page-break-inside: avoid; }
    </style>
</head>
<body>
    @include('partials.document-letterhead')
    <h1>Barangay Health Referral Form</h1>
    <p><strong>Date of Referral:</strong> {{ $referral->date_of_referral }}</p>
    <p><strong>Name:</strong> {{ $referral->name }}</p>
    <p><strong>Age:</strong> {{ $referral->age }}</p>
    <p><strong>Sex:</strong> {{ $referral->gender }}</p>
    <p><strong>Address:</strong> {{ $referral->address }}</p>
    @foreach([
        'requests_for' => 'Reason for Referral',
        'vital_signs' => 'Vital Signs',
        'treatment_given' => 'Treatment / Intervention Given',
        'medication_given' => 'Name of Medication Given',
        'self_medication' => 'Self-Medication',
        'maintenance_schedule' => 'Maintenance Schedule/s',
    ] as $field => $label)
        <div class="clinical-field"><h2>{{ $label }}</h2><p>{{ $referral->{$field} }}</p></div>
    @endforeach
    <p><strong>Referred By:</strong> {{ $referral->referred_by }}</p>
</body>
</html>
