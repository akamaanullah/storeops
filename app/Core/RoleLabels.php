<?php

namespace App\Core;

class RoleLabels {
    private const LABELS = [
        'admin' => 'Administrator',
        'team_lead' => 'Team Lead',
        'user' => 'User',
    ];

    public static function label(?string $role): string {
        return self::LABELS[$role ?? ''] ?? ucfirst(str_replace('_', ' ', (string)($role ?? 'User')));
    }
}
