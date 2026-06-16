<?php
$title = "Administrative System Logs - " . APP_NAME;
include __DIR__ . '/../layout/header.php';
?>

<!-- Header controls -->
<div class="mb-6">
    <h1 class="text-2xl font-serif italic text-natural-heading tracking-tight">Audit Logs</h1>
    <p class="text-xs text-natural-darkmute mt-1">Track system activity including logins, status changes, and user actions</p>
</div>

<!-- Logs lists Card panel -->
<div class="bg-white border border-natural-border rounded-3xl shadow-sm overflow-hidden text-xs">
    <div class="overflow-x-auto">
        <?php if (empty($logs)): ?>
            <div class="p-8 text-center text-sm text-natural-muted font-medium italic">
                No activity recorded yet.
            </div>
        <?php else: ?>
            <table class="w-full text-left border-collapse text-natural-text">
                <thead>
                    <tr class="bg-natural-pane text-[9px] font-bold uppercase tracking-wider text-natural-muted border-b border-natural-border font-mono">
                        <th class="py-4 px-6">Timestamp (UTC)</th>
                        <th class="py-4 px-4">User</th>
                        <th class="py-4 px-4">Action</th>
                        <th class="py-4 px-4">Work Order</th>
                        <th class="py-4 px-4">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-natural-border text-xs text-natural-text">
                    <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-natural-subtle/20 transition-colors">
                            <td class="py-4 px-6 text-natural-muted font-mono text-[11px] whitespace-nowrap">
                                <?= date('Y-m-d H:i:s', strtotime($log->created_at)) ?>
                            </td>
                            <td class="py-4 px-4 font-bold text-natural-heading flex items-center space-x-2.5">
                                <div class="w-6 h-6 rounded-full bg-natural-pane text-natural-primary font-semibold text-[10px] flex items-center justify-center border border-natural-border select-none uppercase leading-none">
                                    <?= substr($log->user_name, 0, 2) ?>
                                </div>
                                <span class="block truncate max-w-[120px]"><?= htmlspecialchars($log->user_name) ?></span>
                            </td>
                            <td class="py-4 px-4">
                                <?php
                                $badgeClass = 'bg-slate-100 text-slate-650';
                                switch ($log->action) {
                                    case 'login': $badgeClass = 'bg-natural-pane text-natural-primary border-natural-border'; break;
                                    case 'logout': $badgeClass = 'bg-natural-subtle/30 text-natural-muted border-natural-border/50'; break;
                                    case 'job_create': $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-100'; break;
                                    case 'job_edit': $badgeClass = 'bg-amber-50 text-amber-700 border-amber-100'; break;
                                    case 'status_change': $badgeClass = 'bg-purple-50 text-purple-700 border-purple-100'; break;
                                    case 'comment_add': $badgeClass = 'bg-blue-50 text-blue-700 border-blue-100'; break;
                                    case 'payment_add': $badgeClass = 'bg-pink-50 text-pink-700 border-pink-100'; break;
                                    case 'assignment_change': $badgeClass = 'bg-amber-50 text-amber-700 border-amber-100'; break;
                                }
                                ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold border uppercase tracking-wider font-mono <?= $badgeClass ?>"><?= $log->action ?></span>
                            </td>
                            <td class="py-4 px-4">
                                <?php if ($log->job_id): ?>
                                    <a href="<?= BASE_URL ?>/jobs/<?= $log->job_id ?>" class="text-natural-primary font-bold hover:underline block truncate max-w-[150px]" title="View work order">
                                        <?= htmlspecialchars($log->job_name ?: '#' . $log->job_id) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-natural-muted select-none">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 text-natural-darkmute leading-relaxed font-normal font-sans">
                                <?= htmlspecialchars($log->detail) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php include __DIR__ . '/../../partials/pagination.php'; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
