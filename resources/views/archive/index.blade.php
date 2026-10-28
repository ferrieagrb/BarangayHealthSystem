@extends($layout)

@section('content')
<style>
    .archive-page { padding: 8px 4px; }
    .archive-page h1 { margin: 0 0 4px; }
    .archive-page .archive-sub { color: #64748b; margin: 0 0 18px; }
    .archive-alert { padding: 10px 14px; border-radius: 8px; margin-bottom: 14px; }
    .archive-alert.ok { background: #d4edda; color: #155724; }
    .archive-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
    .archive-tabs a { padding: 8px 14px; border-radius: 999px; background: #eef2f7; color: #334155; text-decoration: none; font-size: .9rem; }
    .archive-tabs a.active { background: #2563eb; color: #fff; }
    .archive-tabs a span { opacity: .75; margin-left: 4px; }
    .archive-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.08); overflow: hidden; }
    .archive-table { width: 100%; border-collapse: collapse; }
    .archive-table th, .archive-table td { padding: 12px 28px; text-align: center; border-bottom: 1px solid #eef2f7; }
    .archive-table th:first-child, .archive-table td:first-child { text-align: left; }
    .archive-table th { background: #f8fafc; font-size: .8rem; text-transform: uppercase; color: #64748b; }
    .archive-restore { background: #16a34a; color: #fff; border: 0; padding: 7px 16px; border-radius: 8px; cursor: pointer; }
    .archive-empty { padding: 30px; text-align: center; color: #64748b; }
</style>

<div class="archive-page">
    <h1>Archive</h1>
    <p class="archive-sub">Records are never deleted. Archived records are kept here and can be restored at any time.</p>

    @if (session('success'))
        <div class="archive-alert ok">{{ session('success') }}</div>
    @endif

    <div class="archive-tabs">
        @foreach($tabs as $key => $tab)
            <a href="{{ route($indexRoute, ['type' => $key]) }}" class="{{ $key === $type ? 'active' : '' }}">{{ $tab['label'] }}<span>({{ $tab['count'] }})</span></a>
        @endforeach
    </div>

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
                                <button type="submit" class="archive-restore">Restore</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="archive-empty">Nothing archived here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 15px;">
        {{ $items->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
