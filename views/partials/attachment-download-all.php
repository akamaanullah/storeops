<?php
/** @var string $href */
/** @var int $count */
?>
<a href="<?= htmlspecialchars($href) ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold text-natural-primary bg-natural-subtle border border-natural-border rounded-lg hover:bg-natural-pane transition-colors" title="Download all as ZIP">
    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
    </svg>
    Download All (<?= (int)$count ?>)
</a>
