<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Citizen Health Record | BHEAMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 18px; background: #f1f5f9; color: #172033; font-family: Inter, Arial, sans-serif; }
        .record-page { max-width: 1000px; margin: 0 auto; }
        .record-header, .record-card { background: #fff; border: 1px solid #d5dce6; border-radius: 12px; box-shadow: 0 5px 18px rgba(15, 23, 42, .06); }
        .record-header { margin-bottom: 16px; padding: 16px 20px; border-top: 5px solid #173b82; }
        .brand { display:flex; align-items:center; gap:12px; color:#173b82; font-weight:800; letter-spacing:.04em; }
        .brand-mark { width:42px;height:42px;display:grid;place-items:center;border-radius:50%;background:#e8efff;font-size:21px; }
        .brand small { display:block;margin-top:3px;color:#64748b;font-size:10px;letter-spacing:.08em; }
        .share-notice { margin:14px 0 0;padding:11px 13px;border-radius:8px;background:#eff6ff;color:#1e3a8a;font-size:13px;line-height:1.55; }
        .record-card { padding:clamp(16px,3vw,28px); }
        .record-card h1 { margin:0 0 6px;font-size:clamp(22px,4vw,30px);color:#142b52; }
        .record-card h2 { margin:26px 0 10px;padding:10px 12px;border:1px solid #cbd5e1;background:#f1f5f9;color:#334155;font-size:15px;text-transform:uppercase;letter-spacing:.03em; }
        .record-card p { line-height:1.6; }
        .record-facts { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 24px;padding:14px 0 20px;border-bottom:1px solid #cbd5e1; }
        .fact { min-width:0;font-size:14px;overflow-wrap:anywhere; }
        .fact span { display:block;margin-bottom:4px;color:#64748b;font-size:12px; }
        .fact strong { color:#1e293b; }
        .table-wrap { max-width:100%;overflow-x:auto;border:1px solid #cbd5e1;border-radius:7px;overscroll-behavior-x:contain; }
        table { width:100%;min-width:720px;border-collapse:collapse;font-size:13px;text-align:left; }
        th,td { padding:10px 9px;border-bottom:1px solid #d5dce6;vertical-align:top; }
        th { background:#e9edf3;color:#334155;white-space:nowrap; }
        tbody tr:last-child td { border-bottom:0; }
        .empty { padding:14px;text-align:center;color:#64748b;font-style:italic; }
        .access-card { max-width:560px;margin:12vh auto;padding:clamp(22px,5vw,36px);background:#fff;border:1px solid #e2e8f0;border-radius:16px;text-align:center;box-shadow:0 12px 35px rgba(15,23,42,.08); }
        .access-card h1 { margin:0 0 12px;font-size:25px; }
        .access-card p { color:#475569;line-height:1.6; }
        .access-card a { color:#155eef;font-weight:700; }
        @media(max-width:560px) {
            body { padding:10px; }
            .record-facts { grid-template-columns:1fr;gap:11px; }
            .record-header { padding:14px; }
            .brand-mark { width:36px;height:36px; }
        }
    </style>
@include('partials.favicon')
</head>
<body>
    @if(!$citizen)
        <main class="access-card">
            <div class="brand-mark" aria-hidden="true" style="margin:0 auto 16px;">▦</div>
            <h1>Citizen Health ID</h1>
            @if($sharingUnavailable ?? false)
                <p>Health record sharing is temporarily unavailable. Please try again later.</p>
            @elseif($tokenFound)
                <p>The citizen has not enabled sharing, or the sharing permission has expired or been revoked.</p>
                <p>Ask the citizen to enable health record sharing from their signed-in BHEAMS account before viewing this record.</p>
            @else
                <p>This Health ID link is invalid or no longer available.</p>
            @endif
            <p><a href="{{ route('landing') }}">Visit the BHEAMS public portal</a></p>
        </main>
    @else
        <main class="record-page">
            <header class="record-header">
                <div class="brand">
                    <span class="brand-mark" aria-hidden="true">+</span>
                    <span>BHEAMS<small>BARANGAY AMUYONG HEALTH RECORD</small></span>
                </div>
                <p class="share-notice">This record is being shared with the citizen's consent until {{ $share->expires_at->format('M d, Y g:i A') }}. Contact number is not included. Do not copy or retain information beyond your care needs.</p>
            </header>

            <article class="record-card">
                <h1>Amuyong Health Record</h1>
                <p style="margin:0;color:#64748b;">Medical information shared by the citizen through their Citizen Health ID.</p>

                <h2>Citizen Information</h2>
                <div class="record-facts">
                    <div class="fact"><span>Full name</span><strong>{{ trim($citizen->Citizen_LName . ', ' . $citizen->Citizen_FName . ' ' . ($citizen->Citizen_MName ?: '')) }}</strong></div>
                    <div class="fact"><span>Purok / Location</span><strong>{{ $citizen->Citizen_Purok ?: 'Not recorded' }}</strong></div>
                    <div class="fact"><span>Date of birth</span><strong>{{ $citizen->Citizen_BirthDate ? \Carbon\Carbon::parse($citizen->Citizen_BirthDate)->format('F d, Y') : 'Not recorded' }}</strong></div>
                    <div class="fact"><span>Age</span><strong>{{ $citizen->Citizen_BirthDate ? \Carbon\Carbon::parse($citizen->Citizen_BirthDate)->age . ' years old' : ($citizen->Citizen_Age !== null ? $citizen->Citizen_Age . ' years old' : 'Not recorded') }}</strong></div>
                    <div class="fact"><span>Height</span><strong>{{ $citizen->Citizen_Height !== null ? $citizen->Citizen_Height . ' cm' : 'Not recorded' }}</strong></div>
                    <div class="fact"><span>Weight</span><strong>{{ $citizen->Citizen_Weight !== null ? $citizen->Citizen_Weight . ' kg' : 'Not recorded' }}</strong></div>
                    <div class="fact"><span>Sex</span><strong>{{ $citizen->Citizen_Sex ?: 'Not recorded' }}</strong></div>
                    <div class="fact"><span>Housing status</span><strong>{{ $citizen->Citizen_HousingStatus ?: 'Not recorded' }}</strong></div>
                    <div class="fact"><span>Pregnancy status</span><strong>{{ $citizen->Citizen_Pregnant === null ? 'Not recorded' : ($citizen->Citizen_Pregnant ? 'Yes' : 'No') }}</strong></div>
                    <div class="fact"><span>Taking pills</span><strong>{{ $citizen->Citizen_InPills === null ? 'Not recorded' : ($citizen->Citizen_InPills ? 'Yes' : 'No') }}</strong></div>
                    <div class="fact"><span>Deceased status</span><strong>{{ $citizen->Citizen_IsDeceased === null ? 'Not recorded' : ($citizen->Citizen_IsDeceased ? 'Deceased' : 'Living') }}</strong></div>
                    <div class="fact"><span>Family name</span><strong>{{ $citizen->family?->family_name ?: 'Not assigned' }}</strong></div>
                    <div class="fact"><span>Subgroup</span><strong>{{ $citizen->subgroup?->name ?: ($citizen->family?->subgroup?->name ?: 'Not assigned') }}</strong></div>
                </div>

                <h2>Diagnosis &amp; Health Records</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Date recorded</th><th>Diagnosis</th><th>BMI</th><th>Weight / Height</th><th>Temperature</th><th>Blood pressure</th><th>Comments / Notes</th></tr></thead>
                        <tbody>
                            @forelse($citizen->healthRecords as $record)
                                <tr>
                                    <td>{{ $record->record_date ? \Carbon\Carbon::parse($record->record_date)->format('M d, Y') : 'Not recorded' }}</td>
                                    <td>{{ $record->diagnosis ?: 'Not recorded' }}</td>
                                    <td>{{ $record->bmi !== null ? number_format((float) $record->bmi, 1) . ' kg/m²' : 'Not recorded' }}</td>
                                    <td>{{ $record->weight !== null ? $record->weight . ' kg' : 'Not recorded' }} / {{ $record->height !== null ? $record->height . ' cm' : 'Not recorded' }}</td>
                                    <td>{{ $record->temperature !== null ? number_format((float) $record->temperature, 1) . ' °C' : 'Not recorded' }}</td>
                                    <td>{{ $record->blood_pressure ?: 'Not recorded' }}</td>
                                    <td>{{ $record->comments ?: 'Not recorded' }}</td>
                                </tr>
                            @empty
                                <tr><td class="empty" colspan="7">No diagnosis or health records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h2>Immunization History</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Vaccine</th><th>Product name / Dose number</th><th>Date administered</th><th>Healthcare professional / Clinic site</th></tr></thead>
                        <tbody>
                            @forelse($citizen->vaccinations as $vaccination)
                                <tr>
                                    <td>{{ $vaccination->vaccine_name }}</td>
                                    <td>{{ $vaccination->vaccine_name }} — {{ $vaccination->dose_number }}</td>
                                    <td>{{ $vaccination->date_administered ? \Carbon\Carbon::parse($vaccination->date_administered)->format('M d, Y') : 'Not recorded' }}</td>
                                    <td>{{ $vaccination->administered_by ?: 'Not recorded' }}</td>
                                </tr>
                            @empty
                                <tr><td class="empty" colspan="4">No vaccination records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h2>Maintenance Medicines</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Medicine</th><th>Quantity</th><th>Dosage</th><th>Frequency</th><th>Start date</th><th>End date / Status</th><th>Notes</th></tr></thead>
                        <tbody>
                            @forelse($citizen->maintenanceRecords as $medicine)
                                @php
                                    $startDate = $medicine->start_date ? \Carbon\Carbon::parse($medicine->start_date) : null;
                                    $endDate = $medicine->end_date ? \Carbon\Carbon::parse($medicine->end_date) : null;
                                    $medicineStatus = $startDate && $startDate->isFuture()
                                        ? 'Not started'
                                        : ($endDate && $endDate->lt(today()) ? 'Ended' : 'Ongoing');
                                @endphp
                                <tr>
                                    <td>{{ $medicine->medicine_name ?: 'Not recorded' }}</td>
                                    <td>{{ $medicine->quantity_dispensed ?? 'Not recorded' }}</td>
                                    <td>{{ $medicine->dosage ?: 'Not recorded' }}</td>
                                    <td>{{ $medicine->frequency ?: 'Not recorded' }}</td>
                                    <td>{{ $startDate ? $startDate->format('M d, Y') : 'Not recorded' }}</td>
                                    <td>{{ $endDate ? $endDate->format('M d, Y') . ' / ' . $medicineStatus : $medicineStatus }}</td>
                                    <td>{{ $medicine->notes ?: 'Not recorded' }}</td>
                                </tr>
                            @empty
                                <tr><td class="empty" colspan="7">No maintenance medicines recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </main>
    @endif
</body>
</html>
