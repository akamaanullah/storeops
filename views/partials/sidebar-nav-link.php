<?php
/**
 * Sidebar navigation link.
 * Required: $snHref, $snLabel, $snIcon (icon key for sidebar_icon())
 * Optional: $snActive (bool), $snBadge (int|null)
 */
require_once __DIR__ . '/sidebar-icons.php';

$snActive = !empty($snActive);
$snBadge = isset($snBadge) ? (int)$snBadge : null;
$iconKey = $snIcon ?? 'dashboard';
?>
<a
    href="<?= htmlspecialchars($snHref) ?>"
    class="sidebar-nav__link<?= $snActive ? ' sidebar-nav__link--active' : '' ?>"
    <?= $snActive ? 'aria-current="page"' : '' ?>
>
    <span class="sidebar-nav__icon-wrap">
        <?= sidebar_icon($iconKey) ?>
    </span>
    <span class="sidebar-nav__label"><?= htmlspecialchars($snLabel) ?></span>
    <?php if ($snBadge !== null): ?>
        <span class="sidebar-nav__badge rt-sidebar-notif-badge<?= $snBadge <= 0 ? ' sidebar-nav__badge--hidden' : '' ?>"><?= $snBadge > 0 ? $snBadge : '0' ?></span>
    <?php endif; ?>
</a>
