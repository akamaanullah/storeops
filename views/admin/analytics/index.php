<?php
$title = "Analytics Dashboard - " . APP_NAME;
include __DIR__ . '/../layout/header.php';

// Prepare Chart.js data
$statusLabels = [];
$statusCounts = [];
$statusColors = [];
$statusColorMap = [
    'New' => '#38bdf8', // sky-400
    'Assigned' => '#818cf8', // indigo-400
    'Scheduled' => '#f472b6', // pink-400
    'Work In Progress' => '#fbbf24', // amber-400
    'Pending' => '#f43f5e', // rose-500
    'Cancelled' => '#94a3b8', // slate-400
    'Done' => '#34d399' // emerald-400
];

foreach ($statusBreakdown as $sb) {
    $statusLabels[] = $sb['status'];
    $statusCounts[] = (int)$sb['count'];
    $statusColors[] = $statusColorMap[$sb['status']] ?? '#94a3b8'; // slate-400 fallback
}

$trendLabels = [];
$clientTrendData = [];
$vendorTrendData = [];
$profitTrendData = [];

foreach ($monthlyTrends as $mt) {
    $trendLabels[] = $mt['month_label'];
    $clientTrendData[] = (float)$mt['client_total'];
    $vendorTrendData[] = (float)$mt['vendor_total'];
    $profitTrendData[] = (float)$mt['profit_total'];
}

// Ensure at least some labels exist if trend is empty
if (empty($trendLabels)) {
    $trendLabels = [date('M Y')];
    $clientTrendData = [0.00];
    $vendorTrendData = [0.00];
    $profitTrendData = [0.00];
}
?>

<div class="space-y-6">
    <!-- Header Block -->
    <div>
        <h1 class="text-2xl font-serif italic text-natural-heading tracking-tight">System & Performance Analytics</h1>
        <p class="text-xs text-natural-darkmute mt-1">Detailed review of financial ledger performance, work orders status, and staff metrics</p>
    </div>

    <!-- Summary Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <!-- Card 1: Collected Client Revenue -->
        <div class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Collected Revenue</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-bold text-emerald-600 tracking-tight font-sans">$<?= number_format($metrics['total_collected'], 2) ?></h3>
                <p class="text-[9.5px] text-natural-muted mt-0.5">Total Client expected: <strong>$<?= number_format($metrics['total_contract'], 2) ?></strong></p>
            </div>
        </div>

        <!-- Card 2: Cleared Vendor Payouts -->
        <div class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Vendor Payouts</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-bold text-blue-600 tracking-tight font-sans">$<?= number_format($metrics['total_vendor_paid'], 2) ?></h3>
                <p class="text-[9.5px] text-natural-muted mt-0.5">Total Vendor expected: <strong>$<?= number_format($metrics['total_vendor_contract'], 2) ?></strong></p>
            </div>
        </div>

        <!-- Card 3: Realized Net Profit -->
        <?php
        $profitClass = $metrics['net_profit'] >= 0 ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : 'text-rose-500 bg-rose-50 border-rose-100';
        $profitValClass = $metrics['net_profit'] >= 0 ? 'text-emerald-600' : 'text-rose-500';
        $expectedProfit = $metrics['total_contract'] - $metrics['total_vendor_contract'];
        ?>
        <div class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Realized Profit</span>
                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 border <?= $profitClass ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-bold tracking-tight font-sans <?= $profitValClass ?>">$<?= number_format($metrics['net_profit'], 2) ?></h3>
                <p class="text-[9.5px] text-natural-muted mt-0.5">Total expected net margin: <strong>$<?= number_format($expectedProfit, 2) ?></strong></p>
            </div>
        </div>

        <!-- Card 4: Outstanding Dues -->
        <div class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-bold text-natural-muted uppercase tracking-widest block font-mono">Outstanding Dues</span>
                <div class="w-7 h-7 rounded-lg bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
            <div>
                <div class="flex flex-col space-y-0.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-natural-muted font-medium text-[10px]">Client Receivables:</span>
                        <span class="font-bold text-rose-500 font-mono">$<?= number_format($metrics['outstanding'], 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-natural-muted font-medium text-[10px]">Vendor Payables:</span>
                        <span class="font-bold text-blue-500 font-mono">$<?= number_format($metrics['vendor_outstanding'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Status Distribution Chart (Doughnut) -->
        <div class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 flex flex-col justify-between min-h-[350px]">
            <div>
                <h2 class="font-bold text-natural-heading text-sm">Job Status Distribution</h2>
                <p class="text-[10px] text-natural-muted mt-0.5">Active statuses comparison breakdown</p>
            </div>
            <div class="relative w-full h-44 my-4 flex items-center justify-center">
                <?php if (empty($statusBreakdown)): ?>
                    <span class="text-xs text-natural-muted italic">No work orders recorded.</span>
                <?php else: ?>
                    <canvas id="statusChart"></canvas>
                <?php endif; ?>
            </div>
            <div class="flex flex-wrap gap-x-4 gap-y-1.5 justify-center text-[10px] text-natural-darkmute font-semibold">
                <?php foreach ($statusBreakdown as $sb): ?>
                    <span class="flex items-center space-x-1.5">
                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: <?= $statusColorMap[$sb['status']] ?? '#94a3b8' ?>"></span>
                        <span><?= htmlspecialchars($sb['status']) ?> (<?= $sb['count'] ?>)</span>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Collections Trend Line Chart -->
        <div class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 lg:col-span-2 flex flex-col justify-between min-h-[350px]">
            <div>
                <h2 class="font-bold text-natural-heading text-sm">Monthly Collections &amp; Payouts</h2>
                <p class="text-[10px] text-natural-muted mt-0.5">Billing trend for the past 12 months</p>
            </div>
            <div class="w-full h-52 my-3">
                <canvas id="trendChart"></canvas>
            </div>
            <div class="text-[9.5px] text-natural-muted italic text-center">
                Showing revenue collections, payouts, and net profits by month
            </div>
        </div>
    </div>

    <!-- Staff & Ledger Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Staff Leaderboard -->
        <div class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 lg:col-span-2 space-y-4">
            <div>
                <h2 class="font-bold text-natural-heading text-sm">Staff Performance Leaderboard</h2>
                <p class="text-[10px] text-natural-muted mt-0.5">Activity and financial clearance stats by team member</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-natural-border text-[9px] font-bold text-natural-muted uppercase tracking-widest font-mono">
                            <th class="py-2.5">Team Member</th>
                            <th class="py-2.5">Role</th>
                            <th class="py-2.5 text-center">Assigned</th>
                            <th class="py-2.5 text-center">Completed</th>
                            <th class="py-2.5 text-center">Ratio</th>
                            <th class="py-2.5 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-natural-border/50 text-natural-text">
                        <?php if (empty($staffLeaderboard)): ?>
                            <tr>
                                <td colspan="6" class="py-4 text-center italic text-natural-muted">No staff members found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($staffLeaderboard as $staff): ?>
                                <?php 
                                    $assigned = (int)$staff['total_assigned'];
                                    $completed = (int)$staff['total_completed'];
                                    $rate = $assigned > 0 ? round(($completed / $assigned) * 100) : 0;
                                ?>
                                <tr class="hover:bg-natural-pane/20 transition-colors">
                                    <td class="py-3 font-semibold text-natural-heading"><?= htmlspecialchars($staff['full_name']) ?></td>
                                    <td class="py-3 text-[10px] uppercase font-mono text-natural-muted"><?= htmlspecialchars($staff['role']) ?></td>
                                    <td class="py-3 text-center"><?= $assigned ?></td>
                                    <td class="py-3 text-center"><?= $completed ?></td>
                                    <td class="py-3 text-center">
                                        <span class="inline-flex items-center space-x-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full inline-block <?= $rate >= 75 ? 'bg-emerald-500' : ($rate >= 40 ? 'bg-amber-500' : 'bg-rose-500') ?>"></span>
                                            <span class="font-bold"><?= $rate ?>%</span>
                                        </span>
                                    </td>
                                    <td class="py-3 text-right font-bold text-natural-heading">$<?= number_format($staff['total_collected'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white border border-natural-border rounded-3xl shadow-sm p-6 flex flex-col justify-between space-y-4">
            <div>
                <h2 class="font-bold text-natural-heading text-sm">Recent Ledger Activity</h2>
                <p class="text-[10px] text-natural-muted mt-0.5">Latest 5 billing transactions executed</p>
            </div>
            <div class="flex-1 divide-y divide-natural-border">
                <?php if (empty($recentTransactions)): ?>
                    <p class="text-xs text-natural-muted italic text-center py-6">No transactions recorded yet.</p>
                <?php else: ?>
                    <?php foreach ($recentTransactions as $trans): ?>
                        <div class="py-2.5 flex flex-col space-y-1.5 text-xs text-natural-text leading-relaxed">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-natural-heading font-mono">$<?= number_format($trans['amount'], 2) ?></span>
                                <div class="flex items-center space-x-1.5">
                                    <?php
                                    $partyLabel = ($trans['party'] ?? 'client') === 'vendor' ? 'Vendor' : 'Client';
                                    $partyBadgeClass = ($trans['party'] ?? 'client') === 'vendor' 
                                        ? 'bg-blue-50 text-blue-700 border-blue-200' 
                                        : 'bg-emerald-50 text-emerald-700 border-emerald-150';
                                    ?>
                                    <span class="px-1 py-0.5 rounded border text-[7px] font-extrabold uppercase <?= $partyBadgeClass ?>"><?= $partyLabel ?></span>
                                    <span class="text-[9px] font-bold text-natural-primary uppercase tracking-wider font-mono"><?= htmlspecialchars($trans['reference_code']) ?></span>
                                    <span class="px-1.5 py-0.5 bg-natural-pane text-natural-muted uppercase text-[7px] font-extrabold rounded border border-natural-border"><?= htmlspecialchars($trans['type']) ?></span>
                                </div>
                            </div>
                            <p class="text-[10px] text-natural-muted truncate" title="<?= htmlspecialchars($trans['note']) ?>"><?= htmlspecialchars($trans['note'] ?: 'No notes provided.') ?></p>
                            <span class="text-[9px] text-natural-muted block"><?= date('M j, Y, H:i', strtotime($trans['created_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="text-center pt-2">
                <p class="text-[9px] text-natural-muted tracking-wide uppercase font-mono">End of recent updates</p>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Doughnut Chart: Job Status
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($statusLabels) ?>,
                datasets: [{
                    data: <?= json_encode($statusCounts) ?>,
                    backgroundColor: <?= json_encode($statusColors) ?>,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw} orders`;
                            }
                        }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // 2. Line Chart: Monthly Trend
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        // Create premium gradient fill
        const ctx = trendCtx.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.25)'); // Indigo-500 25%
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0.00)');

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($trendLabels) ?>,
                datasets: [
                    {
                        label: 'Client Revenue ($)',
                        data: <?= json_encode($clientTrendData) ?>,
                        borderColor: '#10b981', // Emerald-500
                        borderWidth: 2,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 1.5,
                        pointRadius: 3.5,
                        pointHoverRadius: 5,
                        fill: false,
                        tension: 0.35
                    },
                    {
                        label: 'Vendor Payouts ($)',
                        data: <?= json_encode($vendorTrendData) ?>,
                        borderColor: '#f43f5e', // Rose-500
                        borderWidth: 2,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#f43f5e',
                        pointBorderWidth: 1.5,
                        pointRadius: 3.5,
                        pointHoverRadius: 5,
                        fill: false,
                        tension: 0.35
                    },
                    {
                        label: 'Net Margin ($)',
                        data: <?= json_encode($profitTrendData) ?>,
                        borderColor: '#6366f1', // Indigo-500
                        borderWidth: 2.5,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#6366f1',
                        pointBorderWidth: 2,
                        pointRadius: 4.5,
                        pointHoverRadius: 6,
                        fill: true,
                        backgroundColor: gradient,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 9,
                                family: 'Inter, sans-serif'
                            },
                            color: '#64748b'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(226, 232, 240, 0.6)'
                        },
                        ticks: {
                            font: {
                                size: 9,
                                family: 'monospace'
                            },
                            color: '#64748b',
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 9,
                                family: 'Inter, sans-serif'
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
