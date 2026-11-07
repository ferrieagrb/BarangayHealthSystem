<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $devices = $request->user()->pushSubscriptions()
            ->orderByDesc('last_seen_at')
            ->orderByDesc('id')
            ->get(['id', 'endpoint_hash', 'device_name', 'created_at', 'last_seen_at']);

        return response()->json(['devices' => $devices]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url:https', 'max:4096'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
        ]);

        abort_unless($this->isSupportedEndpoint($data['endpoint']), 422, 'This browser push service is not supported.');

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $data['endpoint'])],
            [
                'user_id' => $request->user()->id,
                'endpoint' => $data['endpoint'],
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => 'aes128gcm',
                'device_name' => $this->deviceName($request->userAgent()),
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['message' => 'This device is subscribed to notifications.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url:https', 'max:4096'],
        ]);

        abort_unless($this->isSupportedEndpoint($data['endpoint']), 422, 'This browser push service is not supported.');

        $request->user()->pushSubscriptions()
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))
            ->delete();

        return response()->json(['message' => 'This device was unsubscribed.']);
    }

    public function revoke(Request $request, int $subscriptionId): JsonResponse
    {
        $request->user()->pushSubscriptions()->findOrFail($subscriptionId)->delete();

        return response()->json(['message' => 'This device was removed.']);
    }

    private function deviceName(?string $userAgent): string
    {
        $userAgent = $userAgent ?? '';

        $browser = match (true) {
            str_contains($userAgent, 'Edg/') || str_contains($userAgent, 'EdgiOS/') => 'Edge',
            str_contains($userAgent, 'OPR/') => 'Opera',
            str_contains($userAgent, 'FxiOS/') || str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'CriOS/') || str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Unknown browser',
        };

        $platform = match (true) {
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') || str_contains($userAgent, 'iPod') => 'iOS',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS X') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'unknown device',
        };

        return $browser . ' on ' . $platform;
    }

    private function isSupportedEndpoint(string $endpoint): bool
    {
        $host = strtolower((string) parse_url($endpoint, PHP_URL_HOST));

        return $host === 'fcm.googleapis.com'
            || $host === 'web.push.apple.com'
            || str_ends_with($host, '.push.services.mozilla.com')
            || str_ends_with($host, '.push.services.opera.com')
            || str_ends_with($host, '.notify.windows.com');
    }
}