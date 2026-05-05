@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/citizen_show.css') }}">
@endsection

@section('content')

<div class="page-top">
    <div class="page-title">
        <h1>Citizen Health Record</h1>
        <p>Medical history details</p>
    </div>
    <div class="right">
            <a href="{{ route('healthrecord') }}" class="btn-secondary">← Back</a>
    </div>
</div>

<br>


<div class="info-card">
    <div class="card">
        <h2>
            {{ $citizen->Citizen_FName }} {{ $citizen->Citizen_LName }}
        </h2>

        <p><strong>Age:</strong> {{ $citizen->Citizen_Age }}</p>
        <p><strong>Purok:</strong> {{ $citizen->Citizen_Purok }}</p>
        <p><strong>Contact:</strong> {{ $citizen->Citizen_ContactNo }}</p>
    </div>
</div>

<br>


<div class="diagnosis-section">
    <h3>Health Records</h3>

        @if($citizen->healthRecords->isEmpty())
            <p>No health records yet.</p>
        @endif

        @foreach($citizen->healthRecords as $record)
    <div class="diagnosis" style="padding:10px; border:1px solid #ccc; margin-bottom:10px;">
        
        <p><strong>Diagnosis:</strong> {{ $record->diagnosis }}</p>

        <p><strong>Date:</strong> 
            {{ $record->record_date ?? $record->created_at->format('Y-m-d') }}
        </p>

        <p><strong>Comments:</strong> 
            {{ $record->comments ?? 'No notes' }}
        </p>

        <p><small>Added: {{ $record->created_at->format('Y-m-d h:i A') }}</small></p>

    </div>
@endforeach
</div>

<!-- ADD NEW DIAGNOSIS -->
<br>
<br>

<h3>Add Diagnosis</h3>

<form method="POST" action="{{ route('health.record.store') }}">
    @csrf

    <input type="hidden" name="citizen_id" value="{{ $citizen->id }}">

    <!-- Diagnosis (manual input) -->
    <label>Diagnosis</label>
    <textarea name="diagnosis" required placeholder="Enter diagnosis..."></textarea>

    <!-- Date -->
    <label>Date</label>
    <input type="date" name="record_date" required>

    <!-- Comments -->
    <label>Comments / Notes</label>
    <textarea name="comments" placeholder="Enter notes..."></textarea>

    <button type="submit" onclick="this.disabled=true; this.form.submit();">
    Save
</button>
</form>

@endsection