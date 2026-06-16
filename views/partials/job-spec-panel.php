<?php
/** @var object $job */
/** @var array $users */
/** @var bool $isAdminOrTL */
/** @var bool $isAssigned */
?>
<div class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 space-y-6">
    <div>
        <h2 class="font-bold text-natural-heading tracking-tight text-sm">Work Order Configuration</h2>
        <p class="text-[10px] text-natural-muted mt-0.5">
            <?php if ($isAdminOrTL): ?>
                Administrator controls
            <?php elseif ($isAssigned): ?>
                Assigned user updates
            <?php else: ?>
                Read-only details
            <?php endif; ?>
        </p>
    </div>

    <?php if ($isAdminOrTL): ?>
        <form action="<?= BASE_URL . $job->path() ?>/complete" method="POST" class="space-y-4 text-xs">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
            <input type="hidden" name="action_type" value="edit_fields">

            <div class="space-y-1.5">
                <label class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Urgency Status</label>
                <div class="grid grid-cols-2 gap-2 text-center">
                    <label class="border border-natural-border rounded-xl py-2 px-3 flex items-center justify-center space-x-1.5 cursor-pointer hover:bg-natural-pane/30">
                        <input type="radio" name="urgency" value="Within SLA" <?= $job->urgency === 'Within SLA' ? 'checked' : '' ?> class="w-4 h-4 text-natural-primary focus:ring-natural-primary/50">
                        <span class="font-medium text-natural-text">Within SLA</span>
                    </label>
                    <label class="border border-natural-border rounded-xl py-2 px-3 flex items-center justify-center space-x-1.5 cursor-pointer hover:bg-natural-pane/30">
                        <input type="radio" name="urgency" value="Urgent" <?= $job->urgency === 'Urgent' ? 'checked' : '' ?> class="w-4 h-4 text-rose-600 focus:ring-rose-500">
                        <span class="font-semibold text-rose-600">Urgent</span>
                    </label>
                </div>
            </div>

            <?php include __DIR__ . '/job-w9-toggle.php'; ?>

            <div class="space-y-1.5">
                <label for="assign_worker" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Assigned User</label>
                <select id="assign_worker" name="assigned_to" class="w-full px-3 py-2.5 border border-natural-border rounded-xl text-natural-text bg-natural-bg/50 focus:outline-none focus:ring-2 focus:ring-natural-primary/50 animate-shadow">
                    <option value="">-- No Assignment (Mark 'New') --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u->id ?>" <?= $job->assigned_to === $u->id ? 'selected' : '' ?>><?= htmlspecialchars($u->full_name) ?> (<?= htmlspecialchars($u->role) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-1.5">
                <label for="created_at" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Job Add Date (Created At)</label>
                <input id="created_at" type="datetime-local" name="created_at" value="<?= date('Y-m-d\TH:i', strtotime($job->created_at)) ?>" class="w-full px-3 py-2.5 border border-natural-border rounded-xl text-natural-text bg-natural-bg/50 focus:outline-none focus:ring-2 focus:ring-natural-primary/50">
            </div>

            <div class="space-y-1.5">
                <label for="sla_date" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Job SLA Date</label>
                <input id="sla_date" type="datetime-local" name="sla_date" value="<?= $job->sla_date ? date('Y-m-d\TH:i', strtotime($job->sla_date)) : '' ?>" class="w-full px-3 py-2.5 border border-natural-border rounded-xl text-natural-text bg-natural-bg/50 focus:outline-none focus:ring-2 focus:ring-natural-primary/50">
            </div>

            <?php include __DIR__ . '/job-status-tabs.php'; ?>

            <button type="submit" class="w-full py-2.5 bg-natural-primary hover:bg-natural-primary-hover text-white font-semibold rounded-xl transition-colors shadow-sm uppercase tracking-wider text-[10px] font-mono">Save Changes</button>
        </form>

        <form action="<?= BASE_URL . $job->path() ?>/delete" method="POST" class="pt-4 border-t border-natural-border" onsubmit="return confirm('Are you sure you want to permanently delete this work order? This action cannot be undone.');">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
            <button type="submit" class="w-full py-2.5 bg-rose-650 hover:bg-rose-700 text-white font-semibold rounded-xl transition-colors shadow-sm uppercase tracking-wider text-[10px] font-mono bg-rose-600 hover:bg-rose-700">Delete Work Order</button>
        </form>

    <?php elseif ($isAssigned): ?>
        <form action="<?= BASE_URL . $job->path() ?>/complete" method="POST" class="space-y-4 text-xs">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
            <input type="hidden" name="action_type" value="edit_assigned_fields">

            <?php include __DIR__ . '/job-w9-toggle.php'; ?>

            <div class="space-y-3 pt-1 border-t border-natural-border">
                <div class="flex justify-between items-center py-1">
                    <span class="font-bold text-natural-muted uppercase text-[9px] tracking-wider font-mono">Urgency Level</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-natural-pane text-natural-primary border border-natural-border"><?= htmlspecialchars($job->urgency) ?></span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="font-bold text-natural-muted uppercase text-[9px] tracking-wider font-mono">SLA Deadline</span>
                    <span class="font-semibold text-natural-text"><?= $job->sla_date ? date('M j, Y, g:i a', strtotime($job->sla_date)) : 'None' ?></span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="font-bold text-natural-muted uppercase text-[9px] tracking-wider font-mono">Assigned User</span>
                    <span class="font-semibold text-natural-text"><?= htmlspecialchars($job->assigned_name) ?></span>
                </div>
            </div>

            <?php include __DIR__ . '/job-status-tabs.php'; ?>

            <div class="p-3 bg-natural-pane border border-natural-border rounded-xl text-[10px] text-natural-darkmute leading-relaxed flex items-start space-x-1.5">
                <svg class="w-4 h-4 text-natural-primary shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Update W-9 status, work order progress, and billing. Urgency and user assignment are managed by administrators or team leads.</span>
            </div>

            <button type="submit" class="w-full py-2.5 bg-natural-primary hover:bg-natural-primary-hover text-white font-semibold rounded-xl transition-colors shadow-sm uppercase tracking-wider text-[10px] font-mono">Save Work Order Updates</button>
        </form>

    <?php else: ?>
        <div class="space-y-4 text-xs">
            <div class="flex justify-between items-center py-2 border-b border-natural-border">
                <span class="font-bold text-natural-muted uppercase text-[9px] tracking-wider font-mono">W9 Clearance Required?</span>
                <span class="font-bold text-natural-text"><?= htmlspecialchars($job->w9) ?></span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-natural-border">
                <span class="font-bold text-natural-muted uppercase text-[9px] tracking-wider font-mono">Urgency Level</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-natural-pane text-natural-primary border border-natural-border"><?= htmlspecialchars($job->urgency) ?></span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-natural-border">
                <span class="font-bold text-natural-muted uppercase text-[9px] tracking-wider font-mono">SLA Deadline</span>
                <span class="font-semibold text-natural-text"><?= $job->sla_date ? date('M j, Y, g:i a', strtotime($job->sla_date)) : 'None' ?></span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-natural-border">
                <span class="font-bold text-natural-muted uppercase text-[9px] tracking-wider font-mono">Assigned User</span>
                <span class="font-semibold text-natural-text"><?= $job->assigned_to ? htmlspecialchars($job->assigned_name) : 'Unassigned' ?></span>
            </div>
            <div class="p-3 bg-natural-pane border border-natural-border rounded-xl text-[10px] text-natural-darkmute leading-relaxed flex items-start space-x-1.5 mt-2">
                <svg class="w-4 h-4 text-natural-primary shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>Only the assigned user or an administrator can update W-9 and billing on this work order.</span>
            </div>
        </div>
    <?php endif; ?>
</div>
