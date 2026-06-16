<?php
$title = "Edit User - " . APP_NAME;
include __DIR__ . '/../layout/header.php';
?>

<!-- Form Card Layout -->
<div class="max-w-2xl mx-auto">
    <!-- Back control link -->
    <a href="<?= BASE_URL ?>/users" class="inline-flex items-center text-xs text-natural-primary font-bold hover:underline mb-6 space-x-1.5 focus:outline-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        <span>Back to Accounts</span>
    </a>

    <div class="bg-white border border-natural-border rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-natural-border bg-natural-pane/30">
            <h1 class="text-xl font-serif italic text-natural-heading tracking-tight">Edit User Account</h1>
            <p class="text-xs text-natural-darkmute mt-1">Modify credentials, roles, and administrative statuses for: <strong class="text-natural-heading font-semibold"><?= htmlspecialchars($targetUser->full_name) ?></strong></p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="m-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center space-x-2.5">
                <svg class="w-5 h-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <form action="<?= BASE_URL ?>/users/<?= $targetUser->id ?>/edit" method="POST" class="p-6 space-y-6 text-xs text-natural-text">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
            
            <?php $editOld = $old ?? []; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <?php
                $lnValue = $editOld['username'] ?? $targetUser->username;
                $lnError = $usernameError ?? '';
                $lnExcludeUserId = (int)$targetUser->id;
                $lnInitialValue = $targetUser->username;
                include __DIR__ . '/../../partials/login-name-field.php';
                ?>

                <?php
                $fnValue = $editOld['full_name'] ?? $targetUser->full_name;
                include __DIR__ . '/../../partials/full-name-field.php';
                ?>
            </div>

            <!-- Password (Optional) -->
            <?php
            $pfId = 'password';
            $pfName = 'password';
            $pfLabel = 'New Password (Leave blank to keep current)';
            $pfPlaceholder = 'Choose a new secure password or leave empty';
            $pfMinlength = null;
            include __DIR__ . '/../../partials/password-field.php';
            ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- System Role dropdown -->
                <div class="space-y-1.5">
                    <label for="role" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">System Role *</label>
                    <select id="role" name="role" required class="w-full px-3 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text">
                        <?php $editRole = $editOld['role'] ?? $targetUser->role; ?>
                        <option value="user" <?= $editRole === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="team_lead" <?= $editRole === 'team_lead' ? 'selected' : '' ?>>Team Lead</option>
                        <option value="admin" <?= $editRole === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    </select>
                </div>

                <!-- Account Status dropdown -->
                <div class="space-y-1.5">
                    <label for="status" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Account Status *</label>
                    <select id="status" name="status" required <?= ($targetUser->id === (int)$currentUser['id']) ? 'disabled' : '' ?> class="w-full px-3 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text disabled:opacity-60 disabled:cursor-not-allowed">
                        <?php $editStatus = $editOld['status'] ?? $targetUser->status; ?>
                        <option value="active" <?= $editStatus === 'active' ? 'selected' : '' ?>>Active / Operational</option>
                        <option value="suspended" <?= $editStatus === 'suspended' ? 'selected' : '' ?>>Suspended / Blocked</option>
                    </select>
                    <?php if ($targetUser->id === (int)$currentUser['id']): ?>
                        <input type="hidden" name="status" value="active">
                        <span class="text-[10px] text-natural-muted block mt-1">Status can't be modified for your own logged-in admin session.</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submit action -->
            <div class="flex justify-end pt-4 border-t border-natural-border">
                <button type="submit" class="px-6 py-3 bg-natural-primary hover:bg-natural-primary-hover text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../partials/login-name-validation-scripts.php'; ?>
<?php include __DIR__ . '/../layout/footer.php'; ?>
