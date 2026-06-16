<?php
/**
 * Compact sidebar footer branding.
 */
$productName = APP_NAME;
$companyName = COMPANY_NAME;
$year = date('Y');
?>
<div class="text-[10px] text-natural-muted font-medium leading-normal pt-2 border-t border-natural-border/60">
    &copy; <?= $year ?> <?= htmlspecialchars($productName) ?><br>
    <span class="text-natural-darkmute/80">A <?= htmlspecialchars($companyName) ?> solution</span>
</div>
