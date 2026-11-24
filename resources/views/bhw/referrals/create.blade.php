@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/referral_create.css') }}?v={{ filemtime(public_path('css/bhw/referral_create.css')) }}">
<link rel="stylesheet" href="{{ asset('css/bhw/referral_document.css') }}?v={{ filemtime(public_path('css/bhw/referral_document.css')) }}">
@endsection

@section('content')
<div class="main referral-create-page">
    <div class="page-top">
        <div>
            <h1>Generate Referral</h1>
            <p>Select the citizen visiting the health center to prepare their referral form.</p>
        </div>
        <a href="{{ route('referrals.index') }}" class="btn-secondary">Back to referrals</a>
    </div>

    @if ($errors->any())
        <div class="alert-error" role="alert">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <section class="citizen-picker" aria-labelledby="citizen-picker-title">
        <h2 id="citizen-picker-title">Choose a citizen</h2>
        <form action="{{ route('referrals.create') }}" method="GET" class="citizen-search" role="search">
            @if($citizen)<input type="hidden" name="citizen_id" value="{{ $citizen->id }}">@endif
            <label for="citizen-search">Search by name or citizen ID</label>
            <div class="citizen-search-controls">
                <input id="citizen-search" type="search" name="search" value="{{ request('search') }}" placeholder="Enter a citizen's name or ID" maxlength="255">
                <button type="submit" class="btn-primary">Search</button>
            </div>
        </form>
        <div class="citizen-options">
            @forelse($citizens as $option)
                <a href="{{ route('referrals.create', ['citizen_id' => $option->id, 'search' => request('search'), 'page' => request('page')]) }}#referral-preview"
                    class="citizen-option {{ $citizen?->id === $option->id ? 'is-selected' : '' }}"
                    @if($citizen?->id === $option->id) aria-current="true" @endif>
                    <span class="citizen-option-details">
                        <strong>{{ $option->Citizen_LName }}, {{ $option->Citizen_FName }} {{ $option->Citizen_MName }}</strong>
                        <small>ID {{ $option->id }} &middot; {{ $option->Citizen_Purok ?: 'Purok not recorded' }} &middot; {{ $option->Citizen_BirthDate ? \Carbon\Carbon::parse($option->Citizen_BirthDate)->format('M d, Y') : 'Birth date not recorded' }}</small>
                    </span>
                    <span class="citizen-option-action">{{ $citizen?->id === $option->id ? 'Selected' : 'Select' }}</span>
                </a>
            @empty
                <p class="picker-empty">No citizens found in your accessible records. Try another name or ID.</p>
            @endforelse
        </div>
        {{ $citizens->links('partials.pagination') }}
    </section>

    <div class="referral-preview-heading" id="referral-preview">
        <div>
            <h2>Referral form preview</h2>
            <p>{{ $citizen ? 'Patient information is filled in from the citizen record.' : 'Select a citizen above to fill in this form.' }} Clinical details and signatures are left blank for healthcare personnel.</p>
        </div>
        <form action="{{ route('referrals.store') }}" method="POST">
            @csrf
            <input type="hidden" name="citizen_id" value="{{ $citizen?->id }}">
            <button type="submit" class="btn-primary" @disabled(!$citizen)>Generate Referral PDF</button>
        </form>
    </div>
    <div class="referral-preview-scroll">
        @include('bhw.referrals.partials.document', ['forPdf' => false])
    </div>
</div>
@endsection
