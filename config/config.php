<?php
// config/config.php

// Align PHP timezone with local database and system timezone
date_default_timezone_set('Asia/Karachi');

// Ensure critical environment configuration is loaded
if (!isset($_ENV['DB_NAME']) || empty($_ENV['DB_NAME'])) {
    die("Environment initialization error: The application configuration (.env) is missing or incomplete.");
}

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'job_tracker');

// Application Configuration
define('APP_NAME', $_ENV['APP_NAME'] ?? 'StoreOps');
define('APP_INITIALS', $_ENV['APP_INITIALS'] ?? 'SO');
define('COMPANY_NAME', $_ENV['COMPANY_NAME'] ?? 'Richmond Tech Group');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');

// Display timezones (header clocks)
define('TIMEZONE_US', $_ENV['TIMEZONE_US'] ?? 'America/New_York');
define('TIMEZONE_LOCAL', $_ENV['TIMEZONE_LOCAL'] ?? 'Asia/Karachi');
define('TIMEZONE_US_LABEL', $_ENV['TIMEZONE_US_LABEL'] ?? 'US');
define('TIMEZONE_LOCAL_LABEL', $_ENV['TIMEZONE_LOCAL_LABEL'] ?? 'PK');

// Base URLs
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
// Remove /public if we are already in the public folder to avoid duplication
$basePath = str_replace('/public', '', $scriptName);
$dynamicBaseUrl = $protocol . '://' . $host . $basePath;

define('BASE_URL', rtrim($_ENV['BASE_URL'] ?? $dynamicBaseUrl, '/'));

$entryScript = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
$docrootIsPublic = str_ends_with($entryScript, '/public/index.php');
define('ASSET_URL', BASE_URL . ($docrootIsPublic ? '' : '/public'));
define('APP_BRAND_ICON_URL', ASSET_URL . '/images/icon.png');

// Upload configurations
define('ALLOWED_UPLOAD_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Directories
define('ROOT_DIR', dirname(__DIR__));
define('APP_DIR', ROOT_DIR . '/app');
define('VIEW_DIR', ROOT_DIR . '/views');
