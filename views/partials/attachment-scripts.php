<script>
    const ASSET_BASE_URL = <?= json_encode(ASSET_URL) ?>;
    const BASE_APP_URL = <?= json_encode(BASE_URL) ?>;

    const PROTECTED_FILE_SERVE_BASE = <?= json_encode(rtrim(BASE_URL, '/') . '/files/serve?p=') ?>;

    function protectedFileUrl(filePath) {
        const path = String(filePath ?? '').replace(/^\//, '');
        return PROTECTED_FILE_SERVE_BASE + encodeURIComponent(path);
    }

    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        const downloadLink = document.getElementById('lightbox-download');
        if (downloadLink) {
            downloadLink.href = src;
            downloadLink.download = src.split('/').pop() || 'attachment';
        }
        document.getElementById('lightbox').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function buildAttachmentTileMarkup(picUrl, picName) {
        return `
            <div class="attachment-tile">
                <button type="button" class="attachment-tile-preview" onclick="openLightbox('${escapeHtml(picUrl)}')" title="Preview image">
                    <img src="${escapeHtml(picUrl)}" alt="Attachment" referrerPolicy="no-referrer">
                </button>
                <a href="${escapeHtml(picUrl)}" download="${escapeHtml(picName)}" class="attachment-tile-download" title="Download" onclick="event.stopPropagation();">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                </a>
            </div>
        `;
    }

    function buildDownloadAllMarkup(href, count) {
        return `
            <a href="${escapeHtml(href)}" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold text-natural-primary bg-natural-subtle border border-natural-border rounded-lg hover:bg-natural-pane transition-colors" title="Download all as ZIP">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download All (${count})
            </a>
        `;
    }

    function buildCommentAttachmentsHtml(commentId, pictures) {
        if (!pictures || pictures.length === 0) {
            return '';
        }

        const tiles = pictures.map((pic) => {
            const fullPicUrl = protectedFileUrl(pic.file_path);
            const fileName = pic.file_path.split('/').pop() || 'attachment';
            return buildAttachmentTileMarkup(fullPicUrl, fileName);
        }).join('');

        const downloadAll = pictures.length > 1
            ? `<div class="flex justify-end mb-2">${buildDownloadAllMarkup(`${BASE_APP_URL}/comments/${commentId}/attachments/download`, pictures.length)}</div>`
            : '';

        return `
            <div class="mt-2.5">
                ${downloadAll}
                <div class="attachment-gallery-grid">${tiles}</div>
            </div>
        `;
    }

    function previewCommentPics(input) {
        const previewList = document.getElementById('comment-pic-preview-list');
        previewList.innerHTML = '';

        if (!input.files || input.files.length === 0) {
            document.getElementById('comment-pic-preview-container').classList.add('hidden');
            return;
        }

        document.getElementById('comment-pic-preview-container').classList.remove('hidden');
        document.getElementById('comment-pic-count').textContent = `${input.files.length} file(s) selected`;

        Array.from(input.files).forEach((file) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'attachment-preview-thumb';
                div.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                previewList.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    function clearCommentPics() {
        document.getElementById('comment-pic-input').value = '';
        document.getElementById('comment-pic-preview-list').innerHTML = '';
        document.getElementById('comment-pic-count').textContent = '';
        document.getElementById('comment-pic-preview-container').classList.add('hidden');
    }
</script>
