<?php
/** @var \App\Models\Job[] $myActiveJobs */
/** @var array<int, int> $queueUnreadComments */

$myActiveJobs = $myActiveJobs ?? [];
$queueUnreadComments = $queueUnreadComments ?? [];
?>
<div class="bg-white border border-natural-border rounded-3xl shadow-sm overflow-hidden flex flex-col">
    <div class="px-6 py-5 border-b border-natural-border bg-natural-pane/30 flex justify-between items-start gap-3">
        <div>
            <h2 class="font-bold text-natural-heading tracking-tight text-sm">My Work Queue</h2>
            <p class="text-[10px] text-natural-muted font-medium mt-0.5">Assigned work orders that require your attention</p>
        </div>
        <?php if (!empty($myActiveJobs)): ?>
            <span class="shrink-0 px-2 py-1 rounded-lg bg-natural-primary text-white text-[9px] font-bold uppercase tracking-wide">
                <?= count($myActiveJobs) ?> active
            </span>
        <?php endif; ?>
    </div>
    <div class="p-4 flex-1">
        <?php if (empty($myActiveJobs)): ?>
            <div class="text-center py-8 px-3 space-y-3">
                <div class="mx-auto w-10 h-10 rounded-xl bg-natural-pane border border-natural-border flex items-center justify-center text-natural-muted">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-natural-heading">No active assignments</p>
                <p class="text-[10px] text-natural-muted leading-relaxed max-w-[14rem] mx-auto">
                    When an administrator or team lead assigns you a work order, it will appear here for status updates, comments, and completion.
                </p>
                <a href="<?= BASE_URL ?>/jobs" class="inline-block text-[10px] font-bold text-natural-primary hover:underline">Browse all jobs</a>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($myActiveJobs as $job): ?>
                    <?php
                        $unread = (int)($queueUnreadComments[(int)$job->id] ?? 0);
                        $statusBadgeClass = match ($job->status) {
                            'New' => 'bg-blue-50 text-blue-600 border-blue-100',
                            'Assigned' => 'bg-orange-50 text-orange-600 border-orange-100',
                            'Scheduled' => 'bg-purple-50 text-purple-600 border-purple-100',
                            'Work In Progress' => 'bg-amber-50 text-amber-700 border-amber-100',
                            'Pending' => 'bg-rose-50 text-rose-600 border-rose-100',
                            default => 'bg-natural-pane text-natural-primary border-natural-border',
                        };
                    ?>
                    <div class="p-4 bg-natural-pane/40 border border-natural-border rounded-2xl space-y-2.5 hover:border-natural-primary/30 transition-colors" data-rt-queue-job-id="<?= (int)$job->id ?>">
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0">
                                <a href="<?= BASE_URL . $job->path() ?>" class="font-bold text-natural-heading text-xs hover:underline hover:text-natural-primary truncate block">
                                    <?= htmlspecialchars($job->ref()) ?> · <?= htmlspecialchars($job->store_name) ?>
                                </a>
                                <?php if ($job->location): ?>
                                    <span class="block text-[10px] text-natural-muted mt-0.5 truncate"><?= htmlspecialchars($job->location) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="shrink-0 px-1.5 py-0.5 border text-[8px] font-bold uppercase rounded <?= $statusBadgeClass ?>"><?= htmlspecialchars($job->status) ?></span>
                        </div>

                        <?php if ($job->issue): ?>
                            <p class="text-[10px] text-natural-darkmute line-clamp-2 leading-relaxed"><?= htmlspecialchars($job->issue) ?></p>
                        <?php endif; ?>

                        <div class="flex flex-wrap items-center gap-2 pt-1" data-rt-queue-badges>
                            <?php if ($job->urgency === 'Urgent'): ?>
                                <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase border bg-natural-subtle text-natural-primary border-natural-border">Urgent</span>
                            <?php endif; ?>
                            <span class="text-[9px] text-natural-muted"><?= htmlspecialchars($job->designation) ?></span>
                            <?php if ($unread > 0): ?>
                                <span class="rt-queue-unread px-1.5 py-0.5 rounded text-[8px] font-bold bg-natural-primary text-white"><?= $unread ?> unread</span>
                            <?php endif; ?>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t border-natural-border/60">
                            <a href="<?= BASE_URL . $job->path() ?>" class="text-[10px] font-bold text-natural-primary hover:underline">Open job &rarr;</a>
                            <form action="<?= BASE_URL . $job->path() ?>/complete" method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                                <input type="hidden" name="action_type" value="complete">
                                <button type="submit" class="px-3 py-1.5 bg-natural-primary hover:bg-natural-primary-hover text-white font-bold text-[9px] uppercase rounded-lg transition-all shadow-sm">
                                    Mark Done
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="<?= BASE_URL ?>/jobs/mine" class="block text-center text-[10px] font-bold text-natural-primary hover:underline mt-4 pt-2 border-t border-natural-border/60">
                View all my assigned jobs
            </a>
        <?php endif; ?>
    </div>
</div>
