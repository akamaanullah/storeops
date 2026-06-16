<?php
/** @var object $job */
$statusOptions = [
    'New' => 'New',
    'Assigned' => 'Assigned',
    'Scheduled' => 'Scheduled',
    'Work In Progress' => 'In Progress',
    'Pending' => 'Pending',
    'Cancelled' => 'Cancelled',
    'Done' => 'Done',
];
?>
<div class="space-y-1.5">
    <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Status Position</span>
    <div class="flex flex-wrap gap-1.5">
        <?php foreach ($statusOptions as $value => $label): ?>
            <label class="status-tab-btn inline-flex items-center justify-center px-2.5 py-2 min-w-[4.5rem] rounded-xl border border-natural-border text-[10px] font-semibold leading-tight text-center cursor-pointer transition-colors select-none text-natural-text hover:bg-natural-pane/40 has-[:checked]:bg-natural-primary has-[:checked]:border-natural-primary has-[:checked]:text-white has-[:checked]:shadow-sm">
                <input type="radio" name="status" value="<?= htmlspecialchars($value) ?>" class="sr-only" <?= $job->status === $value ? 'checked' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </label>
        <?php endforeach; ?>
    </div>
</div>
