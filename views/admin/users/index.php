<?php
$title = "Manage Users - " . APP_NAME;
include __DIR__ . '/../layout/header.php';
?>

<!-- Header controls -->
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
    <div>
        <h1 class="text-2xl font-serif italic text-natural-heading tracking-tight">User Accounts</h1>
        <p class="text-xs text-natural-darkmute mt-1">Manage and audit system users, roles, and access permissions</p>
    </div>
    
    <a href="<?= BASE_URL ?>/users/create" class="px-5 py-2.5 bg-natural-primary hover:bg-natural-primary-hover text-white font-bold text-xs uppercase tracking-wider rounded-full transition-all shadow-sm flex items-center space-x-1.5 focus:outline-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m2 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>Add New User</span>
    </a>
</div>

<!-- Users List Directory Card -->
<div class="bg-white border border-natural-border rounded-3xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <?php if (empty($users)): ?>
            <div class="p-10 text-center text-xs text-natural-muted">
                No user accounts found.
            </div>
        <?php else: ?>
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-natural-pane font-bold uppercase tracking-wider text-[9px] text-natural-muted font-mono border-b border-natural-border">
                    <tr>
                        <th class="py-4 px-6">Name & Email</th>
                        <th class="py-4 px-4 text-center">System Role</th>
                        <th class="py-4 px-4 text-center">Created At</th>
                        <th class="py-4 px-4 text-center">Status Badge</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-natural-border text-natural-text">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-natural-subtle/20 transition-colors">
                            <td class="py-4 px-6 font-semibold">
                                <span class="text-natural-heading font-bold block text-left text-sm"><?= htmlspecialchars($u->full_name) ?></span>
                                <span class="text-[10px] text-natural-muted block mt-0.5 leading-relaxed font-mono">@<?= htmlspecialchars($u->username) ?></span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <?php
                                $roleBadgeClass = 'bg-natural-pane text-natural-primary border-natural-border/50';
                                switch ($u->role) {
                                    case 'admin': $roleBadgeClass = 'bg-rose-50 text-rose-700 border-rose-200'; break;
                                    case 'team_lead': $roleBadgeClass = 'bg-blue-50 text-blue-700 border-blue-200'; break;
                                    case 'user': $roleBadgeClass = 'bg-natural-pane text-natural-primary border-natural-border'; break;
                                }
                                ?>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border uppercase font-mono <?= $roleBadgeClass ?>"><?= htmlspecialchars($u->role) ?></span>
                            </td>
                            <td class="py-4 px-4 text-center text-natural-darkmute font-medium font-mono">
                                <?= date('Y-m-d H:i', strtotime($u->created_at)) ?>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <?php
                                $statusBadgeClass = ($u->status === 'suspended') 
                                    ? 'bg-amber-50 text-amber-700 border-amber-200' 
                                    : 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                ?>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border uppercase <?= $statusBadgeClass ?>"><?= htmlspecialchars($u->status) ?></span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Edit Link -->
                                    <a href="<?= BASE_URL ?>/users/<?= $u->id ?>/edit" class="px-3 py-1.5 bg-white border border-natural-border hover:bg-natural-pane text-natural-darkmute hover:text-natural-heading rounded-lg font-bold text-[10px] transition-colors focus:outline-none shadow-sm flex items-center space-x-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        <span>Edit</span>
                                    </a>

                                    <?php if ($u->id !== (int)$currentUser['id']): ?>
                                        <!-- Suspend / Activate Form Toggle -->
                                        <form action="<?= BASE_URL ?>/users/<?= $u->id ?>/status" method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                                            <button type="submit" class="px-3 py-1.5 border rounded-lg font-bold text-[10px] transition-colors focus:outline-none shadow-sm flex items-center space-x-1 <?= $u->status === 'suspended' ? 'bg-emerald-50 hover:bg-emerald-100 border-emerald-250 text-emerald-700' : 'bg-amber-50 hover:bg-amber-100 border-amber-250 text-amber-700' ?>">
                                                <?php if ($u->status === 'suspended'): ?>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span>Activate</span>
                                                <?php else: ?>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                    </svg>
                                                    <span>Suspend</span>
                                                <?php endif; ?>
                                            </button>
                                        </form>

                                        <!-- Delete Form Action -->
                                        <form action="<?= BASE_URL ?>/users/<?= $u->id ?>/delete" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete user: <?= htmlspecialchars(addslashes($u->full_name)) ?>?');">
                                            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                                            <button type="submit" class="px-3 py-1.5 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 rounded-lg font-bold text-[10px] transition-colors focus:outline-none shadow-sm flex items-center space-x-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-[10px] text-natural-muted italic px-2">Self Session</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
