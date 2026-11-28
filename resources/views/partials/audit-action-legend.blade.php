<form method="GET" action="{{ url()->current() }}" class="audit-level-legend" data-audit-level-filter>
    @foreach(request()->query() as $key => $value)
        @if(is_scalar($value) && $key !== 'action_level' && $key !== 'page' && !str_ends_with($key, '_page'))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <label for="auditActionLevel">Action level:</label>
    <select id="auditActionLevel" name="action_level" class="audit-level-select">
        <option value="all" @selected(\App\Support\AuditActionLevel::selected() === 'all')>All</option>
        @foreach(\App\Support\AuditActionLevel::LEVELS as $level)
            <option value="{{ $level }}" @selected(\App\Support\AuditActionLevel::selected() === $level)>{{ ucfirst($level) }}</option>
        @endforeach
    </select>
    <noscript><button type="submit">Filter</button></noscript>
</form>
<script src="{{ asset('js/audit-level-filter.js') }}?v={{ filemtime(public_path('js/audit-level-filter.js')) }}" defer></script>
