@extends('templates.citizen')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/ecard.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/health-card.css') }}">
@endsection

@section('content')
<div class="bhw-ecard-page citizen-ecard-page mx-auto max-w-7xl px-4 py-8">
    <div class="ecard-page-header print:hidden">
        <h1>Citizen ID & Electronic Health Card</h1>
        <a href="{{ route('citizen.dashboard') }}" class="ecard-button ecard-button-secondary" data-ui-action="secondary">Back to Dashboard</a>
    </div>
    <div class="ecard-actions-top print:hidden">
        <button type="button" class="ecard-print-button" onclick="printCitizenSection('id')" data-ui-action="secondary">Print Health Card Only</button>
        <button type="button" class="ecard-print-button ecard-print-record" onclick="printCitizenSection('record')" data-ui-action="secondary">Print Health Information Only</button>
    </div>
    <div class="ecard-content-stack">
<div class="ecard-id-card-scroll" role="region" aria-label="Citizen Health Card" tabindex="0" data-ecard-fit>
            @include('partials.health-card', ['citizen' => $citizen, 'qrImage' => $qrImage])
        </div>
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

            @php($citizenAge = $citizen->ageForEligibility())
            <div class="mb-6 grid grid-cols-1 gap-4 border-b border-gray-300 pb-4 text-sm sm:grid-cols-2">
                <div><span class="block text-xs text-gray-500">Housing Status</span><span class="font-semibold">{{ $citizen->Citizen_HousingStatus ?: 'N/A' }}</span></div>
                @if($citizen->Citizen_Sex === 'Female')
                    <div><span class="block text-xs text-gray-500">Pregnancy Status</span><span class="font-semibold">{{ (bool) $citizen->Citizen_Pregnant ? 'Yes' : 'No' }}</span></div>
                    @if($citizenAge !== null && $citizenAge >= 16)
                        <div><span class="block text-xs text-gray-500">Taking Pills</span><span class="font-semibold">{{ (bool) $citizen->Citizen_InPills ? 'Yes' : 'No' }}</span></div>
                    @endif
                @endif
                <div><span class="block text-xs text-gray-500">Deceased Status</span><span class="font-semibold">{{ !empty($citizen->Citizen_IsDeceased) ? 'Deceased' : 'Living' }}</span></div>
                <div><span class="block text-xs text-gray-500">Family ID</span><span class="font-semibold">{{ $citizen->family?->family_code ?? 'None' }}</span></div>
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
                                        @if($vax->historical_recording)
                                            <div class="mt-1 text-xs font-normal text-slate-600">Historical record</div>
                                        @endif
                                        @if($vax->eligibility_review_required)
                                            <div class="mt-1 text-xs font-normal text-amber-700">Clinical age eligibility not established: {{ $vax->eligibility_review_reason }}</div>
                                        @endif
                                        @if($vax->catch_up_reviewed)
                                            <div class="mt-1 text-xs font-normal text-blue-700">Catch-up schedule confirmed by health center.</div>
                                        @endif
                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        {{ $vax->vaccine_name }}
                                        — {{ $vax->dose_number }}
                                        @if($vax->cvx_code)
                                            <div class="text-xs">CDC CVX {{ $vax->cvx_code }}</div>
                                        @endif
                                        @if($vax->vaccine_formulation)
                                            <div class="text-xs">{{ $vax->vaccine_formulation }}</div>
                                        @endif
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

<script>
// Label each table cell with its column heading so tables can stack on small screens.
document.querySelectorAll('.print-health-record table').forEach(function (table) {
    var labels = Array.from(table.querySelectorAll('thead th')).map(function (th) { return th.textContent.trim(); });
    table.classList.add('ecard-stack');
    table.querySelectorAll('tbody tr').forEach(function (row) {
        Array.from(row.children).forEach(function (cell, index) {
            if (labels[index]) { cell.setAttribute('data-label', labels[index]); }
        });
    });
});
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
        ? '@page { size: 85.60mm 53.98mm; margin: 0; }'
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
<script src="{{ asset('js/ecard-preview.js') }}?v={{ filemtime(public_path('js/ecard-preview.js')) }}"></script>
<script src="{{ asset('js/ecard-fit.js') }}?v={{ filemtime(public_path('js/ecard-fit.js')) }}"></script>
@endsection
