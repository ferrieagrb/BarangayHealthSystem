<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Citizen Records</title>
    <style>
        @page { margin: 24px; }
        body { color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { margin: 0 0 4px; color: #173b82; font-size: 18px; }
        p { margin: 0 0 14px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 7px 6px; border: 1px solid #cbd5e1; text-align: left; }
        th { background: #e2e8f0; color: #334155; font-size: 9px; text-transform: uppercase; }
        tr:nth-child(even) { background: #f8fafc; }
    </style>
</head>
<body>
    <h1>Citizen Records</h1>
    <p>Generated {{ now()->format('F d, Y h:i A') }} · {{ $citizens->count() }} records</p>
    <p><strong>Filters applied:</strong> {{ $filterSummary ? implode(' | ', $filterSummary) : 'None' }}</p>
    <table>
        <thead>
            <tr>
                @foreach($selectedFields as $field)
                    <th>{{ $fieldDefinitions[$field] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($citizens as $citizen)
                <tr>
                    @foreach($selectedFields as $field)
                        <td>{{ in_array($field, ['Citizen_Pregnant', 'Citizen_InPills', 'Citizen_IsDeceased'], true) ? ($citizen->{$field} ? 'Yes' : 'No') : ($citizen->{$field} ?? '') }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($selectedFields) }}">No citizen records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
