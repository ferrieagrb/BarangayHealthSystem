<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Models\SystemSetting;
use Ifsnop\Mysqldump\Mysqldump;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    public function index()
    {
        // 1. Active Sessions / Concurrent Logins
        $activeSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->select('users.name', 'users.email', 'sessions.ip_address', 'sessions.user_agent', 'sessions.last_activity')
            ->orderBy('sessions.last_activity', 'desc')
            ->take(5)
            ->get();

        $activeUserCount = DB::table('sessions')->whereNotNull('user_id')->distinct('user_id')->count();

        // 2. Audit Trail / Activity Logs
        $auditLogs = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->select('audit_logs.*', 'users.name as admin_name')
            ->orderBy('audit_logs.created_at', 'desc')
            ->take(6)
            ->get();

        // 3. Failed Login Spikes Tracker
        $failedLoginCount = Cache::get('failed_login_spikes_count', 3);

        return view('superadmin.dashboard', compact('activeSessions', 'activeUserCount', 'auditLogs', 'failedLoginCount'));
    }

    public function getActiveSessionsData()
{
    $activeSessions = DB::table('sessions')
        ->whereNotNull('user_id')
        ->join('users', 'sessions.user_id', '=', 'users.id')
        ->select('users.name', 'users.email', 'sessions.ip_address', 'sessions.last_activity')
        ->orderBy('sessions.last_activity', 'desc')
        ->take(5)
        ->get()
        ->map(function ($session) {
            $session->time_ago = Carbon::createFromTimestamp($session->last_activity)->diffForHumans();
            return $session;
        });

    $activeUserCount = DB::table('sessions')->whereNotNull('user_id')->distinct('user_id')->count();

    return response()->json([
        'count' => $activeUserCount,
        'sessions' => $activeSessions
    ]);
}
public function systemSettings()
    {
        return view('superadmin.settings', [
            'allowDarkMode' => SystemSetting::boolean('allow_dark_mode', true),
            'backupFrequency' => SystemSetting::value('backup_frequency', 'disabled'),
            'backupEncryption' => SystemSetting::boolean('backup_encryption', true),
            'backupDestination' => SystemSetting::value('backup_destination', 'hostinger'),
            'maxFailedLoginAttempts' => (int) SystemSetting::value('max_failed_login_attempts', 10),
            'maintenanceMode' => SystemSetting::boolean('maintenance_mode', false),
            'logoUrl' => SystemSetting::logoUrl(),
        ]);
    }

    public function updateSystemSettings(Request $request)
    {
        $validated = $request->validate([
            'backup_frequency' => ['required', 'in:disabled,daily,weekly,monthly'],
            'backup_destination' => ['required', 'in:local,hostinger'],
            'max_failed_login_attempts' => ['required', 'integer', 'min:3', 'max:20'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $settings = [
            'allow_dark_mode' => $request->boolean('allow_dark_mode') ? '1' : '0',
            'backup_frequency' => $validated['backup_frequency'],
            'backup_encryption' => $request->boolean('backup_encryption') ? '1' : '0',
            'backup_destination' => $validated['backup_destination'],
            'max_failed_login_attempts' => (string) $validated['max_failed_login_attempts'],
            'maintenance_mode' => $request->boolean('maintenance_mode') ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        if ($request->hasFile('logo')) {
            $oldLogo = SystemSetting::value('site_logo');
            $logoPath = $request->file('logo')->store('site-logo', 'public');
            SystemSetting::updateOrCreate(['key' => 'site_logo'], ['value' => $logoPath]);

            if ($oldLogo && str_starts_with($oldLogo, 'site-logo/')) {
                Storage::disk('public')->delete($oldLogo);
            }
        }

        return back()->with('success', 'System settings saved successfully.');
    }

    public function downloadBackup()
    {
        $config = config('database.connections.mysql');
        $filename = 'barangay_health_backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $temporaryFile = tempnam(sys_get_temp_dir(), 'barangay_backup_');

        if ($temporaryFile === false) {
            return back()->with('error', 'Backup failed because a temporary file could not be created.');
        }

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset'] ?? 'utf8mb4'
            );

            $dump = new Mysqldump(
                $dsn,
                $config['username'],
                $config['password'] ?? ''
            );
            $dump->start($temporaryFile);
            $backupContent = file_get_contents($temporaryFile);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Backup failed while reading the database. Verify the database connection settings.');
        } finally {
            @unlink($temporaryFile);
        }

        if ($backupContent === false) {
            return back()->with('error', 'Backup failed because the generated dump could not be read.');
        }

        $encrypted = SystemSetting::boolean('backup_encryption', true);
        $downloadFilename = $filename;

        if ($encrypted) {
            $backupContent = Crypt::encryptString($backupContent);
            $downloadFilename .= '.enc';
        }

        $destination = SystemSetting::value('backup_destination', 'hostinger');
        $backupPath = $destination === 'hostinger'
            ? $downloadFilename
            : 'backups/' . $downloadFilename;

        try {
            $disk = $destination === 'hostinger' ? 'sftp' : 'local';
            Storage::disk($disk)->put($backupPath, $backupContent);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Backup was generated but could not be stored on the configured destination.');
        }

        return response($backupContent, 200, [
            'Content-Type' => $encrypted ? 'application/octet-stream' : 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $downloadFilename . '"',
        ]);
    }
}