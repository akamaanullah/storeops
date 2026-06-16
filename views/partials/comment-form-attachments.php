<?php
/** Shared comment form file input block for job show pages */
$jobRef = $job->ref();
$w9UploadUrl = BASE_URL . $job->path() . '/w9';
?>
<div class="comment-input-wrap relative">
    <textarea id="comment-textarea" name="comment" rows="2" placeholder="Write standard text update logs on active tasks..." class="w-full pl-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 rounded-xl bg-natural-bg/50 text-xs text-natural-text leading-relaxed"></textarea>

    <div class="comment-input-actions">
        <!-- Attach image button -->
        <button type="button" onclick="document.getElementById('comment-pic-input').click()" class="text-natural-muted hover:text-natural-primary focus:outline-none" title="Attach pictures">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
            </svg>
        </button>

        <!-- Attach W9 form button -->
        <button type="button" onclick="document.getElementById('w9-form-input').click()" class="text-natural-muted hover:text-emerald-600 focus:outline-none" title="Attach W9 Form (PDF, DOC, JPG, PNG)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </button>
    </div>

    <input id="comment-pic-input" type="file" name="pictures[]" multiple accept="image/*" class="hidden" onchange="previewCommentPics(this)">
    <input id="w9-form-input" type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden" onchange="handleW9Upload(this, '<?= htmlspecialchars($w9UploadUrl) ?>', '<?= htmlspecialchars(CSRF::generateToken()) ?>')">
</div>

<!-- W9 upload status indicator -->
<div id="w9-upload-status" class="hidden items-center space-x-2 text-[10px] font-semibold">
    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <span id="w9-upload-name" class="text-emerald-700 truncate max-w-[200px]"></span>
    <span class="text-natural-muted">attached as W9</span>
</div>

<div id="comment-pic-preview-container" class="hidden bg-natural-pane/50 border border-natural-border p-3 rounded-xl max-w-xs">
    <div class="flex items-center justify-between mb-2">
        <p id="comment-pic-count" class="text-[10px] font-semibold text-natural-heading"></p>
        <button type="button" onclick="clearCommentPics()" class="text-rose-500 hover:text-rose-700 font-bold text-xs focus:outline-none">&times; Clear</button>
    </div>
    <div id="comment-pic-preview-list" class="flex flex-wrap gap-2"></div>
</div>
