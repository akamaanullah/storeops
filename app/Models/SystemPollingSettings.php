<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SystemPollingSettings extends Model {
    public const MIN_SECONDS = 5;
    public const MAX_SECONDS = 300;
    public const MAX_HIDDEN_SECONDS = 600;

    public function __construct(
        public int $interval_global = 25,
        public int $interval_dashboard = 30,
        public int $interval_jobs = 25,
        public int $interval_job = 15,
        public int $interval_hidden = 60,
        public ?int $updated_by = null,
    ) {}

    public static function defaults(): self {
        return new self();
    }

    public static function get(): self {
        $db = (new self())->getDB();

        try {
            $stmt = $db->query("SELECT * FROM system_polling_settings WHERE id = 1 LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException) {
            return self::defaults();
        }

        if (!$row) {
            return self::defaults();
        }

        return self::fromRow($row);
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self {
        return new self(
            (int)$row['interval_global'],
            (int)$row['interval_dashboard'],
            (int)$row['interval_jobs'],
            (int)$row['interval_job'],
            (int)$row['interval_hidden'],
            isset($row['updated_by']) ? (int)$row['updated_by'] : null,
        );
    }

    public static function clamp(int $value, int $min, int $max): int {
        return max($min, min($max, $value));
    }

    /** @param array<string, mixed> $input */
    public static function fromInput(array $input): self {
        return new self(
            self::clamp((int)($input['interval_global'] ?? 25), self::MIN_SECONDS, self::MAX_SECONDS),
            self::clamp((int)($input['interval_dashboard'] ?? 30), self::MIN_SECONDS, self::MAX_SECONDS),
            self::clamp((int)($input['interval_jobs'] ?? 25), self::MIN_SECONDS, self::MAX_SECONDS),
            self::clamp((int)($input['interval_job'] ?? 15), self::MIN_SECONDS, self::MAX_SECONDS),
            self::clamp((int)($input['interval_hidden'] ?? 60), self::MIN_SECONDS, self::MAX_HIDDEN_SECONDS),
        );
    }

    /** @return array<string, int> seconds */
    public function toArray(): array {
        return [
            'interval_global' => $this->interval_global,
            'interval_dashboard' => $this->interval_dashboard,
            'interval_jobs' => $this->interval_jobs,
            'interval_job' => $this->interval_job,
            'interval_hidden' => $this->interval_hidden,
        ];
    }

    /** @return array<string, int> milliseconds for JavaScript */
    public function intervalsMs(): array {
        return [
            'global' => $this->interval_global * 1000,
            'dashboard' => $this->interval_dashboard * 1000,
            'jobs' => $this->interval_jobs * 1000,
            'job' => $this->interval_job * 1000,
            'hidden' => $this->interval_hidden * 1000,
        ];
    }

    public function intervalMsForContext(string $context): int {
        $key = match ($context) {
            'dashboard' => $this->interval_dashboard,
            'jobs' => $this->interval_jobs,
            'job' => $this->interval_job,
            default => $this->interval_global,
        };

        return $key * 1000;
    }

    public function save(?int $adminUserId = null): bool {
        $db = $this->getDB();

        try {
            $stmt = $db->prepare("
                INSERT INTO system_polling_settings
                    (id, interval_global, interval_dashboard, interval_jobs, interval_job, interval_hidden, updated_by)
                VALUES (1, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    interval_global = VALUES(interval_global),
                    interval_dashboard = VALUES(interval_dashboard),
                    interval_jobs = VALUES(interval_jobs),
                    interval_job = VALUES(interval_job),
                    interval_hidden = VALUES(interval_hidden),
                    updated_by = VALUES(updated_by)
            ");

            return $stmt->execute([
                $this->interval_global,
                $this->interval_dashboard,
                $this->interval_jobs,
                $this->interval_job,
                $this->interval_hidden,
                $adminUserId,
            ]);
        } catch (\PDOException) {
            return false;
        }
    }
}
