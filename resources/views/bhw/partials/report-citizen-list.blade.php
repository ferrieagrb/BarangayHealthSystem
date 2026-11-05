<h4 style="margin:16px 0 8px;">{{ $title }}</h4>
<div class="report-table-scroll">
<table class="report-table">
    <thead><tr><th>Name</th><th>Sex</th><th>Age</th><th>Purok</th><th>Subgroup</th></tr></thead>
    <tbody>
        @forelse($groups as $group)
            <tr><th colspan="5">{{ $group['purok'] }} — {{ $group['subgroup'] }} — {{ $group['classification'] }} ({{ count($group['rows']) }})</th></tr>
            @foreach($group['rows'] as $row)
                <tr><td>{{ $row[0] }}</td><td>{{ $row[1] }}</td><td>{{ $row[2] }}</td><td>{{ $row[3] }}</td><td>{{ $row[4] }}</td></tr>
            @endforeach
        @empty
            <tr><td colspan="5" class="report-empty">No citizens match the selected filters.</td></tr>
        @endforelse
    </tbody>
</table>
</div>
