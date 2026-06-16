<?php
/** @var array|null $pagination */
/** @var string|null $jobsRoute */
/** @var string|null $paginationRoute */
/** @var string|null $itemLabel */
if (empty($pagination) || ($pagination['total_pages'] ?? 1) <= 1) {
    return;
}

$route = $paginationRoute ?? $jobsRoute ?? '/jobs';
$itemLabel = $itemLabel ?? 'jobs';
$queryParams = $_GET;
unset($queryParams['page']);

$buildPageUrl = function (int $pageNum) use ($queryParams, $route): string {
    $path = BASE_URL . rtrim($route, '/');
    if ($pageNum > 1) {
        $path .= '/page/' . $pageNum;
    }
    if (!empty($queryParams)) {
        $path .= '?' . http_build_query($queryParams);
    }
    return $path;
};
?>
<div class="px-6 py-4 border-t border-natural-border flex flex-col sm:flex-row justify-between items-center gap-3 text-xs bg-natural-pane/20">
    <span class="text-natural-muted font-medium">
        Page <?= (int)$pagination['page'] ?> of <?= (int)$pagination['total_pages'] ?>
        (<?= (int)$pagination['total'] ?> total <?= htmlspecialchars($itemLabel) ?>)
    </span>
    <div class="flex items-center gap-2">
        <?php if ($pagination['page'] > 1): ?>
            <a href="<?= $buildPageUrl($pagination['page'] - 1) ?>" class="px-3 py-1.5 border border-natural-border rounded-lg hover:bg-natural-subtle font-bold text-natural-primary">Previous</a>
        <?php endif; ?>
        <?php if ($pagination['page'] < $pagination['total_pages']): ?>
            <a href="<?= $buildPageUrl($pagination['page'] + 1) ?>" class="px-3 py-1.5 border border-natural-border rounded-lg hover:bg-natural-subtle font-bold text-natural-primary">Next</a>
        <?php endif; ?>
    </div>
</div>
