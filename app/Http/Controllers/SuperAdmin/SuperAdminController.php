<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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

        // 3. Mock or Query Failed Login Spikes
        $failedLoginCount = Cache::get('failed_login_spikes_count', 3);

        return view('superadmin.dashboard', compact('activeSessions', 'activeUserCount', 'auditLogs', 'failedLoginCount'));
    }
}