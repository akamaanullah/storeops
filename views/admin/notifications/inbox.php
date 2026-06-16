<?php
$title = "Notification Inbox - " . APP_NAME;
include __DIR__ . '/../layout/header.php';
?>

<!-- Header controls -->
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
    <div>
        <h1 class="text-2xl font-serif italic text-natural-heading tracking-tight">Notification Inbox</h1>
        <p class="text-xs text-natural-darkmute mt-1">Review notifications for assignments, updates, and activity</p>
    </div>
    
    <?php if (($pagination['total'] ?? count($notifications)) > 0): ?>
        <form action="<?= BASE_URL ?>/notifications/mark-read" method="POST" class="inline">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
            <button type="submit" class="px-4 py-2 bg-natural-subtle hover:bg-natural-pane text-natural-primary font-bold text-xs tracking-wider uppercase rounded-xl transition-all shadow-sm flex items-center space-x-1.5 focus:outline-none border border-natural-border">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Mark All Read</span>
            </button>
        </form>
    <?php endif; ?>
</div>

<!-- Notifications Card -->
<div class="bg-white border border-natural-border rounded-3xl overflow-hidden shadow-sm">
    <?php include __DIR__ . '/../../partials/notifications-inbox-list.php'; ?>
    <?php include __DIR__ . '/../../partials/pagination.php'; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
