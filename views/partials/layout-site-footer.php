<?php
/**
 * Main site footer branding (copyright + company attribution).
 */
$productName = APP_NAME;
$companyName = COMPANY_NAME;
$year = date('Y');
?>
<p>&copy; <?= $year ?> <?= htmlspecialchars($productName) ?>. All rights reserved.</p>
<p class="mt-2 md:mt-0 font-medium">A <?= htmlspecialchars($companyName) ?> solution.</p>
