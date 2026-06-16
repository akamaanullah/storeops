<?php
/**
 * StoreOps - Front Controller (public/index.php)
 * All incoming requests route through this entry point.
 */

// 1. Set default development error reporting (will be overridden below after config loads)
error_reporting(E_ALL);
ini_set('display_errors', 1);


// 2. Define Root Path constant
define('ROOT_PATH', dirname(__DIR__));

// 3. PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 4. Load Environment Variables
use App\Core\DotEnv;
$envPath = ROOT_PATH . '/.env';
if (file_exists($envPath)) {
    (new DotEnv($envPath))->load();
}

// 5. Load Unified Configuration
require_once ROOT_PATH . '/config/config.php';

// Ensure writable storage paths exist
$logsDir = ROOT_PATH . '/storage/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// Adjust error reporting dynamically based on environment configuration
if (defined('APP_ENV') && APP_ENV === 'production') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/storage/logs/error.log');
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// 6. Global Class Aliases for Views compatibility
class_alias('App\Core\Auth', 'Auth');
class_alias('App\Core\Controller', 'Controller');
class_alias('App\Core\Model', 'Model');
class_alias('App\Core\Router', 'Router');
class_alias('App\Core\CSRF', 'CSRF');
class_alias('App\Core\Validator', 'Validator');
class_alias('App\Core\ProtectedFile', 'ProtectedFile');
class_alias('App\Models\User', 'User');
class_alias('App\Models\Job', 'Job');
class_alias('App\Models\Comment', 'Comment');
class_alias('App\Models\Payment', 'Payment');
class_alias('App\Models\ActivityLog', 'ActivityLog');

// 7. Fire up secure auth session environment
Auth::initSession();

// 8. Define Routes
$router = new Router();

// Dashboard Route
$router->get('', 'DashboardController@index');
$router->get('analytics', 'AnalyticsController@index');

// Authentication Routes
$router->get('auth/login', 'AuthController@showLogin');
$router->post('auth/login', 'AuthController@login');
$router->get('auth/logout', 'AuthController@logout');

// Protected uploads (login required)
$router->get('files/serve', 'FileController@serve');

// Job Routes
$router->get('jobs/mine/page/{page}', 'JobController@myAssigned');
$router->get('jobs/mine', 'JobController@myAssigned');
$router->get('jobs/page/{page}', 'JobController@index');
$router->get('jobs', 'JobController@index');
$router->get('jobs/create', 'JobController@create');
$router->post('jobs/create', 'JobController@store');
$router->get('jobs/{id}/comments', 'CommentController@listForJob');
$router->get('jobs/{id}', 'JobController@show');
$router->get('jobs/{id}/attachments/download', 'JobController@downloadAttachments');
$router->post('jobs/{id}/complete', 'JobController@markComplete');
$router->post('jobs/{id}/read', 'JobController@markRead');
$router->post('jobs/{id}/total-amount', 'JobController@updateTotalAmount');
$router->post('jobs/{id}/vendor-amount', 'JobController@updateVendorAmount');
$router->post('jobs/{id}/delete', 'JobController@delete');

// Comments and Comment Votes Routes
$router->post('jobs/{id}/comment', 'CommentController@store');
$router->get('comments/{id}/attachments/download', 'CommentController@downloadAttachments');
$router->post('comments/{id}/vote', 'CommentController@vote');
$router->post('comments/{id}/edit', 'CommentController@edit');

// Payments Route
$router->post('jobs/{id}/payment', 'CommentController@addPayment');
$router->post('payments/{id}/edit', 'CommentController@editPayment');

// W9 Form Document Routes
$router->post('jobs/{id}/w9', 'CommentController@uploadW9');
$router->post('jobs/{id}/w9/delete', 'CommentController@deleteW9');

$router->get('settings/notifications', 'SettingsController@notifications');
$router->post('settings/notifications', 'SettingsController@saveNotifications');
$router->get('settings/profile', 'SettingsController@profile');
$router->post('settings/profile', 'SettingsController@saveProfile');
$router->get('settings/polling', 'SettingsController@polling');
$router->post('settings/polling', 'SettingsController@savePolling');

// Notifications JSON list Route
$router->get('api/updates', 'UpdatesController@poll');
$router->get('notifications', 'UserController@notifications');
$router->post('notifications/mark-read', 'UserController@markAllRead');
$router->post('notifications/mark-one-read', 'UserController@markOneRead');
$router->get('notifications/inbox/page/{page}', 'UserController@inbox');
$router->get('notifications/inbox', 'UserController@inbox');

// Activity Logs Route (admin only)
$router->get('logs/page/{page}', 'UserController@logs');
$router->get('logs', 'UserController@logs');

// User Management Routes (admin only)
$router->get('api/users/check-login-name', 'UserController@checkLoginName');
$router->get('users', 'UserController@usersIndex');
$router->get('users/create', 'UserController@usersCreate');
$router->post('users/create', 'UserController@usersStore');
$router->get('users/{id}/edit', 'UserController@usersEdit');
$router->post('users/{id}/edit', 'UserController@usersUpdate');
$router->post('users/{id}/delete', 'UserController@usersDelete');
$router->post('users/{id}/status', 'UserController@usersToggleStatus');

// 9. Dispatch the Request
$router->dispatch();
