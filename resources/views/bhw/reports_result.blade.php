@extends('templates.layout')

@section('CSSown')
<link rel="stylesheet" href="{{ asset('css/bhw/shared-ui.css') }}">
@endsection

@section('content')
<div class="page-top" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1>{{ $title }}</h1>
        <p>Generated on {{ date('F d, Y') }}</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button onclick="window.print()" class="btn-secondary">🖨️ Print / Save PDF</button>
    </div>
</div>

<!-- Rest of your report result table/content goes here -->
<div class="table-container" style="margin-top: 20px; background: white; padding: 25px; border-radius: 8px;">
    <!-- Render tables based on report type as structured previously -->
    <div style="margin-top: 30px;">
        <a href="{{ route('bhw.reports') }}" class="btn-secondary">← Back to Filter</a>
    </div>
</div>
@endsection
