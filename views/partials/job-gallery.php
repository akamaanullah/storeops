<?php
/** @var array $pictures */
/** @var object $job */
$pictureCount = count($pictures);
?>
<div class="pt-4 border-t border-natural-border">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest font-mono">Job Pictures Attachment Gallery</span>
        <?php if ($pictureCount > 1): ?>
            <?php
                $href = BASE_URL . $job->path() . '/attachments/download?scope=job';
                $count = $pictureCount;
                include __DIR__ . '/attachment-download-all.php';
            ?>
        <?php endif; ?>
    </div>

    <?php if ($pictureCount === 0): ?>
        <p class="text-[11px] text-natural-muted font-medium italic">No visual attachments verified on this work order sheet.</p>
    <?php else: ?>
        <div class="attachment-gallery-grid attachment-gallery-grid--job">
            <?php foreach ($pictures as $pic): ?>
                <?php
                    $picUrl = ProtectedFile::url($pic['file_path']);
                    $picName = basename($pic['file_path']);
                    include __DIR__ . '/attachment-tile.php';
                ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
