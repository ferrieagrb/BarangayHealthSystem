<script src="{{ asset('js/input-autocomplete.js') }}"></script>
@once
<script type="application/json" data-input-capitalization>@json(config('input_capitalization'))</script>
<script src="{{ asset('js/input-capitalization.js') }}?v={{ filemtime(public_path('js/input-capitalization.js')) }}" defer></script>
@endonce
