<?php
$title = "Work Order Profile - " . APP_NAME;
include __DIR__ . '/../layout/header.php';

$isAdminOrTL = in_array($user['role'], ['admin', 'team_lead']);
$isAssigned = ($job->assigned_to === (int)$user['id']);
?>

<!-- Directory Link back -->
<div class="mb-6">
    <a href="<?= BASE_URL ?>/jobs" class="inline-flex items-center text-xs text-natural-primary font-bold hover:underline space-x-1.5 focus:outline-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        <span>Back to Work Orders</span>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Left 2 column blocks: Job Details + pictures + Comments feedback -->
    <div class="lg:col-span-2 space-y-8">
        
        <!-- Main Job Details Profile Card -->
        <div class="bg-white border border-natural-border rounded-3xl shadow-sm overflow-hidden">
            <!-- Header status strip -->
            <div class="px-6 py-5 border-b border-natural-border flex justify-between items-center bg-natural-pane/30">
                <div>
                    <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono"><?= htmlspecialchars($job->ref()) ?> spec sheet</span>
                    <h1 class="text-xl font-serif italic text-natural-heading tracking-tight mt-1"><?= htmlspecialchars($job->store_name) ?></h1>
                </div>
                <!-- Status -->
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
                <span class="px-3 py-1 text-xs font-bold rounded-full border <?= $statusBadgeClass ?>"><?= $job->status ?></span>
            </div>

            <!-- Profile Info Body -->
            <div class="p-6 space-y-6 text-xs text-natural-text">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 leading-relaxed">
                    <div class="space-y-1">
                        <span class="block text-[9px] font-bold text-natural-muted uppercase tracking-widest font-mono">Store / Location</span>
                        <p class="font-bold text-natural-text text-sm"><?= htmlspecialchars($job->location) ?></p>
                    </div>
                    <div class="space-y-1">
                        <span class="block text-[9px] font-bold text-natural-muted uppercase tracking-widest font-mono">Scope Designation</span>
                        <p class="font-bold text-natural-text text-sm"><?= htmlspecialchars($job->designation) ?></p>
                    </div>
                </div>

                <div class="space-y-1.5 leading-relaxed pt-4 border-t border-natural-border">
                    <span class="block text-[9px] font-bold text-natural-muted uppercase tracking-widest font-mono">Technical Location Address</span>
                    <p class="font-medium text-natural-text text-xs"><?= htmlspecialchars($job->address) ?></p>
                </div>

                <div class="space-y-2 leading-relaxed pt-4 border-t border-natural-border">
                    <span class="block text-[9px] font-bold text-natural-muted uppercase tracking-widest font-mono">Reported Maintenance Issue</span>
                    <p class="text-xs text-natural-darkmute whitespace-pre-line leading-relaxed font-normal bg-natural-pane p-4 rounded-xl border border-natural-border"><?= htmlspecialchars($job->issue) ?></p>
                </div>

                <?php include __DIR__ . '/../../partials/job-gallery.php'; ?>

                <!-- Footer Quick Execution complete buttons -->
                <div class="flex justify-between items-center pt-6 border-t border-natural-border flex-wrap gap-4">
                    <div class="text-[10px] text-natural-muted">
                        <span class="block">Work Order Created: <strong class="text-natural-darkmute"><?= date('F j, Y, g:i a', strtotime($job->created_at)) ?></strong></span>
                        <?php if ($job->sla_date): ?>
                            <span class="block mt-0.5">SLA Deadline: <strong class="text-rose-600"><?= date('F j, Y, g:i a', strtotime($job->sla_date)) ?></strong></span>
                        <?php endif; ?>
                        <span class="block mt-0.5">Assigned User: <strong class="text-natural-darkmute"><?= $job->assigned_to ? htmlspecialchars($job->assigned_name) : 'Unassigned' ?></strong></span>
                    </div>

                    <?php if ($job->status !== 'Done' && ($isAssigned || $isAdminOrTL)): ?>
                        <form action="<?= BASE_URL . $job->path() ?>/complete" method="POST" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                            <input type="hidden" name="action_type" value="complete">
                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-colors shadow flex items-center space-x-1.5 leading-none focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Complete Work Order</span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Comments and Feedback Discussion Thread Section -->
        <?php include __DIR__ . '/../../partials/comments-discussion-section.php'; ?>
    </div>

    <!-- Right 1 column block: Role configuration boards + Payments histories list -->
    <div class="space-y-8">
        <?php include __DIR__ . '/../../partials/job-spec-panel.php'; ?>
        <?php include __DIR__ . '/../../partials/job-payment-panel.php'; ?>
    </div>
</div>

<?php include __DIR__ . '/../../partials/attachment-lightbox.php'; ?>
<?php include __DIR__ . '/../../partials/attachment-scripts.php'; ?>
<?php include __DIR__ . '/../../partials/job-show-comment-scripts.php'; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
