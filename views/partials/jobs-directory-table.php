<?php
/** @var array $jobs */
/** @var array<int,int> $unreadComments */
$unreadComments = $unreadComments ?? [];
?>
<?php if (empty($jobs)): ?>
    <div class="p-10 text-center text-xs text-natural-muted">
        No work orders match your filters.
    </div>
<?php else: ?>
    <table class="w-full text-left text-xs border-collapse">
        <thead class="bg-natural-pane font-bold uppercase tracking-wider text-[9px] text-natural-muted font-mono border-b border-natural-border">
            <tr>
                <th class="py-4 px-6">Store / Location</th>
                <th class="py-4 px-4">Designation</th>
                <th class="py-4 px-4 text-center">Assigned User</th>
                <th class="py-4 px-4 text-center">Status</th>
                <th class="py-4 px-4 text-center">Urgency</th>
                <?php if (in_array(Auth::user()['role'] ?? '', ['admin', 'team_lead'], true)): ?>
                    <th class="py-4 px-4 text-center">Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-natural-border text-natural-text">
            <?php foreach ($jobs as $job): ?>
                <?php $unread = (int)($unreadComments[$job->id] ?? 0); ?>
                <tr class="hover:bg-natural-subtle/20 transition-colors <?= $unread > 0 ? 'bg-rose-50/30' : '' ?>" data-rt-job-row="<?= (int)$job->id ?>">
                    <td class="py-4 px-6 font-semibold" data-rt-job-store-cell>
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 rt-job-title-wrap">
                            <a href="<?= BASE_URL . $job->path() ?>" class="text-natural-primary font-bold hover:underline"><?= htmlspecialchars($job->store_name) ?></a>
                            <?php if ($unread > 0): ?>
                                <a href="<?= BASE_URL . $job->path() ?>#comments-timeline" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold hover:bg-rose-100 transition-colors" title="Unread comments">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    <?= $unread ?> unread comment<?= $unread === 1 ? '' : 's' ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <span class="text-[10px] text-natural-darkmute block mt-1 leading-relaxed font-normal">
                            <span class="font-mono text-[9px] text-natural-muted"><?= htmlspecialchars($job->ref()) ?></span>
                            · Location: <?= htmlspecialchars($job->location) ?> • <?= htmlspecialchars($job->address) ?>
                            <span class="block text-[9px] text-natural-muted mt-0.5 font-medium">
                                Added: <span class="text-natural-heading font-semibold"><?= date('M j, Y', strtotime($job->created_at)) ?></span>
                                <?php if ($job->sla_date): ?>
                                    · SLA: <span class="text-rose-600 font-extrabold"><?= date('M j, Y', strtotime($job->sla_date)) ?></span>
                                <?php endif; ?>
                            </span>
                        </span>
                    </td>
                    <td class="py-4 px-4 text-natural-text font-medium"><?= htmlspecialchars($job->designation) ?></td>
                    <td class="py-4 px-4 text-center">
                        <?php if ($job->assigned_to): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-natural-pane text-natural-primary border border-natural-border/50">
                                <?= htmlspecialchars($job->assigned_name) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-[10px] text-natural-muted italic">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-4 px-4 text-center">
                        <?php
                        $statusBadgeClass = 'bg-slate-100 text-slate-800 border-slate-200';
                        switch ($job->status) {
                            case 'New': $statusBadgeClass = 'bg-blue-50 text-blue-600 border border-blue-100'; break;
                            case 'Assigned': $statusBadgeClass = 'bg-orange-50 text-orange-600 border border-orange-100'; break;
                            case 'Scheduled': $statusBadgeClass = 'bg-purple-50 text-purple-600 border border-purple-100'; break;
                            case 'Work In Progress': $statusBadgeClass = 'bg-amber-50 text-amber-700 border border-amber-100'; break;
                            case 'Pending': $statusBadgeClass = 'bg-rose-50 text-rose-600 border border-rose-100'; break;
                            case 'Cancelled': $statusBadgeClass = 'bg-slate-100 text-slate-600 border border-slate-200'; break;
                            case 'Done': $statusBadgeClass = 'bg-emerald-50 text-emerald-600 border border-emerald-150'; break;
                        }
                        ?>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold border <?= $statusBadgeClass ?>"><?= $job->status ?></span>
                    </td>
                    <td class="py-4 px-4 text-center">
                        <?php
                        $urgencyBadgeClass = $job->urgency === 'Urgent'
                            ? 'bg-rose-50 text-rose-600 border-rose-100'
                            : 'bg-natural-pane text-natural-primary border-natural-border';
                        ?>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $urgencyBadgeClass ?>"><?= $job->urgency ?></span>
                    </td>
                    <?php if (in_array(Auth::user()['role'] ?? '', ['admin', 'team_lead'], true)): ?>
                        <td class="py-4 px-4 text-center">
                            <form action="<?= BASE_URL . $job->path() ?>/delete" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete this work order?');">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                                <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 hover:text-rose-700 rounded-lg transition-colors" title="Delete Job">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
