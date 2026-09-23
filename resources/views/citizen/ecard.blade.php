@extends('templates.citizen')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-gray-800">Electronic Health & Vaccination Card</h1>
        <a href="{{ route('citizen.dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded text-sm shadow hover:bg-gray-700">Back to Dashboard</a>
    </div>

    <!-- Physical Card Container Style -->
    <div class="bg-white border-2 border-gray-400 rounded-lg shadow-md p-6 relative font-sans text-gray-900">
        
        <div class="border-b-2 border-gray-800 pb-3 mb-4">
            <h2 class="text-2xl font-black uppercase tracking-tight text-gray-900">Amuyong Health Record</h2>
            <p class="text-xs text-gray-600 mt-1">Please keep this record card, which includes medical information about the vaccines and medicines you have received.</p>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6 border-b border-gray-300 pb-4 text-sm">
            <div class="col-span-2 border-b border-dashed border-gray-400 pb-1">
                <span class="text-xs text-gray-500 block">Last Name, First Name</span>
                <span class="font-bold text-base">{{ $citizen->Citizen_LName }}, {{ $citizen->Citizen_FName }}</span>
            </div>
            <div class="border-b border-dashed border-gray-400 pb-1">
                <span class="text-xs text-gray-500 block">Purok / Location</span>
                <span class="font-semibold text-base">{{ $citizen->Citizen_Purok }}</span>
            </div>
            <div class="border-b border-dashed border-gray-400 pb-1">
                <span class="text-xs text-gray-500 block">Date of Birth</span>
                <span class="font-semibold text-sm">{{ $citizen->Citizen_BirthDate ?? 'N/A' }} (Age: {{ $citizen->Citizen_Age }})</span>
            </div>
            <div class="col-span-2 border-b border-dashed border-gray-400 pb-1">
                <span class="text-xs text-gray-500 block">Patient Contact Number</span>
                <span class="font-semibold text-sm">{{ $citizen->Citizen_ContactNo ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Vaccination Table Section -->
        <div class="mb-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700 mb-2 bg-gray-100 p-1 border border-gray-300">Immunization History</h3>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-400 text-xs text-left">
                    <thead>
                        <tr class="bg-gray-200 text-gray-800">
                            <th class="border border-gray-400 p-2 w-1/4">Vaccine</th>
                            <th class="border border-gray-400 p-2 w-1/3">Product Name / Dose Number</th>
                            <th class="border border-gray-400 p-2 w-1/5">Date Administered</th>
                            <th class="border border-gray-400 p-2 w-1/4">Healthcare Professional / Clinic Site</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($citizen->vaccinations as $vax)
                            <tr>
                                <td class="border border-gray-400 p-2 font-semibold">COVID-19 / Health Vax</td>
                                <td class="border border-gray-400 p-2">{{ $vax->vaccine_name }} — <span class="text-blue-700 font-medium">{{ $vax->dose_number }}</span></td>
                                <td class="border border-gray-400 p-2">{{ $vax->date_administered }}</td>
                                <td class="border border-gray-400 p-2">{{ $vax->administered_by }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="border border-gray-400 p-3 italic text-gray-500">1st Dose</td>
                                <td class="border border-gray-400 p-3 text-gray-400">------------------------------</td>
                                <td class="border border-gray-400 p-3 text-gray-400">mm / dd / yy</td>
                                <td class="border border-gray-400 p-3"></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-6">
    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700 mb-2 bg-gray-100 p-1 border border-gray-300">
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

        <!-- Print Action Button -->
        <div class="mt-6 text-right">
            <button onclick="window.print()" class="bg-blue-800 text-white px-5 py-2 rounded text-sm font-semibold shadow hover:bg-blue-900 print:hidden">
                🖨️ Print Card
            </button>
        </div>

    </div>
</div>
@endsection
