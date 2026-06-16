<?php
/**
 * Reusable password input with show/hide toggle.
 *
 * Required: $pfId, $pfName, $pfLabel
 * Optional: $pfRequired, $pfPlaceholder, $pfMinlength, $pfValue, $pfExtraClass, $pfHint
 */
$pfRequired = $pfRequired ?? false;
$pfPlaceholder = $pfPlaceholder ?? '';
$pfMinlength = $pfMinlength ?? null;
$pfValue = $pfValue ?? '';
$pfHint = $pfHint ?? null;
$pfLabelClass = $pfLabelClass ?? 'text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono';
$pfExtraClass = $pfExtraClass ?? 'w-full px-4 py-3 pr-12 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text';
?>
<div class="space-y-1.5">
    <label for="<?= htmlspecialchars($pfId) ?>" class="<?= htmlspecialchars($pfLabelClass) ?>"><?= htmlspecialchars($pfLabel) ?></label>
    <div class="password-field-wrap relative">
        <input
            id="<?= htmlspecialchars($pfId) ?>"
            type="password"
            name="<?= htmlspecialchars($pfName) ?>"
            <?= $pfRequired ? 'required' : '' ?>
            <?= $pfMinlength ? 'minlength="' . (int)$pfMinlength . '"' : '' ?>
            value="<?= htmlspecialchars($pfValue) ?>"
            placeholder="<?= htmlspecialchars($pfPlaceholder) ?>"
            class="<?= htmlspecialchars($pfExtraClass) ?>"
            autocomplete="<?= htmlspecialchars($pfAutocomplete ?? ($pfName === 'password' ? 'current-password' : 'new-password')) ?>"
        >
        <button
            type="button"
            class="password-toggle-btn"
            aria-label="Show password"
            aria-pressed="false"
            data-target="<?= htmlspecialchars($pfId) ?>"
        >
            <svg class="password-toggle-icon password-toggle-icon--show" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            <svg class="password-toggle-icon password-toggle-icon--hide hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
            </svg>
        </button>
    </div>
    <?php if ($pfHint): ?>
        <p class="text-[10px] text-natural-muted leading-relaxed"><?= htmlspecialchars($pfHint) ?></p>
    <?php endif; ?>
</div>
