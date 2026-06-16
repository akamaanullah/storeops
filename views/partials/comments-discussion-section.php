<?php
use App\Models\Comment;

/** @var \App\Models\Job $job */
/** @var \App\Models\Comment[] $comments */
/** @var array $user */
$commentsLastReadAt = $commentsLastReadAt ?? null;
$commentsHasMore = $commentsHasMore ?? false;
$commentsNextOffset = $commentsNextOffset ?? count($comments);
$commentsTotal = $commentsTotal ?? count($comments);
$currentUserId = (int)$user['id'];

$unreadOnPage = 0;
foreach ($comments as $c) {
    if (Comment::isUnreadForUser($c, $commentsLastReadAt, $currentUserId)) {
        $unreadOnPage++;
    }
}
$latestCommentId = !empty($comments) ? (int)$comments[0]->id : 0;
?>
<div id="comments-section" class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 space-y-6"
     data-rt-job-ref="<?= htmlspecialchars($job->ref()) ?>"
     data-rt-job-id="<?= (int)$job->id ?>"
     data-rt-latest-comment-id="<?= $latestCommentId ?>"
     data-rt-current-user-id="<?= $currentUserId ?>"
     data-rt-current-user-role="<?= htmlspecialchars($user['role']) ?>">
    <div>
        <h2 class="font-bold text-natural-heading tracking-tight text-sm">Comments &amp; Updates</h2>
        <p class="text-[10px] text-natural-muted">Add notes, status updates, and attachments for this work order</p>
    </div>

    <form id="comment-form" action="<?= BASE_URL . $job->path() ?>/comment" method="POST" enctype="multipart/form-data" class="pb-4 border-b border-natural-border flex space-x-3.5 items-start">
        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
        <div class="w-8 h-8 rounded-full bg-natural-pane font-bold text-xs text-natural-primary flex items-center justify-center border border-natural-border select-none uppercase shrink-0">
            <?= htmlspecialchars(substr($user['name'], 0, 2)) ?>
        </div>
        <div class="flex-1 space-y-3">
            <?php include __DIR__ . '/comment-form-attachments.php'; ?>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-natural-primary hover:bg-natural-primary-hover text-white font-semibold text-xs rounded-xl transition-all shadow">Post Comment</button>
            </div>
        </div>
    </form>

    <div id="comments-timeline" class="space-y-4">
        <?php if (empty($comments)): ?>
            <p class="text-xs text-natural-muted py-4 text-center">No comments yet. Be the first to add an update.</p>
        <?php else: ?>
            <?php if ($unreadOnPage > 0): ?>
                <div class="comments-unread-banner text-[10px] font-semibold text-natural-primary bg-natural-pane border border-natural-border rounded-xl px-3 py-2 text-center">
                    <?= $unreadOnPage === 1 ? '1 new unread message' : $unreadOnPage . ' new unread messages' ?>
                </div>
            <?php endif; ?>

            <?php
                $separatorShown = false;
                $seenUnread = false;
                foreach ($comments as $comment):
                    $isUnread = Comment::isUnreadForUser($comment, $commentsLastReadAt, $currentUserId);
                    if ($isUnread) {
                        $seenUnread = true;
                    }
                    if (!$isUnread && $seenUnread && !$separatorShown):
                        include __DIR__ . '/comment-unread-separator.php';
                        $separatorShown = true;
                    endif;
                    include __DIR__ . '/comment-timeline-item.php';
                endforeach;
            ?>
        <?php endif; ?>
    </div>

    <?php if ($commentsHasMore): ?>
        <div id="comments-load-more-wrap" class="flex justify-center pt-1">
            <button
                type="button"
                id="comments-load-more-btn"
                data-job-ref="<?= htmlspecialchars($job->ref()) ?>"
                data-offset="<?= (int)$commentsNextOffset ?>"
                data-total="<?= (int)$commentsTotal ?>"
                class="px-4 py-2 text-xs font-semibold text-natural-primary bg-natural-pane border border-natural-border rounded-xl hover:bg-white transition-colors"
            >
                Load More
            </button>
        </div>
    <?php else: ?>
        <div id="comments-load-more-wrap" class="hidden"></div>
    <?php endif; ?>
</div>
