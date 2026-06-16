<?php
/**
 * Authentication and Role Management
 */

namespace App\Core;

use App\Models\User;

class Auth {
    /**
     * Start PHP session securely if not already started.
     */
    public static function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session cookie parameters to mitigate XSS and CSRF
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    /**
     * Get the logged-in user or null.
     */
    public static function user(): ?array {
        self::initSession();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if a user is authenticated.
     */
    public static function check(): bool {
        return self::user() !== null;
    }

    /**
     * Get the current user's role.
     */
    public static function role(): ?string {
        $user = self::user();
        return $user['role'] ?? null;
    }

    /**
     * Login a user by setting their session.
     */
    public static function login(array $user): void {
        self::initSession();
        session_regenerate_id(true);
        // Remove password from session for safety
        unset($user['password']);
        $_SESSION['user'] = $user;
    }

    /**
     * Terminate the session (sign out).
     */
    public static function logout(): void {
        self::initSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Middleware: Require authentication and compile options for roles.
     * Redirects to login if unauthenticated or unauthorized.
     */
    public static function middleware(array|string $allowedRoles = []): void {
        if (!self::check()) {
            header("Location: " . rtrim(BASE_URL, '/') . "/auth/login");
            exit;
        }

        self::ensureActiveUser();

        if (empty($allowedRoles)) {
            return;
        }

        if (is_string($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        $userRole = self::role();
        if (!in_array($userRole, $allowedRoles)) {
            self::denyAccess('You do not have permission to access that page.');
        }
    }

    /**
     * Handle forbidden access: JSON for API requests, flash + redirect for browser.
     */
    public static function denyAccess(string $message = 'You do not have permission to access this page.'): never {
        self::initSession();
        $_SESSION['flash_error'] = $message;

        if (self::wantsJsonRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $message]);
            exit;
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/');
        exit;
    }

    private static function wantsJsonRequest(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
    }

    /**
     * Confirm the session user still exists and is active; refresh profile/role from DB.
     */
    private static function ensureActiveUser(): void {
        $sessionUser = self::user();
        if ($sessionUser === null) {
            return;
        }

        $userId = (int)($sessionUser['id'] ?? 0);
        if ($userId <= 0) {
            self::endSessionWithLoginError('Your session is invalid. Please sign in again.');
        }

        $user = User::find($userId);
        if ($user === null) {
            self::endSessionWithLoginError('Your account is no longer available. Please contact the administrator.');
        }

        if (($user->status ?? 'active') === 'suspended') {
            self::endSessionWithLoginError('Your account has been suspended. Please contact the administrator.');
        }

        self::syncSessionUser($user);
    }

    private static function syncSessionUser(User $user): void {
        self::initSession();
        $_SESSION['user'] = [
            'id' => $user->id,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'name' => $user->full_name,
            'role' => $user->role,
            'created_at' => $user->created_at,
        ];
    }

    private static function endSessionWithLoginError(string $message): never {
        self::logout();
        self::initSession();
        $_SESSION['login_error'] = $message;

        if (self::wantsJsonRequest()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $message,
                'redirect' => rtrim(BASE_URL, '/') . '/auth/login',
            ]);
            exit;
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/auth/login');
        exit;
    }
}
