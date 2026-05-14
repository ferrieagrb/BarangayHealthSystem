@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/citizen_show.css') }}">
@endsection

@section('content')
<div class="page-top">
    <div class="page-title">
        <h1>Citizen Health Record</h1>
    </div>
    <div class="right">
        <a href="{{ route('healthrecord') }}" class="btn-secondary">← Back</a>
    </div>
</div>

<div class="info-card">
    <div class="card">
        <h2>{{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}</h2>
        <p><strong>Age:</strong> {{ $citizen->Citizen_Age }}</p>
        <p><strong>Purok:</strong> {{ $citizen->Citizen_Purok }}</p>
        <p><strong>Contact:</strong> {{ $citizen->Citizen_ContactNo }}</p>
    </div>
</div>

<br>

<div class="tabs-wrapper">
    <ul class="tabs">
        <li class="tab-link current" data-tab="tab-health">Health Records</li>
        <li class="tab-link" data-tab="tab-vaccine">Vaccinations</li>
    </ul>

    <!-- Health Records -->
    <div id="tab-health" class="tab-content current">
        @if($citizen->healthRecords->isEmpty())
            <p>No health records yet.</p>
        @endif
        @foreach($citizen->healthRecords as $record)
            <div class="diagnosis">
                <p><strong>Diagnosis:</strong> {{ $record->diagnosis }}</p>
                <p><strong>Date:</strong> {{ $record->record_date ?? $record->created_at->format('Y-m-d') }}</p>
                <p><strong>Notes:</strong> {{ $record->comments ?? 'No notes' }}</p>
            </div>
        @endforeach
    </div>

    <!-- Vaccinations -->
    <div id="tab-vaccine" class="tab-content">
        @if($citizen->vaccinations->isEmpty())
            <p>No vaccination records yet.</p>
        @endif
        @foreach($citizen->vaccinations as $vaccine)
            <div class="vaccine">
                <p><strong>Vaccine:</strong> {{ $vaccine->vaccine_name }}</p>
                <p><strong>Date:</strong> {{ $vaccine->date_administered->format('Y-m-d') }}</p>
                <p><strong>Notes:</strong> {{ $vaccine->notes ?? 'None' }}</p>
            </div>
        @endforeach

        <!-- Add New Vaccination -->
        <h4>Add Vaccination</h4>
        <form method="POST" action="{{ route('vaccination.store') }}">
            @csrf
            <input type="hidden" name="citizen_id" value="{{ $citizen->id }}">
            <label>Vaccine Name</label>
            <input type="text" name="vaccine_name" required>
            <label>Date Administered</label>
            <input type="date" name="date_administered" required>
            <label>Notes</label>
            <textarea name="notes" placeholder="Enter notes..."></textarea>
            <button type="submit">Save</button>
        </form>
    </div>
</div>

<script>
const tabLinks = document.querySelectorAll('.tab-link');
const tabContents = document.querySelectorAll('.tab-content');

tabLinks.forEach(link => {
    link.addEventListener('click', function() {
        const tabId = this.getAttribute('data-tab');
        tabLinks.forEach(l => l.classList.remove('current'));
        tabContents.forEach(c => c.classList.remove('current'));
        this.classList.add('current');
        document.getElementById(tabId).classList.add('current');
    });
});
</script>
@endsection