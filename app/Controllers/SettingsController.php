<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Validator;
use App\Models\User;
use App\Models\UserNotificationSettings;
use App\Models\SystemPollingSettings;

class SettingsController extends Controller {
    public function notifications(): void {
        Auth::middleware();

        $userId = (int)Auth::user()['id'];
        $settings = UserNotificationSettings::forUser($userId);

        $this->render('settings.notifications', [
            'settings' => $settings,
            'user' => Auth::user(),
        ]);
    }

    public function saveNotifications(): void {
        Auth::middleware();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Auth::denyAccess('Security token expired. Please try again.');
            return;
        }

        $userId = (int)Auth::user()['id'];

        $browserEnabled = isset($_POST['browser_enabled']) && (int)$_POST['browser_enabled'] === 1;

        $settings = new UserNotificationSettings(
            (int)$userId,
            $browserEnabled,
            $browserEnabled && isset($_POST['notify_job_assign']),
            $browserEnabled && isset($_POST['notify_status_update']),
            $browserEnabled && isset($_POST['notify_comments']),
            $browserEnabled && isset($_POST['notify_votes'])
        );

        if ($settings->save()) {
            Auth::initSession();
            $_SESSION['flash_success'] = 'Notification settings saved successfully.';
        } else {
            Auth::initSession();
            $_SESSION['flash_error'] = 'Could not save notification settings. Ensure the database schema is up to date.';
        }

        $this->redirect('/settings/notifications');
    }

    public function polling(): void {
        Auth::middleware('admin');

        $this->render('settings.polling', [
            'settings' => SystemPollingSettings::get(),
            'user' => Auth::user(),
        ]);
    }

    public function savePolling(): void {
        Auth::middleware('admin');

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Auth::denyAccess('Security token expired. Please try again.');
            return;
        }

        $settings = SystemPollingSettings::fromInput($_POST);
        $adminId = (int)Auth::user()['id'];

        if ($settings->save($adminId)) {
            Auth::initSession();
            $_SESSION['flash_success'] = 'Polling intervals saved. All users will use the new timings on their next update check.';
        } else {
            Auth::initSession();
            $_SESSION['flash_error'] = 'Could not save polling settings. Ensure the database schema is up to date.';
        }

        $this->redirect('/settings/polling');
    }

    public function profile(): void {
        Auth::middleware();

        $this->render('settings.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function saveProfile(): void {
        Auth::middleware();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Auth::denyAccess('Security token expired. Please try again.');
            return;
        }

        $sessionUser = Auth::user();
        $userId = (int)$sessionUser['id'];
        $fullName = trim($_POST['full_name'] ?? '');
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['new_password_confirmation'] ?? '');

        Auth::initSession();

        if ($fullNameError = Validator::fullName($fullName)) {
            $_SESSION['flash_error'] = $fullNameError;
            $this->redirect('/settings/profile');
            return;
        }

        $dbUser = User::findWithPassword($userId);
        if (!$dbUser) {
            $_SESSION['flash_error'] = 'Could not load your account. Please sign in again.';
            $this->redirect('/settings/profile');
            return;
        }

        $changingPassword = $currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '';

        if ($changingPassword) {
            if ($currentPassword === '') {
                $_SESSION['flash_error'] = 'Enter your current password to set a new one.';
                $this->redirect('/settings/profile');
                return;
            }

            if (!password_verify($currentPassword, (string)$dbUser->password)) {
                $_SESSION['flash_error'] = 'Current password is incorrect.';
                $this->redirect('/settings/profile');
                return;
            }

            if ($passwordError = Validator::passwordStrength($newPassword)) {
                $_SESSION['flash_error'] = $passwordError;
                $this->redirect('/settings/profile');
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['flash_error'] = 'New password and confirmation do not match.';
                $this->redirect('/settings/profile');
                return;
            }
        }

        $user = User::find($userId);
        if (!$user) {
            $_SESSION['flash_error'] = 'Account not found.';
            $this->redirect('/settings/profile');
            return;
        }

        $user->full_name = $fullName;
        if ($changingPassword) {
            $user->password = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        if ($user->save()) {
            $_SESSION['user']['full_name'] = $fullName;
            $_SESSION['user']['name'] = $fullName;
            $_SESSION['flash_success'] = $changingPassword
                ? 'Profile and password updated successfully.'
                : 'Profile updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Could not save profile. Please try again.';
        }

        $this->redirect('/settings/profile');
    }
}
