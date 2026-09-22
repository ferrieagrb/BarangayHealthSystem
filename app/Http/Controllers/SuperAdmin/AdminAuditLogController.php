<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminAuditLogController extends Controller
{
    public function index()
    {
        $auditLogs = [];
        if (Schema::hasTable('admin_activity_logs')) {
            $auditLogs = DB::table('admin_activity_logs')
                ->join('users', 'admin_activity_logs.admin_id', '=', 'users.id')
                ->select('admin_activity_logs.*', 'users.name as admin_name', 'users.email as admin_email')
                ->orderByDesc('admin_activity_logs.created_at')
                ->paginate(25); // Paginated for a full dedicated page
        }

        return view('superadmin.audit-logs', compact('auditLogs'));
    }
}