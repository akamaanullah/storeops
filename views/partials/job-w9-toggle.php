<?php
/** @var object $job */
?>
<div class="space-y-1.5">
    <label class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">W9 Clearance Mandatory</label>
    <div class="grid grid-cols-2 gap-2 text-center">
        <label class="border border-natural-border rounded-xl py-2 px-3 flex items-center justify-center space-x-1.5 cursor-pointer hover:bg-natural-pane/30">
            <input type="radio" name="w9" value="No" <?= $job->w9 === 'No' ? 'checked' : '' ?> class="w-4 h-4 text-natural-text focus:ring-natural-primary/50">
            <span class="font-medium text-natural-text">No</span>
        </label>
        <label class="border border-natural-border rounded-xl py-2 px-3 flex items-center justify-center space-x-1.5 cursor-pointer hover:bg-natural-pane/30">
            <input type="radio" name="w9" value="Yes" <?= $job->w9 === 'Yes' ? 'checked' : '' ?> class="w-4 h-4 text-natural-primary focus:ring-natural-primary/50">
            <span class="font-medium text-natural-text">Yes</span>
        </label>
    </div>
</div>
