<?php
/**
 * Database Schema Migration / Update Script
 * Run this script by visiting http://yourdomain.com/update_db.php
 * REMOVE or RENAME this file immediately after running it.
 */

// 1. Enable error display for this diagnostic script
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 2. Define Root Path constant
define('ROOT_PATH', dirname(__DIR__));

// 3. PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 4. Load Environment Variables
use App\Core\DotEnv;
$envPath = ROOT_PATH . '/.env';
if (file_exists($envPath)) {
    (new DotEnv($envPath))->load();
} else {
    die("Error: .env file not found at " . $envPath);
}

// 5. Load Unified Configuration
require_once ROOT_PATH . '/config/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $db = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "<h1>StoreOps Database Migration Tool</h1>";
    echo "<p style='color: green;'>Connected to database <strong>" . htmlspecialchars(DB_NAME) . "</strong> successfully.</p>";
    
    // Check missing columns in jobs table
    echo "<h2>Checking 'jobs' table columns...</h2>";
    
    // 1. Check/Add reference_code
    $stmt = $db->query("SHOW COLUMNS FROM `jobs` LIKE 'reference_code'");
    $hasRefCode = $stmt->fetch();
    if (!$hasRefCode) {
        echo "Adding 'reference_code' column...<br>";
        $db->exec("ALTER TABLE `jobs` ADD COLUMN `reference_code` VARCHAR(24) NULL DEFAULT NULL AFTER `id`");
        $db->exec("ALTER TABLE `jobs` ADD UNIQUE KEY `idx_jobs_reference_code` (`reference_code`)");
        echo "<strong style='color: green;'>Added 'reference_code' successfully.</strong><br>";
    } else {
        echo "'reference_code' already exists.<br>";
    }

    // 2. Check/Add w9_form_path
    $stmt = $db->query("SHOW COLUMNS FROM `jobs` LIKE 'w9_form_path'");
    $hasW9Path = $stmt->fetch();
    if (!$hasW9Path) {
        echo "Adding 'w9_form_path' column...<br>";
        $db->exec("ALTER TABLE `jobs` ADD COLUMN `w9_form_path` VARCHAR(500) NULL DEFAULT NULL COMMENT 'Path to uploaded W9 form PDF/document'");
        echo "<strong style='color: green;'>Added 'w9_form_path' successfully.</strong><br>";
    } else {
        echo "'w9_form_path' already exists.<br>";
    }

    // 3. Check/Add total_amount
    $stmt = $db->query("SHOW COLUMNS FROM `jobs` LIKE 'total_amount'");
    $hasTotalAmount = $stmt->fetch();
    if (!$hasTotalAmount) {
        echo "Adding 'total_amount' column...<br>";
        $db->exec("ALTER TABLE `jobs` ADD COLUMN `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total contract/job amount'");
        echo "<strong style='color: green;'>Added 'total_amount' successfully.</strong><br>";
    } else {
        echo "'total_amount' already exists.<br>";
    }

    // 4. Check/Modify status ENUM to include 'Pending' and 'Cancelled'
    $stmt = $db->query("SHOW COLUMNS FROM `jobs` LIKE 'status'");
    $statusCol = $stmt->fetch();
    if ($statusCol) {
        if (strpos($statusCol['Type'], 'Pending') === false || strpos($statusCol['Type'], 'Cancelled') === false) {
            echo "Modifying 'jobs.status' to include 'Pending' and 'Cancelled'...<br>";
            $db->exec("ALTER TABLE `jobs` MODIFY COLUMN `status` ENUM('New', 'Assigned', 'Scheduled', 'Work In Progress', 'Pending', 'Cancelled', 'Done') NOT NULL DEFAULT 'New'");
            echo "<strong style='color: green;'>Updated 'jobs.status' enum successfully.</strong><br>";
        } else {
            echo "'jobs.status' already supports 'Pending' and 'Cancelled'.<br>";
        }
    }

    // 5. Check/Add sla_date
    $stmt = $db->query("SHOW COLUMNS FROM `jobs` LIKE 'sla_date'");
    $hasSlaDate = $stmt->fetch();
    if (!$hasSlaDate) {
        echo "Adding 'sla_date' column...<br>";
        $db->exec("ALTER TABLE `jobs` ADD COLUMN `sla_date` DATETIME NULL DEFAULT NULL COMMENT 'Job SLA deadline date'");
        echo "<strong style='color: green;'>Added 'sla_date' successfully.</strong><br>";
    } else {
        echo "'sla_date' already exists.<br>";
    }

    // Check payments table changes
    echo "<h2>Checking 'payments' table...</h2>";
    $stmt = $db->query("SHOW COLUMNS FROM `payments` LIKE 'type'");
    $typeCol = $stmt->fetch();
    if ($typeCol) {
        // Check if enum contains pending
        if (strpos($typeCol['Type'], 'pending') === false) {
            echo "Modifying 'payments.type' to include 'pending'...<br>";
            $db->exec("ALTER TABLE `payments` MODIFY COLUMN `type` ENUM('full', 'partial', 'pending') NOT NULL");
            echo "<strong style='color: green;'>Updated 'payments.type' enum successfully.</strong><br>";
        } else {
            echo "'payments.type' already supports 'pending'.<br>";
        }
    }

    // Backfill reference codes if needed
    echo "<h2>Backfilling Reference Codes...</h2>";
    $backfillCount = $db->exec("
        UPDATE `jobs`
        SET `reference_code` = CONCAT('WO-', YEAR(created_at), '-', LPAD(id, 5, '0'))
        WHERE `reference_code` IS NULL OR `reference_code` = ''
    ");
    echo "Generated reference codes for <strong>" . $backfillCount . "</strong> jobs.<br>";

    echo "<h2 style='color: green;'>All migrations checked and executed successfully!</h2>";
    echo "<p style='color: red; font-weight: bold;'>WARNING: Please delete this file ('public/update_db.php') immediately from your server to secure your database.</p>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>Migration Error</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
