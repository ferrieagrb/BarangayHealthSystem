@extends('templates.citizen')

@section('CSSown')
<style>
.announcements-page{max-width:1100px;margin:0 auto;padding:clamp(18px,3vw,36px);color:#172033}
.announcements-top{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:24px}
.announcements-top h1{margin:0;color:#173b82;font-size:clamp(24px,3vw,32px);font-weight:800}
.announcements-top p{margin:8px 0 0;color:#475569;line-height:1.6;font-size:16px}
.ann-back{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:11px 17px;border-radius:9px;background:#1d4ed8;color:#fff;text-decoration:none;font-weight:750}
.ann-list{display:grid;gap:18px}
.ann-card{padding:clamp(18px,2.5vw,26px);border:1px solid #dbe3ef;border-radius:14px;background:#fff;box-shadow:0 5px 18px #0f172a0a}
.ann-card h2{margin:0 0 9px;color:#173b82;font-size:clamp(19px,2vw,23px);line-height:1.35}
.ann-date{display:block;margin-bottom:14px;color:#64748b;font-size:14px}
.ann-content{margin:0;color:#334155;font-size:16px;line-height:1.75;white-space:pre-line;overflow-wrap:anywhere}
.ann-empty{padding:34px;text-align:center;border:1px dashed #cbd5e1;border-radius:14px;background:#fff;color:#475569;font-size:16px}
@media(max-width:600px){.announcements-page{padding:18px 13px}.announcements-top{align-items:stretch}.ann-back{width:100%;box-sizing:border-box}}
</style>
@endsection

@section('content')
<main class="announcements-page">
    <header class="announcements-top">
        <div>
            <h1>Health Center Announcements</h1>
            <p>Read the latest notices, health advisories, and community programs.</p>
        </div>
        <a class="ann-back" href="{{ route('citizen.dashboard') }}"><i class="bx bx-arrow-back" aria-hidden="true"></i>&nbsp; Return to Homepage</a>
    </header>
    <div class="ann-list">
        @forelse($announcements as $announcement)
            <article class="ann-card">
                <h2>{{ $announcement->title }}</h2>
                <time class="ann-date" datetime="{{ optional($announcement->created_at)->toIso8601String() }}">Posted {{ optional($announcement->created_at)->format('F d, Y · h:i A') }}</time>
                <p class="ann-content">{{ $announcement->content ?? $announcement->description ?? 'Please contact the Barangay Health Center for more information.' }}</p>
            </article>
        @empty
            <div class="ann-empty">No announcements are available at this time.</div>
        @endforelse
    </div>
</main>
@endsection
