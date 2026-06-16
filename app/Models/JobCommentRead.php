<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class JobCommentRead extends Model {
    public static function markRead(int $userId, int $jobId): void {
        $db = (new self())->getDB();
        $stmt = $db->prepare("
            INSERT INTO job_comment_reads (user_id, job_id, last_read_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE last_read_at = NOW()
        ");
        $stmt->execute([$userId, $jobId]);
    }

    public static function getLastReadAt(int $userId, int $jobId): ?string {
        $db = (new self())->getDB();

        try {
            $stmt = $db->prepare("SELECT last_read_at FROM job_comment_reads WHERE user_id = ? AND job_id = ? LIMIT 1");
            $stmt->execute([$userId, $jobId]);
            $value = $stmt->fetchColumn();
            return $value !== false ? (string)$value : null;
        } catch (\PDOException) {
            return null;
        }
    }

    /**
     * @param int[] $jobIds
     * @return array<int, int> job_id => unread_count
     */
    public static function countsForJobs(int $userId, array $jobIds): array {
        if (empty($jobIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($jobIds), '?'));
        $db = (new self())->getDB();

        try {
            $sql = "
                SELECT c.job_id, COUNT(*) AS unread_count
                FROM comments c
                LEFT JOIN job_comment_reads r
                    ON r.job_id = c.job_id AND r.user_id = ?
                WHERE c.user_id != ?
                  AND c.created_at > COALESCE(r.last_read_at, '1970-01-01 00:00:00')
                  AND c.job_id IN ($placeholders)
                GROUP BY c.job_id
            ";
            $params = array_merge([$userId, $userId], $jobIds);
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            $counts = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $counts[(int)$row['job_id']] = (int)$row['unread_count'];
            }
            return $counts;
        } catch (\PDOException) {
            return [];
        }
    }

    public static function totalUnread(int $userId): int {
        $db = (new self())->getDB();

        try {
            $stmt = $db->prepare("
                SELECT COUNT(*)
                FROM comments c
                LEFT JOIN job_comment_reads r
                    ON r.job_id = c.job_id AND r.user_id = ?
                WHERE c.user_id != ?
                  AND c.created_at > COALESCE(r.last_read_at, '1970-01-01 00:00:00')
            ");
            $stmt->execute([$userId, $userId]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException) {
            return 0;
        }
    }
}
