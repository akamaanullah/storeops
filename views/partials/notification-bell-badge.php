<?php
/** @var array $currentUser */
use App\Models\User as UserModel;

$headerUnreadCount = UserModel::countUnreadNotifications((int)$currentUser['id']);
$headerBadgeHidden = $headerUnreadCount <= 0;
$headerBadgeLabel = $headerUnreadCount > 99 ? '99+' : (string)$headerUnreadCount;
?>
<span id="notification-count-badge"
      class="notification-bell-badge <?= $headerBadgeHidden ? 'notification-bell-badge--hidden' : '' ?>"
      aria-label="<?= $headerUnreadCount ?> unread notifications"><?= htmlspecialchars($headerBadgeLabel) ?></span>
