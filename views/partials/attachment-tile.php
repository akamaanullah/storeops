<?php
/** @var string $picUrl */
/** @var string $picName */
?>
<div class="attachment-tile">
    <button type="button" class="attachment-tile-preview" onclick="openLightbox('<?= htmlspecialchars($picUrl, ENT_QUOTES) ?>')" title="Preview image">
        <img src="<?= htmlspecialchars($picUrl) ?>" alt="Attachment" referrerPolicy="no-referrer">
    </button>
    <a href="<?= htmlspecialchars($picUrl) ?>" download="<?= htmlspecialchars($picName) ?>" class="attachment-tile-download" title="Download" onclick="event.stopPropagation();">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
    </a>
</div>
