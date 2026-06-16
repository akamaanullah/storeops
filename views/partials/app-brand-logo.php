<?php
/**
 * StoreOps brand initials badge.
 *
 * @var string $brandLogoSize sidebar|mobile|login
 */
$brandLogoSize = $brandLogoSize ?? 'sidebar';
$initialsClass = match ($brandLogoSize) {
    'mobile' => 'app-brand-initials app-brand-initials--sm',
    'login' => 'app-brand-initials app-brand-initials--lg',
    default => 'app-brand-initials app-brand-initials--md',
};
?>
<span class="<?= htmlspecialchars($initialsClass) ?>"
      role="img"
      aria-label="<?= htmlspecialchars(APP_NAME) ?> logo"><?= htmlspecialchars(APP_INITIALS) ?></span>
