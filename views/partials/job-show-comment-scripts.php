<!-- AJAX comment + vote scripts -->
<script>
    const COMMENTS_BASE_URL = '<?= BASE_URL ?>';
    const COMMENTS_PER_PAGE = <?= \App\Models\Comment::PER_PAGE ?>;

    function buildCommentHtml(c, isUnread = false) {
        const pictures = c.pictures && c.pictures.length
            ? c.pictures
            : (c.picture_path ? [{ file_path: c.picture_path }] : []);
        const pictureHtml = buildCommentAttachmentsHtml(c.id, pictures);
        const initialLetters = escapeHtml(c.user_name.trim().substring(0, 2).toUpperCase());
        const commentBody = c.comment ? `<p class="text-natural-darkmute leading-relaxed text-xs comment-body-text">${escapeHtml(c.comment)}</p>` : '';
        const unreadClass = isUnread ? ' comment-unread' : '';
        const newBadge = isUnread
            ? '<span class="ml-1.5 px-1.5 py-0.5 bg-natural-subtle text-natural-primary font-bold uppercase text-[8px] rounded border border-natural-border">New</span>'
            : '';
        const likeActive = c.user_vote === 'like';
        const dislikeActive = c.user_vote === 'dislike';

        const section = document.getElementById('comments-section');
        const currentUserId = section ? parseInt(section.dataset.rtCurrentUserId || '0', 10) : 0;
        const isOwner = currentUserId > 0 && parseInt(c.user_id || '0', 10) === currentUserId;

        const editButtonHtml = isOwner
            ? `<span class="text-natural-border/60 select-none text-[10px]">•</span>
               <button onclick="startEditComment(${c.id}, this)" class="text-natural-muted hover:text-natural-primary font-medium focus:outline-none transition-colors text-[11px]">Edit</button>`
            : '';

        return `
            <div class="flex items-start space-x-4 p-4 border border-natural-border rounded-2xl hover:bg-natural-pane/30 transition-colors text-xs text-natural-text leading-relaxed${unreadClass}" data-comment-id="${c.id}">
                <div class="w-8 h-8 rounded-full bg-natural-pane border border-natural-border flex items-center justify-center font-bold text-xs shrink-0 text-natural-primary uppercase">
                    ${initialLetters}
                </div>
                <div class="flex-1 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-bold text-natural-heading text-xs">${escapeHtml(c.user_name)}</span>
                            <span class="ml-1.5 px-2 py-0.5 bg-natural-pane text-natural-primary font-bold uppercase text-[8px] rounded border border-natural-border/50">${escapeHtml(c.user_role)}</span>
                            ${newBadge}
                        </div>
                        <span class="text-[10px] text-natural-muted">${escapeHtml(c.created_at)}</span>
                    </div>
                    <div class="comment-text-wrapper space-y-2">
                        ${commentBody}
                    </div>
                    ${pictureHtml}
                    <div class="flex items-center space-x-4 pt-1">
                        <button onclick="castVote(${c.id}, 'like', this)" class="inline-flex items-center space-x-1.5 text-natural-muted hover:text-natural-primary font-medium select-none focus:outline-none transition-colors${likeActive ? ' text-natural-primary font-bold' : ''}">
                            <svg class="w-4 h-4 stroke-current ${likeActive ? 'text-natural-primary fill-natural-primary' : 'text-natural-muted fill-none'}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2m0 10V10"></path>
                            </svg>
                            <span class="likes-count text-[11px]">${c.likes}</span>
                        </button>
                        <button onclick="castVote(${c.id}, 'dislike', this)" class="inline-flex items-center space-x-1.5 text-natural-muted hover:text-natural-primary font-medium select-none focus:outline-none transition-colors${dislikeActive ? ' text-natural-primary font-bold' : ''}">
                            <svg class="w-4 h-4 stroke-current ${dislikeActive ? 'text-natural-primary fill-natural-primary' : 'text-natural-muted fill-none'}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v5a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m7-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2m0-10V4"></path>
                            </svg>
                            <span class="dislikes-count text-[11px]">${c.dislikes}</span>
                        </button>
                        ${editButtonHtml}
                    </div>
                </div>
            </div>
        `;
    }

    document.getElementById('comment-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;
        const textarea = document.getElementById('comment-textarea');
        const fileInput = document.getElementById('comment-pic-input');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        if (!textarea.value.trim() && (!fileInput.files || fileInput.files.length === 0)) {
            alert('Please enter a comment or attach at least one image.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Posting...';

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (!response.ok) {
                const errData = await response.json();
                alert(errData.error || 'Failed to submit comment.');
                return;
            }

            const res = await response.json();
            if (res.success) {
                form.reset();
                clearCommentPics();

                const timeline = document.getElementById('comments-timeline');
                if (!timeline.querySelector('[data-comment-id]')) {
                    timeline.innerHTML = '';
                }

                const unreadBanner = timeline.querySelector('.comments-unread-banner');
                if (unreadBanner) {
                    unreadBanner.remove();
                }

                // Mark all existing comments on page as read in UI
                timeline.querySelectorAll('.comment-unread').forEach(el => {
                    el.classList.remove('comment-unread');
                    el.querySelector('span.bg-natural-subtle')?.remove();
                });

                // Remove unread separators
                timeline.querySelectorAll('.comments-unread-separator').forEach(el => el.remove());

                timeline.insertAdjacentHTML('afterbegin', buildCommentHtml(res.comment, false));

                const section = document.getElementById('comments-section');
                if (section && res.comment && res.comment.id) {
                    section.dataset.rtLatestCommentId = String(res.comment.id);
                }
            }
        } catch (err) {
            console.error("Comment submit error:", err);
            alert("An unexpected error occurred. Please try again.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });

    const loadMoreBtn = document.getElementById('comments-load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', async function() {
            const btn = this;
            const jobRef = btn.dataset.jobRef;
            const offset = parseInt(btn.dataset.offset || '0', 10);
            const originalText = btn.textContent;

            btn.disabled = true;
            btn.textContent = 'Loading...';

            try {
                const url = `${COMMENTS_BASE_URL}/jobs/${encodeURIComponent(jobRef)}/comments?offset=${offset}&limit=${COMMENTS_PER_PAGE}`;
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    alert('Failed to load more comments.');
                    return;
                }

                const res = await response.json();
                if (!res.success || !res.comments.length) {
                    document.getElementById('comments-load-more-wrap')?.classList.add('hidden');
                    return;
                }

                const timeline = document.getElementById('comments-timeline');
                const fragment = res.comments.map(c => buildCommentHtml(c, false)).join('');
                timeline.insertAdjacentHTML('beforeend', fragment);

                btn.dataset.offset = String(res.nextOffset);

                if (!res.hasMore) {
                    document.getElementById('comments-load-more-wrap')?.classList.add('hidden');
                }
            } catch (err) {
                console.error('Load more comments error:', err);
                alert('An unexpected error occurred while loading comments.');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }

    async function castVote(commentId, voteType, btnEl) {
        try {
            const response = await fetch(`<?= BASE_URL ?>/comments/${commentId}/vote`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= CSRF::generateToken() ?>'
                },
                body: JSON.stringify({ vote: voteType })
            });

            if (!response.ok) return;

            const res = await response.json();
            if (res.success) {
                const parent = btnEl.parentElement;
                const likeBtn = parent.children[0];
                const dislikeBtn = parent.children[1];

                likeBtn.querySelector('.likes-count').textContent = res.likes;
                dislikeBtn.querySelector('.dislikes-count').textContent = res.dislikes;

                likeBtn.classList.remove('text-natural-primary', 'font-bold');
                dislikeBtn.classList.remove('text-natural-primary', 'font-bold');

                const likeSvg = likeBtn.querySelector('svg');
                const dislikeSvg = dislikeBtn.querySelector('svg');

                likeSvg.className = "w-4 h-4 stroke-current text-natural-muted fill-none";
                dislikeSvg.className = "w-4 h-4 stroke-current text-natural-muted fill-none";

                if (res.myVote === 'like') {
                    likeBtn.classList.add('text-natural-primary', 'font-bold');
                    likeSvg.className = "w-4 h-4 stroke-current text-natural-primary fill-natural-primary";
                } else if (res.myVote === 'dislike') {
                    dislikeBtn.classList.add('text-natural-primary', 'font-bold');
                    dislikeSvg.className = "w-4 h-4 stroke-current text-natural-primary fill-natural-primary";
                }
            }
        } catch (e) {
            console.error("Voting system error:", e);
        }
    }

    window.startEditComment = function(commentId, btn) {
        const commentEl = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (!commentEl) return;

        const textWrapper = commentEl.querySelector('.comment-text-wrapper');
        const bodyTextEl = textWrapper.querySelector('.comment-body-text');
        const originalText = bodyTextEl ? bodyTextEl.textContent : '';

        if (textWrapper.querySelector('.comment-edit-form')) return;

        if (bodyTextEl) bodyTextEl.classList.add('hidden');

        const formHtml = `
            <div class="comment-edit-form space-y-2 mt-1">
                <textarea class="w-full px-3 py-2 border border-natural-border rounded-xl bg-natural-bg/50 focus:outline-none text-xs text-natural-text" rows="3">${escapeHtml(originalText)}</textarea>
                <div class="flex items-center space-x-2 justify-end">
                    <button type="button" onclick="cancelEditComment(${commentId})" class="px-2.5 py-1 text-[10px] bg-natural-pane border border-natural-border rounded-lg text-natural-muted hover:text-natural-primary font-semibold transition-colors">Cancel</button>
                    <button type="button" onclick="saveComment(${commentId}, this)" class="px-2.5 py-1 text-[10px] bg-natural-primary hover:bg-natural-primary-hover text-white font-semibold rounded-lg shadow-sm transition-colors">Save</button>
                </div>
            </div>
        `;

        textWrapper.insertAdjacentHTML('beforeend', formHtml);
        textWrapper.querySelector('textarea').focus();
    };

    window.cancelEditComment = function(commentId) {
        const commentEl = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (!commentEl) return;

        const textWrapper = commentEl.querySelector('.comment-text-wrapper');
        const bodyTextEl = textWrapper.querySelector('.comment-body-text');
        const editForm = textWrapper.querySelector('.comment-edit-form');

        if (editForm) editForm.remove();
        if (bodyTextEl) bodyTextEl.classList.remove('hidden');
    };

    window.saveComment = async function(commentId, btn) {
        const commentEl = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (!commentEl) return;

        const textWrapper = commentEl.querySelector('.comment-text-wrapper');
        const textarea = textWrapper.querySelector('textarea');
        const newText = textarea.value.trim();

        if (newText === '') {
            alert('Comment text cannot be empty.');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Saving...';

        try {
            const formData = new FormData();
            formData.append('comment', newText);
            formData.append('csrf_token', window.__RT_CONFIG ? window.__RT_CONFIG.csrfToken : '');

            const response = await fetch(`${COMMENTS_BASE_URL}/comments/${commentId}/edit`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': window.__RT_CONFIG ? window.__RT_CONFIG.csrfToken : ''
                },
                body: formData
            });

            if (!response.ok) {
                const errData = await response.json();
                alert(errData.error || 'Failed to update comment.');
                return;
            }

            const res = await response.json();
            if (res.success) {
                let bodyTextEl = textWrapper.querySelector('.comment-body-text');
                if (!bodyTextEl) {
                    textWrapper.innerHTML = `<p class="text-natural-darkmute leading-relaxed text-xs comment-body-text"></p>`;
                    bodyTextEl = textWrapper.querySelector('.comment-body-text');
                }
                bodyTextEl.textContent = newText;
                cancelEditComment(commentId);
            }
        } catch (err) {
            console.error("Save comment error:", err);
            alert("An unexpected error occurred. Please try again.");
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save';
        }
    };

    // ── W9 Document Upload ───────────────────────────────────────────────────────
    window.handleW9Upload = async function(input, uploadUrl, csrfToken) {
        const file = input.files && input.files[0];
        if (!file) return;

        const statusDiv  = document.getElementById('w9-upload-status');
        const nameSpan   = document.getElementById('w9-upload-name');

        // Show pending state
        if (statusDiv) {
            statusDiv.classList.remove('hidden');
            statusDiv.classList.add('flex');
        }
        if (nameSpan) nameSpan.textContent = 'Uploading…';

        try {
            const fd = new FormData();
            fd.append('w9_form', file);
            fd.append('csrf_token', csrfToken);

            const res = await fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                },
                body: fd
            });

            const data = await res.json();
            if (data.success) {
                if (nameSpan) nameSpan.textContent = data.filename;
                // Update right panel
                if (typeof window.updateW9Panel === 'function') {
                    window.updateW9Panel(data);
                }
            } else {
                if (statusDiv) { statusDiv.classList.add('hidden'); statusDiv.classList.remove('flex'); }
                alert(data.error || 'Failed to upload W9 form.');
            }
        } catch (e) {
            console.error('W9 upload error:', e);
            if (statusDiv) { statusDiv.classList.add('hidden'); statusDiv.classList.remove('flex'); }
            alert('An unexpected error occurred during W9 upload.');
        } finally {
            // Reset file input so same file can be re-selected
            input.value = '';
        }
    };
</script>

