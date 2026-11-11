@once
<meta name="medical-terminology-search-url" content="{{ route('bhw.medical-terminology.search') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/bhw/medical-autocomplete.css') }}?v={{ is_file(public_path('css/bhw/medical-autocomplete.css')) ? filemtime(public_path('css/bhw/medical-autocomplete.css')) : '1' }}">
<script src="{{ asset('js/medical-autocomplete.js') }}?v={{ is_file(public_path('js/medical-autocomplete.js')) ? filemtime(public_path('js/medical-autocomplete.js')) : '1' }}" defer></script>
@endonce
