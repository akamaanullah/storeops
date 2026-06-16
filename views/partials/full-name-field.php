<?php
/**
 * Full name input field.
 *
 * @var string $fnValue
 * @var string $fnId
 * @var string $fnName
 */

use App\Core\Validator;

$fnId = $fnId ?? 'full_name';
$fnName = $fnName ?? 'full_name';
$fnValue = $fnValue ?? '';
$maxLen = Validator::MAX_FULL_NAME_LENGTH;
?>
<div class="space-y-1.5">
    <label for="<?= htmlspecialchars($fnId) ?>" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Full Name *</label>
    <input
        id="<?= htmlspecialchars($fnId) ?>"
        type="text"
        name="<?= htmlspecialchars($fnName) ?>"
        required
        maxlength="<?= (int)$maxLen ?>"
        value="<?= htmlspecialchars($fnValue) ?>"
        placeholder="e.g. John Doe"
        class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text"
    >
</div>
