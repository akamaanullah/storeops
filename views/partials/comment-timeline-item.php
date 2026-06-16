<?php
/** @var \App\Models\Comment $comment */
/** @var bool $isUnread */
$isUnread = $isUnread ?? false;
$itemClass = 'flex items-start space-x-4 p-4 border border-natural-border rounded-2xl hover:bg-natural-pane/30 transition-colors text-xs text-natural-text leading-relaxed';
if ($isUnread) {
    $itemClass .= ' comment-unread';
}
?>
<div class="<?= $itemClass ?>" data-comment-id="<?= (int)$comment->id ?>">
    <div class="w-8 h-8 rounded-full bg-natural-pane border border-natural-border flex items-center justify-center font-bold text-xs shrink-0 text-natural-primary uppercase">
        <?= htmlspecialchars(substr($comment->user_name, 0, 2)) ?>
    </div>
    <div class="flex-1 space-y-2">
        <div class="flex items-center justify-between">
            <div>
                <span class="font-bold text-natural-heading text-xs"><?= htmlspecialchars($comment->user_name) ?></span>
                <span class="ml-1.5 px-2 py-0.5 bg-natural-pane text-natural-primary font-bold uppercase text-[8px] rounded border border-natural-border/50"><?= htmlspecialchars($comment->user_role) ?></span>
                <?php if ($isUnread): ?>
                    <span class="ml-1.5 px-1.5 py-0.5 bg-natural-subtle text-natural-primary font-bold uppercase text-[8px] rounded border border-natural-border">New</span>
                <?php endif; ?>
            </div>
            <span class="text-[10px] text-natural-muted"><?= date('M j, Y, H:i', strtotime($comment->created_at)) ?></span>
        </div>
        <div class="comment-text-wrapper space-y-2">
            <?php if ($comment->comment !== null && $comment->comment !== ''): ?>
                <p class="text-natural-darkmute leading-relaxed text-xs comment-body-text"><?= htmlspecialchars($comment->comment) ?></p>
            <?php endif; ?>
        </div>

        <?php
            $pictures = $comment->pictures;
            $commentId = $comment->id;
            include __DIR__ . '/comment-attachments.php';
        ?>

        <div class="flex items-center space-x-4 pt-1">
            <button onclick="castVote(<?= $comment->id ?>, 'like', this)" class="inline-flex items-center space-x-1.5 text-natural-muted hover:text-natural-primary font-medium select-none focus:outline-none transition-colors <?= $comment->user_vote === 'like' ? 'text-natural-primary font-bold' : '' ?>">
                <svg class="w-4 h-4 stroke-current <?= $comment->user_vote === 'like' ? 'text-natural-primary fill-natural-primary' : 'text-natural-muted fill-none' ?>" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2m0 10V10"></path>
                </svg>
                <span class="likes-count text-[11px]"><?= $comment->likes ?></span>
            </button>
            <button onclick="castVote(<?= $comment->id ?>, 'dislike', this)" class="inline-flex items-center space-x-1.5 text-natural-muted hover:text-natural-primary font-medium select-none focus:outline-none transition-colors <?= $comment->user_vote === 'dislike' ? 'text-natural-primary font-bold' : '' ?>">
                <svg class="w-4 h-4 stroke-current <?= $comment->user_vote === 'dislike' ? 'text-natural-primary fill-natural-primary' : 'text-natural-muted fill-none' ?>" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v5a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m7-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2m0-10V4"></path>
                </svg>
                <span class="dislikes-count text-[11px]"><?= $comment->dislikes ?></span>
            </button>
            <?php if ((int)$comment->user_id === $currentUserId): ?>
                <span class="text-natural-border/60 select-none text-[10px]">•</span>
                <button onclick="startEditComment(<?= $comment->id ?>, this)" class="text-natural-muted hover:text-natural-primary font-medium focus:outline-none transition-colors text-[11px]">Edit</button>
            <?php endif; ?>
        </div>
    </div>
</div>
