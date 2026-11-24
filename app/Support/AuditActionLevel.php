<?php

namespace App\Support;

final class AuditActionLevel
{
    public static function forAction(?string $action): string
    {
        $action = strtolower(trim($action ?? ''));
        $words = preg_split('/[^a-z0-9]+/', $action, -1, PREG_SPLIT_NO_EMPTY);

        // Check sensitive actions before general update/success classifications.
        if (in_array($action, ['permissions.updated', 'user.password_reset'], true)
            || array_intersect($words, ['delete', 'deleted', 'purge', 'purged', 'destroy', 'destroyed', 'blocked', 'locked', 'critical', 'alert', 'exception'])) {
            return 'high';
        }

        if (array_intersect($words, ['archive', 'archived', 'update', 'updated', 'edit', 'edited', 'changed', 'import', 'imported', 'export', 'exported', 'withdraw', 'withdrawn', 'failed', 'denied', 'reset', 'unlocked', 'disabled', 'rejected', 'revoked', 'rate', 'limited'])) {
            return 'medium';
        }

        if (array_intersect($words, ['create', 'created', 'add', 'added', 'deposit', 'deposited', 'restore', 'restored', 'approved', 'success', 'fulfilled', 'claimed', 'posted', 'released'])) {
            return 'low';
        }

        if (array_intersect($words, ['view', 'viewed', 'read', 'login', 'logout'])) {
            return 'info';
        }

        return 'unclassified';
    }
}
