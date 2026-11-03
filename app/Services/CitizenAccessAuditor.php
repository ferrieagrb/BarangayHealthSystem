<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Records who read or exported citizen data in audit_logs and raises a
 * security.alert entry when the volume looks like bulk harvesting.
 */
class CitizenAccessAuditor
{
    public const VIEW_ALERT_THRESHOLD = 40;
    public const VIEW_WINDOW_SECONDS = 600;
    public const EXPORT_ALERT_ROWS = 200;
    public const EXPORT_ALERT_COUNT = 3;
    public const EXPORT_WINDOW_SECONDS = 3600;

    public function recordView(int|string $citizenId, string $context): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $this->write('citizen.viewed', "Viewed citizen #{$citizenId} ({$context}).");

        $count = $this->bump("citizen-views:{$user->id}", self::VIEW_WINDOW_SECONDS);

        if ($count > self::VIEW_ALERT_THRESHOLD) {
            $this->alertOnce(
                "citizen-views-alert:{$user->id}",
                self::VIEW_WINDOW_SECONDS,
                "{$user->name} ({$user->role}) opened {$count} citizen records within " . (self::VIEW_WINDOW_SECONDS / 60) . ' minutes.'
            );
        }
    }

    public function recordExport(string $format, int $rowCount): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $this->write('citizen.exported', "Exported {$rowCount} citizen record(s) as {$format}.");

        $exports = $this->bump("citizen-exports:{$user->id}", self::EXPORT_WINDOW_SECONDS);

        if ($rowCount >= self::EXPORT_ALERT_ROWS || $exports >= self::EXPORT_ALERT_COUNT) {
            $this->alertOnce(
                "citizen-export-alert:{$user->id}",
                self::EXPORT_WINDOW_SECONDS,
                "{$user->name} ({$user->role}) exported {$rowCount} citizen record(s) as {$format}; {$exports} export(s) in the last hour."
            );
        }
    }

    private function bump(string $key, int $ttlSeconds): int
    {
        Cache::add($key, 0, $ttlSeconds);

        return (int) Cache::increment($key);
    }

    private function alertOnce(string $key, int $ttlSeconds, string $description): void
    {
        if (!Cache::add($key, true, $ttlSeconds)) {
            return;
        }

        $this->write('security.alert', 'Bulk citizen data access: ' . $description);
        Log::warning('Bulk citizen data access: ' . $description);
    }

    private function write(string $action, string $description): void
    {
        try {
            DB::table('audit_logs')->insert([
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
