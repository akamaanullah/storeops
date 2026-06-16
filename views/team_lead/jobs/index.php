<?php
$title = "Work Orders - " . APP_NAME;
include __DIR__ . '/../layout/header.php';
?>

<!-- Header controls -->
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
    <div>
        <h1 class="text-2xl font-serif italic text-natural-heading tracking-tight">Work Orders</h1>
        <p class="text-xs text-natural-darkmute mt-1">Search and filter all work orders</p>
    </div>
    
    <?php if (in_array($user['role'], ['admin', 'team_lead'])): ?>
        <a href="<?= BASE_URL ?>/jobs/create" class="px-5 py-2.5 bg-natural-primary hover:bg-natural-primary-hover text-white font-bold text-xs uppercase tracking-wider rounded-full transition-all shadow-sm flex items-center space-x-1.5 focus:outline-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Create Work Order</span>
        </a>
    <?php endif; ?>
</div>

<!-- Filter controls -->
<div class="bg-white p-6 border border-natural-border rounded-3xl shadow-sm text-xs mb-6">
    <form action="<?= BASE_URL ?>/jobs" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 text-natural-text leading-relaxed font-normal">
        <!-- Search field -->
        <div class="md:col-span-2 space-y-1">
            <label for="search-input" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Search Keywords</label>
            <div class="relative">
                <input id="search-input" type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Search store name, location or task..." class="w-full pl-8 pr-3 py-2.5 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/30 rounded-xl text-xs bg-natural-bg/50 text-natural-text">
                <span class="absolute left-3 top-3 text-natural-muted">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
            </div>
        </div>

        <!-- Status selection -->
        <div class="space-y-1">
            <label for="status-selector" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Status</label>
            <select id="status-selector" name="status" class="w-full px-2 py-2.5 border border-natural-border rounded-xl bg-natural-bg/50 text-natural-text focus:outline-none focus:ring-2 focus:ring-natural-primary/30">
                <option value="">-- All Statuses --</option>
                <option value="New" <?= ($filters['status'] ?? '') === 'New' ? 'selected' : '' ?>>New</option>
                <option value="Assigned" <?= ($filters['status'] ?? '') === 'Assigned' ? 'selected' : '' ?>>Assigned</option>
                <option value="Scheduled" <?= ($filters['status'] ?? '') === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                <option value="Work In Progress" <?= ($filters['status'] ?? '') === 'Work In Progress' ? 'selected' : '' ?>>Work In Progress</option>
                <option value="Pending" <?= ($filters['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Done" <?= ($filters['status'] ?? '') === 'Done' ? 'selected' : '' ?>>Done</option>
            </select>
        </div>

        <!-- Urgency level -->
        <div class="space-y-1">
            <label for="urgency-selector" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Urgency level</label>
            <select id="urgency-selector" name="urgency" class="w-full px-2 py-2.5 border border-natural-border rounded-xl bg-natural-bg/50 text-natural-text focus:outline-none focus:ring-2 focus:ring-natural-primary/30">
                <option value="">-- All Priorities --</option>
                <option value="Within SLA" <?= ($filters['urgency'] ?? '') === 'Within SLA' ? 'selected' : '' ?>>Within SLA</option>
                <option value="Urgent" <?= ($filters['urgency'] ?? '') === 'Urgent' ? 'selected' : '' ?>>Urgent</option>
            </select>
        </div>

        <!-- Filter buttons -->
        <div class="md:col-span-4 flex justify-between items-center mt-5 pt-3 border-t border-natural-border">
            <a href="<?= BASE_URL ?>/jobs" class="font-bold text-natural-muted hover:text-natural-primary">Clear all filters</a>
            <button type="submit" class="px-5 py-2.5 bg-natural-primary text-white font-bold rounded-xl shadow-sm hover:bg-natural-primary-hover transition-colors">Apply Filters</button>
        </div>
    </form>
</div>

<!-- Jobs List Directory Card -->
<div class="bg-white border border-natural-border rounded-3xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <?php include __DIR__ . '/../../partials/jobs-directory-table.php'; ?>
    </div>
    <?php include __DIR__ . '/../../partials/pagination.php'; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
