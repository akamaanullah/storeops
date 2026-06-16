<?php
/** @var array $pictures Each item: ['file_path' => string] */
/** @var int|null $commentId */
if (empty($pictures)) {
    return;
}
$pictureCount = count($pictures);
$showDownloadAll = $pictureCount > 1 && !empty($commentId);
?>
<div class="mt-2.5">
    <?php if ($showDownloadAll): ?>
        <div class="flex justify-end mb-2">
            <?php
                $href = BASE_URL . '/comments/' . (int)$commentId . '/attachments/download';
                $count = $pictureCount;
                include __DIR__ . '/attachment-download-all.php';
            ?>
        </div>
    <?php endif; ?>
    <div class="attachment-gallery-grid">
        <?php foreach ($pictures as $pic): ?>
            <?php
                $picUrl = ProtectedFile::url($pic['file_path']);
                $picName = basename($pic['file_path']);
                include __DIR__ . '/attachment-tile.php';
            ?>
        <?php endforeach; ?>
    </div>
</div>
