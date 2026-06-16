<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;
use App\Models\UserNotificationSettings;
use App\Models\SystemPollingSettings;
use App\Models\Comment;
use App\Models\JobCommentRead;
use App\Core\JobReference;

class RealtimeUpdates {
    /**
     * Cheap fingerprint — one round-trip before heavy queries.
     */
    public static function computeVersion(int $userId, string $role, string $context, array $options = []): string {
        $model = new User();
        $db = $model->getDB();

        $parts = [];

        $stmt = $db->prepare("
            SELECT
                (SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0) AS notif_unread,
                (SELECT COALESCE(UNIX_TIMESTAMP(MAX(created_at)), 0) FROM notifications WHERE user_id = ?) AS notif_ts,
                (SELECT COUNT(*) FROM comments c
                    LEFT JOIN job_comment_reads r ON r.job_id = c.job_id AND r.user_id = ?
                    WHERE c.user_id != ? AND c.created_at > COALESCE(r.last_read_at, '1970-01-01 00:00:00')
                ) AS comment_unread,
                (SELECT COALESCE(UNIX_TIMESTAMP(MAX(c.created_at)), 0) FROM comments c WHERE c.user_id != ?) AS comment_ts
        ");
        $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        $parts[] = implode(':', $row);

        if ($context === 'dashboard') {
            $filters = ($role === 'user') ? ['for_user_id' => $userId] : [];
            $stats = Job::countStats($filters);
            $parts[] = 'stats:' . json_encode($stats);

            $queueStmt = $db->prepare("
                SELECT COALESCE(UNIX_TIMESTAMP(MAX(j.updated_at)), 0)
                FROM jobs j
                WHERE j.assigned_to = ? AND j.status != 'Done'
            ");
            $queueStmt->execute([$userId]);
            $parts[] = 'queue:' . (int)$queueStmt->fetchColumn();
        }

        if ($context === 'job' && !empty($options['job_id'])) {
            $jobId = (int)$options['job_id'];
            $jobStmt = $db->prepare("
                SELECT
                    COALESCE(MAX(c.id), 0),
                    COALESCE(UNIX_TIMESTAMP(MAX(c.created_at)), 0),
                    COALESCE(UNIX_TIMESTAMP(MAX(j.updated_at)), 0)
                FROM jobs j
                LEFT JOIN comments c ON c.job_id = j.id
                WHERE j.id = ?
            ");
            $jobStmt->execute([$jobId]);
            $parts[] = 'job:' . implode(':', $jobStmt->fetch(\PDO::FETCH_NUM) ?: [0, 0, 0]);
        }

        $parts[] = 'prefs:' . json_encode(UserNotificationSettings::forUser($userId)->toArray());
        $parts[] = 'poll:' . json_encode(SystemPollingSettings::get()->toArray());

        return hash('sha256', implode('|', $parts));
    }

    public static function buildPayload(int $userId, string $role, string $context, array $options = []): array {
        $payload = [
            'notifications' => self::notificationPayload($userId),
        ];

        $jobIds = array_values(array_unique(array_filter(array_map('intval', $options['job_ids'] ?? []))));

        if ($context === 'dashboard') {
            $filters = ($role === 'user') ? ['for_user_id' => $userId] : [];
            $stats = Job::countStats($filters);
            $payload['stats'] = [
                'total' => $stats['total'],
                'new' => $stats['new'],
                'assigned' => $stats['assigned'],
                'in_progress' => $stats['scheduled'] + $stats['wip'],
                'done' => $stats['done'],
            ];

            $queueJobs = Job::activeAssignedToUser($userId, 10);
            $queueJobIds = array_map(static fn($job) => (int)$job->id, $queueJobs);
            $payload['unreadComments'] = [
                'byJobId' => JobCommentRead::countsForJobs($userId, $queueJobIds),
                'total' => JobCommentRead::totalUnread($userId),
            ];
        } elseif ($context === 'jobs' && !empty($jobIds)) {
            $counts = JobCommentRead::countsForJobs($userId, $jobIds);
            $payload['unreadComments'] = [
                'byJobId' => $counts,
                'total' => array_sum($counts),
            ];
        } elseif ($context === 'jobs') {
            $payload['unreadComments'] = [
                'byJobId' => [],
                'total' => JobCommentRead::totalUnread($userId),
            ];
        }

        if ($context === 'job' && !empty($options['job_id'])) {
            $jobId = (int)$options['job_id'];
            $afterId = max(0, (int)($options['after_comment_id'] ?? 0));
            $newComments = Comment::newSinceForJob($jobId, $userId, $afterId);
            $latestId = $afterId;
            foreach ($newComments as $comment) {
                $latestId = max($latestId, (int)$comment->id);
            }
            $payload['jobComments'] = [
                'comments' => array_map(static fn($c) => $c->toApiArray(), $newComments),
                'latestCommentId' => $latestId,
            ];
        }

        return $payload;
    }

    public static function notificationPayload(int $userId): array {
        $items = User::withResolvedJobIds(User::getNotifications($userId));
        $settings = UserNotificationSettings::forUser($userId);

        return [
            'unreadCount' => User::countUnreadNotifications($userId),
            'items' => $items,
            'settings' => $settings->toArray(),
        ];
    }

    public static function resolveJobIdFromRef(string $jobRef): ?int {
        if ($jobRef === '') {
            return null;
        }
        if (JobReference::isValidFormat($jobRef)) {
            $job = Job::findByReference($jobRef);
            if ($job) {
                return (int)$job->id;
            }
            $fallbackId = JobReference::legacyIdFromRoute($jobRef);
            if ($fallbackId) {
                return $fallbackId;
            }
        }
        if (ctype_digit($jobRef)) {
            $job = Job::find((int)$jobRef);
            return $job ? (int)$job->id : null;
        }
        return null;
    }
}
