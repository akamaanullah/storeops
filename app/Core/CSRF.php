<?php
/**
 * Cross-Site Request Forgery (CSRF) Protection Security Layer
 */

namespace App\Core;

class CSRF {
    /**
     * Generate a cryptographically secure CSRF token and store it in the session.
     */
    public static function generateToken(): string {
        Auth::initSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate a token against the token stored in the session.
     */
    public static function validate(?string $token): bool {
        Auth::initSession();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
