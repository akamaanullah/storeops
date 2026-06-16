<?php

namespace App\Core;

class JobReference {
    public const PATTERN = '/^WO-\d{4}-\d+$/i';

    /**
     * Generate immutable reference for a new/existing job row.
     */
    public static function generate(int $jobId, ?string $createdAt = null): string {
        $year = $createdAt ? date('Y', strtotime($createdAt)) : date('Y');
        return sprintf('WO-%s-%05d', $year, $jobId);
    }

    /** @deprecated Use generate() — kept for backward compatibility */
    public static function format(int $jobId, ?string $createdAt = null): string {
        return self::generate($jobId, $createdAt);
    }

    public static function pathFromReference(string $referenceCode): string {
        return '/jobs/' . rawurlencode(strtoupper(trim($referenceCode)));
    }

    public static function isValidFormat(string $code): bool {
        return (bool)preg_match(self::PATTERN, trim($code));
    }

    /**
     * Legacy route fallback: extract numeric id from WO-YYYY-N or plain digits.
     */
    public static function legacyIdFromRoute(string $param): ?int {
        $param = trim($param);
        if ($param === '') {
            return null;
        }
        if (preg_match('/^WO-\d{4}-(\d+)$/i', $param, $matches)) {
            return (int)$matches[1];
        }
        if (ctype_digit($param)) {
            return (int)$param;
        }
        return null;
    }

    /**
     * Extract numeric job ID from notification message (legacy formats).
     */
    public static function legacyIdFromText(string $message): ?int {
        if (preg_match('/WO-\d{4}-(\d+)/i', $message, $matches)) {
            return (int)$matches[1];
        }
        if (preg_match('/job\s*#(\d+)/i', $message, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }

    /**
     * Replace legacy Job #N / stale WO codes with the canonical stored reference.
     */
    public static function normalizeLegacyMessage(string $message, string $canonicalRef, ?int $legacyJobId = null): string {
        $canonicalRef = strtoupper(trim($canonicalRef));
        $message = preg_replace('/WO-\d{4}-\d+/i', $canonicalRef, $message) ?? $message;

        if ($legacyJobId !== null) {
            $id = preg_quote((string)$legacyJobId, '/');
            $message = preg_replace(
                ['/\bjob\s*#' . $id . '\b/i', '/\bJob\s*#' . $id . '\b/'],
                $canonicalRef,
                $message
            ) ?? $message;
        }

        return $message;
    }
}
