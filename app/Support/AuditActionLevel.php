<?php

namespace App\Support;

final class AuditActionLevel
{
    public const LEVELS = ['high', 'medium', 'low', 'info', 'unclassified'];

    public static function selected(): string
    {
        $level = request()->query('action_level', 'all');

        return in_array($level, self::LEVELS, true) ? $level : 'all';
    }

    public static function applyFilter($query, ?string $column = 'action'): void
    {
        $level = self::selected();
        if ($level === 'all') {
            return;
        }

        // Activity records without an action field have no assigned level.
        if ($column === null) {
            if ($level !== 'unclassified') {
                $query->whereRaw('1 = 0');
            }
            return;
        }

        $actions = (clone $query)->reorder()->select($column)->distinct()->pluck($column)
            ->filter(fn ($action) => self::forAction($action) === $level)->all();

        $query->where(function ($query) use ($column, $actions, $level) {
            $query->whereIn($column, array_values(array_filter($actions, fn ($action) => $action !== null)));
            if ($level === 'unclassified') {
                $query->orWhereNull($column);
            }
        });
    }

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
