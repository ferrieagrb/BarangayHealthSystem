{{-- Saved user preferences are rendered before paint; previews never enter storage. --}}
<link rel="stylesheet" href="{{ asset('css/theme.css') }}">
<script src="{{ asset('js/appearance.js') }}" defer></script>
<link rel="stylesheet" href="{{ asset('css/pagination.css') }}?v={{ filemtime(public_path('css/pagination.css')) }}">
<script src="{{ asset('js/pagination.js') }}?v={{ filemtime(public_path('js/pagination.js')) }}" defer></script>
@include('partials.input-autocomplete-assets')
@include('partials.button-action-assets')
@if(auth()->check())
    @include('partials.medical-autocomplete-assets')
@endif
