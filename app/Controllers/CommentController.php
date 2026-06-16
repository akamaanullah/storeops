<?php
/**
 * Comment and Auxiliary Operations Controller - PHP 8 Custom MVC
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
use App\Models\Comment;
use App\Models\User;
use App\Models\Payment;
use App\Models\ActivityLog;
use App\Models\JobCommentRead;

class CommentController extends Controller {
    public function listForJob(string $id): void {
        Auth::middleware();

        $job = Job::findByRoute($id);
        if ($job === null) {
            $this->json(['success' => false, 'error' => 'Job not found.'], 404);
            return;
        }

        $jobId = (int)$job->id;

        $currentUser = Auth::user();
        $userId = (int)$currentUser['id'];
        $offset = max(0, (int)($_GET['offset'] ?? 0));
        $limit = min(20, max(1, (int)($_GET['limit'] ?? Comment::PER_PAGE)));

        $result = Comment::forJobPaginated($jobId, $userId, $limit, $offset);
        $loaded = count($result['comments']);

        $this->json([
            'success' => true,
            'comments' => array_map(fn(Comment $c) => $c->toApiArray(), $result['comments']),
            'hasMore' => ($offset + $loaded) < $result['total'],
            'nextOffset' => $offset + $loaded,
            'total' => $result['total'],
        ]);
    }

    public function store(string $id): void {
        Auth::middleware();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token validation failed.");
        }

        $job = Job::findByRoute($id);
        if (!$job) {
            http_response_code(404);
            die("Job not found.");
        }

        $jobId = (int)$job->id;
        $currentUser = Auth::user();

        $commentText = trim($_POST['comment'] ?? '');
        $hasUploads = isset($_FILES['pictures']['name'][0]) && $_FILES['pictures']['name'][0] !== '';

        if ($commentText === '' && !$hasUploads) {
            $this->redirect($job->path());
            return;
        }

        $uploadDir = ROOT_PATH . '/public/uploads/comments/' . $jobId . '/';
        $relativePrefix = '/uploads/comments/' . $jobId;
        $storedPaths = [];

        if ($hasUploads) {
            $storedPaths = Upload::storeMultipleImages(
                $_FILES['pictures'],
                $uploadDir,
                $relativePrefix,
                Validator::MAX_COMMENT_UPLOAD_FILES
            );
        }

        $comment = new Comment(
            null,
            $jobId,
            (int)$currentUser['id'],
            $commentText,
            $storedPaths[0] ?? null
        );

        $saved = $comment->save();
        if ($saved && !empty($storedPaths)) {
            $comment->addPictures($storedPaths);
        }

        if ($saved) {
            ActivityLog::log((int)$currentUser['id'], $jobId, 'comment_add', "Submitted a feedback comment.");
            JobCommentRead::markRead((int)$currentUser['id'], $jobId);
            User::markNotificationsReadForJob((int)$currentUser['id'], $jobId);

            $preview = Validator::truncatePreview($commentText !== '' ? $commentText : 'shared attachment(s)');
            $jobRef = $job->ref();

            if ($job->assigned_to && (int)$job->assigned_to !== (int)$currentUser['id']) {
                User::addNotification(
                    (int)$job->assigned_to,
                    "{$currentUser['name']} commented on {$jobRef}: \"{$preview}\"",
                    $jobId,
                    'comment'
                );
            }
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                  || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

        if ($isAjax) {
            if ($saved) {
                $pictures = array_map(
                    fn($path) => ['file_path' => $path],
                    $storedPaths
                );

                $this->json([
                    'success' => true,
                    'comment' => [
                        'id' => $comment->id,
                        'user_id' => (int)$currentUser['id'],
                        'comment' => $comment->comment,
                        'picture_path' => $comment->picture_path,
                        'pictures' => $pictures,
                        'created_at' => date('M j, Y, H:i', time()),
                        'user_name' => $currentUser['name'],
                        'user_role' => $currentUser['role'],
                        'likes' => 0,
                        'dislikes' => 0,
                        'user_vote' => null
                    ]
                ]);
            } else {
                $this->json(['success' => false, 'error' => 'Database error during save.'], 500);
            }
        } else {
            $this->redirect($job->path());
        }
    }

    public function downloadAttachments(string $id): void {
        Auth::middleware();
        $commentId = (int)$id;
        $comment = Comment::find($commentId);

        if (!$comment) {
            http_response_code(404);
            die('Comment not found.');
        }

        $files = [];
        foreach (Comment::getPicturePathsForComment($commentId) as $index => $path) {
            $files[] = [
                'path' => AttachmentZip::publicPath($path),
                'name' => ($index + 1) . '_' . basename($path),
            ];
        }

        AttachmentZip::download($files, "comment_{$commentId}_attachments.zip");
    }

    public function vote(string $id): void {
        Auth::middleware();

        if (!CSRF::validate($this->requestHeader('X-CSRF-Token'))) {
            $this->json(['success' => false, 'error' => 'CSRF token verification failed.'], 403);
            return;
        }

        $commentId = (int)$id;
        $currentUser = Auth::user();
        $voterId = (int)$currentUser['id'];

        $comment = Comment::find($commentId);
        if (!$comment) {
            $this->json(['success' => false, 'error' => 'Comment not found.'], 404);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $voteType = $input['vote'] ?? '';

        if (!Validator::inEnum($voteType, ['like', 'dislike'])) {
            $this->json(['success' => false, 'error' => 'Invalid vote selection.'], 400);
            return;
        }

        $result = Comment::castVote($commentId, $voterId, $voteType);

        if ($result['success']) {
            if (
                in_array($result['action'], ['added', 'changed'], true)
                && $comment->user_id !== null
                && (int)$comment->user_id !== $voterId
                && $result['vote'] !== null
            ) {
                $jobId = (int)$comment->job_id;
                $jobEntity = Job::find($jobId);
                $jobRef = $jobEntity ? $jobEntity->ref() : Job::refFor($jobId);
                $voteLabel = $result['vote'] === 'like' ? 'liked' : 'disliked';
                User::addNotification(
                    (int)$comment->user_id,
                    "{$currentUser['name']} {$voteLabel} your comment on {$jobRef}.",
                    $jobId,
                    'vote'
                );
            }

            $db = (new Comment())->getDB();
            
            $countStmt = $db->prepare("
                SELECT 
                    SUM(CASE WHEN vote = 'like' THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN vote = 'dislike' THEN 1 ELSE 0 END) as dislikes
                FROM comment_votes WHERE comment_id = ?
            ");
            $countStmt->execute([$commentId]);
            $counts = $countStmt->fetch();

            $userVoteStmt = $db->prepare("SELECT vote FROM comment_votes WHERE comment_id = ? AND user_id = ? LIMIT 1");
            $userVoteStmt->execute([$commentId, $voterId]);
            $userVoteRow = $userVoteStmt->fetch();
            $myCurrentVote = $userVoteRow ? $userVoteRow['vote'] : null;

            $this->json([
                'success' => true,
                'likes' => (int)($counts['likes'] ?? 0),
                'dislikes' => (int)($counts['dislikes'] ?? 0),
                'myVote' => $myCurrentVote
            ]);
        } else {
            $this->json(['success' => false, 'error' => 'Unable to process vote.'], 500);
        }
    }

    public function addPayment(string $id): void {
        Auth::middleware();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Auth::denyAccess('Security token expired. Please try again.');
        }

        $job = Job::findByRoute($id);
        if (!$job) {
            http_response_code(404);
            die("Job not found.");
        }

        $jobId = (int)$job->id;
        $currentUser = Auth::user();

        $isAdminOrTL = in_array($currentUser['role'], ['admin', 'team_lead'], true);
        $isAssigned = ($job->assigned_to === (int)$currentUser['id']);
        if (!$isAdminOrTL && !$isAssigned) {
            Auth::denyAccess('Only the assigned coordinator or Administrators can record payments on this job.');
        }

        $amount = (float)($_POST['amount'] ?? 0);
        $type = $_POST['type'] ?? 'partial';
        $party = $_POST['party'] ?? 'client';
        $note = trim($_POST['note'] ?? '');

        if (!Validator::inEnum($party, ['client', 'vendor'])) {
            http_response_code(400);
            die('Invalid transaction party.');
        }

        if ($party === 'client' && $currentUser['role'] !== 'admin') {
            Auth::denyAccess('Only Administrators can record client revenue payments.');
        }

        if ($amount <= 0 || $amount > Validator::MAX_PAYMENT_AMOUNT) {
            $this->redirect($job->path());
            return;
        }

        if (!Validator::inEnum($type, Validator::PAYMENT_TYPES)) {
            http_response_code(400);
            die('Invalid payment type.');
        }

        $payment = new Payment(null, $jobId, $type, $party, $amount, $note);

        if ($payment->save()) {
            ActivityLog::log((int)$currentUser['id'], $jobId, 'payment_add', "Recorded a {$type} " . ucfirst($party) . " payment of $" . number_format($amount, 2) . ". Details: {$note}");
            Auth::initSession();
            $_SESSION['flash_success'] = 'Payment recorded successfully.';
        }

        $this->redirect($job->path());
    }

    public function edit(string $id): void {
        Auth::middleware();

        if (!CSRF::validate($this->requestHeader('X-CSRF-Token') ?? $_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'CSRF token verification failed.'], 403);
            return;
        }

        $commentId = (int)$id;
        $comment = Comment::find($commentId);
        if (!$comment) {
            $this->json(['success' => false, 'error' => 'Comment not found.'], 404);
            return;
        }

        $currentUser = Auth::user();
        $isAuthor = ((int)$comment->user_id === (int)$currentUser['id']);
        if (!$isAuthor) {
            $this->json(['success' => false, 'error' => 'You do not have permission to edit this comment.'], 403);
            return;
        }

        $text = trim($_POST['comment'] ?? '');
        if ($text === '') {
            $this->json(['success' => false, 'error' => 'Comment text cannot be empty.'], 400);
            return;
        }

        if (Comment::updateCommentText($commentId, $text)) {
            ActivityLog::log((int)$currentUser['id'], $comment->job_id, 'comment_edit', "Edited a comment.");
            $this->json(['success' => true, 'comment' => $text]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to update comment in database.'], 500);
        }
    }

    public function editPayment(string $id): void {
        Auth::middleware();

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Auth::denyAccess('Security token expired. Please try again.');
        }

        $paymentId = (int)$id;
        $payment = Payment::find($paymentId);
        if (!$payment) {
            http_response_code(404);
            die("Payment not found.");
        }

        $job = Job::find($payment->job_id);
        if (!$job) {
            http_response_code(404);
            die("Job not found.");
        }

        $currentUser = Auth::user();
        $isAdminOrTL = in_array($currentUser['role'], ['admin', 'team_lead'], true);
        $isAssigned = ($job->assigned_to === (int)$currentUser['id']);
        if (!$isAdminOrTL && !$isAssigned) {
            Auth::denyAccess('You do not have permission to edit this payment.');
        }

        $amount = (float)($_POST['amount'] ?? 0);
        $type = $_POST['type'] ?? 'partial';
        $party = $_POST['party'] ?? $payment->party;
        $note = trim($_POST['note'] ?? '');

        if (!Validator::inEnum($party, ['client', 'vendor'])) {
            http_response_code(400);
            die('Invalid transaction party.');
        }

        // Security check: if existing OR new party is 'client', only admin can save
        if (($payment->party === 'client' || $party === 'client') && $currentUser['role'] !== 'admin') {
            Auth::denyAccess('Only Administrators can edit or set client revenue payments.');
        }

        if ($amount <= 0 || $amount > Validator::MAX_PAYMENT_AMOUNT) {
            Auth::initSession();
            $_SESSION['flash_error'] = 'Invalid payment amount.';
            $this->redirect($job->path());
            return;
        }

        if (!Validator::inEnum($type, Validator::PAYMENT_TYPES)) {
            http_response_code(400);
            die('Invalid payment type.');
        }

        $payment->amount = $amount;
        $payment->type = $type;
        $payment->party = $party;
        $payment->note = $note;

        if ($payment->update()) {
            ActivityLog::log((int)$currentUser['id'], $job->id, 'payment_edit', "Updated payment details. New amount: $" . number_format($amount, 2) . " (" . ucfirst($party) . ")");
            Auth::initSession();
            $_SESSION['flash_success'] = 'Payment updated successfully.';
        } else {
            Auth::initSession();
            $_SESSION['flash_error'] = 'Failed to update payment due to database error.';
        }

        $this->redirect($job->path());
    }

    /**
     * Upload (or replace) W9 form document for a job.
     * POST /jobs/{id}/w9
     */
    public function uploadW9(string $id): void {
        Auth::middleware();

        if (!CSRF::validate($this->requestHeader('X-CSRF-Token') ?? $_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'CSRF token verification failed.'], 403);
            return;
        }

        $job = Job::findByRoute($id);
        if (!$job) {
            $this->json(['success' => false, 'error' => 'Job not found.'], 404);
            return;
        }

        $currentUser = Auth::user();

        // Any authenticated user who can see the job can attach a W9
        $hasFile = isset($_FILES['w9_form']['name']) && $_FILES['w9_form']['error'] !== UPLOAD_ERR_NO_FILE;
        if (!$hasFile) {
            $this->json(['success' => false, 'error' => 'No file uploaded.'], 400);
            return;
        }

        $uploadDir       = ROOT_PATH . '/public/uploads/w9/' . (int)$job->id . '/';
        $relativePrefix  = '/uploads/w9/' . (int)$job->id;

        // Delete old W9 file if exists
        if ($job->w9_form_path) {
            $oldFile = ROOT_PATH . '/public' . $job->w9_form_path;
            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }
        }

        $path = Upload::storeDocument($_FILES['w9_form'], $uploadDir, $relativePrefix);
        if (!$path) {
            $this->json(['success' => false, 'error' => 'Invalid file. Accepted types: PDF, DOC, DOCX, JPG, PNG (max 10 MB).'], 422);
            return;
        }

        if ($job->saveW9Path($path)) {
            ActivityLog::log(
                (int)$currentUser['id'],
                (int)$job->id,
                'w9_upload',
                'Uploaded W9 form document.'
            );
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $this->json([
                'success'  => true,
                'path'     => $path,
                'ext'      => $ext,
                'filename' => basename($path),
            ]);
        } else {
            $this->json(['success' => false, 'error' => 'Failed to save W9 path in database.'], 500);
        }
    }

    /**
     * Delete W9 form document for a job.
     * POST /jobs/{id}/w9/delete
     */
    public function deleteW9(string $id): void {
        Auth::middleware();

        if (!CSRF::validate($this->requestHeader('X-CSRF-Token') ?? $_POST['csrf_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'CSRF token verification failed.'], 403);
            return;
        }

        $job = Job::findByRoute($id);
        if (!$job) {
            $this->json(['success' => false, 'error' => 'Job not found.'], 404);
            return;
        }

        $currentUser = Auth::user();
        $isAdminOrTL = in_array($currentUser['role'], ['admin', 'team_lead'], true);
        if (!$isAdminOrTL) {
            $this->json(['success' => false, 'error' => 'Only administrators or team leads can remove the W9 document.'], 403);
            return;
        }

        if ($job->w9_form_path) {
            $file = ROOT_PATH . '/public' . $job->w9_form_path;
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        $job->clearW9Path();
        ActivityLog::log(
            (int)$currentUser['id'],
            (int)$job->id,
            'w9_delete',
            'Removed W9 form document.'
        );
        $this->json(['success' => true]);
    }
}
