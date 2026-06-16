<script>
(function() {
    const cfg = window.__RT_CONFIG || { context: 'global', jobRef: '', interval: 25000, hiddenInterval: 60000 };
    const baseUrl = <?= json_encode(rtrim(BASE_URL, '/')) ?>;

    const state = {
        version: null,
        timer: null,
        inFlight: null,
        abortController: null,
        errorCount: 0,
        baseInterval: cfg.interval || 25000,
        hiddenInterval: cfg.hiddenInterval || 60000,
    };

    function escapeHTML(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function resolveJobId(n) {
        if (n.job_id) return parseInt(n.job_id, 10);
        const woMatch = String(n.message || '').match(/WO-\d{4}-(\d+)/i);
        if (woMatch) return parseInt(woMatch[1], 10);
        const legacyMatch = String(n.message || '').match(/job\s*#(\d+)/i);
        return legacyMatch ? parseInt(legacyMatch[1], 10) : null;
    }

    function resolveJobRef(n, jobId) {
        if (n.job_ref) return n.job_ref;
        if (!jobId) return null;
        const year = n.created_at ? new Date(n.created_at).getFullYear() : new Date().getFullYear();
        return `WO-${year}-${String(jobId).padStart(5, '0')}`;
    }

    function currentInterval() {
        const hidden = document.visibilityState === 'hidden';
        const backoff = Math.min(state.errorCount, 4);
        const base = state.baseInterval || cfg.interval || 25000;
        const hiddenMs = state.hiddenInterval || cfg.hiddenInterval || 60000;
        const multiplier = hidden ? (hiddenMs / base) : 1;
        const errorMult = backoff > 0 ? Math.pow(2, backoff) : 1;
        return Math.min(Math.round(base * multiplier * errorMult), 120000);
    }

    function applyPollingConfig(polling) {
        if (!polling) return;
        const context = cfg.context || 'global';
        const map = {
            global: polling.global,
            dashboard: polling.dashboard,
            jobs: polling.jobs,
            job: polling.job,
        };
        if (map[context]) {
            state.baseInterval = map[context];
            cfg.interval = map[context];
        }
        if (polling.hidden) {
            state.hiddenInterval = polling.hidden;
            cfg.hiddenInterval = polling.hidden;
        }
        if (polling.global) cfg.polling = polling;
    }

    function collectJobIds() {
        const ids = new Set();
        document.querySelectorAll('[data-rt-job-row]').forEach(el => {
            const id = parseInt(el.dataset.rtJobRow, 10);
            if (id > 0) ids.add(id);
        });
        return Array.from(ids);
    }

    function buildPollUrl(skipSince) {
        const params = new URLSearchParams();
        params.set('context', cfg.context || 'global');
        if (state.version && !skipSince) params.set('since', state.version);

        if (cfg.context === 'jobs') {
            const jobIds = collectJobIds();
            if (jobIds.length) params.set('job_ids', jobIds.join(','));
        }

        if (cfg.context === 'job') {
            const section = document.getElementById('comments-section');
            if (cfg.jobRef) params.set('job_ref', cfg.jobRef);
            if (section) {
                params.set('after_comment_id', section.dataset.rtLatestCommentId || '0');
            }
        }

        return `${baseUrl}/api/updates?${params.toString()}`;
    }

    function applyNotificationBadge(count) {
        const badge = document.getElementById('notification-count-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : String(count);
                badge.classList.remove('notification-bell-badge--hidden');
                badge.setAttribute('aria-label', count + ' unread notifications');
            } else {
                badge.classList.add('notification-bell-badge--hidden');
                badge.setAttribute('aria-label', 'No unread notifications');
            }
        }

        document.querySelectorAll('.rt-sidebar-notif-badge').forEach(el => {
            if (count > 0) {
                el.textContent = count > 99 ? '99+' : String(count);
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });
    }

    function renderNotificationList(notifications) {
        const list = document.getElementById('notification-items-list');
        if (!list) return;

        if (!notifications.length) {
            list.innerHTML = '<div class="p-4 text-center text-xs text-natural-muted font-medium">No new notifications.</div>';
            return;
        }

        list.innerHTML = notifications.map(n => {
            const isUnread = !parseInt(n.is_read, 10);
            const bgClass = isUnread ? 'bg-natural-subtle/30 font-medium' : 'bg-white';
            const dotClass = isUnread ? 'bg-natural-primary' : 'bg-transparent';
            const time = new Date(n.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const jobId = resolveJobId(n);
            const jobRef = resolveJobRef(n, jobId);
            const isCommentNotice = /comment(ed)? on|your comment on|liked your comment|disliked your comment/i.test(n.message || '');
            const jobHref = jobId && jobRef
                ? `${baseUrl}/jobs/${encodeURIComponent(jobRef)}${isCommentNotice ? '#comments-timeline' : ''}`
                : null;
            const inner = `
                <div class="${bgClass} hover:bg-natural-pane/30 transition-colors flex items-start space-x-3.5 text-xs text-natural-text px-4 py-3">
                    <span class="w-1.5 h-1.5 mt-1.5 rounded-full ${dotClass} shrink-0"></span>
                    <div class="flex-1 min-w-0">
                        <p class="leading-relaxed">${escapeHTML(n.display_message || n.message)}</p>
                        <span class="text-[10px] text-natural-muted mt-1 block">${time}</span>
                        ${jobHref && jobRef ? `<span class="text-[10px] font-bold text-natural-primary mt-1 block font-mono">Open ${escapeHTML(jobRef)} →</span>` : ''}
                    </div>
                </div>
            `;
            return jobHref
                ? `<a href="${jobHref}" class="block border-b border-natural-border/60 last:border-0">${inner}</a>`
                : `<div class="border-b border-natural-border/60 last:border-0">${inner}</div>`;
        }).join('');
    }

    function unreadBadgeHtml(count) {
        const label = count === 1 ? '1 unread comment' : `${count} unread comments`;
        return `<a href="#" class="rt-inline-unread-badge inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold hover:bg-rose-100 transition-colors" title="Unread comments">
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            ${label}
        </a>`;
    }

    function applyUnreadComments(byJobId) {
        if (!byJobId) return;

        document.querySelectorAll('[data-rt-job-row]').forEach(row => {
            const jobId = parseInt(row.dataset.rtJobRow, 10);
            const count = byJobId[jobId] || 0;
            row.classList.toggle('bg-rose-50/30', count > 0);

            const storeCell = row.querySelector('[data-rt-job-store-cell]');
            if (!storeCell) return;

            let badge = storeCell.querySelector('.rt-inline-unread-badge');
            if (count > 0) {
                if (!badge) {
                    storeCell.querySelector('.rt-job-title-wrap')?.insertAdjacentHTML('beforeend', unreadBadgeHtml(count));
                    badge = storeCell.querySelector('.rt-inline-unread-badge');
                }
                if (badge) {
                    const label = count === 1 ? '1 unread comment' : `${count} unread comments`;
                    badge.innerHTML = `<svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg> ${label}`;
                    const jobLink = storeCell.querySelector('a.text-natural-primary');
                    if (jobLink && badge.tagName === 'A') {
                        badge.href = jobLink.href.split('#')[0] + '#comments-timeline';
                    }
                }
            } else if (badge) {
                badge.remove();
            }
        });

        document.querySelectorAll('[data-rt-queue-job-id]').forEach(card => {
            const jobId = parseInt(card.dataset.rtQueueJobId, 10);
            const count = byJobId[jobId] || 0;
            let badge = card.querySelector('.rt-queue-unread');
            if (count > 0) {
                if (!badge) {
                    const wrap = card.querySelector('[data-rt-queue-badges]');
                    if (wrap) {
                        wrap.insertAdjacentHTML('beforeend', `<span class="rt-queue-unread px-1.5 py-0.5 rounded text-[8px] font-bold bg-natural-primary text-white">${count} unread</span>`);
                    }
                } else {
                    badge.textContent = `${count} unread`;
                }
            } else if (badge) {
                badge.remove();
            }
        });
    }

    function applyStats(stats) {
        if (!stats) return;
        const map = {
            total: stats.total,
            new: stats.new,
            assigned: stats.assigned,
            in_progress: stats.in_progress,
            done: stats.done,
        };
        Object.entries(map).forEach(([key, value]) => {
            document.querySelectorAll(`[data-rt-stat="${key}"]`).forEach(el => {
                el.textContent = String(value);
            });
        });
    }

    function applyNewComments(jobComments) {
        if (!jobComments || !jobComments.comments || !jobComments.comments.length) {
            if (jobComments && jobComments.latestCommentId) {
                const section = document.getElementById('comments-section');
                if (section) section.dataset.rtLatestCommentId = String(jobComments.latestCommentId);
            }
            return;
        }

        const timeline = document.getElementById('comments-timeline');
        const section = document.getElementById('comments-section');
        if (!timeline || typeof buildCommentHtml !== 'function') return;

        if (!timeline.querySelector('[data-comment-id]')) {
            timeline.innerHTML = '';
        }

        const existingIds = new Set();
        timeline.querySelectorAll('[data-comment-id]').forEach(el => {
            existingIds.add(parseInt(el.dataset.commentId, 10));
        });

        const toPrepend = jobComments.comments.filter(c => !existingIds.has(parseInt(c.id, 10)));
        if (!toPrepend.length) {
            if (section && jobComments.latestCommentId) {
                section.dataset.rtLatestCommentId = String(jobComments.latestCommentId);
            }
            return;
        }

        const currentUserId = section ? parseInt(section.dataset.rtCurrentUserId || '0', 10) : 0;
        let banner = timeline.querySelector('.comments-unread-banner');
        let newFromOthers = 0;

        toPrepend.reverse().forEach(c => {
            const isOwn = currentUserId > 0 && parseInt(c.user_id || '0', 10) === currentUserId;
            timeline.insertAdjacentHTML('afterbegin', buildCommentHtml(c, !isOwn));
            if (!isOwn) newFromOthers++;
        });

        if (newFromOthers > 0) {
            if (!banner) {
                timeline.insertAdjacentHTML('afterbegin',
                    `<div class="comments-unread-banner text-[10px] font-semibold text-natural-primary bg-natural-pane border border-natural-border rounded-xl px-3 py-2 text-center rt-live-banner">${newFromOthers === 1 ? '1 new message' : newFromOthers + ' new messages'}</div>`
                );
            } else {
                banner.textContent = newFromOthers === 1 ? '1 new message' : newFromOthers + ' new messages';
            }

            if (document.visibilityState === 'visible') {
                const jobId = section ? section.dataset.rtJobId : null;
                if (jobId) {
                    fetch(`${baseUrl}/jobs/${jobId}/read`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-Token': cfg.csrfToken || '',
                        }
                    }).then(() => {
                        setTimeout(() => {
                            const liveBanner = timeline.querySelector('.rt-live-banner');
                            if (liveBanner) liveBanner.remove();

                            timeline.querySelectorAll('.comment-unread').forEach(el => {
                                el.classList.remove('comment-unread');
                                el.querySelector('span.bg-natural-subtle')?.remove();
                            });
                        }, 5000);
                    }).catch(err => console.warn('Failed to auto-mark comments as read:', err));
                }
            }
        }

        if (section && jobComments.latestCommentId) {
            section.dataset.rtLatestCommentId = String(jobComments.latestCommentId);
        }
    }

    function applyPayload(data) {
        if (data.unreadComments) {
            applyUnreadComments(data.unreadComments.byJobId || {});
        }
        if (data.stats) {
            applyStats(data.stats);
        }
        if (data.jobComments) {
            applyNewComments(data.jobComments);
        }
    }

    function syncNotifications(data) {
        if (!data.notifications) return;

        applyNotificationBadge(data.notifications.unreadCount || 0);

        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown && !dropdown.classList.contains('hidden')) {
            renderNotificationList(data.notifications.items || []);
        }

        if (data.notifications.settings && window.BrowserNotifications) {
            window.BrowserNotifications.updatePrefs(data.notifications.settings);
        }

        if (window.BrowserNotifications?.process) {
            window.BrowserNotifications.process(
                data.notifications.items || [],
                data.notifications.settings || null
            );
        }

        document.dispatchEvent(new CustomEvent('realtime:update', { detail: data }));
    }

    async function poll(forceFull) {
        if (state.inFlight) return state.inFlight;

        state.abortController = new AbortController();

        state.inFlight = fetch(buildPollUrl(!!forceFull), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: state.abortController.signal,
            credentials: 'same-origin',
        }).then(async response => {
            if (!response.ok) throw new Error('Poll failed');
            const data = await response.json();
            state.errorCount = 0;

            if (data.version) {
                state.version = data.version;
            }

            if (data.polling) {
                applyPollingConfig(data.polling);
            }

            syncNotifications(data);

            if (data.changed !== false) {
                applyPayload(data);
            }

            return data;
        }).catch(err => {
            if (err.name !== 'AbortError') {
                state.errorCount = Math.min(state.errorCount + 1, 5);
                console.warn('Realtime poll error:', err);
            }
        }).finally(() => {
            state.inFlight = null;
            state.abortController = null;
        });

        return state.inFlight;
    }

    function scheduleNext() {
        clearTimeout(state.timer);
        state.timer = setTimeout(async () => {
            await poll(false);
            scheduleNext();
        }, currentInterval());
    }

    function start() {
        if (cfg.polling) applyPollingConfig(cfg.polling);
        state.baseInterval = cfg.interval || state.baseInterval;
        state.hiddenInterval = cfg.hiddenInterval || state.hiddenInterval;
        poll(true).finally(scheduleNext);
    }

    function stop() {
        clearTimeout(state.timer);
        if (state.abortController) {
            state.abortController.abort();
        }
    }

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            poll(false);
        }
    });

    window.RealtimePoll = {
        refresh: () => poll(true),
        poll: () => poll(false),
        renderNotificationList,
        applyNotificationBadge,
    };

    start();
})();
</script>
