<?php
/**
 * Dashboard Controller - PHP 8 Custom MVC
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Job;
use App\Models\JobCommentRead;

class DashboardController extends Controller {
    public function index(): void {
        Auth::middleware();

        $currentUser = Auth::user();
        $userId = (int)$currentUser['id'];
        $isUserRole = ($currentUser['role'] ?? '') === 'user';

        $jobFilters = $isUserRole ? ['for_user_id' => $userId] : [];

        $stats = Job::countStats($jobFilters);
        $recentJobs = Job::recent(5, $jobFilters);
        $myActiveJobs = Job::activeAssignedToUser($userId, 10);

        usort($myActiveJobs, static function ($a, $b) {
            if ($a->urgency !== $b->urgency) {
                return $a->urgency === 'Urgent' ? -1 : 1;
            }
            $priority = ['Work In Progress' => 0, 'Pending' => 1, 'Scheduled' => 2, 'Assigned' => 3, 'New' => 4];
            return ($priority[$a->status] ?? 99) <=> ($priority[$b->status] ?? 99);
        });

        $queueJobIds = array_map(static fn($job) => (int)$job->id, $myActiveJobs);
        $queueUnreadComments = JobCommentRead::countsForJobs($userId, $queueJobIds);

        $this->render('dashboard', [
            'stats' => $stats,
            'recentJobs' => $recentJobs,
            'myActiveJobs' => $myActiveJobs,
            'queueUnreadComments' => $queueUnreadComments,
            'user' => $currentUser
        ]);
    }
}
