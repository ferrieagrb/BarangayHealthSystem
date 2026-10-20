<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SystemConnectionsController extends Controller
{
    public function page()
    {
        return view('superadmin.system-connections');
    }

    public function status()
    {
        return response()->json([
            'checked_at' => now()->toIso8601String(),
            'connections' => [
                $this->checkDatabase(),
                $this->checkCache(),
                $this->checkQueue(),
                $this->checkPrivateStorage(),
                $this->checkSftp(),
                $this->checkMail(),
                $this->checkHttps(),
                $this->checkApplicationApi(),
                $this->checkFacebook(),
                $this->checkGoogleMaps(),
                $this->checkQrFeature(),
            ],
        ]);
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('select 1');
            return $this->result('Database', 'Connected', 'healthy', 'MySQL connection is responding.');
        } catch (Throwable $exception) {
            report($exception);
            return $this->result('Database', 'Failed', 'failed', 'The database connection did not respond.');
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'system_connection_check_' . bin2hex(random_bytes(6));
            Cache::put($key, 'ok', 30);
            $working = Cache::get($key) === 'ok';
            Cache::forget($key);
            return $working
                ? $this->result('Cache', 'Connected', 'healthy', 'Cache read/write is working.')
                : $this->result('Cache', 'Failed', 'failed', 'Cache write/read verification failed.');
        } catch (Throwable $exception) {
            report($exception);
            return $this->result('Cache', 'Failed', 'failed', 'The configured cache store is unavailable.');
        }
    }

    private function checkQueue(): array
    {
        $connection = (string) config('queue.default', 'sync');
        $ready = $connection !== '';
        if ($connection === 'database' && Schema::hasTable('jobs')) {
            $ready = true;
        }

        return $this->result(
            'Queue',
            $ready ? ucfirst($connection) . ' configured' : 'Not configured',
            $ready ? 'healthy' : 'warning',
            $ready ? 'Queue connection is configured.' : 'No queue connection is configured.'
        );
    }

    private function checkPrivateStorage(): array
    {
        $path = storage_path('app/private');
        $ready = is_dir($path) && is_writable($path);

        return $this->result(
            'Private Storage',
            $ready ? 'Writable' : 'Unavailable',
            $ready ? 'healthy' : 'failed',
            $ready ? 'Encrypted backups can be saved locally.' : 'The private storage directory is missing or not writable.'
        );
    }

    private function checkSftp(): array
    {
        $configured = (bool) env('BACKUP_SFTP_HOST')
            && (bool) env('BACKUP_SFTP_USERNAME')
            && ((bool) env('BACKUP_SFTP_PASSWORD') || (bool) env('BACKUP_SFTP_PRIVATE_KEY'));

        if (!$configured) {
            return $this->result('Hostinger SFTP', 'Not configured', 'warning', 'Add BACKUP_SFTP_* values to .env.');
        }

        try {
            Storage::disk('sftp')->files('');
            return $this->result('Hostinger SFTP', 'Connected', 'healthy', 'The remote backup directory responded.');
        } catch (Throwable $exception) {
            report($exception);
            return $this->result('Hostinger SFTP', 'Failed', 'failed', 'Credentials, host, root path, or permissions may be incorrect.');
        }
    }

    private function checkMail(): array
    {
        $mailer = (string) config('mail.default', 'log');
        $isConfigured = $mailer !== '' && $mailer !== 'log';

        return $this->result(
            'Mail',
            $isConfigured ? ucfirst($mailer) . ' configured' : 'Log driver',
            $isConfigured ? 'healthy' : 'warning',
            $isConfigured ? 'Mail transport is configured.' : 'Messages are logged locally and not delivered externally.'
        );
    }

    private function checkHttps(): array
    {
        $secure = request()->isSecure() || str_starts_with((string) config('app.url'), 'https://');

        return $this->result(
            'HTTPS',
            $secure ? 'Enabled' : 'Not detected',
            $secure ? 'healthy' : 'warning',
            $secure ? 'Secure application URL detected.' : 'Use HTTPS in production for camera, login, and session security.'
        );
    }

    private function checkApplicationApi(): array
    {
        $ready = app()->bound('router') && Schema::hasTable('sessions');

        return $this->result(
            'Application API',
            $ready ? 'Available' : 'Unavailable',
            $ready ? 'healthy' : 'failed',
            $ready ? 'The Laravel application and session layer are responding.' : 'The application service is not ready.'
        );
    }

    private function checkFacebook(): array
    {
        $pageId = config('services.facebook.page_id');
        $token = config('services.facebook.access_token');

        if (!$pageId || !$token) {
            return $this->result('Facebook API', 'Not configured', 'warning', 'Add FACEBOOK_PAGE_ID and FACEBOOK_PAGE_ACCESS_TOKEN to .env.');
        }

        try {
            $response = Http::timeout(5)->get("https://graph.facebook.com/{$pageId}", [
                'fields' => 'id,name',
                'access_token' => $token,
            ]);

            return $response->successful()
                ? $this->result('Facebook API', 'Connected', 'healthy', 'Facebook Graph API responded for the configured page.')
                : $this->result(
                    'Facebook API',
                    'Failed',
                    'failed',
                    'Facebook rejected the request' . ($response->json('error.code') ? ' (code ' . $response->json('error.code') . ')' : '') . '. Use a valid Page ID and Page access token.'
                );
        } catch (Throwable $exception) {
            report($exception);
            return $this->result('Facebook API', 'Failed', 'failed', 'Facebook Graph API could not be reached.');
        }
    }

    private function checkGoogleMaps(): array
    {
        $key = config('services.google_maps.key');

        return $key
            ? $this->result('Google Maps API', 'Configured', 'healthy', 'The browser map integration has an API key. Google reports quota and referrer errors in the browser console.')
            : $this->result('Google Maps API', 'Not configured', 'warning', 'Add GOOGLE_MAPS_API_KEY to .env.');
    }

    private function checkQrFeature(): array
    {
        $ready = Route::has('qr.scanner')
            && Route::has('qr.scanner.lookup')
            && class_exists(\App\Http\Controllers\QRScannerController::class)
            && view()->exists('bhw.qr_scanner');

        return $this->result(
            'QR Checker',
            $ready ? 'Available' : 'Unavailable',
            $ready ? 'healthy' : 'failed',
            $ready ? 'Scanner page and authenticated QR lookup route are registered.' : 'The scanner route, controller, or view is missing.'
        );
    }

    private function result(string $name, string $status, string $state, string $detail): array
    {
        return compact('name', 'status', 'state', 'detail');
    }
}
