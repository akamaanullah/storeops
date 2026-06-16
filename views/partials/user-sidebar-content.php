<?php
use App\Models\User as UserModel;

/** @var array|null $currentUser */
/** @var string $currentPath */

if (!$currentUser || ($currentUser['role'] ?? '') !== 'user') {
    return;
}

$userId = (int)$currentUser['id'];
$isDashboard = ($currentPath === '' || $currentPath === 'index.php');
$isMyAssigned = ($currentPath === 'jobs/mine' || preg_match('#^jobs/mine/page/\d+$#', $currentPath));
$isAllJobs = ($currentPath === 'jobs' || preg_match('#^jobs/page/\d+$#', $currentPath));
$isNotifications = (strpos($currentPath, 'notifications') === 0);
$isNotificationSettings = ($currentPath === 'settings/notifications');
$isProfileSettings = ($currentPath === 'settings/profile');

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

    $snHref = BASE_URL . '/jobs/mine';
    $snLabel = 'My Assigned Jobs';
    $snIcon = 'mine';
    $snActive = $isMyAssigned;
    include __DIR__ . '/sidebar-nav-link.php';

    $snSectionTitle = 'Account';
    include __DIR__ . '/sidebar-nav-section.php';

    $snHref = BASE_URL . '/notifications/inbox';
    $snLabel = 'Notifications';
    $snIcon = 'bell';
    $snActive = $isNotifications;
    $snBadge = $unreadNotifs;
    include __DIR__ . '/sidebar-nav-link.php';

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
    ?>
</nav>
