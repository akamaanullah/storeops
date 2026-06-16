<?php
$title = "Create Work Order - " . APP_NAME;
include __DIR__ . '/../layout/header.php';
?>

<!-- Form Card Layout -->
<div class="max-w-3xl mx-auto">
    <!-- Back control link -->
    <a href="<?= BASE_URL ?>/jobs" class="inline-flex items-center text-xs text-natural-primary font-bold hover:underline mb-6 space-x-1.5 focus:outline-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        <span>Back to Work Orders</span>
    </a>

    <div class="bg-white border border-natural-border rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-natural-border bg-natural-pane/30">
            <h1 class="text-xl font-serif italic text-natural-heading tracking-tight">Create New Work Order</h1>
            <p class="text-xs text-natural-darkmute mt-1">Enter the details below to create a new work order</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="m-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center space-x-2.5">
                <svg class="w-5 h-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Create Form -->
        <form action="<?= BASE_URL ?>/jobs/create" method="POST" enctype="multipart/form-data" class="p-6 space-y-6 text-xs text-natural-text">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Store Name -->
                <div class="space-y-1.5">
                    <label for="store_name" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Store Name *</label>
                    <input id="store_name" type="text" name="store_name" required value="<?= htmlspecialchars($old['store_name'] ?? '') ?>" placeholder="e.g. Target Store #2401" class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text">
                </div>

                <!-- Location -->
                <div class="space-y-1.5">
                    <label for="location" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Location *</label>
                    <input id="location" type="text" name="location" required value="<?= htmlspecialchars($old['location'] ?? '') ?>" placeholder="e.g. Dallas, TX" class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text">
                </div>
            </div>

            <!-- Full Address -->
            <div class="space-y-1.5">
                <label for="address" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Full Address *</label>
                <input id="address" type="text" name="address" required value="<?= htmlspecialchars($old['address'] ?? '') ?>" placeholder="e.g. 1400 Low Street, Dallas, TX 75201" class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text">
            </div>

            <!-- Issue Description -->
            <div class="space-y-1.5">
                <label for="issue" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Issue Description *</label>
                <textarea id="issue" name="issue" rows="4" required placeholder="Describe technical issue in complete paragraphs..." class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text leading-relaxed"><?= htmlspecialchars($old['issue'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Designation -->
                <div class="space-y-1.5">
                    <label for="designation" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Designation Scope *</label>
                    <input id="designation" type="text" name="designation" required value="<?= htmlspecialchars($old['designation'] ?? '') ?>" placeholder="e.g. Lead HVAC Tech, Commercial Electrician" class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text">
                </div>

                <!-- Assigned To dropdown -->
                <div class="space-y-1.5">
                    <label for="assigned_to" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Assign Staff To</label>
                    <select id="assigned_to" name="assigned_to" class="w-full px-3 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text">
                        <option value="">-- Leave Unassigned (Mark status 'New') --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u->id ?>" <?= (isset($old['assigned_to']) && $old['assigned_to'] == $u->id) ? 'selected' : '' ?>><?= htmlspecialchars($u->full_name) ?> (<?= htmlspecialchars($u->role) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Total Job Amount -->
                <div class="space-y-1.5">
                    <label for="total_amount" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Total Client Amount ($)</label>
                    <input id="total_amount" type="number" step="0.01" min="0" name="total_amount" value="<?= htmlspecialchars($old['total_amount'] ?? '0.00') ?>" placeholder="0.00" class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text">
                </div>

                <!-- Vendor Amount -->
                <div class="space-y-1.5">
                    <label for="vendor_amount" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Total Vendor Amount ($)</label>
                    <input id="vendor_amount" type="number" step="0.01" min="0" name="vendor_amount" value="<?= htmlspecialchars($old['vendor_amount'] ?? '0.00') ?>" placeholder="0.00" class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-natural-border">
                <!-- Job Add Date (Created At) -->
                <div class="space-y-1.5">
                    <label for="created_at" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Job Add Date (Created At)</label>
                    <input id="created_at" type="datetime-local" name="created_at" value="<?= isset($old['created_at']) && $old['created_at'] !== '' ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($old['created_at']))) : date('Y-m-d\TH:i') ?>" class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text">
                </div>

                <!-- Job SLA Date -->
                <div class="space-y-1.5">
                    <label for="sla_date" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Job SLA Date</label>
                    <input id="sla_date" type="datetime-local" name="sla_date" value="<?= isset($old['sla_date']) && $old['sla_date'] !== '' ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($old['sla_date']))) : '' ?>" class="w-full px-4 py-3 border border-natural-border focus:outline-none focus:ring-2 focus:ring-natural-primary/50 focus:border-natural-primary rounded-xl bg-natural-bg/50 text-xs text-natural-text">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-natural-border">
                <!-- Urgency Custom Option -->
                <div class="space-y-1.5">
                    <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block mb-2 font-mono">Urgency Priority</span>
                    <div class="flex space-x-6">
                        <label class="inline-flex items-center space-x-2 cursor-pointer font-medium text-natural-text">
                            <input type="radio" name="urgency" value="Within SLA" <?= (!isset($old['urgency']) || $old['urgency'] === 'Within SLA') ? 'checked' : '' ?> class="w-4 h-4 text-natural-primary focus:ring-natural-primary/50 border-natural-border">
                            <span>Within SLA</span>
                        </label>
                        <label class="inline-flex items-center space-x-2 cursor-pointer font-medium text-natural-text">
                            <input type="radio" name="urgency" value="Urgent" <?= (isset($old['urgency']) && $old['urgency'] === 'Urgent') ? 'checked' : '' ?> class="w-4 h-4 text-rose-600 focus:ring-rose-500 border-natural-border">
                            <span class="text-rose-600">Urgent</span>
                        </label>
                    </div>
                </div>

                <!-- W9 Clearance -->
                <div class="space-y-1.5">
                    <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block mb-2 font-mono">W9 Clearance Mandatory</span>
                    <div class="flex space-x-6">
                        <label class="inline-flex items-center space-x-2 cursor-pointer font-medium text-natural-text">
                            <input type="radio" name="w9" value="No" <?= (!isset($old['w9']) || $old['w9'] === 'No') ? 'checked' : '' ?> class="w-4 h-4 text-natural-primary focus:ring-natural-primary/50 border-natural-border">
                            <span>No</span>
                        </label>
                        <label class="inline-flex items-center space-x-2 cursor-pointer font-medium text-natural-text">
                            <input type="radio" name="w9" value="Yes" <?= (isset($old['w9']) && $old['w9'] === 'Yes') ? 'checked' : '' ?> class="w-4 h-4 text-natural-primary focus:ring-natural-primary/50 border-natural-border">
                            <span>Yes</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Multiple File Upload panel -->
            <div class="space-y-1.5 pt-4 border-t border-natural-border">
                <label for="pictures-input" class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block mb-1 font-mono">Attached Pictures (Max 10, JPG/PNG, Max 5MB each)</label>
                <div class="border-2 border-dashed border-natural-border hover:border-natural-primary transition-colors rounded-2xl p-6 text-center cursor-pointer relative bg-natural-bg/50" onclick="document.getElementById('pictures-input').click()">
                    <input id="pictures-input" type="file" name="pictures[]" multiple accept="image/*" class="hidden" onchange="previewUploads(this)">
                    <div class="space-y-1.5">
                        <svg class="mx-auto h-8 w-8 text-natural-darkmute" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-xs text-natural-text justify-center">
                            <span class="relative font-bold text-natural-primary hover:text-natural-primary-hover">Upload multiple files</span>
                            <p class="pl-1 text-natural-muted">or drag and drop them here</p>
                        </div>
                        <p class="text-[10px] text-natural-muted">PNG, JPG, WEBP up to 5MB</p>
                    </div>
                </div>
                <div id="file-previews-list" class="grid grid-cols-3 gap-3 pt-3"></div>
            </div>

            <!-- Submit action -->
            <div class="flex justify-end pt-4 border-t border-natural-border">
                <button type="submit" class="px-6 py-3 bg-natural-primary hover:bg-natural-primary-hover text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-sm">Save Work Order</button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewUploads(input) {
        const previewCo = document.getElementById('file-previews-list');
        previewCo.innerHTML = '';
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'border border-natural-border rounded-xl overflow-hidden relative h-20 bg-natural-bg';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    previewCo.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
