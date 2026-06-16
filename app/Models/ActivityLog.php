<?php
/**
 * ActivityLog Model - PHP 8 Custom MVC
 */

namespace App\Models;

use App\Core\Model;

class ActivityLog extends Model {
    public const PER_PAGE = 20;
    public function __construct(
        public ?int $id = null,
        public ?int $user_id = null,
        public ?int $job_id = null,
        public ?string $action = null,
        public ?string $detail = null,
        public ?string $created_at = null,
        
        // Joined details
        public ?string $user_name = null,
        public ?string $job_name = null
    ) {}

    /**
     * Create an activity footprint in the logs
     */
    public static function log(int $userId, ?int $jobId, string $action, string $detail): bool {
        $model = new self();
        $db = $model->getDB();
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, job_id, action, detail) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $jobId, $action, $detail]);
    }

    /**
     * List all activities for admin view
     */
    public static function listAll(): array {
        return self::listPaginated(1, PHP_INT_MAX)['items'];
    }

    /**
     * @return array{items: self[], total: int, page: int, per_page: int, total_pages: int}
     */
    public static function listPaginated(int $page = 1, ?int $perPage = null): array {
        $model = new self();
        $db = $model->getDB();
        $perPage = $perPage ?? self::PER_PAGE;
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $total = (int)$db->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $sql = "SELECT a.*, u.full_name as user_name, j.store_name as job_name 
                FROM activity_logs a
                JOIN users u ON a.user_id = u.id
                LEFT JOIN jobs j ON a.job_id = j.id
                ORDER BY a.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$perPage, $offset]);

        $items = [];
        while ($row = $stmt->fetch()) {
            $items[] = new self(
                (int)$row['id'],
                (int)$row['user_id'],
                $row['job_id'] ? (int)$row['job_id'] : null,
                $row['action'],
                $row['detail'],
                $row['created_at'],
                $row['user_name'],
                $row['job_name']
            );
        }

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }
}
