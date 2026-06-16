<?php
/** @var object $job */
/** @var array $payments */
/** @var bool $isAdminOrTL */
/** @var bool $isAssigned */
$canRecordPayment = $isAdminOrTL || $isAssigned;
$w9DeleteUrl = BASE_URL . $job->path() . '/w9/delete';
$updateAmountUrl = BASE_URL . $job->path() . '/total-amount';
$updateVendorAmountUrl = BASE_URL . $job->path() . '/vendor-amount';

$totalClientPaid = 0.00;
$totalVendorPaid = 0.00;
$totalClientPending = 0.00;
$totalVendorPending = 0.00;

foreach ($payments as $pay) {
    $amt = (float)$pay['amount'];
    $isPending = ($pay['type'] ?? '') === 'pending';
    $party = $pay['party'] ?? 'client';
    
    if ($party === 'vendor') {
        if ($isPending) {
            $totalVendorPending += $amt;
        } else {
            $totalVendorPaid += $amt;
        }
    } else {
        if ($isPending) {
            $totalClientPending += $amt;
        } else {
            $totalClientPaid += $amt;
        }
    }
}

$totalClientAmount = (float)($job->total_amount ?? 0.00);
$totalVendorAmount = (float)($job->vendor_amount ?? 0.00);

$clientRemaining = $totalClientAmount - $totalClientPaid;
$vendorRemaining = $totalVendorAmount - $totalVendorPaid;

// Net margin (Collected revenue minus vendor payouts)
$netMargin = $totalClientPaid - $totalVendorPaid;
?>

<!-- Financial Summary Panel -->
<div id="financial-summary-panel" class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 space-y-4 mb-6">
    <div>
        <h2 class="font-bold text-natural-heading tracking-tight text-sm">Financial Summary</h2>
        <p class="text-[10px] text-natural-muted mt-0.5">Client revenue and vendor payouts split</p>
    </div>

    <div class="space-y-4 text-xs">
        <!-- Split Grid -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Client Side -->
            <div class="space-y-2 border-r border-natural-border/60 pr-2">
                <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest font-mono block">Client Account</span>
                
                <!-- Client Expected -->
                <div class="space-y-1">
                    <span class="text-natural-muted font-medium block text-[10px]">Expected Value</span>
                    <div id="total-amount-display-row" class="flex items-center space-x-1.5 font-bold text-natural-heading">
                        <span id="total-amount-value" class="text-xs">$<?= number_format($totalClientAmount, 2) ?></span>
                        <?php if ($canRecordPayment): ?>
                            <button type="button" onclick="startEditTotalAmount()" class="text-natural-primary hover:text-natural-primary-hover focus:outline-none" title="Edit Client Expected Value">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if ($canRecordPayment): ?>
                        <div id="total-amount-edit-row" class="hidden">
                            <div class="flex items-center space-x-1">
                                <span class="text-natural-muted font-bold text-xs">$</span>
                                <input id="total-amount-input" type="number" step="0.01" min="0" value="<?= htmlspecialchars(number_format($totalClientAmount, 2, '.', '')) ?>" class="w-16 px-1.5 py-0.5 text-xs border border-natural-border rounded bg-natural-bg/50 focus:outline-none font-bold text-natural-heading text-right font-mono">
                                <button type="button" onclick="saveTotalAmount()" class="px-1.5 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[8px] rounded">Save</button>
                                <button type="button" onclick="cancelEditTotalAmount()" class="px-1.5 py-0.5 bg-natural-pane border border-natural-border text-natural-muted hover:text-natural-primary font-bold text-[8px] rounded">X</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Client Received -->
                <div class="space-y-0.5 pt-1.5">
                    <span class="text-natural-muted font-medium block text-[10px]">Received Revenue</span>
                    <span id="client-paid-display" class="font-bold text-emerald-600">$<?= number_format($totalClientPaid, 2) ?></span>
                </div>

                <!-- Client Outstanding -->
                <div class="space-y-0.5 pt-1.5">
                    <span class="text-natural-muted font-medium block text-[10px]">Remaining Due</span>
                    <div class="flex flex-wrap items-center gap-1">
                        <span id="client-remaining-display" class="font-bold <?= $clientRemaining <= 0 && $totalClientAmount > 0 ? 'text-emerald-600' : 'text-rose-500' ?>">
                            $<?= number_format(max(0.00, $clientRemaining), 2) ?>
                        </span>
                        <span id="paid-in-full-badge" class="<?= $clientRemaining <= 0 && $totalClientAmount > 0 ? 'inline-block' : 'hidden' ?> px-1 bg-emerald-50 text-emerald-700 text-[7px] font-extrabold uppercase border border-emerald-200 rounded">Paid</span>
                    </div>
                </div>
            </div>

            <!-- Vendor Side -->
            <div class="space-y-2">
                <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest font-mono block">Vendor Account</span>

                <!-- Vendor Expected -->
                <div class="space-y-1">
                    <span class="text-natural-muted font-medium block text-[10px]">Contract Value</span>
                    <div id="vendor-amount-display-row" class="flex items-center space-x-1.5 font-bold text-natural-heading">
                        <span id="vendor-amount-value" class="text-xs">$<?= number_format($totalVendorAmount, 2) ?></span>
                        <?php if ($canRecordPayment): ?>
                            <button type="button" onclick="startEditVendorAmount()" class="text-natural-primary hover:text-natural-primary-hover focus:outline-none" title="Edit Vendor Contract Value">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if ($canRecordPayment): ?>
                        <div id="vendor-amount-edit-row" class="hidden">
                            <div class="flex items-center space-x-1">
                                <span class="text-natural-muted font-bold text-xs">$</span>
                                <input id="vendor-amount-input" type="number" step="0.01" min="0" value="<?= htmlspecialchars(number_format($totalVendorAmount, 2, '.', '')) ?>" class="w-16 px-1.5 py-0.5 text-xs border border-natural-border rounded bg-natural-bg/50 focus:outline-none font-bold text-natural-heading text-right font-mono">
                                <button type="button" onclick="saveVendorAmount()" class="px-1.5 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[8px] rounded">Save</button>
                                <button type="button" onclick="cancelEditVendorAmount()" class="px-1.5 py-0.5 bg-natural-pane border border-natural-border text-natural-muted hover:text-natural-primary font-bold text-[8px] rounded">X</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Vendor Paid -->
                <div class="space-y-0.5 pt-1.5">
                    <span class="text-natural-muted font-medium block text-[10px]">Vendor Payouts</span>
                    <span id="vendor-paid-display" class="font-bold text-blue-650 text-blue-600">$<?= number_format($totalVendorPaid, 2) ?></span>
                </div>

                <!-- Vendor Outstanding -->
                <div class="space-y-0.5 pt-1.5">
                    <span class="text-natural-muted font-medium block text-[10px]">Remaining Due</span>
                    <div class="flex flex-wrap items-center gap-1">
                        <span id="vendor-remaining-display" class="font-bold <?= $vendorRemaining <= 0 && $totalVendorAmount > 0 ? 'text-emerald-600' : 'text-rose-500' ?>">
                            $<?= number_format(max(0.00, $vendorRemaining), 2) ?>
                        </span>
                        <span id="vendor-paid-in-full-badge" class="<?= $vendorRemaining <= 0 && $totalVendorAmount > 0 ? 'inline-block' : 'hidden' ?> px-1 bg-emerald-50 text-emerald-700 text-[7px] font-extrabold uppercase border border-emerald-200 rounded">Paid</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Margin Row -->
        <div class="pt-4 border-t border-natural-border/60 flex items-center justify-between">
            <div class="space-y-0.5">
                <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest font-mono block">Realized Net Margin</span>
                <p class="text-[9.5px] text-natural-muted">Collected Revenue minus Vendor Payouts</p>
            </div>
            <span id="net-margin-value" class="text-base font-bold <?= $netMargin > 0 ? 'text-emerald-600' : ($netMargin < 0 ? 'text-rose-500' : 'text-natural-heading') ?>">
                <?= $netMargin < 0 ? '-$' . number_format(abs($netMargin), 2) : '$' . number_format($netMargin, 2) ?>
            </span>
        </div>
    </div>
</div>

<!-- W9 Form Panel -->
<div id="w9-panel" class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 space-y-4 mb-6">
    <div>
        <h2 class="font-bold text-natural-heading tracking-tight text-sm">W9 Form</h2>
        <p class="text-[10px] text-natural-muted mt-0.5">Attached W9 document for this work order</p>
    </div>

    <div id="w9-panel-content">
        <?php if ($job->w9_form_path): ?>
            <?php
                $w9Path  = $job->w9_form_path;
                $w9Ext   = strtolower(pathinfo($w9Path, PATHINFO_EXTENSION));
                $w9Name  = basename($w9Path);
                $w9Url   = BASE_URL . '/files/serve?path=' . urlencode($w9Path);
                $isPdf   = $w9Ext === 'pdf';
                $isImage = in_array($w9Ext, ['jpg', 'jpeg', 'png'], true);
            ?>
            <div id="w9-attached-block" class="space-y-3">
                <?php if ($isImage): ?>
                    <a href="<?= htmlspecialchars($w9Url) ?>" target="_blank" class="block rounded-xl overflow-hidden border border-natural-border hover:opacity-90 transition-opacity">
                        <img src="<?= htmlspecialchars($w9Url) ?>" alt="W9 Form" class="w-full h-32 object-cover">
                    </a>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($w9Url) ?>" target="_blank"
                       class="flex items-center space-x-3 p-3 bg-natural-pane border border-natural-border rounded-xl hover:bg-natural-pane/70 transition-colors group">
                        <div class="w-9 h-9 rounded-lg bg-rose-50 border border-rose-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-natural-heading truncate group-hover:text-natural-primary transition-colors"><?= htmlspecialchars($w9Name) ?></p>
                            <p class="text-[10px] text-natural-muted uppercase font-mono"><?= htmlspecialchars(strtoupper($w9Ext)) ?> Document</p>
                        </div>
                        <svg class="w-4 h-4 text-natural-muted group-hover:text-natural-primary shrink-0 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                <?php endif; ?>

                <?php if ($isAdminOrTL): ?>
                    <button type="button" onclick="deleteW9Form('<?= htmlspecialchars($w9DeleteUrl) ?>', '<?= htmlspecialchars(CSRF::generateToken()) ?>')"
                            class="w-full py-1.5 text-[10px] font-semibold text-rose-500 hover:text-rose-700 hover:bg-rose-50 border border-rose-200 rounded-lg transition-colors focus:outline-none">
                        Remove W9 Document
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div id="w9-empty-block" class="flex flex-col items-center justify-center py-5 space-y-2 bg-natural-pane/40 border border-dashed border-natural-border rounded-xl">
                <svg class="w-8 h-8 text-natural-muted/50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-[10px] text-natural-muted text-center leading-relaxed">No W9 form attached yet.<br>Use the <strong>📎</strong> doc icon in the comment box to attach.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payment Panel -->
<div class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 space-y-6">
    <div>
        <h2 class="font-bold text-natural-heading tracking-tight text-sm">Payment History</h2>
        <p class="text-[10px] text-natural-muted mt-0.5">Transactions recorded for this work order</p>
    </div>

    <div class="space-y-3.5">
        <?php if (empty($payments)): ?>
            <p class="text-xs text-natural-muted text-center py-2.5 bg-natural-pane/30 border border-natural-border rounded-xl italic">No payments recorded yet.</p>
        <?php else: ?>
            <div class="divide-y divide-natural-border max-h-56 overflow-y-auto pr-1">
                <?php foreach ($payments as $pay): ?>
                    <div class="py-3 flex flex-col space-y-1.5 text-xs text-natural-text leading-relaxed">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-natural-heading font-mono">$<?= number_format($pay['amount'], 2) ?></span>
                            <div class="flex items-center space-x-2">
                                <?php
                                $partyLabel = ($pay['party'] ?? 'client') === 'vendor' ? 'Vendor Payout' : 'Client Revenue';
                                $partyBadgeClass = ($pay['party'] ?? 'client') === 'vendor' 
                                    ? 'bg-blue-50 text-blue-700 border-blue-200' 
                                    : 'bg-emerald-50 text-emerald-700 border-emerald-150';
                                ?>
                                <span class="px-1.5 py-0.5 rounded border text-[7px] font-extrabold uppercase <?= $partyBadgeClass ?>"><?= $partyLabel ?></span>
                                <span class="px-2 py-0.5 bg-natural-pane text-natural-primary uppercase tracking-wider font-bold text-[8px] rounded border border-natural-border"><?= htmlspecialchars($pay['type']) ?></span>
                                <?php if ($canRecordPayment): ?>
                                    <?php
                                    // Non-admins cannot edit client payments
                                    $canEditThisPayment = Auth::user()['role'] === 'admin' || (($pay['party'] ?? 'client') === 'vendor');
                                    ?>
                                    <?php if ($canEditThisPayment): ?>
                                        <button type="button" onclick="editPaymentLedger(<?= htmlspecialchars(json_encode([
                                            'id'     => (int)$pay['id'],
                                            'amount' => (float)$pay['amount'],
                                            'type'   => $pay['type'],
                                            'party'  => $pay['party'] ?? 'client',
                                            'note'   => $pay['note']
                                        ])) ?>)" class="text-[10px] text-natural-primary font-bold hover:underline transition-colors focus:outline-none">Edit</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-[10px] text-natural-darkmute leading-normal"><?= htmlspecialchars($pay['note'] ?: 'Payment recorded.') ?></p>
                        <span class="text-[9px] text-natural-muted block"><?= date('M j, Y, H:i', strtotime($pay['created_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($canRecordPayment): ?>
        <div class="pt-4 border-t border-natural-border space-y-4">
            <span id="billing-ledger-title" class="font-bold text-natural-muted uppercase tracking-widest text-[9px] block font-mono">Record Billing Ledger</span>

            <form id="billing-ledger-form" action="<?= BASE_URL . $job->path() ?>/payment" method="POST" class="space-y-3.5 text-xs">
                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label for="amount_billing" class="text-[8px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Amount ($)</label>
                        <input id="amount_billing" type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3 py-2 border border-natural-border rounded-xl bg-natural-bg/50 focus:outline-none font-mono">
                    </div>

                    <div class="space-y-1">
                        <label for="type_billing" class="text-[8px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Type</label>
                        <select id="type_billing" name="type" class="w-full px-2 py-2 border border-natural-border rounded-xl bg-natural-bg/50 focus:outline-none">
                            <option value="partial">Partial</option>
                            <option value="full">Full Payment</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>

                <!-- Transaction Target Party (Admin Only can record client payments) -->
                <?php if (Auth::user()['role'] === 'admin'): ?>
                    <div class="space-y-1">
                        <label for="party_billing" class="text-[8px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Ledger Category</label>
                        <select id="party_billing" name="party" class="w-full px-2 py-2 border border-natural-border rounded-xl bg-natural-bg/50 focus:outline-none">
                            <option value="client">Client Payment (Revenue)</option>
                            <option value="vendor">Vendor Payment (Payout)</option>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" id="party_billing" name="party" value="vendor">
                <?php endif; ?>

                <div class="space-y-1">
                    <label for="note_billing" class="text-[8px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Payment Note</label>
                    <input id="note_billing" type="text" name="note" required placeholder="Card authorization code, check number..." class="w-full px-3 py-2 border border-natural-border rounded-xl bg-natural-bg/50 focus:outline-none">
                </div>

                <div class="flex gap-2">
                    <button id="billing-ledger-submit" type="submit" class="flex-1 py-2 bg-natural-subtle hover:bg-natural-pane text-natural-primary font-bold text-xs rounded-xl transition-all border border-natural-border">Execute Payment</button>
                    <button id="billing-ledger-cancel" type="button" onclick="cancelEditPaymentLedger()" class="hidden py-2 px-4 bg-natural-pane hover:bg-natural-border text-natural-muted font-bold text-xs rounded-xl transition-all border border-natural-border">Cancel</button>
                </div>
            </form>
        </div>

        <script>
            let defaultBillingFormAction = '';

            function editPaymentLedger(payment) {
                const form = document.getElementById('billing-ledger-form');
                const title = document.getElementById('billing-ledger-title');
                const submitBtn = document.getElementById('billing-ledger-submit');
                const cancelBtn = document.getElementById('billing-ledger-cancel');

                if (!form || !title || !submitBtn || !cancelBtn) return;

                if (!defaultBillingFormAction) {
                    defaultBillingFormAction = form.action;
                }

                // Populate fields
                document.getElementById('amount_billing').value = payment.amount;
                document.getElementById('type_billing').value = payment.type;
                document.getElementById('note_billing').value = payment.note;

                const partySelect = document.getElementById('party_billing');
                if (partySelect) {
                    partySelect.value = payment.party || 'client';
                }

                // Switch form mode
                form.action = `<?= BASE_URL ?>/payments/${payment.id}/edit`;
                title.textContent = 'Update Payment Ledger';
                submitBtn.textContent = 'Update Payment';
                submitBtn.classList.remove('bg-natural-subtle', 'text-natural-primary');
                submitBtn.classList.add('bg-natural-primary', 'text-white', 'hover:bg-natural-primary-hover');
                cancelBtn.classList.remove('hidden');

                form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function cancelEditPaymentLedger() {
                const form = document.getElementById('billing-ledger-form');
                const title = document.getElementById('billing-ledger-title');
                const submitBtn = document.getElementById('billing-ledger-submit');
                const cancelBtn = document.getElementById('billing-ledger-cancel');

                if (!form || !title || !submitBtn || !cancelBtn) return;

                // Reset fields
                document.getElementById('amount_billing').value = '';
                document.getElementById('type_billing').value = 'partial';
                document.getElementById('note_billing').value = '';

                const partySelect = document.getElementById('party_billing');
                if (partySelect && partySelect.type !== 'hidden') {
                    partySelect.value = 'client';
                }

                // Switch back to Record mode
                form.action = defaultBillingFormAction || `<?= BASE_URL . $job->path() ?>/payment`;
                title.textContent = 'Record Billing Ledger';
                submitBtn.textContent = 'Execute Payment';
                submitBtn.classList.add('bg-natural-subtle', 'text-natural-primary');
                submitBtn.classList.remove('bg-natural-primary', 'text-white', 'hover:bg-natural-primary-hover');
                cancelBtn.classList.add('hidden');
            }

            // ── W9 Panel updater (called from comment-form-attachments script) ──────
            window.updateW9Panel = function(data) {
                const panel = document.getElementById('w9-panel-content');
                if (!panel) return;

                const ext     = (data.ext || '').toLowerCase();
                const isImage = ['jpg', 'jpeg', 'png'].includes(ext);
                const isPdf   = ext === 'pdf';
                const baseUrl = '<?= BASE_URL ?>';
                const fileUrl = baseUrl + '/files/serve?path=' + encodeURIComponent(data.path);
                const deleteUrl = '<?= htmlspecialchars($w9DeleteUrl) ?>';
                const csrfToken = window.__RT_CONFIG ? window.__RT_CONFIG.csrfToken : '';

                let innerHtml = '<div id="w9-attached-block" class="space-y-3">';

                if (isImage) {
                    innerHtml += `<a href="${fileUrl}" target="_blank" class="block rounded-xl overflow-hidden border border-natural-border hover:opacity-90 transition-opacity">
                        <img src="${fileUrl}" alt="W9 Form" class="w-full h-32 object-cover">
                    </a>`;
                } else {
                    innerHtml += `<a href="${fileUrl}" target="_blank" class="flex items-center space-x-3 p-3 bg-natural-pane border border-natural-border rounded-xl hover:bg-natural-pane/70 transition-colors group">
                        <div class="w-9 h-9 rounded-lg bg-rose-50 border border-rose-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-natural-heading truncate group-hover:text-natural-primary transition-colors">${escapeHtml(data.filename)}</p>
                            <p class="text-[10px] text-natural-muted uppercase font-mono">${escapeHtml(ext)} Document</p>
                        </div>
                        <svg class="w-4 h-4 text-natural-muted group-hover:text-natural-primary shrink-0 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>`;
                }

                <?php if ($isAdminOrTL): ?>
                innerHtml += `<button type="button" onclick="deleteW9Form('${deleteUrl}', '${csrfToken}')"
                    class="w-full py-1.5 text-[10px] font-semibold text-rose-500 hover:text-rose-700 hover:bg-rose-50 border border-rose-200 rounded-lg transition-colors focus:outline-none">
                    Remove W9 Document
                </button>`;
                <?php endif; ?>

                innerHtml += '</div>';
                panel.innerHTML = innerHtml;
            };

            window.deleteW9Form = async function(url, csrfToken) {
                if (!confirm('Remove the W9 document from this work order?')) return;
                try {
                    const fd = new FormData();
                    fd.append('csrf_token', csrfToken);
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
                        body: fd
                    });
                    const data = await res.json();
                    if (data.success) {
                        const panel = document.getElementById('w9-panel-content');
                        if (panel) {
                            panel.innerHTML = `<div id="w9-empty-block" class="flex flex-col items-center justify-center py-5 space-y-2 bg-natural-pane/40 border border-dashed border-natural-border rounded-xl">
                                <svg class="w-8 h-8 text-natural-muted/50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-[10px] text-natural-muted text-center leading-relaxed">No W9 form attached yet.<br>Use the <strong>📎</strong> doc icon in the comment box to attach.</p>
                            </div>`;
                        }
                        // Clear status indicator too
                        const status = document.getElementById('w9-upload-status');
                        if (status) { status.classList.add('hidden'); status.classList.remove('flex'); }
                    } else {
                        alert(data.error || 'Failed to remove W9 document.');
                    }
                } catch (e) {
                    alert('An unexpected error occurred.');
                }
            };

            window.startEditTotalAmount = function() {
                document.getElementById('total-amount-display-row').classList.add('hidden');
                document.getElementById('total-amount-edit-row').classList.remove('hidden');
                document.getElementById('total-amount-input').focus();
            };

            window.cancelEditTotalAmount = function() {
                document.getElementById('total-amount-display-row').classList.remove('hidden');
                document.getElementById('total-amount-edit-row').classList.add('hidden');
            };

            window.saveTotalAmount = async function() {
                const input = document.getElementById('total-amount-input');
                const val = parseFloat(input.value);
                if (isNaN(val) || val < 0) {
                    alert('Please enter a valid amount (greater than or equal to 0).');
                    return;
                }

                const saveBtn = document.querySelector('#total-amount-edit-row button[onclick="saveTotalAmount()"]');
                const originalText = saveBtn.textContent;
                saveBtn.disabled = true;
                saveBtn.textContent = '...';

                try {
                    const fd = new FormData();
                    fd.append('total_amount', val);
                    fd.append('csrf_token', '<?= CSRF::generateToken() ?>');

                    const res = await fetch('<?= $updateAmountUrl ?>', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-Token': '<?= CSRF::generateToken() ?>'
                        },
                        body: fd
                    });

                    const data = await res.json();
                    if (data.success) {
                        const newAmt = parseFloat(data.total_amount);
                        
                        // Update display text and input value
                        document.getElementById('total-amount-value').textContent = '$' + newAmt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        input.value = newAmt.toFixed(2);

                        // Recalculate financial summary
                        const paidText = document.getElementById('client-paid-display').textContent.replace(/[^0-9.]/g, '');
                        const paidAmt = parseFloat(paidText) || 0.00;
                        const remaining = newAmt - paidAmt;

                        const balDisplay = document.getElementById('client-remaining-display');
                        balDisplay.textContent = remaining < 0
                            ? '-$' + Math.abs(remaining).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                            : '$' + remaining.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                        const badge = document.getElementById('paid-in-full-badge');
                        if (remaining <= 0 && newAmt > 0) {
                            balDisplay.className = 'font-bold text-emerald-600';
                            badge.classList.remove('hidden');
                            badge.classList.add('inline-block');
                        } else {
                            balDisplay.className = 'font-bold text-rose-500';
                            badge.classList.add('hidden');
                            badge.classList.remove('inline-block');
                        }

                        recalculateNetMargins();
                        cancelEditTotalAmount();
                    } else {
                        alert(data.error || 'Failed to update total amount.');
                    }
                } catch (e) {
                    alert('An unexpected error occurred.');
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;
                }
            };

            // ── Vendor Amount script ──────
            window.startEditVendorAmount = function() {
                document.getElementById('vendor-amount-display-row').classList.add('hidden');
                document.getElementById('vendor-amount-edit-row').classList.remove('hidden');
                document.getElementById('vendor-amount-input').focus();
            };

            window.cancelEditVendorAmount = function() {
                document.getElementById('vendor-amount-display-row').classList.remove('hidden');
                document.getElementById('vendor-amount-edit-row').classList.add('hidden');
            };

            window.saveVendorAmount = async function() {
                const input = document.getElementById('vendor-amount-input');
                const val = parseFloat(input.value);
                if (isNaN(val) || val < 0) {
                    alert('Please enter a valid amount (greater than or equal to 0).');
                    return;
                }

                const saveBtn = document.querySelector('#vendor-amount-edit-row button[onclick="saveVendorAmount()"]');
                const originalText = saveBtn.textContent;
                saveBtn.disabled = true;
                saveBtn.textContent = '...';

                try {
                    const fd = new FormData();
                    fd.append('vendor_amount', val);
                    fd.append('csrf_token', '<?= CSRF::generateToken() ?>');

                    const res = await fetch('<?= $updateVendorAmountUrl ?>', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-Token': '<?= CSRF::generateToken() ?>'
                        },
                        body: fd
                    });

                    const data = await res.json();
                    if (data.success) {
                        const newAmt = parseFloat(data.vendor_amount);
                        
                        // Update display text and input value
                        document.getElementById('vendor-amount-value').textContent = '$' + newAmt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        input.value = newAmt.toFixed(2);

                        // Recalculate financial summary
                        const paidText = document.getElementById('vendor-paid-display').textContent.replace(/[^0-9.]/g, '');
                        const paidAmt = parseFloat(paidText) || 0.00;
                        const remaining = newAmt - paidAmt;

                        const balDisplay = document.getElementById('vendor-remaining-display');
                        balDisplay.textContent = remaining < 0
                            ? '-$' + Math.abs(remaining).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                            : '$' + remaining.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                        const badge = document.getElementById('vendor-paid-in-full-badge');
                        if (remaining <= 0 && newAmt > 0) {
                            balDisplay.className = 'font-bold text-emerald-600';
                            badge.classList.remove('hidden');
                            badge.classList.add('inline-block');
                        } else {
                            balDisplay.className = 'font-bold text-rose-500';
                            badge.classList.add('hidden');
                            badge.classList.remove('inline-block');
                        }

                        recalculateNetMargins();
                        cancelEditVendorAmount();
                    } else {
                        alert(data.error || 'Failed to update vendor amount.');
                    }
                } catch (e) {
                    alert('An unexpected error occurred.');
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;
                }
            };

            function recalculateNetMargins() {
                const clientPaidText = document.getElementById('client-paid-display').textContent.replace(/[^0-9.]/g, '');
                const clientPaid = parseFloat(clientPaidText) || 0.00;

                const vendorPaidText = document.getElementById('vendor-paid-display').textContent.replace(/[^0-9.]/g, '');
                const vendorPaid = parseFloat(vendorPaidText) || 0.00;

                const netMargin = clientPaid - vendorPaid;
                const marginDisplay = document.getElementById('net-margin-value');
                
                marginDisplay.textContent = netMargin < 0
                    ? '-$' + Math.abs(netMargin).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    : '$' + netMargin.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                if (netMargin > 0) {
                    marginDisplay.className = 'text-base font-bold text-emerald-600';
                } else if (netMargin < 0) {
                    marginDisplay.className = 'text-base font-bold text-rose-600';
                } else {
                    marginDisplay.className = 'text-base font-bold text-natural-heading';
                }
            }
        </script>
    <?php endif; ?>
</div>
