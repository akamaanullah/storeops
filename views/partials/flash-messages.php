<?php
Auth::initSession();
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

if (!$flashError && !$flashSuccess) {
    return;
}
?>
<?php if ($flashError): ?>
    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-800 shadow-sm" role="alert">
        <svg class="w-5 h-5 shrink-0 text-rose-500 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <div>
            <p class="font-bold text-rose-900">Access denied</p>
            <p class="mt-0.5 leading-relaxed"><?= htmlspecialchars($flashError) ?></p>
        </div>
    </div>
<?php endif; ?>
<?php if ($flashSuccess): ?>
    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-800 shadow-sm" role="status">
        <svg class="w-5 h-5 shrink-0 text-emerald-500 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="font-medium leading-relaxed"><?= htmlspecialchars($flashSuccess) ?></p>
    </div>
<?php endif; ?>
