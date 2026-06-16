<?php
/**
 * Input validation helpers
 */

namespace App\Core;

class Validator {
    public const ROLES = ['admin', 'team_lead', 'user'];
    public const USER_STATUSES = ['active', 'suspended'];
    public const JOB_STATUSES = ['New', 'Assigned', 'Scheduled', 'Work In Progress', 'Pending', 'Cancelled', 'Done'];
    public const URGENCIES = ['Within SLA', 'Urgent'];
    public const W9_VALUES = ['Yes', 'No'];
    public const PAYMENT_TYPES = ['full', 'partial', 'pending'];
    public const PAYMENT_CATEGORIES = ['client', 'vendor'];
    public const MAX_PAYMENT_AMOUNT = 999999.99;
    public const MIN_PASSWORD_LENGTH = 8;
    public const MIN_USERNAME_LENGTH = 2;
    public const MAX_USERNAME_LENGTH = 50;
    public const MIN_FULL_NAME_LENGTH = 2;
    public const MAX_FULL_NAME_LENGTH = 100;
    public const MAX_JOB_UPLOAD_FILES = 10;
    public const MAX_COMMENT_UPLOAD_FILES = 10;

    public static function inEnum(string $value, array $allowed): bool {
        return in_array($value, $allowed, true);
    }

    public static function passwordStrength(string $password): ?string {
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return 'Password must be at least ' . self::MIN_PASSWORD_LENGTH . ' characters long.';
        }
        return null;
    }

    public static function username(string $username): ?string {
        $username = trim($username);

        if ($username === '') {
            return 'Login name is required.';
        }

        $length = strlen($username);
        if ($length < self::MIN_USERNAME_LENGTH) {
            return 'Login name must be at least ' . self::MIN_USERNAME_LENGTH . ' characters long.';
        }
        if ($length > self::MAX_USERNAME_LENGTH) {
            return 'Login name cannot exceed ' . self::MAX_USERNAME_LENGTH . ' characters.';
        }
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]*[a-zA-Z0-9]$|^[a-zA-Z0-9]$/', $username)) {
            return 'Login name may only use letters, numbers, underscores, and hyphens.';
        }

        return null;
    }

    public static function fullName(string $fullName): ?string {
        $fullName = trim($fullName);

        if ($fullName === '') {
            return 'Full name is required.';
        }

        $length = mb_strlen($fullName);
        if ($length < self::MIN_FULL_NAME_LENGTH) {
            return 'Full name must be at least ' . self::MIN_FULL_NAME_LENGTH . ' characters long.';
        }
        if ($length > self::MAX_FULL_NAME_LENGTH) {
            return 'Full name cannot exceed ' . self::MAX_FULL_NAME_LENGTH . ' characters.';
        }
        if (preg_match('/\s{2,}/', $fullName)) {
            return 'Full name cannot contain consecutive spaces.';
        }
        if (!preg_match('/^[\p{L}\p{N}][\p{L}\p{N}\s.\'-]*[\p{L}\p{N}]$|^[\p{L}\p{N}]$/u', $fullName)) {
            return 'Full name may only use letters, numbers, spaces, hyphens, apostrophes, and periods.';
        }

        return null;
    }

    public static function truncatePreview(string $text, int $length = 40): string {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . '...';
    }
}
