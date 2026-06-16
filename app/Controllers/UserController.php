<?php
/**
 * User and Logs Controller - PHP 8 Custom MVC
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Validator;
use App\Models\User;
use App\Models\ActivityLog;

class UserController extends Controller {
    public function notifications(): void {
        Auth::middleware();
        $currentUser = Auth::user();
        $userId = (int)$currentUser['id'];

        $list = User::withResolvedJobIds(User::getNotifications($userId));

        $this->json([
            'notifications' => $list,
            'unreadCount' => User::countUnreadNotifications($userId),
        ]);
    }

    public function markAllRead(): void {
        Auth::middleware();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            if ($this->wantsJson()) {
                $this->json(['success' => false, 'error' => 'CSRF token verification failed.'], 403);
            }
            http_response_code(403);
            die('CSRF token verification failed.');
        }

        User::markNotificationsRead((int)Auth::user()['id']);

        if ($this->wantsJson()) {
            $this->json(['success' => true]);
            return;
        }

        $this->redirect('/notifications/inbox');
    }

    public function markOneRead(): void {
        Auth::middleware();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die('CSRF token verification failed.');
        }

        $userId = (int)Auth::user()['id'];
        $notifId = (int)($_POST['notification_id'] ?? 0);

        if ($notifId > 0) {
            User::markNotificationsRead($userId, $notifId);
        }

        $this->redirect('/notifications/inbox');
    }

    public function logs(?string $page = null): void {
        Auth::middleware('admin');

        if ($page === null && isset($_GET['page'])) {
            $legacyPage = max(1, (int)$_GET['page']);
            $this->redirect($legacyPage > 1 ? '/logs/page/' . $legacyPage : '/logs');
            return;
        }

        $pageNum = max(1, (int)($page ?? 1));
        $result = ActivityLog::listPaginated($pageNum);

        $this->render('logs.index', [
            'logs' => $result['items'],
            'pagination' => $result,
            'paginationRoute' => '/logs',
            'itemLabel' => 'log entries',
            'user' => Auth::user()
        ]);
    }

    public function usersIndex(): void {
        Auth::middleware('admin');
        $users = User::all();
        $this->render('users.index', [
            'users' => $users,
            'user' => Auth::user()
        ]);
    }

    public function checkLoginName(): void {
        Auth::middleware('admin');

        $username = trim($_GET['username'] ?? $_GET['name'] ?? '');
        $excludeId = max(0, (int)($_GET['exclude_id'] ?? 0));

        if ($username === '') {
            $this->json(['available' => false, 'error' => 'Login name is required.']);
            return;
        }

        if ($formatError = Validator::username($username)) {
            $this->json(['available' => false, 'error' => $formatError]);
            return;
        }

        $taken = User::usernameTaken($username, $excludeId > 0 ? $excludeId : null);

        $this->json([
            'available' => !$taken,
            'error' => $taken ? 'This login name is already in use.' : null,
        ]);
    }

    public function usersCreate(): void {
        Auth::middleware('admin');
        Auth::initSession();
        $error = $_SESSION['user_create_error'] ?? null;
        $usernameError = $_SESSION['user_create_username_error'] ?? null;
        $old = $_SESSION['user_create_old'] ?? null;
        unset($_SESSION['user_create_error'], $_SESSION['user_create_username_error'], $_SESSION['user_create_old']);

        $this->render('users.create', [
            'user' => Auth::user(),
            'error' => $error,
            'usernameError' => $usernameError,
            'old' => $old
        ]);
    }

    public function usersStore(): void {
        Auth::middleware('admin');
        Auth::initSession();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['user_create_error'] = 'CSRF token verification failed.';
            $this->redirect('/users/create');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active';
        $oldPayload = ['username' => $username, 'full_name' => $fullName, 'role' => $role, 'status' => $status];

        if ($username === '') {
            $_SESSION['user_create_username_error'] = 'Login name is required.';
            $_SESSION['user_create_old'] = $oldPayload;
            $this->redirect('/users/create');
            return;
        }

        if ($fullName === '') {
            $_SESSION['user_create_error'] = 'Full name is required.';
            $_SESSION['user_create_old'] = $oldPayload;
            $this->redirect('/users/create');
            return;
        }

        if ($password === '') {
            $_SESSION['user_create_error'] = 'Password is required.';
            $_SESSION['user_create_old'] = $oldPayload;
            $this->redirect('/users/create');
            return;
        }

        if ($usernameError = Validator::username($username)) {
            $_SESSION['user_create_username_error'] = $usernameError;
            $_SESSION['user_create_old'] = $oldPayload;
            $this->redirect('/users/create');
            return;
        }

        if ($fullNameError = Validator::fullName($fullName)) {
            $_SESSION['user_create_error'] = $fullNameError;
            $_SESSION['user_create_old'] = $oldPayload;
            $this->redirect('/users/create');
            return;
        }

        if ($passwordError = Validator::passwordStrength($password)) {
            $_SESSION['user_create_error'] = $passwordError;
            $_SESSION['user_create_old'] = $oldPayload;
            $this->redirect('/users/create');
            return;
        }

        if (!Validator::inEnum($role, Validator::ROLES) || !Validator::inEnum($status, Validator::USER_STATUSES)) {
            $_SESSION['user_create_error'] = 'Invalid role or account status submitted.';
            $this->redirect('/users/create');
            return;
        }

        if (User::usernameTaken($username)) {
            $_SESSION['user_create_username_error'] = 'This login name is already in use.';
            $_SESSION['user_create_old'] = $oldPayload;
            $this->redirect('/users/create');
            return;
        }

        $newUser = new User(
            null,
            $username,
            $fullName,
            password_hash($password, PASSWORD_BCRYPT),
            $role,
            null,
            $status
        );

        if ($newUser->save()) {
            ActivityLog::log((int)Auth::user()['id'], null, 'user_create', "Created user account for {$fullName} (@{$username}) as {$role}.");
            $this->redirect('/users');
        } else {
            $_SESSION['user_create_error'] = 'Failed to create user. Database error.';
            $_SESSION['user_create_old'] = $oldPayload;
            $this->redirect('/users/create');
        }
    }

    public function usersEdit(string $id): void {
        Auth::middleware('admin');
        $userId = (int)$id;
        $targetUser = User::find($userId);

        if (!$targetUser) {
            http_response_code(404);
            die("User not found.");
        }

        Auth::initSession();
        $error = $_SESSION['user_edit_error'] ?? null;
        $usernameError = $_SESSION['user_edit_username_error'] ?? null;
        $old = $_SESSION['user_edit_old'] ?? null;
        unset($_SESSION['user_edit_error'], $_SESSION['user_edit_username_error'], $_SESSION['user_edit_old']);

        $this->render('users.edit', [
            'user' => Auth::user(),
            'targetUser' => $targetUser,
            'error' => $error,
            'usernameError' => $usernameError,
            'old' => $old,
        ]);
    }

    public function usersUpdate(string $id): void {
        Auth::middleware('admin');
        $userId = (int)$id;
        $targetUser = User::find($userId);

        if (!$targetUser) {
            http_response_code(404);
            die("User not found.");
        }

        Auth::initSession();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['user_edit_error'] = 'CSRF token verification failed.';
            $this->redirect("/users/{$userId}/edit");
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active';
        $oldPayload = ['username' => $username, 'full_name' => $fullName, 'role' => $role, 'status' => $status];

        if ($username === '') {
            $_SESSION['user_edit_username_error'] = 'Login name is required.';
            $_SESSION['user_edit_old'] = $oldPayload;
            $this->redirect("/users/{$userId}/edit");
            return;
        }

        if ($fullName === '') {
            $_SESSION['user_edit_error'] = 'Full name is required.';
            $_SESSION['user_edit_old'] = $oldPayload;
            $this->redirect("/users/{$userId}/edit");
            return;
        }

        if ($usernameError = Validator::username($username)) {
            $_SESSION['user_edit_username_error'] = $usernameError;
            $_SESSION['user_edit_old'] = $oldPayload;
            $this->redirect("/users/{$userId}/edit");
            return;
        }

        if ($fullNameError = Validator::fullName($fullName)) {
            $_SESSION['user_edit_error'] = $fullNameError;
            $_SESSION['user_edit_old'] = $oldPayload;
            $this->redirect("/users/{$userId}/edit");
            return;
        }

        if (!Validator::inEnum($role, Validator::ROLES) || !Validator::inEnum($status, Validator::USER_STATUSES)) {
            $_SESSION['user_edit_error'] = 'Invalid role or account status submitted.';
            $this->redirect("/users/{$userId}/edit");
            return;
        }

        if (!empty($password) && ($passwordError = Validator::passwordStrength($password))) {
            $_SESSION['user_edit_error'] = $passwordError;
            $this->redirect("/users/{$userId}/edit");
            return;
        }

        if (User::usernameTaken($username, $userId)) {
            $_SESSION['user_edit_username_error'] = 'This login name is already in use.';
            $_SESSION['user_edit_old'] = $oldPayload;
            $this->redirect("/users/{$userId}/edit");
            return;
        }

        $targetUser->username = $username;
        $targetUser->full_name = $fullName;
        $targetUser->role = $role;
        $targetUser->status = $status;

        if (!empty($password)) {
            $targetUser->password = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($targetUser->save()) {
            ActivityLog::log((int)Auth::user()['id'], null, 'user_edit', "Updated user account for {$fullName} (@{$username}).");
            $this->redirect('/users');
        } else {
            $_SESSION['user_edit_error'] = 'Failed to update user details. Database error.';
            $this->redirect("/users/{$userId}/edit");
        }
    }

    public function usersDelete(string $id): void {
        Auth::middleware('admin');
        $userId = (int)$id;
        $currentUser = Auth::user();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token verification failed.");
        }

        if ($userId === (int)$currentUser['id']) {
            http_response_code(400);
            die("Cannot delete your own administrator session account.");
        }

        $targetUser = User::find($userId);
        if ($targetUser && $targetUser->delete()) {
            ActivityLog::log((int)$currentUser['id'], null, 'user_delete', "Deleted user account {$targetUser->full_name} (@{$targetUser->username}).");
        }

        $this->redirect('/users');
    }

    public function usersToggleStatus(string $id): void {
        Auth::middleware('admin');
        $userId = (int)$id;
        $currentUser = Auth::user();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token verification failed.");
        }

        if ($userId === (int)$currentUser['id']) {
            http_response_code(400);
            die("Cannot suspend your own administrator session account.");
        }

        $targetUser = User::find($userId);
        if ($targetUser) {
            $newStatus = ($targetUser->status === 'suspended') ? 'active' : 'suspended';
            $db = (new User())->getDB();
            $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
            if ($stmt->execute([$newStatus, $userId])) {
                ActivityLog::log((int)$currentUser['id'], null, 'user_status_change', "Changed user {$targetUser->full_name} (@{$targetUser->username}) status to {$newStatus}.");
            }
        }

        $this->redirect('/users');
    }

    public function inbox(?string $page = null): void {
        Auth::middleware();
        $currentUser = Auth::user();

        if ($page === null && isset($_GET['page'])) {
            $legacyPage = max(1, (int)$_GET['page']);
            $this->redirect($legacyPage > 1 ? '/notifications/inbox/page/' . $legacyPage : '/notifications/inbox');
            return;
        }

        $pageNum = max(1, (int)($page ?? 1));
        $result = User::getNotificationsPaginated((int)$currentUser['id'], $pageNum);
        $notifications = User::withResolvedJobIds($result['items']);

        $this->render('notifications.inbox', [
            'notifications' => $notifications,
            'pagination' => $result,
            'paginationRoute' => '/notifications/inbox',
            'itemLabel' => 'notifications',
            'user' => $currentUser
        ]);
    }
}
