<?php
/** @var \App\Models\UserNotificationSettings $settings */
?>
<div class="mb-8">
    <h1 class="text-2xl font-serif italic text-natural-heading tracking-tight">Notification Settings</h1>
    <p class="text-xs text-natural-darkmute mt-1">Control in-app alerts and browser pop-up notifications for your account.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-10 items-start">
    <!-- Browser permission -->
    <div class="bg-white border border-natural-border rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-natural-border bg-natural-pane/30 flex items-center gap-3">
            <span class="p-2 rounded-xl bg-white border border-natural-border text-natural-primary shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </span>
            <div>
                <h2 class="font-bold text-natural-heading text-sm">Browser Notifications</h2>
                <p class="text-[10px] text-natural-muted mt-0.5">Desktop pop-ups when this tab is in the background</p>
            </div>
        </div>

        <div class="p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-2xl bg-natural-pane/40 border border-natural-border">
                <div class="min-w-0">
                    <span class="text-xs font-semibold text-natural-heading block">Pop-up status</span>
                    <span id="browser-permission-status" class="text-[10px] text-natural-muted mt-1 block leading-relaxed">Checking…</span>
                    <span id="browser-permission-hint" class="text-[10px] text-natural-muted/80 mt-1 block leading-relaxed hidden"></span>
                </div>
                <span id="browser-permission-badge" class="self-start sm:self-center shrink-0 px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase tracking-wide bg-natural-subtle text-natural-muted border border-natural-border">…</span>
            </div>

            <div class="space-y-4">
                <p class="text-[10px] text-natural-muted leading-relaxed">
                    If you blocked notifications earlier, open your browser site settings for this URL, reset permission, then click the button below.
                </p>
                <button
                    type="button"
                    id="enable-browser-btn"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2 bg-natural-primary hover:bg-natural-primary-hover text-white font-semibold text-xs rounded-xl transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-natural-primary/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-natural-primary"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span id="enable-browser-btn-label">Allow notifications</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Preferences form -->
    <form action="<?= BASE_URL ?>/settings/notifications" method="POST" class="bg-white border border-natural-border rounded-3xl shadow-sm overflow-hidden lg:col-span-1 flex flex-col">
        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">

        <div class="px-6 py-4 border-b border-natural-border bg-natural-pane/30 flex items-center gap-3">
            <span class="p-2 rounded-xl bg-white border border-natural-border text-natural-primary shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </span>
            <div>
                <h2 class="font-bold text-natural-heading text-sm">Notification Types</h2>
                <p class="text-[10px] text-natural-muted mt-0.5">Choose which events trigger browser pop-ups</p>
            </div>
        </div>

        <div class="p-6 space-y-4 flex-1">
            <label class="flex items-start gap-4 p-4 rounded-2xl border-2 border-natural-primary/20 bg-natural-subtle/20 hover:bg-natural-subtle/40 transition-colors cursor-pointer">
                <input type="checkbox" name="browser_enabled" id="browser_enabled" value="1"
                    class="mt-1 w-4 h-4 rounded border-natural-border text-natural-primary focus:ring-natural-primary/30"
                    <?= $settings->browser_enabled ? 'checked' : '' ?>>
                <span class="flex-1 min-w-0">
                    <span class="text-xs font-bold text-natural-heading block">Enable browser pop-ups</span>
                    <span class="text-[10px] text-natural-muted block mt-1 leading-relaxed">Master switch — requires browser permission above. In-app inbox always receives all notifications.</span>
                </span>
            </label>

            <div id="notification-type-toggles" class="grid grid-cols-1 gap-3 pt-2 transition-opacity <?= !$settings->browser_enabled ? 'opacity-50' : '' ?>">
                <?php
                $typesDisabled = !$settings->browser_enabled;
                $toggles = [
                    'notify_job_assign' => [
                        'Job assignment',
                        'When a job is assigned to you',
                        'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                    ],
                    'notify_status_update' => [
                        'Status updates',
                        'When job status changes or is marked complete',
                        'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                    ],
                    'notify_comments' => [
                        'Comments',
                        'When someone comments on your assigned job',
                        'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                    ],
                    'notify_votes' => [
                        'Likes & dislikes',
                        'When someone reacts to your comment',
                        'M14 9V5a3 3 0 00-6 0v4M7 11h2v10H7v-10zm4 0h2a2 2 0 012 2v8a2 2 0 01-2 2h-2v-12z',
                    ],
                ];
                foreach ($toggles as $field => [$label, $desc, $iconPath]):
                ?>
                <label class="flex items-start gap-3 p-4 rounded-2xl border border-natural-border hover:border-natural-primary/30 hover:bg-natural-pane/30 transition-all cursor-pointer group rt-type-toggle-label <?= $typesDisabled ? 'pointer-events-none' : '' ?>">
                    <input type="checkbox" name="<?= $field ?>" class="rt-type-toggle mt-1 w-4 h-4 rounded border-natural-border text-natural-primary focus:ring-natural-primary/30"
                        <?= ($settings->$field && $settings->browser_enabled) ? 'checked' : '' ?>
                        <?= $typesDisabled ? 'disabled' : '' ?>>
                    <span class="flex items-start gap-3 flex-1 min-w-0">
                        <span class="p-1.5 rounded-lg bg-natural-pane border border-natural-border text-natural-muted group-hover:text-natural-primary transition-colors shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="<?= $iconPath ?>"></path>
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="text-xs font-semibold text-natural-heading block"><?= htmlspecialchars($label) ?></span>
                            <span class="text-[10px] text-natural-muted block mt-0.5 leading-relaxed"><?= htmlspecialchars($desc) ?></span>
                        </span>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-natural-border bg-natural-pane/20 flex flex-wrap items-center justify-end gap-3">
            <a href="<?= BASE_URL ?>/notifications/inbox"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white hover:bg-natural-pane text-natural-darkmute font-semibold text-xs rounded-xl transition-all border border-natural-border focus:outline-none">
                View Inbox
            </a>
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 px-5 py-2 bg-natural-primary hover:bg-natural-primary-hover text-white font-semibold text-xs rounded-xl transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-natural-primary/30">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
                Save settings
            </button>
        </div>
    </form>
</div>

<script>
(function() {
    const statusEl = document.getElementById('browser-permission-status');
    const hintEl = document.getElementById('browser-permission-hint');
    const badgeEl = document.getElementById('browser-permission-badge');
    const enableBtn = document.getElementById('enable-browser-btn');
    const enableBtnLabel = document.getElementById('enable-browser-btn-label');
    const browserToggle = document.getElementById('browser_enabled');
    const typeTogglesWrap = document.getElementById('notification-type-toggles');
    const typeToggleInputs = () => document.querySelectorAll('.rt-type-toggle');
    const typeToggleLabels = () => document.querySelectorAll('.rt-type-toggle-label');
    let savedTypeStates = null;
    let browserPermission = typeof Notification !== 'undefined' ? Notification.permission : 'unsupported';

    const badgeBase = 'self-start sm:self-center shrink-0 px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase tracking-wide border ';

    function setEnableBtnStyle(mode) {
        if (!enableBtn) return;
        enableBtn.classList.remove(
            'bg-natural-primary', 'hover:bg-natural-primary-hover', 'text-white',
            'bg-white', 'text-emerald-700', 'border-emerald-200', 'hover:bg-emerald-50', 'border',
            'bg-natural-pane', 'text-natural-muted', 'border-natural-border', 'hover:bg-natural-subtle'
        );
        if (mode === 'granted' || mode === 'active') {
            enableBtn.classList.add('bg-white', 'text-emerald-700', 'border', 'border-emerald-200', 'hover:bg-emerald-50');
        } else if (mode === 'off') {
            enableBtn.classList.add('bg-natural-pane', 'text-natural-muted', 'border', 'border-natural-border', 'hover:bg-natural-subtle');
        } else if (mode === 'denied') {
            enableBtn.classList.add('bg-natural-primary', 'hover:bg-natural-primary-hover', 'text-white');
        } else {
            enableBtn.classList.add('bg-natural-primary', 'hover:bg-natural-primary-hover', 'text-white');
        }
    }

    function renderPopUpStatus() {
        const appEnabled = !!browserToggle?.checked;

        if (browserPermission === 'unsupported' || !('Notification' in window)) {
            statusEl.textContent = 'Not supported in this browser';
            hintEl.classList.add('hidden');
            badgeEl.textContent = 'Unsupported';
            badgeEl.className = badgeBase + 'bg-rose-50 text-rose-600 border-rose-100';
            if (enableBtnLabel) enableBtnLabel.textContent = 'Unsupported';
            if (enableBtn) enableBtn.disabled = true;
            if (browserToggle) browserToggle.disabled = true;
            return;
        }

        if (browserToggle) browserToggle.disabled = false;

        if (!appEnabled) {
            statusEl.textContent = 'Browser pop-ups are turned off in your settings.';
            if (browserPermission === 'granted') {
                hintEl.textContent = 'Browser permission is still allowed, but no desktop alerts will show until you enable the master switch and save.';
                hintEl.classList.remove('hidden');
            } else {
                hintEl.classList.add('hidden');
            }
            badgeEl.textContent = 'Off';
            badgeEl.className = badgeBase + 'bg-slate-100 text-slate-600 border-slate-200';
            if (enableBtnLabel) {
                enableBtnLabel.textContent = browserPermission === 'granted'
                    ? 'Pop-ups disabled'
                    : 'Allow notifications';
            }
            setEnableBtnStyle('off');
            if (enableBtn) enableBtn.disabled = browserPermission === 'denied';
            return;
        }

        hintEl.classList.add('hidden');

        if (browserPermission === 'granted') {
            statusEl.textContent = 'Pop-ups are active — you will receive desktop alerts for enabled types.';
            badgeEl.textContent = 'Active';
            badgeEl.className = badgeBase + 'bg-emerald-50 text-emerald-700 border-emerald-100';
            if (enableBtnLabel) enableBtnLabel.textContent = 'Browser allowed';
            setEnableBtnStyle('active');
            if (enableBtn) enableBtn.disabled = false;
            return;
        }

        if (browserPermission === 'denied') {
            statusEl.textContent = 'Browser blocked notifications — reset permission in site settings.';
            badgeEl.textContent = 'Blocked';
            badgeEl.className = badgeBase + 'bg-rose-50 text-rose-600 border-rose-100';
            if (enableBtnLabel) enableBtnLabel.textContent = 'Permission blocked';
            setEnableBtnStyle('denied');
            if (enableBtn) enableBtn.disabled = true;
            if (browserToggle) {
                browserToggle.checked = false;
            }
            return;
        }

        statusEl.textContent = 'Browser permission not granted yet — click the button below to allow.';
        badgeEl.textContent = 'Not set';
        badgeEl.className = badgeBase + 'bg-amber-50 text-amber-700 border-amber-100';
        if (enableBtnLabel) enableBtnLabel.textContent = 'Allow notifications';
        setEnableBtnStyle('default');
        if (enableBtn) enableBtn.disabled = false;
    }

    function syncTypeToggles() {
        const enabled = !!browserToggle?.checked;
        const inputs = typeToggleInputs();

        if (!enabled) {
            if (savedTypeStates === null) {
                savedTypeStates = {};
                inputs.forEach((inp) => {
                    savedTypeStates[inp.name] = inp.checked;
                });
            }
            inputs.forEach((inp) => {
                inp.checked = false;
                inp.disabled = true;
            });
            typeToggleLabels().forEach((label) => label.classList.add('pointer-events-none'));
            typeTogglesWrap?.classList.add('opacity-50');
        } else {
            inputs.forEach((inp) => {
                inp.disabled = false;
                if (savedTypeStates && savedTypeStates[inp.name]) {
                    inp.checked = true;
                }
            });
            typeToggleLabels().forEach((label) => label.classList.remove('pointer-events-none'));
            typeTogglesWrap?.classList.remove('opacity-50');
            savedTypeStates = null;
        }

        renderPopUpStatus();
    }

    syncTypeToggles();

    enableBtn?.addEventListener('click', async function() {
        if (typeof Notification === 'undefined') return;
        const result = await Notification.requestPermission();
        browserPermission = result;
        if (result === 'granted' && browserToggle) {
            browserToggle.checked = true;
        }
        syncTypeToggles();
    });

    browserToggle?.addEventListener('change', function() {
        if (this.checked && typeof Notification !== 'undefined' && Notification.permission !== 'granted') {
            Notification.requestPermission().then((result) => {
                browserPermission = result;
                syncTypeToggles();
            });
            return;
        }
        syncTypeToggles();
    });
})();
</script>
