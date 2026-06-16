<?php
/** @var \App\Models\SystemPollingSettings $settings */
$min = \App\Models\SystemPollingSettings::MIN_SECONDS;
$max = \App\Models\SystemPollingSettings::MAX_SECONDS;
$maxHidden = \App\Models\SystemPollingSettings::MAX_HIDDEN_SECONDS;

$intervalFields = [
    [
        'name' => 'interval_job',
        'label' => 'Job detail & live comments',
        'desc' => 'New comments on work order pages',
        'value' => (int)$settings->interval_job,
        'max' => $max,
        'badge' => 'Fastest',
        'badge_class' => 'bg-rose-50 text-rose-700 border-rose-200',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>',
    ],
    [
        'name' => 'interval_dashboard',
        'label' => 'Dashboard',
        'desc' => 'Stats cards and work queue badges',
        'value' => (int)$settings->interval_dashboard,
        'max' => $max,
        'badge' => 'Overview',
        'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>',
    ],
    [
        'name' => 'interval_jobs',
        'label' => 'Jobs directory',
        'desc' => 'Unread comment badges on job rows',
        'value' => (int)$settings->interval_jobs,
        'max' => $max,
        'badge' => 'Directory',
        'badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>',
    ],
    [
        'name' => 'interval_global',
        'label' => 'Other pages',
        'desc' => 'Notification bell, inbox, and settings',
        'value' => (int)$settings->interval_global,
        'max' => $max,
        'badge' => 'Global',
        'badge_class' => 'bg-natural-subtle text-natural-darkmute border-natural-border',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>',
    ],
    [
        'name' => 'interval_hidden',
        'label' => 'Background tab',
        'desc' => 'When the browser tab is not visible',
        'value' => (int)$settings->interval_hidden,
        'max' => $maxHidden,
        'badge' => 'Power save',
        'badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>',
        'full' => true,
    ],
];
?>
<div class="mb-8">
    <h1 class="text-2xl font-serif italic text-natural-heading tracking-tight">Realtime Polling Settings</h1>
    <p class="text-xs text-natural-darkmute mt-1">Control how often the platform checks for live updates. Admin only — applies to all users.</p>
</div>

<div class="grid grid-cols-1 xl:grid-cols-5 gap-8 xl:gap-10 items-start max-w-6xl">
    <form action="<?= BASE_URL ?>/settings/polling" method="POST" id="polling-settings-form" class="xl:col-span-3 bg-white border border-natural-border rounded-3xl shadow-sm overflow-hidden">
        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">

        <div class="px-6 py-4 border-b border-natural-border bg-natural-pane/30 flex items-center gap-3">
            <span class="p-2 rounded-xl bg-white border border-natural-border text-natural-primary shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-natural-heading text-sm">Poll intervals</h2>
                <p class="text-[10px] text-natural-muted mt-0.5">Range <?= $min ?>–<?= $max ?>s per context · hidden tab up to <?= $maxHidden ?>s</p>
            </div>
        </div>

        <div class="px-6 py-4 border-b border-natural-border bg-natural-bg/50">
            <p class="text-[10px] font-semibold text-natural-darkmute uppercase tracking-wider mb-3">Quick presets</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-polling-preset="fast" class="polling-preset-btn">Fast (5s)</button>
                <button type="button" data-polling-preset="balanced" class="polling-preset-btn">Balanced</button>
                <button type="button" data-polling-preset="economy" class="polling-preset-btn">Economy</button>
            </div>
        </div>

        <div class="p-6 space-y-3">
            <?php foreach ($intervalFields as $field): ?>
            <label class="polling-interval-card <?= !empty($field['full']) ? 'polling-interval-card--full' : '' ?>">
                <span class="polling-interval-card__icon" aria-hidden="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><?= $field['icon'] ?></svg>
                </span>
                <span class="polling-interval-card__body">
                    <span class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-natural-heading"><?= htmlspecialchars($field['label']) ?></span>
                        <span class="px-2 py-0.5 rounded-md text-[8px] font-bold uppercase tracking-wide border <?= htmlspecialchars($field['badge_class']) ?>"><?= htmlspecialchars($field['badge']) ?></span>
                    </span>
                    <span class="text-[10px] text-natural-muted block mt-0.5 leading-relaxed"><?= htmlspecialchars($field['desc']) ?></span>
                </span>
                <span class="polling-interval-card__input-wrap">
                    <input type="number"
                           name="<?= htmlspecialchars($field['name']) ?>"
                           min="<?= $min ?>"
                           max="<?= (int)$field['max'] ?>"
                           required
                           value="<?= (int)$field['value'] ?>"
                           class="polling-interval-card__input"
                           data-polling-field="<?= htmlspecialchars($field['name']) ?>">
                    <span class="polling-interval-card__suffix">sec</span>
                </span>
            </label>
            <?php endforeach; ?>
        </div>

        <div class="px-6 py-4 border-t border-natural-border bg-natural-pane/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-[10px] text-natural-muted leading-relaxed">Saved values apply on the next poll cycle for every logged-in user.</p>
            <button type="submit" class="inline-flex items-center justify-center self-start sm:self-auto px-5 py-2.5 bg-natural-primary hover:bg-natural-primary-hover text-white font-bold text-xs uppercase tracking-wider rounded-full transition-all shadow-sm shrink-0">
                Save intervals
            </button>
        </div>
    </form>

    <div class="xl:col-span-2 space-y-4">
        <div class="bg-white border border-natural-border rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-natural-border bg-natural-pane/30">
                <h2 class="font-bold text-natural-heading text-sm">What updates live?</h2>
                <p class="text-[10px] text-natural-muted mt-0.5">Each interval controls a specific part of the app</p>
            </div>
            <ul class="divide-y divide-natural-border/70">
                <li class="px-6 py-4 flex gap-3">
                    <span class="mt-0.5 w-2 h-2 rounded-full bg-rose-400 shrink-0"></span>
                    <div>
                        <p class="text-xs font-semibold text-natural-heading">Job page</p>
                        <p class="text-[10px] text-natural-muted mt-1 leading-relaxed">New comments appear at the top without refresh — every <strong id="preview-interval_job"><?= (int)$settings->interval_job ?></strong>s.</p>
                    </div>
                </li>
                <li class="px-6 py-4 flex gap-3">
                    <span class="mt-0.5 w-2 h-2 rounded-full bg-blue-400 shrink-0"></span>
                    <div>
                        <p class="text-xs font-semibold text-natural-heading">Dashboard</p>
                        <p class="text-[10px] text-natural-muted mt-1 leading-relaxed">Job counts and work queue unread badges — every <strong id="preview-interval_dashboard"><?= (int)$settings->interval_dashboard ?></strong>s.</p>
                    </div>
                </li>
                <li class="px-6 py-4 flex gap-3">
                    <span class="mt-0.5 w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                    <div>
                        <p class="text-xs font-semibold text-natural-heading">Jobs list</p>
                        <p class="text-[10px] text-natural-muted mt-1 leading-relaxed">Unread comment badges on each row — every <strong id="preview-interval_jobs"><?= (int)$settings->interval_jobs ?></strong>s.</p>
                    </div>
                </li>
                <li class="px-6 py-4 flex gap-3">
                    <span class="mt-0.5 w-2 h-2 rounded-full bg-natural-primary shrink-0"></span>
                    <div>
                        <p class="text-xs font-semibold text-natural-heading">All pages</p>
                        <p class="text-[10px] text-natural-muted mt-1 leading-relaxed">Notification bell count and browser pop-ups — every <strong id="preview-interval_global"><?= (int)$settings->interval_global ?></strong>s.</p>
                    </div>
                </li>
            </ul>
        </div>

        <div class="p-5 rounded-2xl border border-natural-border bg-natural-pane/40">
            <p class="text-[10px] text-natural-darkmute leading-relaxed">
                <span class="font-semibold text-natural-heading">Tip:</span> Lower intervals feel more realtime but increase server requests. Use <strong>Fast</strong> for demos, <strong>Balanced</strong> for daily use, <strong>Economy</strong> on shared hosting.
            </p>
        </div>
    </div>
</div>

<script>
(function() {
    const presets = {
        fast: { interval_job: 5, interval_dashboard: 5, interval_jobs: 5, interval_global: 5, interval_hidden: 30 },
        balanced: { interval_job: 15, interval_dashboard: 30, interval_jobs: 25, interval_global: 25, interval_hidden: 60 },
        economy: { interval_job: 30, interval_dashboard: 60, interval_jobs: 45, interval_global: 45, interval_hidden: 120 },
    };

    function syncPreview(name, value) {
        const el = document.getElementById('preview-' + name);
        if (el) el.textContent = String(value);
    }

    document.querySelectorAll('[data-polling-field]').forEach(input => {
        input.addEventListener('input', () => syncPreview(input.name, input.value));
    });

    document.querySelectorAll('[data-polling-preset]').forEach(btn => {
        btn.addEventListener('click', () => {
            const preset = presets[btn.dataset.pollingPreset];
            if (!preset) return;

            document.querySelectorAll('[data-polling-field]').forEach(input => {
                if (preset[input.name] !== undefined) {
                    input.value = preset[input.name];
                    syncPreview(input.name, input.value);
                }
            });

            document.querySelectorAll('[data-polling-preset]').forEach(b => {
                b.classList.toggle('polling-preset-btn--active', b === btn);
            });
        });
    });
})();
</script>
