@extends('templates.citizen')

@section('content')
<div style="max-width: 900px; margin: 30px auto; padding: 25px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <div style="margin-bottom: 25px;">
        <h2>📢 Health Center Announcements</h2>
        <p style="color: #666; font-size: 14px;">Stay updated with the latest notices, health advisories, and community programs.</p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 20px;">
        @forelse($announcements as $announcement)
            <div style="padding: 20px; border: 1px solid #e9ecef; border-radius: 6px; background: #fdfdfe;">
                <h3 style="margin-top: 0; color: #333; margin-bottom: 8px;">{{ $announcement->title }}</h3>
                <span style="font-size: 12px; color: #888; display: block; margin-bottom: 12px;">
                    Posted on {{ $announcement->created_at->format('F d, Y - h:i A') }}
                </span>
                <p style="color: #445; line-height: 1.5; margin: 0;">{{ $announcement->content ?? $announcement->description }}</p>
            </div>
        @empty
            <div style="text-align: center; color: #6c757d; padding: 40px;">
                No announcements available at this time.
            </div>
        @endforelse
    </div>
</div>
@endsection
