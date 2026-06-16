<?php
/**
 * Job Controller - PHP 8 Custom MVC
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Upload;
use App\Core\Validator;
use App\Core\AttachmentZip;
use App\Core\JobReference;
use App\Models\Job;
use App\Models\User;
use App\Models\Comment;
use App\Models\JobCommentRead;
use App\Models\ActivityLog;

class JobController extends Controller {
    public function index(?string $page = null): void {
        Auth::middleware();

        $currentUser = Auth::user();
        $userId = (int)$currentUser['id'];

        if (($currentUser['role'] ?? '') === 'user' && isset($_GET['assigned_to'])) {
            $requestedAssignee = (int)$_GET['assigned_to'];
            if ($requestedAssignee !== $userId) {
                Auth::denyAccess('You can only view your own assigned jobs.');
                return;
            }

            $query = $_GET;
            unset($query['assigned_to']);
            $redirect = '/jobs/mine';
            if (!empty($query)) {
                $redirect .= '?' . http_build_query($query);
            }
            $this->redirect($redirect);
            return;
        }

        if ($page === null && isset($_GET['page'])) {
            $legacyPage = max(1, (int)$_GET['page']);
            $query = $_GET;
            unset($query['page']);
            $target = $legacyPage > 1 ? '/jobs/page/' . $legacyPage : '/jobs';
            if (!empty($query)) {
                $target .= '?' . http_build_query($query);
            }
            $this->redirect($target);
            return;
        }

        $pageNum = max(1, (int)($page ?? 1));
        $filters = [
            'status' => $_GET['status'] ?? null,
            'urgency' => $_GET['urgency'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'search' => trim($_GET['search'] ?? '') ?: null,
        ];

        $this->renderJobsList($filters, $pageNum, false);
    }

    public function myAssigned(?string $page = null): void {
        Auth::middleware();

        $currentUser = Auth::user();
        $userId = (int)$currentUser['id'];

        if ($page === null && isset($_GET['page'])) {
            $legacyPage = max(1, (int)$_GET['page']);
            $query = $_GET;
            unset($query['page']);
            $target = $legacyPage > 1 ? '/jobs/mine/page/' . $legacyPage : '/jobs/mine';
            if (!empty($query)) {
                $target .= '?' . http_build_query($query);
            }
            $this->redirect($target);
            return;
        }

        $pageNum = max(1, (int)($page ?? 1));
        $filters = [
            'status' => $_GET['status'] ?? null,
            'urgency' => $_GET['urgency'] ?? null,
            'for_user_id' => $userId,
            'search' => trim($_GET['search'] ?? '') ?: null,
        ];

        $this->renderJobsList($filters, $pageNum, true);
    }

    private function renderJobsList(array $filters, int $page, bool $myAssignedView): void {
        $currentUser = Auth::user();

        $result = Job::search($filters, $page);
        $users = User::all();
        $jobIds = array_map(static fn($job) => (int)$job->id, $result['jobs']);
        $unreadComments = JobCommentRead::countsForJobs((int)$currentUser['id'], $jobIds);

        $viewFilters = [
            'status' => $filters['status'] ?? null,
            'urgency' => $filters['urgency'] ?? null,
            'search' => $filters['search'] ?? null,
        ];

        $this->render('jobs.index', [
            'jobs' => $result['jobs'],
            'pagination' => $result,
            'users' => $users,
            'filters' => $viewFilters,
            'user' => $currentUser,
            'unreadComments' => $unreadComments,
            'myAssignedView' => $myAssignedView,
            'jobsRoute' => $myAssignedView ? '/jobs/mine' : '/jobs',
        ]);
    }

    public function create(): void {
        Auth::middleware(['admin', 'team_lead']);

        Auth::initSession();
        $error = $_SESSION['create_job_error'] ?? null;
        $old = $_SESSION['create_job_old'] ?? null;
        unset($_SESSION['create_job_error'], $_SESSION['create_job_old']);

        $users = User::all();
        $this->render('jobs.create', [
            'users' => $users,
            'user' => Auth::user(),
            'error' => $error,
            'old' => $old
        ]);
    }

    public function store(): void {
        Auth::middleware(['admin', 'team_lead']);
        
        $currentUser = Auth::user();
        Auth::initSession();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            $_SESSION['create_job_error'] = 'CSRF security token verification failed. Please try again.';
            $this->redirect('/jobs/create');
            return;
        }

        $store_name = trim($_POST['store_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $issue = trim($_POST['issue'] ?? '');
        $designation = trim($_POST['designation'] ?? '');
        $assigned_to = $_POST['assigned_to'] ?? '';
        
        if ($assigned_to === '') {
            $assigned_to = null;
        } else {
            $assigned_to = (int)$assigned_to;
            if (!User::canAssign($assigned_to)) {
                $_SESSION['create_job_error'] = 'Selected assignee is invalid or suspended.';
                $this->redirect('/jobs/create');
                return;
            }
        }

        $urgency = $_POST['urgency'] ?? 'Within SLA';
        $w9 = $_POST['w9'] ?? 'No';

        if (!Validator::inEnum($urgency, Validator::URGENCIES) || !Validator::inEnum($w9, Validator::W9_VALUES)) {
            $_SESSION['create_job_error'] = 'Invalid urgency or W9 value submitted.';
            $this->redirect('/jobs/create');
            return;
        }

        $total_amount = isset($_POST['total_amount']) ? max(0.0, (float)$_POST['total_amount']) : 0.00;
        $created_at = !empty($_POST['created_at']) ? date('Y-m-d H:i:s', strtotime($_POST['created_at'])) : null;
        $sla_date = !empty($_POST['sla_date']) ? date('Y-m-d H:i:s', strtotime($_POST['sla_date'])) : null;

        if (empty($store_name) || empty($location) || empty($address) || empty($issue) || empty($designation)) {
            $_SESSION['create_job_error'] = 'Please fill out all required fields.';
            $_SESSION['create_job_old'] = compact('store_name', 'location', 'address', 'issue', 'designation') + [
                'assigned_to' => $_POST['assigned_to'] ?? '',
                'urgency' => $urgency,
                'w9' => $w9,
                'total_amount' => $total_amount,
                'created_at' => $_POST['created_at'] ?? '',
                'sla_date' => $_POST['sla_date'] ?? ''
            ];
            $this->redirect('/jobs/create');
            return;
        }

        $job = new Job(
            store_name: $store_name,
            location: $location,
            address: $address,
            issue: $issue,
            designation: $designation,
            status: $assigned_to ? 'Assigned' : 'New',
            urgency: $urgency,
            w9: $w9,
            assigned_to: $assigned_to,
            created_by: (int)$currentUser['id'],
            total_amount: $total_amount,
            created_at: $created_at,
            sla_date: $sla_date
        );

        if ($job->save()) {
            ActivityLog::log((int)$currentUser['id'], $job->id, 'job_create', "Created work order for {$store_name}.");

            if ($job->assigned_to) {
                User::addNotification($job->assigned_to, "You have been assigned to {$job->ref()}: {$store_name}.", $job->id, 'job_assign');
                ActivityLog::log((int)$currentUser['id'], $job->id, 'assignment_change', "Assigned {$job->ref()} to user ID {$job->assigned_to}.");
            }

            $this->handleFileUploads($job);
            $this->redirect($job->path());
        } else {
            $_SESSION['create_job_error'] = 'Failed to store the job. Database error.';
            $this->redirect('/jobs/create');
        }
    }

    public function show(string $id): void {
        Auth::middleware();

        $job = Job::findByRoute($id);
        if (!$job) {
            http_response_code(404);
            die('Work order not found.');
        }

        if (!JobReference::isValidFormat($id) || strcasecmp($id, $job->ref()) !== 0) {
            $this->redirect($job->path());
            return;
        }

        $jobId = (int)$job->id;
        $currentUser = Auth::user();
        $userId = (int)$currentUser['id'];
        $commentsLastReadAt = JobCommentRead::getLastReadAt($userId, $jobId);

        $commentPage = Comment::forJobPaginated($jobId, $userId, Comment::PER_PAGE, 0);
        $comments = $commentPage['comments'];
        $commentsTotal = $commentPage['total'];
        $commentsHasMore = count($comments) < $commentsTotal;
        $commentsNextOffset = count($comments);

        JobCommentRead::markRead($userId, $jobId);
        User::markNotificationsReadForJob($userId, $jobId);

        $pictures = $job->getPictures();
        $payments = $job->getPayments();
        $users = User::all();

        $this->render('jobs.show', [
            'job' => $job,
            'comments' => $comments,
            'commentsLastReadAt' => $commentsLastReadAt,
            'commentsHasMore' => $commentsHasMore,
            'commentsNextOffset' => $commentsNextOffset,
            'commentsTotal' => $commentsTotal,
            'pictures' => $pictures,
            'payments' => $payments,
            'users' => $users,
            'user' => $currentUser
        ]);
    }

    public function markRead(string $id): void {
        Auth::middleware();

        if (!CSRF::validate($this->requestHeader('X-CSRF-Token') ?? $_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'CSRF token verification failed.'], 403);
            return;
        }

        $job = Job::findByRoute($id);
        if (!$job) {
            $this->json(['success' => false, 'error' => 'Work order not found.'], 404);
            return;
        }

        $jobId = (int)$job->id;
        $currentUser = Auth::user();
        $userId = (int)$currentUser['id'];

        JobCommentRead::markRead($userId, $jobId);
        User::markNotificationsReadForJob($userId, $jobId);

        $this->json(['success' => true]);
    }

    public function markComplete(string $id): void {
        Auth::middleware();
        $currentUser = Auth::user();

        $job = Job::findByRoute($id);
        if (!$job) {
            http_response_code(404);
            die('Work order not found.');
        }

        $jobId = (int)$job->id;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token validation failed.");
            }

            $action_type = $_POST['action_type'] ?? 'complete';

            if ($action_type === 'edit_fields') {
                if (!in_array($currentUser['role'], ['admin', 'team_lead'], true)) {
                    Auth::denyAccess('Only Administrators or Team Leads can update job specifications.');
                }

                $old_assigned = $job->assigned_to;
                $new_assigned = $_POST['assigned_to'] !== '' ? (int)$_POST['assigned_to'] : null;

                if ($new_assigned !== null && !User::canAssign($new_assigned)) {
                    Auth::initSession();
                    $_SESSION['flash_error'] = 'Invalid or suspended assignee selected.';
                    $this->redirect($job->path());
                    return;
                }

                $urgency = $_POST['urgency'] ?? $job->urgency;
                $w9 = $_POST['w9'] ?? $job->w9;
                $old_status = $job->status;
                $new_status = $_POST['status'] ?? $job->status;

                if (!Validator::inEnum($urgency, Validator::URGENCIES)
                    || !Validator::inEnum($w9, Validator::W9_VALUES)
                    || !Validator::inEnum($new_status, Validator::JOB_STATUSES)) {
                    Auth::initSession();
                    $_SESSION['flash_error'] = 'Invalid job field values submitted.';
                    $this->redirect($job->path());
                    return;
                }

                $job->assigned_to = $new_assigned;
                $job->urgency = $urgency;
                $job->w9 = $w9;
                $job->status = $new_status;
                
                if (!empty($_POST['created_at'])) {
                    $job->created_at = date('Y-m-d H:i:s', strtotime($_POST['created_at']));
                }
                $job->sla_date = !empty($_POST['sla_date']) ? date('Y-m-d H:i:s', strtotime($_POST['sla_date'])) : null;

                if ($old_assigned === null && $new_assigned !== null && $job->status === 'New') {
                    $job->status = 'Assigned';
                }

                if ($job->save()) {
                    if ($old_assigned !== $new_assigned) {
                        ActivityLog::log((int)$currentUser['id'], $job->id, 'assignment_change', "Modified assignment. Old assignee ID: " . ($old_assigned ?: 'None') . ", New assignee ID: " . ($new_assigned ?: 'None'));
                        if ($new_assigned) {
                            User::addNotification($new_assigned, "You have been assigned to {$job->ref()}.", $job->id, 'job_assign');
                        }
                    }
                    if ($old_status !== $new_status) {
                        ActivityLog::log((int)$currentUser['id'], $job->id, 'status_change', "Status updated from {$old_status} to {$new_status}.");
                        if ($job->assigned_to && $job->assigned_to !== (int)$currentUser['id']) {
                            User::addNotification($job->assigned_to, "{$job->ref()} status updated to {$new_status} by {$currentUser['name']}.", $job->id, 'status_update');
                        }
                    }
                    ActivityLog::log((int)$currentUser['id'], $job->id, 'job_edit', "Updated job specifications.");
                    Auth::initSession();
                    $_SESSION['flash_success'] = 'Job specifications saved successfully.';
                }

            } elseif ($action_type === 'edit_assigned_fields') {
                $isAssigned = ($job->assigned_to === (int)$currentUser['id']);
                if (!$isAssigned) {
                    Auth::denyAccess('Only the assigned coordinator can update this work order.');
                }

                $w9 = $_POST['w9'] ?? $job->w9;
                $old_status = $job->status;
                $new_status = $_POST['status'] ?? $job->status;

                if (!Validator::inEnum($w9, Validator::W9_VALUES)
                    || !Validator::inEnum($new_status, Validator::JOB_STATUSES)) {
                    Auth::initSession();
                    $_SESSION['flash_error'] = 'Invalid field values submitted.';
                    $this->redirect($job->path());
                    return;
                }

                $oldW9 = $job->w9;
                $job->w9 = $w9;
                $job->status = $new_status;

                if ($job->save()) {
                    if ($oldW9 !== $w9) {
                        ActivityLog::log((int)$currentUser['id'], $job->id, 'job_edit', "Assigned coordinator updated W9 from {$oldW9} to {$w9}.");
                    }
                    if ($old_status !== $new_status) {
                        ActivityLog::log((int)$currentUser['id'], $job->id, 'status_change', "Status updated from {$old_status} to {$new_status}.");
                        if ($job->created_by && $job->created_by !== (int)$currentUser['id']) {
                            User::addNotification($job->created_by, "{$job->ref()} status updated to {$new_status} by {$currentUser['name']}.", $job->id, 'status_update');
                        }
                    }
                    Auth::initSession();
                    $_SESSION['flash_success'] = 'Work order updated successfully.';
                }

            } else {
                $isAssigned = ($job->assigned_to === (int)$currentUser['id']);
                $isAdminOrTL = in_array($currentUser['role'], ['admin', 'team_lead'], true);

                if (!$isAssigned && !$isAdminOrTL) {
                    Auth::denyAccess('You can only complete jobs assigned to you.');
                }

                $old_status = $job->status;
                $job->status = 'Done';
                
                if ($job->save()) {
                    ActivityLog::log((int)$currentUser['id'], $job->id, 'status_change', "Marked completed. Status changed from {$old_status} to Done.");
                    
                    if ($job->created_by && $job->created_by !== (int)$currentUser['id']) {
                        User::addNotification($job->created_by, "{$job->ref()} has been marked complete by {$currentUser['name']}.", $job->id, 'status_update');
                    }
                }
            }
        }

        $this->redirect($job->path());
    }

    public function downloadAttachments(string $id): void {
        Auth::middleware();
        $job = Job::findByRoute($id);
        if (!$job) {
            http_response_code(404);
            die('Work order not found.');
        }
        $jobId = (int)$job->id;

        $scope = $_GET['scope'] ?? 'job';
        $files = [];

        if ($scope === 'job' || $scope === 'all') {
            foreach ($job->getPictures() as $index => $pic) {
                $files[] = [
                    'path' => AttachmentZip::publicPath($pic['file_path']),
                    'name' => 'job/' . ($index + 1) . '_' . basename($pic['file_path']),
                ];
            }
        }

        if ($scope === 'comments' || $scope === 'all') {
            foreach (Comment::getPicturePathsForJob($jobId) as $index => $path) {
                $files[] = [
                    'path' => AttachmentZip::publicPath($path),
                    'name' => 'comments/' . ($index + 1) . '_' . basename($path),
                ];
            }
        }

        $label = match ($scope) {
            'comments' => $job->ref() . '_comment_attachments',
            'all' => $job->ref() . '_all_attachments',
            default => $job->ref() . '_attachments',
        };

        AttachmentZip::download($files, $label . '.zip');
    }

    private function handleFileUploads(Job $job): void {
        if (!isset($_FILES['pictures'])) {
            return;
        }

        $userId = (int)Auth::user()['id'];
        $uploadDir = ROOT_PATH . '/public/uploads/jobs/' . $job->id . '/';
        $relativePrefix = '/uploads/jobs/' . $job->id;

        $paths = Upload::storeMultipleImages(
            $_FILES['pictures'],
            $uploadDir,
            $relativePrefix,
            Validator::MAX_JOB_UPLOAD_FILES
        );
        foreach ($paths as $dbPath) {
            $job->addPicture($dbPath, $userId);
        }
    }

    /**
     * Update total contracted amount for a job.
     * POST /jobs/{id}/total-amount
     */
    public function updateTotalAmount(string $id): void {
        Auth::middleware();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            $this->json(['error' => 'CSRF security token verification failed.'], 403);
            return;
        }

        $job = Job::findByRoute($id);
        if (!$job) {
            $this->json(['error' => 'Work order not found.'], 404);
            return;
        }

        $currentUser = Auth::user();
        $isAdminOrTL = in_array($currentUser['role'], ['admin', 'team_lead'], true);
        $isAssigned = $job->assigned_to && (int)$job->assigned_to === (int)$currentUser['id'];

        if (!$isAdminOrTL && !$isAssigned) {
            $this->json(['error' => 'You do not have permission to update the amount of this work order.'], 403);
            return;
        }

        if (!isset($_POST['total_amount']) || !is_numeric($_POST['total_amount'])) {
            $this->json(['error' => 'Invalid amount value.'], 400);
            return;
        }

        $amount = max(0.0, (float)$_POST['total_amount']);

        if ($job->saveTotalAmount($amount)) {
            ActivityLog::log((int)$currentUser['id'], $job->id, 'job_amount_update', "Updated total job amount to $" . number_format($amount, 2) . ".");
            $this->json(['success' => true, 'total_amount' => $amount]);
        } else {
            $this->json(['error' => 'Database error: failed to update amount.'], 500);
        }
    }

    /**
     * Delete a work order (Admin and Team Lead only).
     * POST /jobs/{id}/delete
     */
    public function delete(string $id): void {
        Auth::middleware(['admin', 'team_lead']);

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Auth::initSession();
            $_SESSION['flash_error'] = 'CSRF security token verification failed.';
            $this->redirect('/jobs');
            return;
        }

        $job = Job::findByRoute($id);
        if (!$job) {
            Auth::initSession();
            $_SESSION['flash_error'] = 'Work order not found.';
            $this->redirect('/jobs');
            return;
        }

        $currentUser = Auth::user();
        $storeName = $job->store_name;
        $ref = $job->ref();

        if ($job->delete()) {
            ActivityLog::log((int)$currentUser['id'], $job->id, 'job_delete', "Permanently deleted work order {$ref} ({$storeName}).");
            Auth::initSession();
            $_SESSION['flash_success'] = "Work order {$ref} was deleted successfully.";
        } else {
            Auth::initSession();
            $_SESSION['flash_error'] = 'Failed to delete the work order. Database error.';
        }

        $this->redirect('/jobs');
    }
}
