<h4 style="margin:16px 0 8px;">{{ $title }}</h4>
<div class="report-table-scroll">
<table class="report-table">
    <thead><tr><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Sex</th><th>Birthdate</th><th>Age</th><th>Contact No.</th><th>Purok</th><th>Subgroup</th></tr></thead>
    <tbody>
        @forelse($groups as $group)
            <tr><th colspan="9">{{ $group['purok'] }} — {{ $group['subgroup'] }} — {{ $group['classification'] }} ({{ count($group['rows']) }})</th></tr>
            @foreach($group['rows'] as $row)
                <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @endforeach
        @empty
            <tr><td colspan="9" class="report-empty">No citizens match the selected filters.</td></tr>
        @endforelse
    </tbody>
</table>
</div>
