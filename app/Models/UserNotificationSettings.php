<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class UserNotificationSettings extends Model {
    public const TYPES = ['job_assign', 'status_update', 'comment', 'vote', 'other'];

    public function __construct(
        public int $user_id = 0,
        public bool $browser_enabled = false,
        public bool $notify_job_assign = true,
        public bool $notify_status_update = true,
        public bool $notify_comments = true,
        public bool $notify_votes = true,
    ) {}

    public static function defaults(int $userId): self {
        return new self($userId);
    }

    public static function forUser(int $userId): self {
        $db = (new self())->getDB();

        try {
            $stmt = $db->prepare("SELECT * FROM user_notification_settings WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException) {
            return self::defaults($userId);
        }

        if (!$row) {
            return self::defaults($userId);
        }

        return self::fromRow($row);
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self {
        return new self(
            (int)$row['user_id'],
            (bool)$row['browser_enabled'],
            (bool)$row['notify_job_assign'],
            (bool)$row['notify_status_update'],
            (bool)$row['notify_comments'],
            (bool)$row['notify_votes'],
        );
    }

    /** @return array<string, bool> */
    public function toArray(): array {
        return [
            'browser_enabled' => $this->browser_enabled,
            'notify_job_assign' => $this->notify_job_assign,
            'notify_status_update' => $this->notify_status_update,
            'notify_comments' => $this->notify_comments,
            'notify_votes' => $this->notify_votes,
        ];
    }

    public function allowsBrowserType(string $type): bool {
        if (!$this->browser_enabled) {
            return false;
        }

        return match ($type) {
            'job_assign' => $this->notify_job_assign,
            'status_update' => $this->notify_status_update,
            'comment' => $this->notify_comments,
            'vote' => $this->notify_votes,
            default => true,
        };
    }

    public function save(): bool {
        $db = $this->getDB();

        try {
            $stmt = $db->prepare("
                INSERT INTO user_notification_settings
                    (user_id, browser_enabled, notify_job_assign, notify_status_update, notify_comments, notify_votes)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    browser_enabled = VALUES(browser_enabled),
                    notify_job_assign = VALUES(notify_job_assign),
                    notify_status_update = VALUES(notify_status_update),
                    notify_comments = VALUES(notify_comments),
                    notify_votes = VALUES(notify_votes)
            ");

            return $stmt->execute([
                $this->user_id,
                $this->browser_enabled ? 1 : 0,
                $this->notify_job_assign ? 1 : 0,
                $this->notify_status_update ? 1 : 0,
                $this->notify_comments ? 1 : 0,
                $this->notify_votes ? 1 : 0,
            ]);
        } catch (\PDOException) {
            return false;
        }
    }

    public static function inferType(string $message): string {
        $message = strtolower($message);

        if (str_contains($message, 'assigned to') || str_contains($message, 'you have been assigned')) {
            return 'job_assign';
        }
        if (str_contains($message, 'commented on')) {
            return 'comment';
        }
        if (str_contains($message, 'liked your comment') || str_contains($message, 'disliked your comment')) {
            return 'vote';
        }
        if (str_contains($message, 'status updated') || str_contains($message, 'marked complete')) {
            return 'status_update';
        }

        return 'other';
    }
}
