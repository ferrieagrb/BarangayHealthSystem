<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
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