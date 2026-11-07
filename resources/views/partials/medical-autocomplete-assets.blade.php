@once
<meta name="medical-terminology-search-url" content="{{ route('bhw.medical-terminology.search') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/bhw/medical-autocomplete.css') }}?v={{ filemtime(public_path('css/bhw/medical-autocomplete.css')) }}">
<script src="{{ asset('js/medical-autocomplete.js') }}?v={{ filemtime(public_path('js/medical-autocomplete.js')) }}" defer></script>
@endonce
