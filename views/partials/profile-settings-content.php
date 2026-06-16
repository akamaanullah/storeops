<?php
/** @var array $user */

use App\Core\RoleLabels;

$roleLabel = RoleLabels::label($user['role'] ?? null);
$initials = strtoupper(substr(trim($user['name'] ?? 'U'), 0, 2));
?>
<div class="mb-8">
    <h1 class="text-2xl font-serif italic text-natural-heading tracking-tight">Profile Settings</h1>
    <p class="text-xs text-natural-darkmute mt-1">Update your display name and account password.</p>
</div>

<form action="<?= BASE_URL ?>/settings/profile" method="POST" class="max-w-5xl bg-white border border-natural-border rounded-3xl shadow-sm overflow-hidden">
    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">

    <div class="grid grid-cols-1 lg:grid-cols-2 lg:divide-x divide-natural-border">
        <!-- Account -->
        <section class="p-6 sm:p-8 space-y-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-natural-pane border border-natural-border flex items-center justify-center text-natural-primary font-mono font-bold text-lg uppercase shrink-0">
                    <?= htmlspecialchars($initials) ?>
                </div>
                <div class="min-w-0 pt-0.5">
                    <h2 class="font-bold text-natural-heading text-sm">Account Details</h2>
                    <p class="text-[10px] text-natural-muted mt-1 leading-relaxed">Your login name is managed by an administrator.</p>
                    <span class="inline-flex mt-2 px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase tracking-wide bg-natural-subtle text-natural-darkmute border border-natural-border">
                        <?= htmlspecialchars($roleLabel) ?>
                    </span>
                </div>
            </div>

            <div class="space-y-5">
                <div class="space-y-1.5">
                    <label for="login_username" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Login Name</label>
                    <input
                        id="login_username"
                        type="text"
                        value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                        disabled
                        class="w-full px-4 py-3 border border-natural-border rounded-xl bg-natural-pane/60 text-xs text-natural-muted cursor-not-allowed font-mono"
                    >
                </div>

                <div class="space-y-1.5">
                    <label for="full_name" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Full Name *</label>
                    <input
                        id="full_name"
                        type="text"
                        name="full_name"
                        required
                        value="<?= htmlspecialchars($user['full_name'] ?? $user['name'] ?? '') ?>"
                        placeholder="Your full name"
                        class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text"
                    >
                </div>
            </div>
        </section>

        <!-- Password -->
        <section class="p-6 sm:p-8 space-y-5 bg-natural-pane/15 lg:bg-transparent border-t lg:border-t-0 border-natural-border">
            <div>
                <h2 class="font-bold text-natural-heading text-sm">Change Password</h2>
                <p class="text-[10px] text-natural-muted mt-1 leading-relaxed">Leave all fields blank to keep your current password.</p>
            </div>

            <div class="space-y-5">
                <?php
                unset($pfRequired, $pfMinlength, $pfHint, $pfValue, $pfLabelClass, $pfExtraClass, $pfAutocomplete);

                $pfId = 'current_password';
                $pfName = 'current_password';
                $pfLabel = 'Current Password';
                $pfPlaceholder = 'Enter current password';
                $pfAutocomplete = 'current-password';
                include __DIR__ . '/password-field.php';

                unset($pfMinlength, $pfHint);

                $pfId = 'new_password';
                $pfName = 'new_password';
                $pfLabel = 'New Password';
                $pfPlaceholder = 'Minimum 8 characters';
                $pfMinlength = 8;
                $pfAutocomplete = 'new-password';
                include __DIR__ . '/password-field.php';

                unset($pfMinlength);

                $pfId = 'new_password_confirmation';
                $pfName = 'new_password_confirmation';
                $pfLabel = 'Confirm New Password';
                $pfPlaceholder = 'Re-enter new password';
                $pfAutocomplete = 'new-password';
                include __DIR__ . '/password-field.php';
                ?>
            </div>
        </section>
    </div>

    <div class="px-6 sm:px-8 py-4 border-t border-natural-border bg-natural-pane/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-[10px] text-natural-muted leading-relaxed">Name changes update your header display immediately after save.</p>
        <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-natural-primary hover:bg-natural-primary-hover text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-sm shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
            </svg>
            Save Profile
        </button>
    </div>
</form>
