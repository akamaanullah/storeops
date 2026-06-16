<?php
/** @var object $job */
/** @var array $payments */
/** @var bool $isAdminOrTL */
/** @var bool $isAssigned */
$canRecordPayment = $isAdminOrTL || $isAssigned;
$w9DeleteUrl = BASE_URL . $job->path() . '/w9/delete';
$updateAmountUrl = BASE_URL . $job->path() . '/total-amount';

$totalPaid = 0.00;
foreach ($payments as $pay) {
    if (($pay['type'] ?? '') !== 'pending') {
        $totalPaid += (float)$pay['amount'];
    }
}
$totalAmount = (float)($job->total_amount ?? 0.00);
$remainingBalance = $totalAmount - $totalPaid;
?>

<!-- Financial Summary Panel -->
<div id="financial-summary-panel" class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 space-y-4 mb-6">
    <div>
        <h2 class="font-bold text-natural-heading tracking-tight text-sm">Financial Summary</h2>
        <p class="text-[10px] text-natural-muted mt-0.5">Overall contract value and balances</p>
    </div>

    <div class="space-y-3 text-xs">
        <!-- Total Job Value Display Row -->
        <div id="total-amount-display-row" class="flex justify-between items-center py-2 border-b border-natural-border/50">
            <span class="text-natural-muted font-medium">Total Job Value:</span>
            <div class="flex items-center space-x-2 font-bold text-natural-heading">
                <span id="total-amount-value">$<?= number_format($totalAmount, 2) ?></span>
                <?php if ($canRecordPayment): ?>
                    <button type="button" onclick="startEditTotalAmount()" class="text-natural-primary hover:text-natural-primary-hover focus:outline-none" title="Edit Total Amount">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Total Job Value Edit Row -->
        <?php if ($canRecordPayment): ?>
            <div id="total-amount-edit-row" class="hidden py-2 border-b border-natural-border/50">
                <div class="flex items-center justify-between w-full">
                    <span class="text-natural-muted font-medium">Edit Value:</span>
                    <div class="flex items-center space-x-1.5">
                        <div class="flex items-center space-x-1">
                            <span class="text-natural-muted font-bold text-xs">$</span>
                            <input id="total-amount-input" type="number" step="0.01" min="0" value="<?= htmlspecialchars(number_format($totalAmount, 2, '.', '')) ?>" class="w-20 px-2 py-1 text-xs border border-natural-border rounded-lg bg-natural-bg/50 focus:outline-none font-bold text-natural-heading text-right">
                        </div>
                        <button type="button" onclick="saveTotalAmount()" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[9px] rounded-lg shadow-sm">Save</button>
                        <button type="button" onclick="cancelEditTotalAmount()" class="px-2 py-1 bg-natural-pane border border-natural-border text-natural-muted hover:text-natural-primary font-bold text-[9px] rounded-lg">Cancel</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Paid So Far -->
        <div class="flex justify-between items-center py-2 border-b border-natural-border/50">
            <span class="text-natural-muted font-medium">Paid So Far:</span>
            <span id="total-paid-display" class="font-bold text-emerald-600">$<?= number_format($totalPaid, 2) ?></span>
        </div>

        <!-- Remaining Balance -->
        <div class="flex justify-between items-center py-2">
            <span class="text-natural-muted font-medium">Remaining Balance:</span>
            <div class="flex items-center space-x-2">
                <span id="remaining-balance-display" class="font-bold <?= $remainingBalance <= 0 && $totalAmount > 0 ? 'text-emerald-600' : 'text-rose-500' ?>">
                    <?= $remainingBalance < 0 ? '-$' . number_format(abs($remainingBalance), 2) : '$' . number_format($remainingBalance, 2) ?>
                </span>
                <span id="paid-in-full-badge" class="<?= $remainingBalance <= 0 && $totalAmount > 0 ? 'inline-block' : 'hidden' ?> px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[8px] font-extrabold uppercase border border-emerald-200 rounded">Paid In Full</span>
            </div>
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
                            <span class="font-bold text-natural-heading">$<?= number_format($pay['amount'], 2) ?></span>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 bg-natural-pane text-natural-primary uppercase tracking-wider font-bold text-[8px] rounded border border-natural-border"><?= htmlspecialchars($pay['type']) ?></span>
                                <?php if ($canRecordPayment): ?>
                                    <button type="button" onclick="editPaymentLedger(<?= htmlspecialchars(json_encode([
                                        'id'     => (int)$pay['id'],
                                        'amount' => (float)$pay['amount'],
                                        'type'   => $pay['type'],
                                        'note'   => $pay['note']
                                    ])) ?>)" class="text-[10px] text-natural-primary font-bold hover:underline transition-colors focus:outline-none">Edit</button>
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
                        <input id="amount_billing" type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3 py-2 border border-natural-border rounded-xl bg-natural-bg/50 focus:outline-none">
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
                        const paidText = document.getElementById('total-paid-display').textContent.replace(/[^0-9.]/g, '');
                        const paidAmt = parseFloat(paidText) || 0.00;
                        const remaining = newAmt - paidAmt;

                        const balDisplay = document.getElementById('remaining-balance-display');
                        const formattedRemaining = remaining < 0
                            ? '-$' + Math.abs(remaining).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                            : '$' + remaining.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        balDisplay.textContent = formattedRemaining;

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
        </script>
    <?php endif; ?>
</div>
