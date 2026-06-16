<?php
/**
 * Comment Model - PHP 8 Custom MVC
 */

namespace App\Models;

use App\Core\Model;

class Comment extends Model {
    public const PER_PAGE = 10;

    public function __construct(
        public ?int $id = null,
        public ?int $job_id = null,
        public ?int $user_id = null,
        public ?string $comment = null,
        public ?string $picture_path = null,
        public ?string $created_at = null,
        
        // Dynamic properties
        public ?string $user_name = null,
        public ?string $user_role = null,
        public int $likes = 0,
        public int $dislikes = 0,
        public ?string $user_vote = null,
        public array $pictures = []
    ) {}

    public static function forJob(int $jobId, ?int $currentUserId = null): array {
        return self::forJobPaginated($jobId, $currentUserId, PHP_INT_MAX, 0)['comments'];
    }

    public static function countForJob(int $jobId): int {
        $db = (new self())->getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE job_id = ?");
        $stmt->execute([$jobId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * @return array{comments: self[], total: int}
     */
    public static function forJobPaginated(int $jobId, ?int $currentUserId, int $limit, int $offset = 0): array {
        $total = self::countForJob($jobId);
        if ($total === 0) {
            return ['comments' => [], 'total' => 0];
        }

        $model = new self();
        $db = $model->getDB();

        $sql = "SELECT c.*, u.full_name as user_name, u.role as user_role
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.job_id = ?
                ORDER BY c.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $jobId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return [
            'comments' => self::hydrateFromRows($rows, $currentUserId),
            'total' => $total,
        ];
    }

    /**
     * Newest-first comments with id greater than $afterId (for live polling).
     *
     * @return self[]
     */
    public static function newSinceForJob(int $jobId, ?int $currentUserId, int $afterId): array {
        if ($afterId < 0) {
            return [];
        }

        $model = new self();
        $db = $model->getDB();

        $sql = "SELECT c.*, u.full_name as user_name, u.role as user_role
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.job_id = ? AND c.id > ?
                ORDER BY c.created_at DESC
                LIMIT 20";

        $stmt = $db->prepare($sql);
        $stmt->execute([$jobId, $afterId]);
        $rows = $stmt->fetchAll();

        return self::hydrateFromRows($rows, $currentUserId);
    }

    public static function isUnreadForUser(self $comment, ?string $lastReadAt, int $currentUserId): bool {
        if ((int)$comment->user_id === $currentUserId) {
            return false;
        }
        if ($lastReadAt === null) {
            return true;
        }
        return strtotime($comment->created_at) > strtotime($lastReadAt);
    }

    public function toApiArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'comment' => $this->comment ?? '',
            'picture_path' => $this->picture_path,
            'pictures' => $this->pictures,
            'user_name' => $this->user_name,
            'user_role' => $this->user_role,
            'created_at' => date('M j, Y, H:i', strtotime($this->created_at)),
            'likes' => $this->likes,
            'dislikes' => $this->dislikes,
            'user_vote' => $this->user_vote,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return self[]
     */
    private static function hydrateFromRows(array $rows, ?int $currentUserId): array {
        if (empty($rows)) {
            return [];
        }

        $model = new self();
        $db = $model->getDB();
        $commentIds = array_map(fn($row) => (int)$row['id'], $rows);
        $placeholders = implode(',', array_fill(0, count($commentIds), '?'));
        $picturesByComment = self::loadPicturesByCommentIds($commentIds);

        $voteStmt = $db->prepare("
            SELECT comment_id,
                SUM(CASE WHEN vote = 'like' THEN 1 ELSE 0 END) as likesCount,
                SUM(CASE WHEN vote = 'dislike' THEN 1 ELSE 0 END) as dislikesCount
            FROM comment_votes
            WHERE comment_id IN ($placeholders)
            GROUP BY comment_id
        ");
        $voteStmt->execute($commentIds);
        $voteCounts = [];
        while ($voteRow = $voteStmt->fetch()) {
            $voteCounts[(int)$voteRow['comment_id']] = [
                'likes' => (int)($voteRow['likesCount'] ?? 0),
                'dislikes' => (int)($voteRow['dislikesCount'] ?? 0),
            ];
        }

        $userVotes = [];
        if ($currentUserId) {
            $userVoteStmt = $db->prepare("SELECT comment_id, vote FROM comment_votes WHERE user_id = ? AND comment_id IN ($placeholders)");
            $userVoteStmt->execute(array_merge([$currentUserId], $commentIds));
            while ($userVoteRow = $userVoteStmt->fetch()) {
                $userVotes[(int)$userVoteRow['comment_id']] = $userVoteRow['vote'];
            }
        }

        $comments = [];
        foreach ($rows as $row) {
            $commentId = (int)$row['id'];
            $counts = $voteCounts[$commentId] ?? ['likes' => 0, 'dislikes' => 0];
            $pictures = $picturesByComment[$commentId] ?? [];

            if (empty($pictures) && !empty($row['picture_path'])) {
                $pictures = [['file_path' => $row['picture_path']]];
            }

            $comments[] = new self(
                $commentId,
                (int)$row['job_id'],
                (int)$row['user_id'],
                $row['comment'],
                $pictures[0]['file_path'] ?? ($row['picture_path'] ?? null),
                $row['created_at'],
                $row['user_name'],
                $row['user_role'],
                $counts['likes'],
                $counts['dislikes'],
                $userVotes[$commentId] ?? null,
                $pictures
            );
        }

        return $comments;
    }

    private static function loadPicturesByCommentIds(array $commentIds): array {
        if (empty($commentIds)) {
            return [];
        }

        $model = new self();
        $db = $model->getDB();
        $placeholders = implode(',', array_fill(0, count($commentIds), '?'));

        try {
            $stmt = $db->prepare("SELECT comment_id, file_path FROM comment_pictures WHERE comment_id IN ($placeholders) ORDER BY id ASC");
            $stmt->execute($commentIds);
        } catch (\PDOException) {
            return [];
        }

        $grouped = [];
        while ($row = $stmt->fetch()) {
            $commentId = (int)$row['comment_id'];
            $grouped[$commentId][] = ['file_path' => $row['file_path']];
        }

        return $grouped;
    }

    public static function find(int $id): ?self {
        $model = new self();
        $db = $model->getDB();
        $stmt = $db->prepare("SELECT id, job_id, user_id FROM comments WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return new self((int)$row['id'], (int)$row['job_id'], (int)$row['user_id']);
    }

    /**
     * @return array{success: bool, action: 'added'|'changed'|'removed', vote: ?string}
     */
    public static function castVote(int $commentId, int $userId, string $voteType): array {
        if (self::find($commentId) === null) {
            return ['success' => false, 'action' => 'removed', 'vote' => null];
        }

        $model = new self();
        $db = $model->getDB();

        $stmt = $db->prepare("SELECT id, vote FROM comment_votes WHERE comment_id = ? AND user_id = ?");
        $stmt->execute([$commentId, $userId]);
        $existing = $stmt->fetch();

        if ($existing) {
            if ($existing['vote'] === $voteType) {
                $delStmt = $db->prepare("DELETE FROM comment_votes WHERE id = ?");
                return [
                    'success' => $delStmt->execute([$existing['id']]),
                    'action' => 'removed',
                    'vote' => null,
                ];
            }
            $upStmt = $db->prepare("UPDATE comment_votes SET vote = ? WHERE id = ?");
            return [
                'success' => $upStmt->execute([$voteType, $existing['id']]),
                'action' => 'changed',
                'vote' => $voteType,
            ];
        }

        $insStmt = $db->prepare("INSERT INTO comment_votes (comment_id, user_id, vote) VALUES (?, ?, ?)");
        return [
            'success' => $insStmt->execute([$commentId, $userId, $voteType]),
            'action' => 'added',
            'vote' => $voteType,
        ];
    }

    public function save(): bool {
        $db = $this->getDB();
        $stmt = $db->prepare("INSERT INTO comments (job_id, user_id, comment, picture_path) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([$this->job_id, $this->user_id, $this->comment, $this->picture_path]);
        if ($success) {
            $this->id = (int)$db->lastInsertId();
        }
        return $success;
    }

    public function addPictures(array $paths): void {
        if (empty($paths) || !$this->id) {
            return;
        }

        $db = $this->getDB();
        $stmt = $db->prepare("INSERT INTO comment_pictures (comment_id, file_path) VALUES (?, ?)");

        foreach ($paths as $path) {
            $stmt->execute([$this->id, $path]);
            $this->pictures[] = ['file_path' => $path];
        }

        if ($this->picture_path === null && !empty($paths)) {
            $this->picture_path = $paths[0];
            $update = $db->prepare("UPDATE comments SET picture_path = ? WHERE id = ?");
            $update->execute([$this->picture_path, $this->id]);
        }
    }

    public static function getPicturePathsForComment(int $commentId): array {
        $model = new self();
        $db = $model->getDB();

        try {
            $stmt = $db->prepare("SELECT file_path FROM comment_pictures WHERE comment_id = ? ORDER BY id ASC");
            $stmt->execute([$commentId]);
            $paths = array_column($stmt->fetchAll(), 'file_path');
            if (!empty($paths)) {
                return $paths;
            }
        } catch (\PDOException) {
            // Table may not exist yet on older databases.
        }

        $legacy = $db->prepare("SELECT picture_path FROM comments WHERE id = ? LIMIT 1");
        $legacy->execute([$commentId]);
        $row = $legacy->fetch();
        return !empty($row['picture_path']) ? [$row['picture_path']] : [];
    }

    public static function getPicturePathsForJob(int $jobId): array {
        $model = new self();
        $db = $model->getDB();

        try {
            $stmt = $db->prepare("
                SELECT cp.file_path
                FROM comment_pictures cp
                JOIN comments c ON c.id = cp.comment_id
                WHERE c.job_id = ?
                ORDER BY cp.id ASC
            ");
            $stmt->execute([$jobId]);
            $paths = array_column($stmt->fetchAll(), 'file_path');
            if (!empty($paths)) {
                return $paths;
            }
        } catch (\PDOException) {
        }

        $stmt = $db->prepare("SELECT picture_path FROM comments WHERE job_id = ? AND picture_path IS NOT NULL AND picture_path != ''");
        $stmt->execute([$jobId]);
        return array_column($stmt->fetchAll(), 'picture_path');
    }

    public static function updateCommentText(int $id, string $text): bool {
        $model = new self();
        $db = $model->getDB();
        $stmt = $db->prepare("UPDATE comments SET comment = ? WHERE id = ?");
        return $stmt->execute([$text, $id]);
    }
}
