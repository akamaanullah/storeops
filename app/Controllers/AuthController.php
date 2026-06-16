<?php
/**
 * Auth Controller - PHP 8 Custom MVC
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Models\User;
use App\Models\ActivityLog;

class AuthController extends Controller {
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900; // 15 minutes

    public function showLogin(): void {
        if (Auth::check()) {
            $this->redirect('/');
        }
        
        Auth::initSession();
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        $this->render('auth.login', ['error' => $error]);
    }

    public function login(): void {
        Auth::initSession();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['login_error'] = 'CSRF security token verification failed. Please try again.';
            $this->redirect('/auth/login');
            return;
        }

        if ($this->isLockedOut()) {
            $_SESSION['login_error'] = 'Too many failed login attempts. Please try again in 15 minutes.';
            $this->redirect('/auth/login');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $_SESSION['login_error'] = 'Please enter both login name and password.';
            $this->redirect('/auth/login');
            return;
        }

        $user = User::findByUsername($username);

        if ($user && password_verify($password, $user->password)) {
            if ($user->status === 'suspended') {
                $_SESSION['login_error'] = 'Your account has been suspended. Please contact the administrator.';
                $this->redirect('/auth/login');
                return;
            }

            $this->clearLoginAttempts();

            Auth::login([
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'name' => $user->full_name,
                'role' => $user->role,
                'created_at' => $user->created_at
            ]);

            ActivityLog::log($user->id, null, 'login', "Logged in successfully.");

            $this->redirect('/');
        } else {
            $this->recordFailedAttempt();
            $_SESSION['login_error'] = 'Invalid login name or password. Try again.';
            $this->redirect('/auth/login');
        }
    }

    public function logout(): void {
        $user = Auth::user();
        if ($user) {
            ActivityLog::log((int)$user['id'], null, 'logout', "Logged out of application.");
        }
        Auth::logout();
        $this->redirect('/auth/login');
    }

    private function isLockedOut(): bool {
        $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'locked_until' => 0];
        return ($attempts['locked_until'] ?? 0) > time();
    }

    private function recordFailedAttempt(): void {
        $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'locked_until' => 0];
        $attempts['count'] = ($attempts['count'] ?? 0) + 1;

        if ($attempts['count'] >= self::MAX_LOGIN_ATTEMPTS) {
            $attempts['locked_until'] = time() + self::LOCKOUT_SECONDS;
            $attempts['count'] = 0;
        }

        $_SESSION['login_attempts'] = $attempts;
    }

    private function clearLoginAttempts(): void {
        unset($_SESSION['login_attempts']);
    }
}
