<?php
use App\Core\RoleLabels;

$title = "Dashboard - " . APP_NAME;
include __DIR__ . '/layout/header.php';
?>

<!-- Welcome Banner -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-3xl border border-natural-border shadow-sm mb-8">
  <div class="min-w-0 flex-1">
    <h1 class="text-2xl font-serif italic text-natural-heading tracking-tight leading-tight">Operations Dashboard</h1>
    <p class="text-xs text-natural-darkmute mt-1">Key metrics and pending assignments at a glance · <span class="font-mono text-[9px] font-bold text-natural-primary uppercase bg-natural-subtle px-2 py-0.5 border border-natural-border rounded-xl ml-1"><?= htmlspecialchars(RoleLabels::label($user['role'] ?? null)) ?></span></p>
  </div>

  <div class="flex flex-row flex-wrap items-center justify-start sm:justify-end gap-2 sm:gap-3 w-full sm:w-auto shrink-0">
    <?php include __DIR__ . '/../partials/dashboard-analog-clocks.php'; ?>

    <?php if (in_array($user['role'], ['admin', 'team_lead'])): ?>
    <a href="<?= BASE_URL ?>/jobs/create" class="inline-flex items-center justify-center px-4 py-2 bg-natural-primary hover:bg-natural-primary-hover text-white font-bold text-xs tracking-wider uppercase rounded-full transition-all shadow-sm space-x-1.5 focus:outline-none whitespace-nowrap">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
      </svg>
      <span>Create Work Order</span>
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Total cards metrics -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
  <div class="bg-white p-5 rounded-3xl border border-natural-border shadow-sm flex items-center justify-between col-span-2 lg:col-span-1">
    <div>
      <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block leading-none font-mono">Total Jobs</span>
      <span class="text-3xl font-light text-natural-heading block mt-2.5 leading-none" data-rt-stat="total"><?= $stats['total'] ?></span>
    </div>
    <div class="p-3 bg-natural-pane text-natural-darkmute rounded-xl">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
      </svg>
    </div>
  </div>

  <div class="bg-white p-5 rounded-3xl border border-natural-border shadow-sm flex items-center justify-between">
    <div>
      <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block leading-none font-mono">New</span>
      <span class="text-3xl font-light text-natural-primary block mt-2.5 leading-none" data-rt-stat="new"><?= $stats['new'] ?></span>
    </div>
    <div class="p-3 bg-natural-subtle/40 text-natural-primary rounded-xl">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
    </div>
  </div>

  <div class="bg-white p-5 rounded-3xl border border-natural-border shadow-sm flex items-center justify-between">
    <div>
      <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block leading-none font-mono">Assigned</span>
      <span class="text-3xl font-light text-natural-heading block mt-2.5 leading-none" data-rt-stat="assigned"><?= $stats['assigned'] ?></span>
    </div>
    <div class="p-3 bg-natural-pane text-natural-darkmute rounded-xl">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
      </svg>
    </div>
  </div>

  <div class="bg-white p-5 rounded-3xl border border-natural-border shadow-sm flex items-center justify-between">
    <div>
      <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block leading-none font-mono">In Progress</span>
      <span class="text-3xl font-light text-natural-primary block mt-2.5 leading-none" data-rt-stat="in_progress"><?= $stats['scheduled'] + $stats['wip'] + $stats['pending'] ?></span>
    </div>
    <div class="p-3 bg-natural-subtle/50 text-natural-primary rounded-xl">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
      </svg>
    </div>
  </div>

  <div class="bg-white p-5 rounded-3xl border border-natural-border shadow-sm flex items-center justify-between">
    <div>
      <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block leading-none font-mono">Done</span>
      <span class="text-3xl font-light text-emerald-600 block mt-2.5 leading-none" data-rt-stat="done"><?= $stats['done'] ?></span>
    </div>
    <div class="p-3 bg-emerald-50/50 text-emerald-600 rounded-xl">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
    </div>
  </div>
</div>

<!-- Split layout: Recents Table + Queue lists -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
  <!-- Recents table -->
  <div class="bg-white border border-natural-border rounded-3xl shadow-sm overflow-hidden lg:col-span-2 flex flex-col">
    <div class="px-6 py-5 border-b border-natural-border flex justify-between items-center bg-natural-pane/30">
      <div>
        <h2 class="font-bold text-natural-heading tracking-tight text-sm">Recent Active Jobs</h2>
        <p class="text-[10px] text-natural-muted font-medium">Latest work orders across the platform</p>
      </div>
      <a href="<?= BASE_URL ?>/jobs" class="text-xs text-natural-primary font-bold hover:underline mb-0.5">View all jobs &rarr;</a>
    </div>
    <div class="overflow-x-auto">
      <?php if (empty($recentJobs)): ?>
        <div class="p-10 text-center text-xs text-natural-muted">No work orders found.</div>
      <?php else: ?>
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-natural-pane text-[9px] font-bold uppercase tracking-wider text-natural-muted border-b border-natural-border font-mono">
              <th class="py-3 px-6">Store / Location</th>
              <th class="py-3 px-4">Designation</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4 text-center">Urgency</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-natural-border text-natural-text">
            <?php foreach ($recentJobs as $job): ?>
              <tr class="hover:bg-natural-subtle/20 transition-colors">
                <td class="py-4 px-6 font-medium">
                  <a href="<?= BASE_URL . $job->path() ?>" class="text-natural-primary font-bold hover:underline block text-left"><?= htmlspecialchars($job->store_name) ?></a>
                  <span class="block text-[10px] text-natural-darkmute font-normal mt-0.5"><?= htmlspecialchars($job->location) ?></span>
                </td>
                <td class="py-4 px-4 text-natural-darkmute font-normal"><?= htmlspecialchars($job->designation) ?></td>
                <td class="py-4 px-4 text-center">
                  <?php
                  $statusBadgeClass = 'bg-slate-100 text-slate-800 border-slate-200';
                  switch ($job->status) {
                      case 'New': $statusBadgeClass = 'bg-blue-50 text-blue-600 border-blue-100'; break;
                      case 'Assigned': $statusBadgeClass = 'bg-orange-50 text-orange-600 border-orange-100'; break;
                      case 'Scheduled': $statusBadgeClass = 'bg-purple-50 text-purple-600 border-purple-100'; break;
                      case 'Work In Progress': $statusBadgeClass = 'bg-amber-50 text-amber-700 border-amber-100'; break;
                      case 'Pending': $statusBadgeClass = 'bg-rose-50 text-rose-600 border-rose-100'; break;
                      case 'Cancelled': $statusBadgeClass = 'bg-slate-100 text-slate-600 border border-slate-200'; break;
                      case 'Done': $statusBadgeClass = 'bg-emerald-50 text-emerald-600 border-emerald-150'; break;
                  }
                  ?>
                  <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold border <?= $statusBadgeClass ?>"><?= $job->status ?></span>
                </td>
                <td class="py-4 px-4 text-center">
                  <?php
                  $urgencyBadgeClass = $job->urgency === 'Urgent' 
                      ? 'bg-rose-50 text-rose-600 border-rose-100' 
                      : 'bg-natural-pane text-natural-primary border-natural-border';
                  ?>
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $urgencyBadgeClass ?>"><?= $job->urgency ?></span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <?php include __DIR__ . '/../partials/technician-work-queue.php'; ?>
</div>

<?php include __DIR__ . '/../partials/dashboard-analog-clocks-scripts.php'; ?>
<?php include __DIR__ . '/layout/footer.php'; ?>
