{{-- Saved user preferences are rendered before paint; previews never enter storage. --}}
<link rel="stylesheet" href="{{ asset('css/theme.css') }}">
<link rel="stylesheet" href="{{ asset('css/form-layout.css') }}?v={{ filemtime(public_path('css/form-layout.css')) }}">
<script src="{{ asset('js/appearance.js') }}?v={{ is_file(public_path('js/appearance.js')) ? filemtime(public_path('js/appearance.js')) : '1' }}" defer></script>
<link rel="stylesheet" href="{{ asset('css/pagination.css') }}?v={{ is_file(public_path('css/pagination.css')) ? filemtime(public_path('css/pagination.css')) : '1' }}">
<script src="{{ asset('js/pagination.js') }}?v={{ is_file(public_path('js/pagination.js')) ? filemtime(public_path('js/pagination.js')) : '1' }}" defer></script>
@include('partials.input-autocomplete-assets')
@include('partials.button-action-assets')
@if(auth()->check())
    @include('partials.medical-autocomplete-assets')
@endif
