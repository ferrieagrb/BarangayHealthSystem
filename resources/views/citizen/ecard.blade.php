
@extends('templates.citizen')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<div class="mx-auto max-w-7xl px-4 py-8">

    {{-- PAGE HEADER --}}
    <div class="mb-6 flex items-center justify-between gap-4 print:hidden">
        <h1 class="text-xl font-bold text-gray-800">
            Citizen ID & Electronic Health Card
        </h1>

        <a href="{{ route('citizen.dashboard') }}"
           class="rounded bg-gray-600 px-4 py-2 text-sm text-white">
            Back to Dashboard
        </a>
    </div>

    {{-- TWO-COLUMN LAYOUT --}}
    <div class="grid grid-cols-1 items-start gap-6">

        

{{-- GOVERNMENT-STYLE BHEAMS CITIZEN ID --}}
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

                <div class="bheams-id-field">
                    <span>CONTACT NUMBER</span>
                    <strong>
                        {{ $citizen->Citizen_ContactNo ?: 'N/A' }}
                    </strong>
                </div>
            </div>

            <div class="bheams-id-field">
                <span>ADDRESS / PUROK</span>
                <strong>{{ $citizen->Citizen_Purok ?: 'N/A' }}</strong>
            </div>

        </div>

        {{-- QR VERIFICATION --}}
        <div class="bheams-id-qr">
            <div class="bheams-qr-frame">
                <img src="{{ $qrImage }}" alt="Citizen Verification QR Code">
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

<style>
    .bheams-id-card {
        position: relative;
        isolation: isolate;
        width: 100%;
        max-width: 1000px;
        margin: 40px auto 32px;
        overflow: hidden;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.14);
        color: #1e293b;
        font-family: Arial, Helvetica, sans-serif;
    }

    .bheams-id-decoration {
        position: absolute;
        z-index: -1;
        top: 0;
        right: 0;
        width: 35%;
        height: 100%;
        background:
            linear-gradient(
                135deg,
                transparent 45%,
                rgba(30, 64, 175, 0.035) 45%,
                rgba(30, 64, 175, 0.035) 65%,
                transparent 65%
            );
        pointer-events: none;
    }

    .bheams-id-header {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 20px 28px;
        background: linear-gradient(110deg, #102b78, #17469a);
        color: white;
        border-bottom: 4px solid #bfdbfe;
    }

    .bheams-seal {
        width: 76px;
        height: 76px;
        flex-shrink: 0;
        overflow: hidden;
        border: 2px solid white;
        border-radius: 50%;
        background: white;
        }

    .bheams-logo {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .bheams-brand h2 {
        margin: 0;
        color: white;
        font-size: 30px;
        font-weight: 900;
        line-height: 1.1;
        letter-spacing: 1px;
    }

    .bheams-brand p {
        margin: 5px 0;
        color: white;
        font-size: 17px;
        font-weight: 800;
    }

    .bheams-brand span {
        display: block;
        color: #dbeafe;
        font-size: 9px;
        font-weight: 600;
        letter-spacing: 0.7px;
    }

    .bheams-id-body {
        display: grid;
        grid-template-columns: 145px minmax(0, 1fr) 130px;
        align-items: center;
        gap: 24px;
        padding: 28px;
    }

    .bheams-photo {
        width: 145px;
        height: 180px;
        overflow: hidden;
        border: 2px solid #d1d5db;
        border-radius: 5px;
        background: #f1f5f9;
    }

    .bheams-photo img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .bheams-no-photo {
        display: flex;
        width: 100%;
        height: 100%;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #64748b;
    }

    .bheams-no-photo span {
        font-size: 42px;
        font-weight: 900;
    }

    .bheams-no-photo small {
        margin-top: 8px;
        font-size: 10px;
    }

    .bheams-id-details {
        min-width: 0;
    }

    .bheams-id-title {
        margin-bottom: 16px;
        color: #173b82;
        font-size: 21px;
        font-weight: 900;
        letter-spacing: 0.5px;
    }

    .bheams-id-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 13px;
        overflow-wrap: anywhere;
    }

    .bheams-id-field span {
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.65px;
    }

    .bheams-id-field strong {
        color: #172554;
        font-size: 13px;
        font-weight: 800;
    }

    .bheams-id-field .bheams-citizen-number {
        color: #17469a;
        font-size: 18px;
        letter-spacing: 1px;
    }

    .bheams-id-two-columns {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .bheams-id-qr {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .bheams-qr-frame {
        padding: 8px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: white;
    }

    .bheams-qr-frame img {
        display: block;
        width: 108px;
        height: 108px;
        object-fit: contain;
    }

    .bheams-id-qr > strong {
        margin-top: 10px;
        color: #173b82;
        font-size: 10px;
        letter-spacing: 0.5px;
    }

    .bheams-id-qr > small {
        margin-top: 4px;
        color: #64748b;
        font-size: 9px;
    }

    .bheams-id-footer {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 28px;
        border-top: 1px solid #dbeafe;
        background: #f8fafc;
        color: #334e85;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.7px;
    }

    @media (max-width: 700px) {
        .bheams-id-body {
            grid-template-columns: 100px minmax(0, 1fr);
            gap: 16px;
            padding: 18px;
        }

        .bheams-photo {
            width: 100px;
            height: 130px;
        }

        .bheams-id-qr {
            grid-column: 1 / -1;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 10px;
        }

        .bheams-qr-frame img {
            width: 75px;
            height: 75px;
        }

        .bheams-id-title {
            font-size: 17px;
        }

        .bheams-brand h2 {
            font-size: 23px;
        }

        .bheams-brand p {
            font-size: 14px;
        }

        .bheams-id-two-columns {
            grid-template-columns: 1fr;
            gap: 0;
        }
    }

    @media print {
        .bheams-id-card {
            max-width: none;
            margin: 0 auto 15px;
            box-shadow: none;
            break-inside: avoid;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .bheams-id-body {
            grid-template-columns: 125px minmax(0, 1fr) 115px;
            gap: 18px;
        }

        .bheams-photo {
            width: 125px;
            height: 155px;
        }

        .bheams-qr-frame img {
            width: 90px;
            height: 90px;
        }
    }
</style>



        {{-- RIGHT: EXISTING ELECTRONIC HEALTH RECORD --}}
        <section class="min-w-0 rounded-xl border-2 border-gray-300 bg-white p-5 shadow-lg sm:p-6">

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
                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Purok / Location</span>
                    <span class="font-semibold">
                        {{ $citizen->Citizen_Purok ?: 'N/A' }}
                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Date of Birth / Age</span>
                    <span class="font-semibold">
                        {{ $citizen->Citizen_BirthDate ?: 'N/A' }}
                        (Age: {{ $citizen->Citizen_Age ?? 'N/A' }})
                    </span>
                </div>

                <div>
                    <span class="block text-xs text-gray-500">Patient Contact Number</span>
                    <span class="font-semibold">
                        {{ $citizen->Citizen_ContactNo ?: 'N/A' }}
                    </span>
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
                                        COVID-19 / Health Vax
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

            {{-- DISPENSED MEDICINES --}}
            <div class="mb-6">
                <h3 class="mb-2 border border-gray-300 bg-gray-100 p-2 text-sm font-bold uppercase text-gray-700">
                    Dispensed Medicines
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-400 text-xs text-left">
                        <thead>
                            <tr class="bg-gray-200 text-gray-800">
                                <th class="border border-gray-400 p-2">Medicine</th>
                                <th class="border border-gray-400 p-2">Dosage</th>
                                <th class="border border-gray-400 p-2">Quantity</th>
                                <th class="border border-gray-400 p-2">Date Dispensed</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($citizen->medications as $med)
                                <tr>
                                    <td class="border border-gray-400 p-2 font-semibold">
                                        {{ $med->medicine_name }}
                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        {{ $med->dosage }}
                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        {{ $med->quantity_dispensed }}
                                    </td>
                                    <td class="border border-gray-400 p-2">
                                        {{ $med->date_dispensed }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="border border-gray-400 p-3 text-center italic text-gray-500">
                                        No medication records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PRINT BUTTON --}}
            <div class="mt-6 text-right print:hidden">
                <button
                    type="button"
                    onclick="window.print()"
                    class="rounded bg-blue-800 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-900">
                    🖨️ Print ID & Health Record
                </button>
            </div>

        </section>

    </div>
</div>

<style>
@media print {
    @page {
        size: landscape;
        margin: 10mm;
    }

    body {
        background: white !important;
    }

    .print\:hidden {
        display: none !important;
    }
}
</style>
@endsection
