<?php

namespace App\Jobs;

use App\Models\PushSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use RuntimeException;

class SendWebPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $subscriptionId, public array $payload)
    {
    }

    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(): void
    {
        $storedSubscription = PushSubscription::find($this->subscriptionId);
        if (!$storedSubscription) {
            return;
        }

        $vapid = config('webpush.vapid');
        $webPush = new WebPush([
            'VAPID' => [
                'subject' => $vapid['subject'],
                'publicKey' => $vapid['public_key'],
                'privateKey' => $vapid['private_key'],
            ],
        ], [
            'TTL' => config('webpush.ttl', 3600),
            'urgency' => 'normal',
        ]);

        $subscription = Subscription::create([
            'endpoint' => $storedSubscription->endpoint,
            'publicKey' => $storedSubscription->public_key,
            'authToken' => $storedSubscription->auth_token,
            'contentEncoding' => $storedSubscription->content_encoding,
        ]);

        $report = $webPush->sendOneNotification(
            $subscription,
            json_encode($this->payload, JSON_THROW_ON_ERROR)
        );

        if ($report->isSubscriptionExpired()) {
            $storedSubscription->delete();
            return;
        }

        if (!$report->isSuccess()) {
            $status = $report->getResponse()?->getStatusCode();
            Log::warning('Web Push delivery failed.', [
                'subscription_id' => $storedSubscription->id,
                'status' => $status,
                'reason' => $report->getReason(),
            ]);

            if ($status === null || $status === 429 || $status >= 500) {
                throw new RuntimeException('Temporary Web Push delivery failure.');
            }
        }
    }
}