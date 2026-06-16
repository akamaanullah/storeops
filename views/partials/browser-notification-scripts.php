<script>
(function() {
    const baseUrl = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
    const iconUrl = <?= json_encode(APP_BRAND_ICON_URL) ?>;
    const appName = <?= json_encode(APP_NAME) ?>;
    const STORAGE_KEY = 'storeops_shown_notif_ids';
    let prefs = null;
    let initialized = false;
    let lastKnownMaxId = 0;
    const showingIds = new Set();

    function getSeenIds() {
        try {
            return new Set(JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'));
        } catch {
            return new Set();
        }
    }

    function markSeen(id) {
        if (!id) return;
        const set = getSeenIds();
        set.add(id);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(set).slice(-300)));
        lastKnownMaxId = Math.max(lastKnownMaxId, id);
    }

    function inferType(message) {
        const m = String(message || '').toLowerCase();
        if (m.includes('assigned to') || m.includes('you have been assigned')) return 'job_assign';
        if (m.includes('commented on')) return 'comment';
        if (m.includes('liked your comment') || m.includes('disliked your comment')) return 'vote';
        if (m.includes('status updated') || m.includes('marked complete')) return 'status_update';
        return 'other';
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

    function buildJobUrl(n) {
        const jobId = resolveJobId(n);
        const jobRef = resolveJobRef(n, jobId);
        if (!jobId || !jobRef) return null;
        const type = n.type || inferType(n.message);
        const hash = (type === 'comment' || type === 'vote') ? '#comments-timeline' : '';
        return `${baseUrl}/jobs/${encodeURIComponent(jobRef)}${hash}`;
    }

    function shouldShowBrowser(n) {
        if (!prefs?.browser_enabled) return false;
        if (typeof Notification === 'undefined' || Notification.permission !== 'granted') return false;
        if (parseInt(n.is_read, 10)) return false;
        if (!document.hidden) return false;

        const type = n.type || inferType(n.message);
        const map = {
            job_assign: 'notify_job_assign',
            status_update: 'notify_status_update',
            comment: 'notify_comments',
            vote: 'notify_votes',
        };
        const prefKey = map[type];
        if (!prefKey) return true;
        return !!prefs[prefKey];
    }

    function formatPopup(n) {
        const type = n.type || inferType(n.message);
        const jobRef = resolveJobRef(n, resolveJobId(n));
        const raw = String(n.display_message || n.message || 'You have a new update.').trim();

        const titles = {
            job_assign: 'New Job Assignment',
            status_update: 'Work Order Updated',
            comment: 'New Comment',
            vote: 'Comment Reaction',
            other: appName + ' Alert',
        };

        let body = raw;
        if (jobRef) {
            body = body.replace(new RegExp(jobRef.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi'), '').trim();
            body = body.replace(/^[\s·\-–—:]+|[\s·\-–—:]+$/g, '').trim();
            body = jobRef + (body ? '\n' + body : '');
        }

        return {
            title: titles[type] || titles.other,
            body: body || raw,
            type,
        };
    }

    function showPopup(n) {
        const id = parseInt(n.id, 10);
        if (!id || showingIds.has(id)) return;

        showingIds.add(id);

        const url = buildJobUrl(n);
        const popupMeta = formatPopup(n);

        try {
            const popup = new Notification(popupMeta.title, {
                body: popupMeta.body,
                icon: iconUrl,
                tag: 'storeops-notif-' + id,
                renotify: false,
                silent: false,
                requireInteraction: popupMeta.type === 'job_assign',
                timestamp: n.created_at && !Number.isNaN(Date.parse(n.created_at))
                    ? Date.parse(n.created_at)
                    : Date.now(),
            });

            popup.onclick = function() {
                window.focus();
                if (url) window.location.href = url;
                popup.close();
            };

            popup.onclose = popup.onerror = function() {
                showingIds.delete(id);
            };
        } catch (err) {
            showingIds.delete(id);
            console.warn('Browser notification error:', err);
        }
    }

    function process(items, settings) {
        if (settings) prefs = settings;
        if (!prefs?.browser_enabled) return;
        if (typeof Notification === 'undefined' || Notification.permission !== 'granted') return;

        const list = items || [];

        if (!initialized) {
            list.forEach(n => {
                const id = parseInt(n.id, 10);
                if (id) markSeen(id);
            });
            initialized = true;
            return;
        }

        list.forEach(n => {
            const id = parseInt(n.id, 10);
            if (!id || parseInt(n.is_read, 10)) return;
            if (getSeenIds().has(id)) return;
            if (id <= lastKnownMaxId && initialized) return;

            markSeen(id);

            if (shouldShowBrowser(n)) {
                showPopup(n);
            }
        });
    }

    window.BrowserNotifications = {
        process,
        updatePrefs: function(p) { prefs = p; },
    };
})();
</script>
