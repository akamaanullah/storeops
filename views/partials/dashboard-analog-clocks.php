<?php
/**
 * Dual analog clocks for dashboard (US + local/PK).
 */
$clocks = [
    [
        'key' => 'us',
        'label' => TIMEZONE_US_LABEL,
        'subtitle' => 'Eastern Time',
        'tz' => TIMEZONE_US,
        'ring' => '#3b82f6',
        'dot' => '#2563eb',
        'label_class' => 'text-blue-700',
    ],
    [
        'key' => 'pk',
        'label' => TIMEZONE_LOCAL_LABEL,
        'subtitle' => 'Pakistan',
        'tz' => TIMEZONE_LOCAL,
        'ring' => '#059669',
        'dot' => '#047857',
        'label_class' => 'text-emerald-700',
    ],
];
?>
<div class="dashboard-clocks-panel w-full sm:w-auto">
    <?php foreach ($clocks as $index => $clock): ?>
        <?php if ($index > 0): ?>
        <div class="dashboard-clocks-panel__divider" aria-hidden="true"></div>
        <?php endif; ?>

        <div class="analog-clock-widget"
             data-tz="<?= htmlspecialchars($clock['tz']) ?>"
             data-digital-id="clock-digital-<?= htmlspecialchars($clock['key']) ?>">
            <div class="analog-clock-face-wrap">
                <svg class="analog-clock-face" viewBox="0 0 100 100" aria-hidden="true">
                    <defs>
                        <radialGradient id="clock-face-<?= htmlspecialchars($clock['key']) ?>" cx="35%" cy="30%" r="70%">
                            <stop offset="0%" stop-color="#ffffff"></stop>
                            <stop offset="100%" stop-color="#f7f5f2"></stop>
                        </radialGradient>
                    </defs>
                    <circle cx="50" cy="50" r="48" fill="url(#clock-face-<?= htmlspecialchars($clock['key']) ?>)" stroke="<?= htmlspecialchars($clock['ring']) ?>" stroke-width="1.75" opacity="0.55"></circle>
                    <?php for ($i = 0; $i < 12; $i++): ?>
                    <line x1="50" y1="8" x2="50" y2="<?= $i % 3 === 0 ? '14' : '11' ?>"
                          transform="rotate(<?= $i * 30 ?> 50 50)"
                          stroke="<?= $i % 3 === 0 ? '#8b8b7f' : '#d8d3cb' ?>"
                          stroke-width="<?= $i % 3 === 0 ? '2' : '1' ?>"
                          stroke-linecap="round"></line>
                    <?php endfor; ?>
                    <text x="50" y="19" text-anchor="middle" font-size="6" font-weight="700" fill="#9a9a8c">12</text>
                    <text x="81" y="53" text-anchor="middle" font-size="6" font-weight="700" fill="#9a9a8c">3</text>
                    <text x="50" y="87" text-anchor="middle" font-size="6" font-weight="700" fill="#9a9a8c">6</text>
                    <text x="19" y="53" text-anchor="middle" font-size="6" font-weight="700" fill="#9a9a8c">9</text>
                    <g data-hand="hour" transform="rotate(0 50 50)">
                        <line x1="50" y1="50" x2="50" y2="31" stroke="#44443a" stroke-width="3.25" stroke-linecap="round"></line>
                    </g>
                    <g data-hand="minute" transform="rotate(0 50 50)">
                        <line x1="50" y1="50" x2="50" y2="21" stroke="#6b6b60" stroke-width="2" stroke-linecap="round"></line>
                    </g>
                    <g data-hand="second" transform="rotate(0 50 50)">
                        <line x1="50" y1="56" x2="50" y2="16" stroke="#c2410c" stroke-width="1.15" stroke-linecap="round"></line>
                        <circle cx="50" cy="50" r="2.25" fill="#c2410c"></circle>
                    </g>
                    <circle cx="50" cy="50" r="2.75" fill="#44443a"></circle>
                </svg>
            </div>

            <div class="analog-clock-meta">
                <span class="analog-clock-meta__label <?= htmlspecialchars($clock['label_class']) ?>">
                    <span class="analog-clock-meta__label-dot" style="background-color: <?= htmlspecialchars($clock['dot']) ?>"></span>
                    <?= htmlspecialchars($clock['label']) ?>
                </span>
                <span class="analog-clock-meta__subtitle"><?= htmlspecialchars($clock['subtitle']) ?></span>
                <span id="clock-digital-<?= htmlspecialchars($clock['key']) ?>" class="analog-clock-meta__time">--:--:--</span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
