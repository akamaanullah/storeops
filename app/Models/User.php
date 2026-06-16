<?php
/**
 * User Model - PHP 8 Custom MVC
 */

namespace App\Models;

use App\Core\Model;
use App\Core\JobReference;

class User extends Model {
    public function __construct(
        public ?int $id = null,
        public ?string $username = null,
        public ?string $full_name = null,
        public ?string $password = null,
        public ?string $role = null,
        public ?string $created_at = null,
        public ?string $status = 'active'
    ) {}

    /** @param array<string, mixed> $row */
    private static function mapRow(array $row, bool $includePassword = false): self {
        return new self(
            (int)$row['id'],
            $row['username'],
            $row['full_name'],
            $includePassword ? ($row['password'] ?? null) : null,
            $row['role'],
            $row['created_at'],
            $row['status'] ?? 'active'
        );
    }

    public static function findByUsername(string $username): ?self {
        $username = trim($username);
        if ($username === '') {
            return null;
        }

        $db = (new self())->getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $row = $stmt->fetch();

        return $row ? self::mapRow($row, true) : null;
    }

    public static function usernameTaken(string $username, ?int $excludeUserId = null): bool {
        $existing = self::findByUsername($username);
        if ($existing === null) {
            return false;
        }
        if ($excludeUserId !== null && (int)$existing->id === $excludeUserId) {
            return false;
        }
        return true;
    }

    public static function all(): array {
        $db = (new self())->getDB();
        $stmt = $db->query("SELECT id, username, full_name, role, created_at, status FROM users ORDER BY full_name ASC");
        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = self::mapRow($row);
        }
        return $results;
    }

    public static function find(int $id): ?self {
        $db = (new self())->getDB();
        $stmt = $db->prepare("SELECT id, username, full_name, role, created_at, status FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? self::mapRow($row) : null;
    }

    public static function findWithPassword(int $id): ?self {
        $db = (new self())->getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? self::mapRow($row, true) : null;
    }

    public function save(): bool {
        $db = $this->getDB();

        if ($this->id) {
            if ($this->password) {
                $sql = "UPDATE users SET username = ?, full_name = ?, password = ?, role = ?, status = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                return $stmt->execute([
                    $this->username,
                    $this->full_name,
                    $this->password,
                    $this->role,
                    $this->status ?: 'active',
                    $this->id
                ]);
            }

            $sql = "UPDATE users SET username = ?, full_name = ?, role = ?, status = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                $this->username,
                $this->full_name,
                $this->role,
                $this->status ?: 'active',
                $this->id
            ]);
        }

        $sql = "INSERT INTO users (username, full_name, password, role, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $success = $stmt->execute([
            $this->username,
            $this->full_name,
            $this->password,
            $this->role ?: 'user',
            $this->status ?: 'active'
        ]);

        if ($success) {
            $this->id = (int)$db->lastInsertId();
        }
        return $success;
    }

    public function delete(): bool {
        if (!$this->id) {
            return false;
        }
        $db = $this->getDB();
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    public static function canAssign(int $userId): bool {
        $user = self::find($userId);
        return $user !== null && ($user->status ?? 'active') === 'active';
    }

    public static function getNotifications(int $userId): array {
        $db = (new self())->getDB();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 15");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public const NOTIFICATIONS_PER_PAGE = 20;

    public static function getAllNotifications(int $userId): array {
        return self::getNotificationsPaginated($userId, 1, PHP_INT_MAX)['items'];
    }

    /**
     * @return array{items: array<int, array>, total: int, page: int, per_page: int, total_pages: int}
     */
    public static function getNotificationsPaginated(int $userId, int $page = 1, ?int $perPage = null): array {
        $db = (new self())->getDB();
        $perPage = $perPage ?? self::NOTIFICATIONS_PER_PAGE;
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $countStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $stmt = $db->prepare(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$userId, $perPage, $offset]);

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    public static function addNotification(
        int $userId,
        string $message,
        ?int $jobId = null,
        string $type = 'other'
    ): bool {
        if (!in_array($type, UserNotificationSettings::TYPES, true)) {
            $type = UserNotificationSettings::inferType($message);
        }

        $db = (new self())->getDB();

        if ($jobId !== null) {
            try {
                $stmt = $db->prepare(
                    "INSERT INTO notifications (user_id, message, job_id, type, is_read) VALUES (?, ?, ?, ?, 0)"
                );
                return $stmt->execute([$userId, $message, $jobId, $type]);
            } catch (\PDOException) {
                try {
                    $stmt = $db->prepare("INSERT INTO notifications (user_id, message, job_id, is_read) VALUES (?, ?, ?, 0)");
                    return $stmt->execute([$userId, $message, $jobId]);
                } catch (\PDOException) {
                    // Fallback if job_id column not migrated yet
                }
            }
        }

        try {
            $stmt = $db->prepare("INSERT INTO notifications (user_id, message, type, is_read) VALUES (?, ?, ?, 0)");
            return $stmt->execute([$userId, $message, $type]);
        } catch (\PDOException) {
            $stmt = $db->prepare("INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)");
            return $stmt->execute([$userId, $message]);
        }
    }

    public static function resolveJobId(array $notification): ?int {
        if (!empty($notification['job_id'])) {
            return (int)$notification['job_id'];
        }

        $message = $notification['message'] ?? '';

        if (preg_match('/(WO-\d{4}-\d+)/i', $message, $matches)) {
            $job = Job::findByReference($matches[1]);
            if ($job !== null) {
                return (int)$job->id;
            }
        }

        return JobReference::legacyIdFromText($message);
    }

    public static function linksToCommentsSection(string $message): bool {
        return (bool)preg_match('/comment(ed)? on|your comment on|liked your comment|disliked your comment/i', $message);
    }

    /**
     * @param array<int, array<string, mixed>> $notifications
     * @return array<int, array<string, mixed>>
     */
    public static function withResolvedJobIds(array $notifications): array {
        foreach ($notifications as &$notification) {
            $jobId = self::resolveJobId($notification);
            if ($jobId !== null) {
                $notification['job_id'] = $jobId;
            }
        }
        unset($notification);

        $jobIds = [];
        foreach ($notifications as $notification) {
            if (!empty($notification['job_id'])) {
                $jobIds[] = (int)$notification['job_id'];
            }
        }

        $refByJob = Job::referenceCodesByIds($jobIds);

        foreach ($notifications as &$notification) {
            if (!empty($notification['job_id'])) {
                $jobId = (int)$notification['job_id'];
                $notification['job_ref'] = $refByJob[$jobId] ?? Job::refFor($jobId);
            }
            if (empty($notification['type'])) {
                $notification['type'] = UserNotificationSettings::inferType((string)($notification['message'] ?? ''));
            }
            $notification['display_message'] = self::displayMessage($notification);
        }
        unset($notification);

        return $notifications;
    }

    public static function displayMessage(array $notification): string {
        $message = (string)($notification['message'] ?? '');
        $jobId = self::resolveJobId($notification);

        if ($jobId === null) {
            return $message;
        }

        $ref = $notification['job_ref'] ?? Job::refFor($jobId);
        return JobReference::normalizeLegacyMessage($message, $ref, $jobId);
    }

    public static function markNotificationsRead(int $userId, ?int $notifId = null): bool {
        $db = (new self())->getDB();
        if ($notifId) {
            $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND id = ?");
            return $stmt->execute([$userId, $notifId]);
        }

        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public static function markNotificationsReadForJob(int $userId, int $jobId): bool {
        $db = (new self())->getDB();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND job_id = ? AND is_read = 0");
        return $stmt->execute([$userId, $jobId]);
    }

    public static function countUnreadNotifications(int $userId): int {
        $db = (new self())->getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}
