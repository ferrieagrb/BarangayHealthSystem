@extends('templates.layout') {{-- Change this to your main layout if it differs (e.g., bhw.layouts.app) --}}

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Electronic Health & Vaccination Card</h1>
        <a href="{{ route('citizenlist') }}" class="bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600">Back to List</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Citizen Information Card -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Citizen Profile</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-gray-600">
            <div><strong>Name:</strong> {{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}</div>
            <div><strong>Age:</strong> {{ $citizen->Citizen_Age }}</div>
            <div><strong>Birthdate:</strong> {{ $citizen->Citizen_BirthDate ?? 'N/A' }}</div>
            <div><strong>Contact No:</strong> {{ $citizen->Citizen_ContactNo ?? 'N/A' }}</div>
            <div><strong>Purok:</strong> {{ $citizen->Citizen_Purok }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- VACCINATION SECTION -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Immunization History</h3>
            
            <!-- Vaccination Table -->
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-2">Vaccine</th>
                            <th class="px-4 py-2">Dose</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Administered By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($citizen->vaccinations as $vax)
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $vax->vaccine_name }}</td>
                                <td class="px-4 py-2">{{ $vax->dose_number }}</td>
                                <td class="px-4 py-2">{{ $vax->date_administered }}</td>
                                <td class="px-4 py-2">{{ $vax->administered_by }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500">No vaccination records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Add Vaccination Form -->
            <form action="{{ route('citizen.vaccination.store', $citizen->id) }}" method="POST" class="bg-gray-50 p-4 rounded border">
                @csrf
                <h4 class="font-medium text-gray-700 mb-2">Add Vaccination Record</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600">Vaccine Name</label>
                        <input type="text" name="vaccine_name" required class="w-full mt-1 border rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600">Dose Number</label>
                        <input type="text" name="dose_number" placeholder="e.g. Dose 1, Booster" required class="w-full mt-1 border rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600">Date Administered</label>
                        <input type="date" name="date_administered" required class="w-full mt-1 border rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600">Administered By</label>
                        <input type="text" name="administered_by" required class="w-full mt-1 border rounded p-2 text-sm">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded text-sm font-semibold hover:bg-blue-700">Add Vaccination</button>
                </div>
            </form>
        </div>

        <!-- MEDICATION SECTION -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Dispensed Medicines</h3>
            
            <!-- Medication Table -->
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-2">Medicine</th>
                            <th class="px-4 py-2">Dosage</th>
                            <th class="px-4 py-2">Qty</th>
                            <th class="px-4 py-2">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($citizen->medications as $med)
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $med->medicine_name }}</td>
                                <td class="px-4 py-2">{{ $med->dosage }}</td>
                                <td class="px-4 py-2">{{ $med->quantity_dispensed }}</td>
                                <td class="px-4 py-2">{{ $med->date_dispensed }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500">No medication logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Add Medication Form -->
            <form action="{{ route('citizen.medication.store', $citizen->id) }}" method="POST" class="bg-gray-50 p-4 rounded border">
                @csrf
                <h4 class="font-medium text-gray-700 mb-2">Dispense Medicine Log</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600">Medicine Name</label>
                        <input type="text" name="medicine_name" required class="w-full mt-1 border rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600">Dosage</label>
                        <input type="text" name="dosage" placeholder="e.g. 500mg" required class="w-full mt-1 border rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600">Quantity Dispensed</label>
                        <input type="number" name="quantity_dispensed" min="1" required class="w-full mt-1 border rounded p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600">Date Dispensed</label>
                        <input type="date" name="date_dispensed" required class="w-full mt-1 border rounded p-2 text-sm">
                    </div>
                    <button type="submit" class="w-full bg-green-600 text-white py-2 rounded text-sm font-semibold hover:bg-green-700">Save Medication Log</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection