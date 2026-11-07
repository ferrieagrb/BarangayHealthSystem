@extends($layout)

@section('CSSown')
    <link rel="stylesheet" href="{{ asset('css/admin/record-pages.css') }}?v={{ filemtime(public_path('css/admin/record-pages.css')) }}">
@endsection

@section('content')
<div class="admin-record-page archive-page">
    <div class="page-top">
        <div class="page-title-group">
            <h1>Archive</h1>
            <p>Records are never deleted. Archived records are kept here and can be restored at any time.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="archive-alert ok">{{ session('success') }}</div>
    @endif

    <nav class="archive-tabs" data-archive-tabs aria-label="Archived record categories">
        @foreach($tabs as $key => $tab)
            <a href="{{ route($indexRoute, ['type' => $key]) }}" class="{{ $key === $type ? 'active' : '' }}" @if($key === $type) aria-current="page" @endif>{{ $tab['label'] }}<span>({{ $tab['count'] }})</span></a>
        @endforeach
    </nav>

    <div class="archive-card">
        <table class="archive-table">
            <thead>
                <tr><th>Record</th><th>Details</th><th>Archived on</th><th>Action</th></tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    @php [$title, $detail] = $labeler($item); @endphp
                    <tr>
                        <td>{{ $title ?: '#' . $item->id }}</td>
                        <td>{{ trim((string) $detail) !== '' ? $detail : '—' }}</td>
                        <td>{{ optional($item->deleted_at)->format('M d, Y h:i A') }}</td>
                        <td>
                            <form method="POST" action="{{ route($restoreRoute, ['type' => $type, 'id' => $item->id]) }}" onsubmit="return confirm('Restore this record?')">
                                @csrf
                                <button type="submit" class="archive-restore" data-ui-action="primary">Restore</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="archive-empty">Nothing archived here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        {{ $items->links('partials.pagination') }}
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/audit-log-tabs.js') }}?v={{ filemtime(public_path('js/audit-log-tabs.js')) }}" defer></script>
@endsection
