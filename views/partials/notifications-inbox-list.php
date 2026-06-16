<?php
use App\Models\User;
use App\Models\Job;

/** @var array $notifications */
?>
<?php if (empty($notifications)): ?>
    <div class="p-16 text-center">
        <div class="w-16 h-16 bg-natural-pane text-natural-muted rounded-full flex items-center justify-center mx-auto mb-4 border border-natural-border">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
        </div>
        <h3 class="font-bold text-natural-heading text-sm">Your inbox is clear</h3>
        <p class="text-xs text-natural-muted mt-1">We will notify you when new comments or assignments occur.</p>
    </div>
<?php else: ?>
    <div class="divide-y divide-natural-border">
        <?php foreach ($notifications as $n): ?>
            <?php
                $isUnread = !(int)$n['is_read'];
                $jobId = User::resolveJobId($n);
                $jobRef = $n['job_ref'] ?? ($jobId ? Job::refFor($jobId) : null);
                $isCommentNotice = User::linksToCommentsSection($n['message'] ?? '');
                $jobUrl = $jobId
                    ? BASE_URL . Job::pathFor($jobId) . ($isCommentNotice ? '#comments-timeline' : '')
                    : null;
            ?>
            <div class="p-5 flex items-start justify-between transition-colors <?= $isUnread ? 'bg-natural-subtle/25 hover:bg-natural-subtle/40' : 'hover:bg-natural-subtle/10' ?>">
                <div class="flex items-start space-x-3.5 pr-4 min-w-0 flex-1">
                    <div class="mt-1.5 shrink-0">
                        <?php if ($isUnread): ?>
                            <span class="block w-2.5 h-2.5 bg-natural-primary rounded-full ring-4 ring-natural-subtle/50"></span>
                        <?php else: ?>
                            <span class="block w-2.5 h-2.5 bg-natural-border rounded-full"></span>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-1.5 min-w-0">
                        <p class="text-xs text-natural-text leading-relaxed font-normal <?= $isUnread ? 'font-semibold text-natural-heading' : '' ?>">
                            <?= htmlspecialchars(User::displayMessage($n)) ?>
                        </p>
                        <span class="block text-[10px] text-natural-muted font-medium font-mono">
                            <?= date('Y-m-d H:i:s', strtotime($n['created_at'])) ?>
                        </span>
                        <?php if ($jobUrl && $jobRef): ?>
                            <a href="<?= htmlspecialchars($jobUrl) ?>" class="inline-flex items-center gap-1 text-[10px] font-bold text-natural-primary hover:underline font-mono">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                Open <?= htmlspecialchars($jobRef) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($isUnread): ?>
                    <form action="<?= BASE_URL ?>/notifications/mark-one-read" method="POST" class="inline shrink-0">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                        <input type="hidden" name="notification_id" value="<?= (int)$n['id'] ?>">
                        <button type="submit" class="text-[10px] font-bold text-natural-primary hover:text-natural-primary-hover hover:underline whitespace-nowrap focus:outline-none flex items-center space-x-1 pl-2" title="Mark as Read">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Mark read</span>
                        </button>
                    </form>
                <?php else: ?>
                    <span class="text-[9px] text-natural-muted uppercase font-bold tracking-wider font-mono select-none shrink-0">Read</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
