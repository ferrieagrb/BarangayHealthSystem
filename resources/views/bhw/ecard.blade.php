@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
@endsection

@section('content')
<div class="bhw-ecard-page mx-auto max-w-7xl px-4 py-8">
    <div class="ecard-page-header print:hidden">
        <h1>Citizen ID & Electronic Health Card</h1>
        <a href="{{ route('citizenlist') }}" class="ecard-button ecard-button-secondary">Back to Citizen List</a>
    </div>
    <div class="ecard-actions-top print:hidden">
        <button type="button" class="ecard-print-button" onclick="printCitizenSection('id')">Print Health Card Only</button>
        <button type="button" class="ecard-print-button ecard-print-record" onclick="printCitizenSection('record')">Print Health Information Only</button>
    </div>
    <div class="ecard-content-stack">
<section class="bheams-id-card">

    {{-- DECORATIVE BACKGROUND --}}
    <div class="bheams-id-decoration"></div>

    
    {{-- OFFICIAL-STYLE HEADER --}}
    <div class="bheams-id-header">

        <div class="bheams-seal">
            <img
                src="{{ asset('images/amuyong.png') }}"
                alt="BHEAMS Logo"
                class="bheams-logo"
            >
        </div>

        <div class="bheams-brand">
            <h2>BHEAMS</h2>
            <p>BARANGAY AMUYONG</p>
            <span>BARANGAY HEALTH AND EMERGENCY MANAGEMENT SYSTEM</span>
        </div>

    </div>


    {{-- ID BODY --}}
    <div class="bheams-id-body">

        {{-- CITIZEN PHOTO --}}
        <div class="bheams-photo">
            @if($citizen->photo_path)
                <img
                    src="{{ asset('storage/' . $citizen->photo_path) }}"
                    alt="Citizen Photo"
                >
            @else
                <div class="bheams-no-photo">
                    <span>
                        {{ strtoupper(substr($citizen->Citizen_FName, 0, 1) . substr($citizen->Citizen_LName, 0, 1)) }}
                    </span>
                    <small>NO PHOTO</small>
                </div>
            @endif
        </div>

        {{-- CITIZEN INFORMATION --}}
        <div class="bheams-id-details">

            <div class="bheams-id-title">CITIZEN HEALTH ID</div>

            <div class="bheams-id-field">
                <span>FULL NAME</span>
                <strong>
                    {{ strtoupper($citizen->Citizen_LName) }},
                    {{ strtoupper($citizen->Citizen_FName) }}
                </strong>
            </div>

            <div class="bheams-id-field">
                <span>CITIZEN IDENTIFICATION NUMBER</span>
                <strong class="bheams-citizen-number">
                    C-{{ str_pad($citizen->id, 4, '0', STR_PAD_LEFT) }}
                </strong>
            </div>

            <div class="bheams-id-two-columns">
                <div class="bheams-id-field">
                    <span>DATE OF BIRTH</span>
                    <strong>
                        {{ $citizen->Citizen_BirthDate
                            ? \Carbon\Carbon::parse($citizen->Citizen_BirthDate)->format('M d, Y')
                            : 'N/A' }}
                    </strong>
                </div>

            </div>

            <div class="bheams-id-field">
                <span>ADDRESS / PUROK</span>
                <strong>{{ $citizen->Citizen_Purok ?: 'N/A' }}</strong>
            </div>

        </div>

        {{-- ADDITIONAL ID DETAILS --}}
        <div class="bheams-id-secondary-details">
            <div class="bheams-id-field">
                <span>SEX</span>
                <strong>{{ $citizen->Citizen_Sex ?: 'N/A' }}</strong>
            </div>
            <div class="bheams-id-field">
                <span>AGE</span>
                <strong>{{ $citizen->Citizen_Age !== null && $citizen->Citizen_Age !== '' ? $citizen->Citizen_Age : 'N/A' }}</strong>
            </div>
            <div class="bheams-id-field">
                <span>CONTACT NUMBER</span>
                <strong>{{ $citizen->Citizen_ContactNo ?: 'N/A' }}</strong>
            </div>
        </div>

    {{-- CENTERED QR VERIFICATION --}}
    <div class="bheams-id-qr bheams-id-qr-centered">
        <div class="bheams-qr-frame">
            @if(!empty($qrImage))
                <img src="{{ $qrImage }}" alt="Citizen Verification QR Code">
            @else
                <span>QR code unavailable</span>
            @endif
        </div>
        <strong>SCAN TO VERIFY</strong>
        <small>Citizen Record Verification</small>
    </div>

    </div>

    {{-- ID FOOTER --}}
    <div class="bheams-id-footer">
        <span>BARANGAY AMUYONG</span>
        <span>OFFICIAL CITIZEN IDENTIFICATION</span>
    </div>

</section>
<section class="print-health-record min-w-0 rounded-xl border-2 border-gray-300 bg-white p-5 shadow-lg sm:p-6">

            <div class="mb-5 border-b-2 border-gray-800 pb-3">
                <h2 class="text-2xl font-black uppercase text-gray-900">
                    Amuyong Health Record
                </h2>
                <p class="mt-1 text-xs text-gray-600">
                    Medical information about the vaccines and medicines received.
                </p>
            </div>

            {{-- CITIZEN DETAILS --}}
            <div class="mb-6 grid grid-cols-1 gap-4 border-b border-gray-300 pb-4 text-sm sm:grid-cols-2">

                <div>
                    <span class="block text-xs text-gray-500">Last Name, First Name</span>
                    <span class="font-bold">
                        {{ $citizen->Citizen_LName }},
                        {{ $citizen->Citizen_FName }}
                        {{ $citizen->Citizen_MName ?: '' }}
                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Purok / Location</span>
                    <span class="font-semibold">
                        {{ $citizen->Citizen_Purok ?: 'N/A' }}
                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Date of Birth</span>
                    <span class="font-semibold">
                        @if($citizen->Citizen_BirthDate)
                            {{ \Carbon\Carbon::parse($citizen->Citizen_BirthDate)->format('F d, Y') }}
                        @else
                            N/A
                        @endif
                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Age</span>
                    <span class="font-semibold">
                        @if($citizen->Citizen_BirthDate)
                            {{ \Carbon\Carbon::parse($citizen->Citizen_BirthDate)->age }} years old
                        @elseif(!is_null($citizen->Citizen_Age))
                            {{ $citizen->Citizen_Age }} years old
                        @else
                            N/A
                        @endif
                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Height</span>
                    <span class="font-semibold">{{ $citizen->Citizen_Height !== null && $citizen->Citizen_Height !== '' ? $citizen->Citizen_Height . ' cm' : 'N/A' }}</span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Weight</span>
                    <span class="font-semibold">{{ $citizen->Citizen_Weight !== null && $citizen->Citizen_Weight !== '' ? $citizen->Citizen_Weight . ' kg' : 'N/A' }}</span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Sex</span>
                    <span class="font-semibold">{{ $citizen->Citizen_Sex ?: 'N/A' }}</span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Patient Contact Number</span>
                    <span class="font-semibold">
                        {{ $citizen->Citizen_ContactNo ?: 'N/A' }}
                    </span>
                </div>

            </div>


            <div class="mb-6 grid grid-cols-1 gap-4 border-b border-gray-300 pb-4 text-sm sm:grid-cols-2">
                <div><span class="block text-xs text-gray-500">Housing Status</span><span class="font-semibold">{{ $citizen->Citizen_HousingStatus ?: 'N/A' }}</span></div>
                <div><span class="block text-xs text-gray-500">Pregnancy Status</span><span class="font-semibold">{{ !empty($citizen->Citizen_Pregnant) ? 'Yes' : 'No' }}</span></div>
                <div><span class="block text-xs text-gray-500">Taking Pills</span><span class="font-semibold">{{ !empty($citizen->Citizen_InPills) ? 'Yes' : 'No' }}</span></div>
                <div><span class="block text-xs text-gray-500">Deceased Status</span><span class="font-semibold">{{ !empty($citizen->Citizen_IsDeceased) ? 'Deceased' : 'Living' }}</span></div>
                <div><span class="block text-xs text-gray-500">Family ID</span><span class="font-semibold">{{ $citizen->family?->id ?? 'None' }}</span></div>
                <div><span class="block text-xs text-gray-500">Family Name</span><span class="font-semibold">{{ $citizen->family?->family_name ?: 'No family assigned' }}</span></div>
                <div><span class="block text-xs text-gray-500">Subgroup</span><span class="font-semibold">{{ $citizen->subgroup?->name ?: ($citizen->family?->subgroup?->name ?: 'Not assigned') }}</span></div>
            </div>

            {{-- DIAGNOSIS / HEALTH RECORDS --}}
            <div class="mb-6">
                <h3 class="mb-2 border border-gray-300 bg-gray-100 p-2 text-sm font-bold uppercase text-gray-700">
                    Diagnosis & Health Records
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-400 text-xs text-left">
                        <thead>
                            <tr class="bg-gray-200 text-gray-800">
                                <th class="border border-gray-400 p-2">Date Recorded</th>
                                <th class="border border-gray-400 p-2">Diagnosis</th>
                                <th class="border border-gray-400 p-2">BMI</th>
                                <th class="border border-gray-400 p-2">Weight / Height</th>
                                <th class="border border-gray-400 p-2">Temperature</th>
                                <th class="border border-gray-400 p-2">BP</th>
                                <th class="border border-gray-400 p-2">Comments / Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($citizen->healthRecords->sortByDesc('record_date') as $record)
                                <tr>
                                    <td class="border border-gray-400 p-2">
                                        {{ $record->record_date ? \Carbon\Carbon::parse($record->record_date)->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="border border-gray-400 p-2 font-semibold">{{ $record->diagnosis ?: 'N/A' }}</td>
                                    <td class="border border-gray-400 p-2">{{ $record->bmi !== null ? number_format((float)$record->bmi, 1) . ' kg/m²' : '—' }}</td>
                                    <td class="border border-gray-400 p-2">{{ $record->weight !== null ? $record->weight . ' kg' : '—' }} / {{ $record->height !== null ? $record->height . ' cm' : '—' }}</td>
                                    <td class="border border-gray-400 p-2">{{ $record->temperature !== null ? number_format((float)$record->temperature, 1) . ' °C' : '—' }}</td>
                                    <td class="border border-gray-400 p-2">{{ $record->blood_pressure ?: '—' }}</td>
                                    <td class="border border-gray-400 p-2">{{ $record->comments ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="border border-gray-400 p-3 text-center italic text-gray-500">
                                        No diagnosis or health records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- IMMUNIZATION HISTORY --}}
            <div class="mb-6">
                <h3 class="mb-2 border border-gray-300 bg-gray-100 p-2 text-sm font-bold uppercase text-gray-700">
                    Immunization History
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-400 text-xs text-left">
                        <thead>
                            <tr class="bg-gray-200 text-gray-800">
                                <th class="border border-gray-400 p-2">Vaccine</th>
                                <th class="border border-gray-400 p-2">Product Name / Dose Number</th>
                                <th class="border border-gray-400 p-2">Date Administered</th>
                                <th class="border border-gray-400 p-2">Healthcare Professional / Clinic Site</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($citizen->vaccinations as $vax)
                                <tr>
                                    <td class="border border-gray-400 p-2 font-semibold">
                                        {{ $vax->vaccine_name }}
                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        {{ $vax->vaccine_name }}
                                        — {{ $vax->dose_number }}
                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        {{ $vax->date_administered }}
                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        {{ $vax->administered_by }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="border border-gray-400 p-3 text-center italic text-gray-500">
                                        No vaccination records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MAINTENANCE MEDICINES: match the same heading and table template as the other health-record sections --}}
            <div class="mb-6">
                <h3 class="mb-2 border border-gray-300 bg-gray-100 p-2 text-sm font-bold uppercase text-gray-700">
                    Maintenance Medicines
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-400 text-xs text-left">
                        <thead>
                            <tr class="bg-gray-200 text-gray-800">
                                <th class="border border-gray-400 p-2">Medicine</th>
                                <th class="border border-gray-400 p-2">Quantity</th>
                                <th class="border border-gray-400 p-2">Dosage</th>
                                <th class="border border-gray-400 p-2">Frequency</th>
                                <th class="border border-gray-400 p-2">Start Date</th>
                                <th class="border border-gray-400 p-2">End Date / Status</th>
                                <th class="border border-gray-400 p-2">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($citizen->maintenanceRecords ?? collect()) as $maintenance)
                                <tr>
                                    <td class="border border-gray-400 p-2 font-semibold">{{ $maintenance->medicine_name ?: 'N/A' }}</td>
                                    <td class="border border-gray-400 p-2">{{ $maintenance->quantity_dispensed ?? 'N/A' }}</td>
                                    <td class="border border-gray-400 p-2">{{ $maintenance->dosage ?: 'N/A' }}</td>
                                    <td class="border border-gray-400 p-2">{{ $maintenance->frequency ?: 'N/A' }}</td>
                                    <td class="border border-gray-400 p-2">
                                        {{ $maintenance->start_date ? \Carbon\Carbon::parse($maintenance->start_date)->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        {{ $maintenance->end_date ? \Carbon\Carbon::parse($maintenance->end_date)->format('M d, Y') : 'Ongoing' }}
                                    </td>
                                    <td class="border border-gray-400 p-2">{{ $maintenance->notes ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="border border-gray-400 p-3 text-center italic text-gray-500">
                                        No maintenance medicines recorded.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>



</section>
    </div>
</div>
<style>
.bhw-ecard-page{max-width:1280px;margin:0 auto;padding:32px 24px;color:#172554;font-family:Arial,Helvetica,sans-serif}
.ecard-page-header{display:flex;align-items:center;justify-content:space-between;gap:18px;max-width:1000px;margin:0 auto 20px}.ecard-page-header h1{margin:0;color:#0f172a;font-size:24px;font-weight:800}
.ecard-button{display:inline-flex;align-items:center;justify-content:center;padding:10px 15px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;border:0;cursor:pointer}.ecard-button-secondary{background:#64748b;color:#fff}
.ecard-actions-top{display:flex;justify-content:flex-end;align-items:center;flex-wrap:wrap;gap:12px;max-width:1000px;margin:0 auto 18px}.ecard-print-button{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:10px 16px;border:0;border-radius:7px;background:#1d4ed8;color:#fff;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 3px 8px rgba(15,23,42,.12)}.ecard-print-record{background:#166534}
.ecard-content-stack{display:grid;grid-template-columns:1fr;gap:24px;max-width:1000px;margin:0 auto}
.bheams-id-card{position:relative;isolation:isolate;overflow:hidden;width:100%;max-width:1000px;margin:0 auto;border:1px solid #d1d5db;border-radius:14px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.12);color:#1f2937}
.bheams-id-decoration{position:absolute;inset:0;z-index:-1;background:linear-gradient(135deg,rgba(29,78,216,.06),transparent 45%,rgba(22,101,52,.05))}
.bheams-id-header{display:flex;align-items:center;gap:18px;padding:20px 26px;background:linear-gradient(110deg,#102b78,#17469a);color:#fff;border-bottom:4px solid #bfdbfe}.bheams-seal{display:flex;align-items:center;justify-content:center;flex-shrink:0;width:72px;height:72px;overflow:hidden;border:2px solid #fff;border-radius:50%;background:#fff}.bheams-logo{width:100%;height:100%;object-fit:contain}.bheams-brand{min-width:0;flex:1}.bheams-brand h2{margin:0;color:#fff;font-size:29px;font-weight:900;letter-spacing:1px}.bheams-brand p{margin:4px 0;color:#fff;font-size:16px;font-weight:800}.bheams-brand span{display:block;color:#dbeafe;font-size:9px;font-weight:700;letter-spacing:.5px}
.bheams-id-body{display:grid;grid-template-columns:125px minmax(0,1.25fr) minmax(0,.8fr) 125px;align-items:center;gap:22px;padding:24px}.bheams-photo{display:flex;align-items:center;justify-content:center;width:125px;height:155px;overflow:hidden;border:2px solid #d1d5db;border-radius:5px;background:#f1f5f9;color:#64748b}.bheams-photo img{display:block;width:100%;height:100%;object-fit:cover}.bheams-no-photo{display:flex;flex-direction:column;align-items:center;gap:5px}.bheams-no-photo span{font-size:38px;font-weight:900}.bheams-no-photo small{font-size:10px}.bheams-id-details{min-width:0}.bheams-id-secondary-details{min-width:0;align-self:center}.bheams-id-secondary-details .bheams-id-field{margin-bottom:18px}.bheams-id-title{margin-bottom:10px;color:#173b82;font-size:12px;font-weight:900;letter-spacing:1px}.bheams-id-field{display:flex;flex-direction:column;gap:4px;margin-bottom:12px;overflow-wrap:anywhere}.bheams-id-field span{color:#64748b;font-size:9px;font-weight:800;letter-spacing:.6px}.bheams-id-field strong{color:#172554;font-size:13px;font-weight:800}.bheams-citizen-number{color:#17469a!important;font-size:15px!important;letter-spacing:1px}.bheams-id-two-columns{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.bheams-id-qr{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;text-align:center}.bheams-qr-frame{display:flex;align-items:center;justify-content:center;width:118px;height:118px;padding:7px;border:1px solid #cbd5e1;border-radius:7px;background:#fff}.bheams-qr-frame img{display:block;width:100px;height:100px;object-fit:contain}.bheams-id-qr strong{color:#173b82;font-size:10px;font-weight:900;letter-spacing:.5px}.bheams-id-qr small{color:#64748b;font-size:9px}.bheams-id-footer{display:flex;justify-content:space-between;gap:12px;padding:11px 26px;background:#f8fafc;border-top:1px solid #dbeafe;color:#475569;font-size:9px;font-weight:800;letter-spacing:.5px}
.print-health-record{width:100%;min-width:0;box-sizing:border-box;border:2px solid #d1d5db;border-radius:12px;background:#fff;padding:22px;color:#1f2937;box-shadow:0 5px 16px rgba(15,23,42,.08);font-family:Arial,Helvetica,sans-serif;line-height:1.45}
.print-health-record *{box-sizing:border-box}
.print-health-record h2{margin:0;color:#111827;font-size:24px;font-weight:900;line-height:1.25}
.print-health-record h3{margin:0 0 8px;border:1px solid #d1d5db;background:#f3f4f6;padding:8px;font-size:13px;font-weight:800;text-transform:uppercase;color:#374151}
/* Explicitly define the information grids so the layout remains correct even if Tailwind utilities are unavailable. */
.print-health-record > div.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));column-gap:24px;row-gap:14px;align-items:start;width:100%;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid #d1d5db}
.print-health-record > div.grid > div{display:flex;flex-direction:column;align-items:flex-start;gap:4px;min-width:0;line-height:1.4;overflow-wrap:anywhere}
.print-health-record > div.grid > div > span:first-child{display:block;width:100%;margin:0;color:#64748b;font-size:11px;font-weight:700;line-height:1.35}
.print-health-record > div.grid > div > span:nth-child(2){display:block;width:100%;margin:0;color:#1f2937;font-size:13px;font-weight:700;line-height:1.45;overflow-wrap:anywhere}
.print-health-record table{width:100%;border-collapse:collapse;table-layout:auto}
.print-health-record th,.print-health-record td{border:1px solid #9ca3af;padding:8px;text-align:left;vertical-align:top;overflow-wrap:anywhere;line-height:1.4}
.print-health-record th{background:#e5e7eb;color:#1f2937;font-weight:800}
.print-health-record .overflow-x-auto{width:100%;max-width:100%;overflow-x:auto}
.print-health-record .mb-6{margin-bottom:22px}
.print-health-record .mb-5{margin-bottom:18px}
.print-health-record .mt-1{margin-top:5px}
.print-health-record .pb-3{padding-bottom:12px}
.print-health-record .border-b-2{border-bottom:2px solid #1f2937}
.print-health-record .text-xs{font-size:11px}
.print-health-record .text-sm{font-size:13px}
.print-health-record .p-2{padding:8px}
@media(max-width:700px){.print-health-record > div.grid{grid-template-columns:repeat(2,minmax(0,1fr));column-gap:14px;row-gap:12px}.print-health-record{padding:14px}}

@media(max-width:700px){.bhw-ecard-page{padding:20px 12px}.ecard-page-header{align-items:flex-start;flex-direction:column}.ecard-actions-top{justify-content:stretch}.ecard-actions-top button{flex:1 1 220px}.bheams-id-header{flex-wrap:wrap;padding:18px}.bheams-id-body{grid-template-columns:1fr;padding:18px}.bheams-photo{margin:0 auto}.bheams-id-secondary-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.bheams-id-secondary-details .bheams-id-field{margin-bottom:8px}.bheams-id-qr-centered{grid-column:1/-1}.bheams-id-two-columns{grid-template-columns:1fr}.print-health-record{padding:14px}}
@media print{@page{size:A4 landscape;margin:8mm}html,body,.dashboard,.main-content{background:#fff!important;height:auto!important;min-height:0!important;overflow:visible!important}body *{visibility:hidden!important}body.print-id-only .bheams-id-card,body.print-id-only .bheams-id-card *{visibility:visible!important}body.print-record-only .print-health-record,body.print-record-only .print-health-record *{visibility:visible!important}body.print-id-only .print-health-record,body.print-record-only .bheams-id-card{display:none!important}.top-bar,.sidebar,.sidebar-backdrop,.ecard-page-header,.ecard-actions-top{display:none!important}.main-content{margin:0!important;padding:0!important;width:100%!important}.bhw-ecard-page{max-width:none!important;width:100%!important;margin:0!important;padding:0!important}.ecard-content-stack{max-width:none!important;width:100%!important;margin:0!important;gap:0!important}.bheams-id-card,.print-health-record{width:100%!important;max-width:100%!important;margin:0!important;box-shadow:none!important;break-inside:avoid}.bheams-id-card{-webkit-print-color-adjust:exact;print-color-adjust:exact}.bheams-id-body{grid-template-columns:125px minmax(0,1fr) 125px!important;padding:24px!important}.print-health-record{border:1px solid #9ca3af!important;padding:14px!important}.print-health-record table{font-size:9px}.print-health-record th,.print-health-record td{padding:5px!important}
body.print-id-only .bhw-ecard-page,body.print-id-only .ecard-content-stack{width:3.375in!important;max-width:3.375in!important;height:2.125in!important;max-height:2.125in!important;margin:0!important;padding:0!important;overflow:hidden!important}
body.print-id-only .bheams-id-card{display:flex!important;flex-direction:column!important;width:3.375in!important;height:2.125in!important;min-height:2.125in!important;max-width:3.375in!important;max-height:2.125in!important;margin:0!important;padding:0!important;border:1px solid #d1d5db!important;border-radius:.08in!important;overflow:hidden!important;box-sizing:border-box!important;break-inside:avoid!important;background:#fff!important}
body.print-id-only .bheams-id-header{display:flex!important;align-items:center!important;flex:0 0 .61in!important;min-height:.61in!important;gap:.10in!important;padding:.08in .12in!important;border-bottom:2px solid #bfdbfe!important;flex-wrap:nowrap!important;box-sizing:border-box!important}
body.print-id-only .bheams-seal{width:.39in!important;height:.39in!important;min-width:.39in!important;min-height:.39in!important;flex:0 0 .39in!important;border-width:1px!important}
body.print-id-only .bheams-brand{min-width:0!important;flex:1!important}
body.print-id-only .bheams-brand h2{font-size:14px!important;line-height:1.05!important;letter-spacing:.2px!important;margin:0!important}
body.print-id-only .bheams-brand p{margin:2px 0!important;font-size:7.5px!important;line-height:1.05!important}
body.print-id-only .bheams-brand span{font-size:4.4px!important;line-height:1.1!important;letter-spacing:0!important}
body.print-id-only .bheams-id-body{display:grid!important;grid-template-columns:.58in minmax(0,1.15fr) minmax(0,.68fr) .62in!important;align-items:center!important;gap:.07in!important;flex:1 1 auto!important;min-height:0!important;padding:.11in!important;box-sizing:border-box!important} body.print-id-only .bheams-id-secondary-details{min-width:0!important;align-self:center!important} body.print-id-only .bheams-id-secondary-details .bheams-id-field{margin-bottom:5px!important}
body.print-id-only .bheams-photo{width:.58in!important;height:.72in!important;min-width:.58in!important;max-width:.58in!important;border-width:1px!important;box-sizing:border-box!important}
body.print-id-only .bheams-no-photo span{font-size:23px!important;line-height:1!important}
body.print-id-only .bheams-no-photo small{font-size:5px!important}
body.print-id-only .bheams-id-details{min-width:0!important;align-self:center!important}
body.print-id-only .bheams-id-title{margin-bottom:5px!important;font-size:6.5px!important;line-height:1.1!important;letter-spacing:.3px!important}
body.print-id-only .bheams-id-field{gap:1px!important;margin-bottom:4px!important}
body.print-id-only .bheams-id-field span{font-size:4.8px!important;line-height:1.1!important;letter-spacing:0!important}
body.print-id-only .bheams-id-field strong{font-size:7px!important;line-height:1.1!important}
body.print-id-only .bheams-citizen-number{font-size:8px!important;line-height:1.1!important}
body.print-id-only .bheams-id-two-columns{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:5px!important}
body.print-id-only .bheams-qr-frame{width:.58in!important;height:.58in!important;min-width:.58in!important;min-height:.58in!important;padding:2px!important;border-radius:2px!important;box-sizing:border-box!important}
body.print-id-only .bheams-qr-frame img{display:block!important;width:100%!important;height:100%!important;object-fit:contain!important}
body.print-id-only .bheams-id-qr{gap:3px!important;min-width:0!important}
body.print-id-only .bheams-id-qr strong{font-size:4.8px!important;line-height:1.1!important;letter-spacing:0!important}
body.print-id-only .bheams-id-qr small{font-size:3.8px!important;line-height:1.1!important}
body.print-id-only .bheams-id-footer{display:flex!important;align-items:center!important;justify-content:space-between!important;flex:0 0 .22in!important;min-height:.22in!important;gap:3px!important;padding:0 .12in!important;font-size:4.4px!important;line-height:1.1!important;letter-spacing:0!important;box-sizing:border-box!important}
body.print-id-only .bheams-id-card,body.print-id-only .bheams-id-card *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
}
</style>
<script>
let citizenPrintPageRule = null;
function printCitizenSection(section) {
    document.body.classList.remove('print-id-only', 'print-record-only');
    document.body.classList.add(section === 'id' ? 'print-id-only' : 'print-record-only');

    if (citizenPrintPageRule) {
        citizenPrintPageRule.remove();
        citizenPrintPageRule = null;
    }

    citizenPrintPageRule = document.createElement('style');
    citizenPrintPageRule.id = 'citizen-print-page-size';
    citizenPrintPageRule.textContent = section === 'id'
        ? '@page { size: 3.375in 2.125in; margin: 0; }'
        : '@page { size: A4 landscape; margin: 8mm; }';
    document.head.appendChild(citizenPrintPageRule);

    window.print();
}
window.addEventListener('afterprint', function() {
    document.body.classList.remove('print-id-only', 'print-record-only');
    if (citizenPrintPageRule) {
        citizenPrintPageRule.remove();
        citizenPrintPageRule = null;
    }
});
</script>
@endsection
