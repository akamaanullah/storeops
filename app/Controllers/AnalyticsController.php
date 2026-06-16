<?php
/**
 * Analytics Controller - Admin Insights Dashboard
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Job;
use PDO;

class AnalyticsController extends Controller {
    public function index(): void {
        // Enforce Admin Role only
        Auth::middleware(['admin']);

        $currentUser = Auth::user();
        $db = (new Job())->getDB();

        try {
            // 1. Core Summary Metrics
            // Total client contract value (total amount set on jobs)
            $stmt = $db->query("SELECT IFNULL(SUM(total_amount), 0.00) FROM jobs");
            $totalContractValue = (float)$stmt->fetchColumn();

            // Total vendor contract value (vendor amount set on jobs)
            $stmt = $db->query("SELECT IFNULL(SUM(vendor_amount), 0.00) FROM jobs");
            $totalVendorContract = (float)$stmt->fetchColumn();

            // Total client payments collected (completed full/partial client payments)
            $stmt = $db->query("SELECT IFNULL(SUM(amount), 0.00) FROM payments WHERE type != 'pending' AND party = 'client'");
            $totalCollected = (float)$stmt->fetchColumn();

            // Total vendor payouts paid (completed full/partial vendor payments)
            $stmt = $db->query("SELECT IFNULL(SUM(amount), 0.00) FROM payments WHERE type != 'pending' AND party = 'vendor'");
            $totalVendorPaid = (float)$stmt->fetchColumn();

            // Total pending client payments
            $stmt = $db->query("SELECT IFNULL(SUM(amount), 0.00) FROM payments WHERE type = 'pending' AND party = 'client'");
            $totalPending = (float)$stmt->fetchColumn();

            // Total pending vendor payments
            $stmt = $db->query("SELECT IFNULL(SUM(amount), 0.00) FROM payments WHERE type = 'pending' AND party = 'vendor'");
            $totalVendorPending = (float)$stmt->fetchColumn();

            // Outstanding balance (Client owes us)
            $outstandingBalance = max(0.00, $totalContractValue - $totalCollected);

            // Vendor Outstanding balance (We owe vendor)
            $vendorOutstanding = max(0.00, $totalVendorContract - $totalVendorPaid);

            // Net Margin (Profit = Client Collected - Vendor Paid)
            $netProfit = $totalCollected - $totalVendorPaid;

            // Jobs counters
            $stmt = $db->query("SELECT COUNT(*) FROM jobs");
            $totalJobs = (int)$stmt->fetchColumn();

            $stmt = $db->query("SELECT COUNT(*) FROM jobs WHERE status = 'Done'");
            $completedJobs = (int)$stmt->fetchColumn();

            $completionRate = $totalJobs > 0 ? round(($completedJobs / $totalJobs) * 100, 1) : 0;

            // 2. Job Status Breakdown
            $stmt = $db->query("SELECT status, COUNT(*) as count FROM jobs GROUP BY status");
            $statusBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Job Urgency Breakdown
            $stmt = $db->query("SELECT urgency, COUNT(*) as count FROM jobs GROUP BY urgency");
            $urgencyBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Monthly Collection Trends (Past 12 Months)
            $stmt = $db->query("
                SELECT DATE_FORMAT(created_at, '%b %Y') as month_label,
                       SUM(CASE WHEN party = 'client' THEN amount ELSE 0 END) as client_total,
                       SUM(CASE WHEN party = 'vendor' THEN amount ELSE 0 END) as vendor_total
                FROM payments
                WHERE type != 'pending'
                GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
                ORDER BY MIN(created_at) ASC
                LIMIT 12
            ");
            
            $monthlyTrends = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cTotal = (float)$row['client_total'];
                $vTotal = (float)$row['vendor_total'];
                $monthlyTrends[] = [
                    'month_label' => $row['month_label'],
                    'client_total' => $cTotal,
                    'vendor_total' => $vTotal,
                    'profit_total' => $cTotal - $vTotal
                ];
            }

            // 5. Staff Performance Leaderboard
            $stmt = $db->query("
                SELECT u.id, u.full_name, u.role,
                       COUNT(j.id) as total_assigned,
                       SUM(CASE WHEN j.status = 'Done' THEN 1 ELSE 0 END) as total_completed,
                       IFNULL(SUM(CASE WHEN p.type != 'pending' AND p.party = 'client' THEN p.amount ELSE 0 END), 0) as total_collected
                FROM users u
                LEFT JOIN jobs j ON j.assigned_to = u.id
                LEFT JOIN payments p ON p.job_id = j.id
                GROUP BY u.id, u.full_name, u.role
                ORDER BY total_completed DESC, total_collected DESC
            ");
            $staffLeaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 6. Recent Billing Ledger Entries
            $stmt = $db->query("
                SELECT p.*, j.reference_code, j.store_name
                FROM payments p
                JOIN jobs j ON p.job_id = j.id
                ORDER BY p.created_at DESC
                LIMIT 5
            ");
            $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->render('admin.analytics.index', [
                'user' => $currentUser,
                'metrics' => [
                    'total_contract' => $totalContractValue,
                    'total_vendor_contract' => $totalVendorContract,
                    'total_collected' => $totalCollected,
                    'total_vendor_paid' => $totalVendorPaid,
                    'total_pending' => $totalPending,
                    'total_vendor_pending' => $totalVendorPending,
                    'outstanding' => $outstandingBalance,
                    'vendor_outstanding' => $vendorOutstanding,
                    'net_profit' => $netProfit,
                    'total_jobs' => $totalJobs,
                    'completed_jobs' => $completedJobs,
                    'completion_rate' => $completionRate
                ],
                'statusBreakdown' => $statusBreakdown,
                'urgencyBreakdown' => $urgencyBreakdown,
                'monthlyTrends' => $monthlyTrends,
                'staffLeaderboard' => $staffLeaderboard,
                'recentTransactions' => $recentTransactions
            ]);
        } catch (\PDOException $e) {
            die("Database Error: " . $e->getMessage() . " in query. Code: " . $e->getCode());
        } catch (\Throwable $e) {
            die("System Error: " . $e->getMessage() . " on line " . $e->getLine());
        }
    }
}
