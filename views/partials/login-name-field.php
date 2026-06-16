<?php
/**
 * Login username field with inline validation.
 *
 * @var string $lnValue
 * @var string $lnId
 * @var string $lnName
 * @var string $lnError
 * @var int $lnExcludeUserId
 * @var string $lnInitialValue
 */

use App\Core\Validator;

$lnId = $lnId ?? 'username';
$lnName = $lnName ?? 'username';
$lnValue = $lnValue ?? '';
$lnError = trim($lnError ?? '');
$lnExcludeUserId = (int)($lnExcludeUserId ?? 0);
$lnInitialValue = trim($lnInitialValue ?? $lnValue);
$minLen = Validator::MIN_USERNAME_LENGTH;
$maxLen = Validator::MAX_USERNAME_LENGTH;
$inputClass = 'w-full px-4 py-3 border rounded-xl bg-natural-bg/50 text-xs text-natural-text focus:outline-none focus:ring-2';
$inputClass .= $lnError !== ''
    ? ' border-rose-300 focus:border-rose-400 focus:ring-rose-200'
    : ' border-natural-border focus:ring-natural-primary/50 focus:border-natural-primary';
?>
<div
    class="space-y-1.5"
    data-login-name-field
    data-login-name-check-url="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/api/users/check-login-name') ?>"
    data-login-name-exclude-id="<?= $lnExcludeUserId ?>"
    data-login-name-initial="<?= htmlspecialchars($lnInitialValue) ?>"
>
    <label for="<?= htmlspecialchars($lnId) ?>" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Login Name *</label>
    <input
        id="<?= htmlspecialchars($lnId) ?>"
        type="text"
        name="<?= htmlspecialchars($lnName) ?>"
        required
        autocomplete="off"
        minlength="<?= (int)$minLen ?>"
        maxlength="<?= (int)$maxLen ?>"
        value="<?= htmlspecialchars($lnValue) ?>"
        placeholder="e.g. john_doe"
        class="<?= $inputClass ?>"
        data-login-name-input
    >
    <p class="text-[10px] text-rose-600 leading-relaxed<?= $lnError === '' ? ' hidden' : '' ?>" data-login-name-error role="alert"><?= htmlspecialchars($lnError) ?></p>
    <p class="text-[10px] text-natural-muted leading-relaxed">Must be unique.</p>
</div>
