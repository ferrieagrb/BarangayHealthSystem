<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Minishlink\WebPush\VAPID;
use Tests\TestCase;

class GenerateWebPushKeysTest extends TestCase
{
    public function test_command_generates_a_valid_vapid_key_pair(): void
    {
        $this->assertSame(0, Artisan::call('webpush:keys'));

        $output = Artisan::output();
        preg_match('/^VAPID_PUBLIC_KEY=(.+)$/m', $output, $publicKey);
        preg_match('/^VAPID_PRIVATE_KEY=(.+)$/m', $output, $privateKey);

        $this->assertNotEmpty($publicKey[1] ?? null);
        $this->assertNotEmpty($privateKey[1] ?? null);

        $validated = VAPID::validate([
            'subject' => 'https://example.test',
            'publicKey' => $publicKey[1],
            'privateKey' => $privateKey[1],
        ]);

        $this->assertSame('https://example.test', $validated['subject']);
    }
}