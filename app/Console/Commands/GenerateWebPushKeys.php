<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;

class GenerateWebPushKeys extends Command
{
    protected $signature = 'webpush:keys';

    protected $description = 'Generate VAPID keys for browser push notifications';

    public function handle(): int
    {
        $options = [
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'private_key_bits' => 2048,
        ];
        $opensslConfig = getenv('OPENSSL_CONF');

        if (!$opensslConfig && PHP_OS_FAMILY === 'Windows') {
            $opensslConfig = dirname(PHP_BINARY)
                . DIRECTORY_SEPARATOR . 'extras'
                . DIRECTORY_SEPARATOR . 'ssl'
                . DIRECTORY_SEPARATOR . 'openssl.cnf';
        }

        if ($opensslConfig && is_file($opensslConfig)) {
            $options['config'] = $opensslConfig;
        }

        $key = openssl_pkey_new($options);

        if ($key === false) {
            throw new RuntimeException('Unable to generate an elliptic-curve VAPID key pair.');
        }

        $details = openssl_pkey_get_details($key);
        $publicParts = $details['ec'] ?? null;

        if (!is_array($publicParts) || !isset($publicParts['x'], $publicParts['y'], $publicParts['d'])) {
            throw new RuntimeException('OpenSSL did not return the required P-256 key values.');
        }

        $publicKey = "\x04" . $publicParts['x'] . $publicParts['y'];
        $privateKey = str_pad($publicParts['d'], 32, "\x00", STR_PAD_LEFT);

        $this->line('VAPID_PUBLIC_KEY=' . $this->base64UrlEncode($publicKey));
        $this->line('VAPID_PRIVATE_KEY=' . $this->base64UrlEncode($privateKey));
        $this->newLine();
        $this->warn('Keep the private key secret. Do not commit it or share it with clients.');

        return self::SUCCESS;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}