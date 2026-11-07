<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\PushSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DispatchAnnouncementPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $announcementId)
    {
    }

    public function handle(): void
    {
        $vapid = config('webpush.vapid');
        if (empty($vapid['public_key']) || empty($vapid['private_key'])) {
            Log::warning('Announcement push skipped because VAPID keys are not configured.');
            return;
        }

        $announcement = Announcement::find($this->announcementId);
        if (!$announcement) {
            return;
        }

        $payload = [
            'title' => 'New barangay health announcement',
            'body' => 'An update is available. Sign in to view the announcement.',
            'url' => route('public.announcements.show', $announcement),
            'tag' => 'announcement-' . $announcement->id,
        ];

        PushSubscription::query()
            ->whereHas('user')
            ->orderBy('id')
            ->chunkById(100, function ($subscriptions) use ($payload): void {
                foreach ($subscriptions as $subscription) {
                    \App\Jobs\SendWebPushNotification::dispatch($subscription->id, $payload);
                }
            });
    }
}