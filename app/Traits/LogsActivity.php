<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected function logAdminActivity(string $action, string $description, string $scope = 'governance', $target = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        try {
            DB::table('admin_activity_logs')->insert([
                'admin_id' => Auth::id(),
                'action' => $action,
                'scope' => $scope,
                'description' => $description,
                'target_type' => $target ? get_class($target) : null,
                'target_id' => $target ? $target->id : null,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    protected function logAuditEvent(string $action, string $description, ?int $userId = null): void
    {
        try {
            DB::table('audit_logs')->insert([
                'user_id' => $userId ?? Auth::id(),
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

    protected function logSuperadminActivity(string $action, string $description, string $scope = 'governance', $target = null, ?array $oldValues = null, ?array $newValues = null)
    {
        DB::table('admin_activity_logs')->insert([
            'admin_id' => Auth::id(),
            'action' => $action,
            'scope' => $scope,
            'description' => $description,
            'target_type' => $target ? get_class($target) : null,
            'target_id' => $target ? $target->id : null,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}