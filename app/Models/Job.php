<?php
/**
 * Job Model - PHP 8 Custom MVC
 */

namespace App\Models;

use App\Core\Model;
use App\Core\JobReference;
use PDO;

class Job extends Model {
    public const PER_PAGE = 20;

    public function __construct(
        public ?int $id = null,
        public ?string $store_name = null,
        public ?string $location = null,
        public ?string $address = null,
        public ?string $issue = null,
        public ?string $designation = null,
        public ?string $status = 'New',
        public ?string $urgency = 'Within SLA',
        public ?string $w9 = 'No',
        public ?int $assigned_to = null,
        public ?int $created_by = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $reference_code = null,

        // Dynamic additions (joined fields)
        public ?string $assigned_name = null,
        public ?string $creator_name = null,

        // W9 form attachment
        public ?string $w9_form_path = null,

        // Total contract/job amount
        public ?float $total_amount = 0.00,

        // Total contract amount paid/owed to vendor
        public ?float $vendor_amount = 0.00,

        // Job SLA deadline date
        public ?string $sla_date = null
    ) {}

    public function ref(): string {
        if (!empty($this->reference_code)) {
            return strtoupper($this->reference_code);
        }
        if ($this->id) {
            return JobReference::generate((int)$this->id, $this->created_at);
        }
        return '';
    }

    public function path(): string {
        return JobReference::pathFromReference($this->ref());
    }

    public static function refFor(int $jobId): string {
        return self::referenceCodeForId($jobId)
            ?? JobReference::generate($jobId);
    }

    public static function pathFor(int $jobId): string {
        return JobReference::pathFromReference(self::refFor($jobId));
    }

    public static function referenceCodeForId(int $jobId): ?string {
        $codes = self::referenceCodesByIds([$jobId]);
        return $codes[$jobId] ?? null;
    }

    /**
     * @param int[] $jobIds
     * @return array<int, string> id => reference_code
     */
    public static function referenceCodesByIds(array $jobIds): array {
        if (empty($jobIds)) {
            return [];
        }

        $jobIds = array_values(array_unique(array_map('intval', $jobIds)));
        $placeholders = implode(',', array_fill(0, count($jobIds), '?'));
        $db = (new self())->getDB();

        try {
            $stmt = $db->prepare("SELECT id, reference_code FROM jobs WHERE id IN ($placeholders)");
            $stmt->execute($jobIds);
        } catch (\PDOException) {
            return [];
        }

        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['reference_code'])) {
                $map[(int)$row['id']] = strtoupper($row['reference_code']);
            }
        }
        return $map;
    }

    public static function findByReference(string $code): ?self {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }

        $db = (new self())->getDB();
        try {
            $sql = "SELECT j.*, u1.full_name as assigned_name, u2.full_name as creator_name
                    FROM jobs j
                    LEFT JOIN users u1 ON j.assigned_to = u1.id
                    LEFT JOIN users u2 ON j.created_by = u2.id
                    WHERE j.reference_code = ?
                    LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$code]);
            $row = $stmt->fetch();
            return $row ? self::mapRow($row) : null;
        } catch (\PDOException) {
            return null;
        }
    }

    public static function findByRoute(string $param): ?self {
        $param = trim($param);
        if ($param === '') {
            return null;
        }

        if (JobReference::isValidFormat($param)) {
            $byRef = self::findByReference($param);
            if ($byRef !== null) {
                return $byRef;
            }
        }

        $legacyId = JobReference::legacyIdFromRoute($param);
        return $legacyId !== null ? self::find($legacyId) : null;
    }

    public function ensureReferenceCode(): void {
        if (!$this->id || !empty($this->reference_code)) {
            return;
        }

        $db = $this->getDB();
        try {
            $stmt = $db->prepare("
                UPDATE jobs
                SET reference_code = CONCAT('WO-', YEAR(created_at), '-', LPAD(id, 5, '0'))
                WHERE id = ? AND (reference_code IS NULL OR reference_code = '')
            ");
            $stmt->execute([$this->id]);

            $fetch = $db->prepare("SELECT reference_code FROM jobs WHERE id = ? LIMIT 1");
            $fetch->execute([$this->id]);
            $code = $fetch->fetchColumn();
            if ($code) {
                $this->reference_code = strtoupper((string)$code);
            }
        } catch (\PDOException) {
            $this->reference_code = JobReference::generate((int)$this->id, $this->created_at);
        }
    }

    /**
     * @param int[] $jobIds
     * @return array<int, string> id => created_at
     * @deprecated Use referenceCodesByIds() for display paths
     */
    public static function createdAtByIds(array $jobIds): array {
        if (empty($jobIds)) {
            return [];
        }

        $jobIds = array_values(array_unique(array_map('intval', $jobIds)));
        $placeholders = implode(',', array_fill(0, count($jobIds), '?'));
        $db = (new self())->getDB();
        $stmt = $db->prepare("SELECT id, created_at FROM jobs WHERE id IN ($placeholders)");
        $stmt->execute($jobIds);

        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $map[(int)$row['id']] = $row['created_at'];
        }
        return $map;
    }

    private static function mapRow(array $row): self {
        return new self(
            id: (int)$row['id'],
            store_name: $row['store_name'],
            location: $row['location'],
            address: $row['address'],
            issue: $row['issue'],
            designation: $row['designation'],
            status: $row['status'],
            urgency: $row['urgency'],
            w9: $row['w9'],
            assigned_to: $row['assigned_to'] ? (int)$row['assigned_to'] : null,
            created_by: $row['created_by'] ? (int)$row['created_by'] : null,
            created_at: $row['created_at'],
            updated_at: $row['updated_at'],
            reference_code: !empty($row['reference_code']) ? strtoupper($row['reference_code']) : null,
            assigned_name: $row['assigned_name'] ?? null,
            creator_name: $row['creator_name'] ?? null,
            w9_form_path: $row['w9_form_path'] ?? null,
            total_amount: isset($row['total_amount']) ? (float)$row['total_amount'] : 0.00,
            vendor_amount: isset($row['vendor_amount']) ? (float)$row['vendor_amount'] : 0.00,
            sla_date: $row['sla_date'] ?? null
        );
    }

    private static function buildFilterSql(array $filters, array &$params): string {
        $sql = '';

        if (!empty($filters['status']) && in_array($filters['status'], \App\Core\Validator::JOB_STATUSES, true)) {
            $sql .= " AND j.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['urgency']) && in_array($filters['urgency'], \App\Core\Validator::URGENCIES, true)) {
            $sql .= " AND j.urgency = ?";
            $params[] = $filters['urgency'];
        }
        if (!empty($filters['assigned_to'])) {
            $sql .= " AND j.assigned_to = ?";
            $params[] = (int)$filters['assigned_to'];
        }
        if (!empty($filters['for_user_id'])) {
            $sql .= " AND j.assigned_to = ?";
            $params[] = (int)$filters['for_user_id'];
        }
        if (!empty($filters['exclude_done'])) {
            $sql .= " AND j.status NOT IN ('Done', 'Cancelled')";
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (j.store_name LIKE ? OR j.issue LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        return $sql;
    }

    /**
     * Retrieve jobs with filters, optional pagination.
     *
     * @return array{jobs: self[], total: int, page: int, per_page: int, total_pages: int}
     */
    public static function search(array $filters = [], int $page = 1, ?int $perPage = null): array {
        $model = new self();
        $db = $model->getDB();
        $perPage = $perPage ?? self::PER_PAGE;
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $params = [];
        $where = self::buildFilterSql($filters, $params);

        $countStmt = $db->prepare("SELECT COUNT(*) FROM jobs j WHERE 1=1" . $where);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $sql = "SELECT j.*, u1.full_name as assigned_name, u2.full_name as creator_name 
                FROM jobs j 
                LEFT JOIN users u1 ON j.assigned_to = u1.id
                LEFT JOIN users u2 ON j.created_by = u2.id
                WHERE 1=1" . $where . " ORDER BY j.created_at DESC LIMIT ? OFFSET ?";

        $listParams = array_merge($params, [$perPage, $offset]);
        $stmt = $db->prepare($sql);
        $stmt->execute($listParams);

        $jobs = [];
        while ($row = $stmt->fetch()) {
            $jobs[] = self::mapRow($row);
        }

        return [
            'jobs' => $jobs,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * @deprecated Use search() — kept for internal compatibility
     */
    public static function all(array $filters = []): array {
        return self::search($filters, 1, PHP_INT_MAX)['jobs'];
    }

    public static function recent(int $limit = 5, array $filters = []): array {
        $model = new self();
        $db = $model->getDB();
        $params = [];
        $where = self::buildFilterSql($filters, $params);

        $sql = "SELECT j.*, u1.full_name as assigned_name, u2.full_name as creator_name 
                FROM jobs j 
                LEFT JOIN users u1 ON j.assigned_to = u1.id
                LEFT JOIN users u2 ON j.created_by = u2.id
                WHERE 1=1" . $where . " ORDER BY j.created_at DESC LIMIT ?";

        $params[] = $limit;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = self::mapRow($row);
        }
        return $results;
    }

    public static function countStats(array $filters = []): array {
        $model = new self();
        $db = $model->getDB();
        $params = [];
        $where = self::buildFilterSql($filters, $params);

        $sql = "SELECT status, COUNT(*) as total FROM jobs j WHERE 1=1" . $where . " GROUP BY status";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $stats = [
            'total' => 0,
            'new' => 0,
            'assigned' => 0,
            'scheduled' => 0,
            'wip' => 0,
            'pending' => 0,
            'cancelled' => 0,
            'done' => 0,
        ];

        while ($row = $stmt->fetch()) {
            $count = (int)$row['total'];
            $stats['total'] += $count;
            switch ($row['status']) {
                case 'New': $stats['new'] = $count; break;
                case 'Assigned': $stats['assigned'] = $count; break;
                case 'Scheduled': $stats['scheduled'] = $count; break;
                case 'Work In Progress': $stats['wip'] = $count; break;
                case 'Pending': $stats['pending'] = $count; break;
                case 'Cancelled': $stats['cancelled'] = $count; break;
                case 'Done': $stats['done'] = $count; break;
            }
        }

        return $stats;
    }

    public static function assignedToUser(int $userId, int $limit = 50): array {
        return self::recent($limit, ['for_user_id' => $userId]);
    }

    public static function activeAssignedToUser(int $userId, int $limit = 10): array {
        return self::recent($limit, ['for_user_id' => $userId, 'exclude_done' => true]);
    }

    public static function countAssignedToUser(int $userId, bool $activeOnly = false): int {
        $db = (new self())->getDB();
        $sql = "SELECT COUNT(*) FROM jobs WHERE assigned_to = ?";
        if ($activeOnly) {
            $sql .= " AND status != 'Done'";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function find(int $id): ?self {
        $model = new self();
        $db = $model->getDB();

        $sql = "SELECT j.*, u1.full_name as assigned_name, u2.full_name as creator_name 
                FROM jobs j 
                LEFT JOIN users u1 ON j.assigned_to = u1.id
                LEFT JOIN users u2 ON j.created_by = u2.id
                WHERE j.id = ?
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return self::mapRow($row);
    }

    public function save(): bool {
        $db = $this->getDB();

        if ($this->id) {
            $sql = "UPDATE jobs SET 
                        store_name = ?, location = ?, address = ?, issue = ?, 
                        designation = ?, status = ?, urgency = ?, w9 = ?, 
                        assigned_to = ?, total_amount = ?, vendor_amount = ?, created_at = ?, sla_date = ? WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                $this->store_name,
                $this->location,
                $this->address,
                $this->issue,
                $this->designation,
                $this->status,
                $this->urgency,
                $this->w9,
                $this->assigned_to,
                $this->total_amount,
                $this->vendor_amount,
                $this->created_at,
                $this->sla_date,
                $this->id
            ]);
        }

        $this->created_at = $this->created_at ?: date('Y-m-d H:i:s');

        $sql = "INSERT INTO jobs (store_name, location, address, issue, designation, status, urgency, w9, assigned_to, created_by, total_amount, vendor_amount, created_at, sla_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $success = $stmt->execute([
            $this->store_name,
            $this->location,
            $this->address,
            $this->issue,
            $this->designation,
            $this->status ?: 'New',
            $this->urgency ?: 'Within SLA',
            $this->w9 ?: 'No',
            $this->assigned_to,
            $this->created_by,
            $this->total_amount ?: 0.00,
            $this->vendor_amount ?: 0.00,
            $this->created_at,
            $this->sla_date
        ]);

        if ($success) {
            $this->id = (int)$db->lastInsertId();
            $this->ensureReferenceCode();
        }
        return $success;
    }

    public function getPictures(): array {
        $db = $this->getDB();
        $stmt = $db->prepare("SELECT jp.*, u.full_name as uploader_name FROM job_pictures jp JOIN users u ON jp.uploaded_by = u.id WHERE jp.job_id = ? ORDER BY jp.created_at DESC");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    public function addPicture(string $filePath, int $uploadedBy): bool {
        $db = $this->getDB();
        $stmt = $db->prepare("INSERT INTO job_pictures (job_id, file_path, uploaded_by) VALUES (?, ?, ?)");
        return $stmt->execute([$this->id, $filePath, $uploadedBy]);
    }

    public function getPayments(): array {
        $db = $this->getDB();
        $stmt = $db->prepare("SELECT * FROM payments WHERE job_id = ? ORDER BY created_at DESC");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Save or replace the W9 form attachment path for this job.
     */
    public function saveW9Path(string $path): bool {
        if (!$this->id) return false;
        $db = $this->getDB();
        $stmt = $db->prepare("UPDATE jobs SET w9_form_path = ? WHERE id = ?");
        $success = $stmt->execute([$path, $this->id]);
        if ($success) {
            $this->w9_form_path = $path;
        }
        return $success;
    }

    /**
     * Delete the stored W9 form path for this job.
     */
    public function clearW9Path(): bool {
        if (!$this->id) return false;
        $db = $this->getDB();
        $stmt = $db->prepare("UPDATE jobs SET w9_form_path = NULL WHERE id = ?");
        $success = $stmt->execute([$this->id]);
        if ($success) {
            $this->w9_form_path = null;
        }
        return $success;
    }

    /**
     * Save the total job amount for this work order.
     */
    public function saveTotalAmount(float $amount): bool {
        if (!$this->id) return false;
        $db = $this->getDB();
        $stmt = $db->prepare("UPDATE jobs SET total_amount = ? WHERE id = ?");
        $success = $stmt->execute([$amount, $this->id]);
        if ($success) {
            $this->total_amount = $amount;
        }
        return $success;
    }

    /**
     * Save the total vendor amount for this work order.
     */
    public function saveVendorAmount(float $amount): bool {
        if (!$this->id) return false;
        $db = $this->getDB();
        $stmt = $db->prepare("UPDATE jobs SET vendor_amount = ? WHERE id = ?");
        $success = $stmt->execute([$amount, $this->id]);
        if ($success) {
            $this->vendor_amount = $amount;
        }
        return $success;
    }

    /**
     * Permanently delete this job and its associated uploads from disk.
     */
    public function delete(): bool {
        if (!$this->id) return false;
        $db = $this->getDB();

        // 1. Get and delete all associated job pictures from disk
        $pictures = $this->getPictures();
        foreach ($pictures as $pic) {
            $filePath = ROOT_DIR . '/' . ltrim($pic['file_path'], '/');
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }
        }

        // 2. Delete W9 form if exists
        if ($this->w9_form_path) {
            $w9Path = ROOT_DIR . '/' . ltrim($this->w9_form_path, '/');
            if (file_exists($w9Path) && is_file($w9Path)) {
                @unlink($w9Path);
            }
        }

        // 3. Delete from database (associated DB records cascade automatically via foreign keys)
        $stmt = $db->prepare("DELETE FROM jobs WHERE id = ?");
        return $stmt->execute([$this->id]);
    }
}
