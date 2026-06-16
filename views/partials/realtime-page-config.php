<?php
/** @var string|null $currentPath */

use App\Models\SystemPollingSettings;

$currentPath = $currentPath ?? '';
$rtContext = 'global';
$rtJobRef = '';

if ($currentPath === '' || $currentPath === 'index.php') {
    $rtContext = 'dashboard';
} elseif (
    $currentPath === 'jobs'
    || preg_match('#^jobs/page/\d+$#', $currentPath)
    || $currentPath === 'jobs/mine'
    || preg_match('#^jobs/mine/page/\d+$#', $currentPath)
) {
    $rtContext = 'jobs';
} elseif (preg_match('#^jobs/([^/]+)$#', $currentPath, $matches)) {
    $segment = $matches[1];
    if ($segment !== 'create' && $segment !== 'mine') {
        $rtContext = 'job';
        $rtJobRef = $segment;
    }
}

$polling = SystemPollingSettings::get();
$pollingMs = $polling->intervalsMs();
?>
<script>
window.__RT_CONFIG = <?= json_encode([
    'context' => $rtContext,
    'jobRef' => $rtJobRef,
    'interval' => $polling->intervalMsForContext($rtContext),
    'hiddenInterval' => $pollingMs['hidden'],
    'polling' => $pollingMs,
    'csrfToken' => \App\Core\CSRF::generateToken(),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) ?>;
</script>
