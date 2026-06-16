<?php
/**
 * Payment Model - PHP 8 Custom MVC
 */

namespace App\Models;

use App\Core\Model;

class Payment extends Model {
    public function __construct(
        public ?int $id = null,
        public ?int $job_id = null,
        public ?string $type = null, // 'full', 'partial', or 'pending'
        public ?string $category = 'client', // 'client' or 'vendor'
        public ?float $amount = null,
        public ?string $note = null,
        public ?string $created_at = null
    ) {}

    /**
     * Store new payment linked to job ID
     */
    public function save(): bool {
        $db = $this->getDB();
        $stmt = $db->prepare("INSERT INTO payments (job_id, type, category, amount, note) VALUES (?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            $this->job_id,
            $this->type,
            $this->category ?: 'client',
            $this->amount,
            $this->note
        ]);

        if ($success) {
            $this->id = (int)$db->lastInsertId();
        }
        return $success;
    }

    public static function find(int $id): ?self {
        $model = new self();
        $db = $model->getDB();
        $stmt = $db->prepare("SELECT * FROM payments WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        return new self(
            id: (int)$row['id'],
            job_id: (int)$row['job_id'],
            type: $row['type'],
            category: $row['category'] ?? 'client',
            amount: (float)$row['amount'],
            note: $row['note'],
            created_at: $row['created_at']
        );
    }

    public function update(): bool {
        $db = $this->getDB();
        $stmt = $db->prepare("UPDATE payments SET type = ?, category = ?, amount = ?, note = ? WHERE id = ?");
        return $stmt->execute([
            $this->type,
            $this->category ?: 'client',
            $this->amount,
            $this->note,
            $this->id
        ]);
    }
}
