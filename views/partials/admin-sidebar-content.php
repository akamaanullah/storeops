<?php
use App\Models\User as UserModel;

/** @var array|null $currentUser */
/** @var string $currentPath */

if (!$currentUser || ($currentUser['role'] ?? '') !== 'admin') {
    return;
}

$userId = (int)$currentUser['id'];
$isDashboard = ($currentPath === '' || $currentPath === 'index.php');
$isCreate = ($currentPath === 'jobs/create');
$isAllJobs = ($currentPath === 'jobs' || preg_match('#^jobs/page/\d+$#', $currentPath));
$isLogs = ($currentPath === 'logs' || preg_match('#^logs/page/\d+$#', $currentPath));
$isUsers = (strpos($currentPath, 'users') === 0);
$isNotifications = (strpos($currentPath, 'notifications') === 0);
$isNotificationSettings = ($currentPath === 'settings/notifications');
$isProfileSettings = ($currentPath === 'settings/profile');
$isPollingSettings = ($currentPath === 'settings/polling');
$isAnalytics = ($currentPath === 'analytics');

$unreadNotifs = UserModel::countUnreadNotifications($userId);
?>
<nav class="sidebar-nav" aria-label="Main navigation">
    <?php
    $snSectionTitle = 'Overview';
    include __DIR__ . '/sidebar-nav-section.php';

    $snHref = BASE_URL . '/';
    $snLabel = 'Dashboard';
    $snIcon = 'dashboard';
    $snActive = $isDashboard;
    include __DIR__ . '/sidebar-nav-link.php';

    $snHref = BASE_URL . '/jobs';
    $snLabel = 'All Jobs';
    $snIcon = 'jobs';
    $snActive = $isAllJobs;
    include __DIR__ . '/sidebar-nav-link.php';

    $snHref = BASE_URL . '/jobs/create';
    $snLabel = 'Create Job';
    $snIcon = 'create';
    $snActive = $isCreate;
    include __DIR__ . '/sidebar-nav-link.php';

    $snHref = BASE_URL . '/analytics';
    $snLabel = 'Analytics';
    $snIcon = 'analytics';
    $snActive = $isAnalytics;
    include __DIR__ . '/sidebar-nav-link.php';

    $snSectionTitle = 'Administration';
    include __DIR__ . '/sidebar-nav-section.php';

    $snHref = BASE_URL . '/logs';
    $snLabel = 'Audit Logs';
    $snIcon = 'logs';
    $snActive = $isLogs;
    unset($snBadge);
    include __DIR__ . '/sidebar-nav-link.php';

    $snHref = BASE_URL . '/users';
    $snLabel = 'Manage Users';
    $snIcon = 'users';
    $snActive = $isUsers;
    include __DIR__ . '/sidebar-nav-link.php';

    $snHref = BASE_URL . '/notifications/inbox';
    $snLabel = 'Notifications';
    $snIcon = 'bell';
    $snActive = $isNotifications;
    $snBadge = $unreadNotifs;
    include __DIR__ . '/sidebar-nav-link.php';

    $snSectionTitle = 'Settings';
    include __DIR__ . '/sidebar-nav-section.php';

    $snHref = BASE_URL . '/settings/profile';
    $snLabel = 'Profile';
    $snIcon = 'profile';
    $snActive = $isProfileSettings;
    unset($snBadge);
    include __DIR__ . '/sidebar-nav-link.php';

    $snHref = BASE_URL . '/settings/notifications';
    $snLabel = 'Alert Preferences';
    $snIcon = 'notifications-settings';
    $snActive = $isNotificationSettings;
    include __DIR__ . '/sidebar-nav-link.php';

    $snHref = BASE_URL . '/settings/polling';
    $snLabel = 'Polling Intervals';
    $snIcon = 'polling';
    $snActive = $isPollingSettings;
    include __DIR__ . '/sidebar-nav-link.php';
    ?>
</nav>
