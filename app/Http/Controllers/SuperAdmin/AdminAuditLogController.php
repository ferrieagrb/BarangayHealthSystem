<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $source = $request->input('source', 'all');
        $category = $request->input('category', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $categoryActions = [
            'administrative_changes' => null,
            'successful_logins' => ['login.success'],
            'failed_logins' => ['login.failed'],
            'invalid_login_submissions' => ['login.validation_failed'],
            'rate_limited_attempts' => ['login.rate_limited'],
            'locked_accounts' => ['account.locked'],
            'blocked_logins' => ['login.blocked'],
            'logout_events' => ['logout'],
            'user_management' => ['user.created', 'user.deleted'],
            'account_unlocks' => ['account.unlocked'],
            'permission_changes' => ['permissions.updated'],
            'import_export_events' => ['citizens.imported', 'citizens.exported'],
        ];
        $actions = $categoryActions[$category] ?? null;
        if ($category === 'administrative_changes' && $source === 'all') {
            $source = 'administrative';
        }
        $auditLogs = collect();
        if (Schema::hasTable('admin_activity_logs') && in_array($source, ['all', 'administrative'])) {
            $adminQuery = DB::table('admin_activity_logs')
                ->join('users', 'admin_activity_logs.admin_id', '=', 'users.id')
                ->select('admin_activity_logs.*', 'users.name as admin_name', 'users.email as admin_email');
            $this->applyFilters($adminQuery, $actions, $dateFrom, $dateTo, 'admin_activity_logs');
            $auditLogs = $auditLogs->merge($adminQuery->get()
                ->map(function ($log) {
                    $log->source = 'Administrative';
                    return $log;
                }));
        }

        if (Schema::hasTable('audit_logs') && in_array($source, ['all', 'authentication'])) {
            $authQuery = DB::table('audit_logs')
                ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                ->select(
                    'audit_logs.id',
                    'audit_logs.action',
                    'audit_logs.description',
                    'audit_logs.ip_address',
                    'audit_logs.created_at',
                    'users.name as admin_name',
                    'users.email as admin_email'
                );
            $this->applyFilters($authQuery, $actions, $dateFrom, $dateTo, 'audit_logs');
            $auditLogs = $auditLogs->merge($authQuery->get()
                ->map(function ($log) {
                    $log->source = 'Authentication / Security';
                    return $log;
                }));
        }

        $auditLogs = $auditLogs->sortByDesc('created_at')->values();
        $perPage = 25;
        $page = max(1, $request->integer('page', 1));
        $auditLogs = new LengthAwarePaginator(
            $auditLogs->forPage($page, $perPage)->values(),
            $auditLogs->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('superadmin.audit-logs', compact('auditLogs'));
    }

    private function applyFilters($query, ?array $actions, ?string $dateFrom, ?string $dateTo, string $table): void
    {
        if ($actions) {
            $query->whereIn($table . '.action', $actions);
        }
        if ($dateFrom) {
            $query->whereDate($table . '.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate($table . '.created_at', '<=', $dateTo);
        }
    }
}