-- 
-- StoreOps - Complete Database Schema
-- Import into your MySQL database via phpMyAdmin or CLI.
-- Do not run CREATE DATABASE on shared hosting — select your database first.
-- 

-- 1. users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'team_lead', 'user') NOT NULL DEFAULT 'user',
  `status` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. jobs table
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reference_code` VARCHAR(24) NULL,
  `store_name` VARCHAR(150) NOT NULL,
  `location` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `issue` TEXT NOT NULL,
  `designation` VARCHAR(100) NOT NULL,
  `status` ENUM('New', 'Assigned', 'Scheduled', 'Work In Progress', 'Pending', 'Cancelled', 'Done') NOT NULL DEFAULT 'New',
  `urgency` ENUM('Within SLA', 'Urgent') NOT NULL DEFAULT 'Within SLA',
  `w9` ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
  `assigned_to` INT NULL,
  `created_by` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sla_date` DATETIME NULL DEFAULT NULL,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `idx_jobs_reference_code` (`reference_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. job_pictures table
CREATE TABLE IF NOT EXISTS `job_pictures` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_id` INT NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `uploaded_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. comments table
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `comment` TEXT NOT NULL,
  `picture_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4b. comment_pictures table
CREATE TABLE IF NOT EXISTS `comment_pictures` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `comment_id` INT NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`comment_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. comment_votes table
CREATE TABLE IF NOT EXISTS `comment_votes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `comment_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `vote` ENUM('like', 'dislike') NOT NULL,
  UNIQUE KEY `unique_comment_user_vote` (`comment_id`, `user_id`),
  FOREIGN KEY (`comment_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. payments table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_id` INT NOT NULL,
  `type` ENUM('full', 'partial', 'pending') NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `note` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `job_id` INT NULL,
  `type` ENUM('job_assign', 'status_update', 'comment', 'vote', 'other') NOT NULL DEFAULT 'other',
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7b. user_notification_settings
CREATE TABLE IF NOT EXISTS `user_notification_settings` (
  `user_id` INT NOT NULL PRIMARY KEY,
  `browser_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `notify_job_assign` TINYINT(1) NOT NULL DEFAULT 1,
  `notify_status_update` TINYINT(1) NOT NULL DEFAULT 1,
  `notify_comments` TINYINT(1) NOT NULL DEFAULT 1,
  `notify_votes` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7c. system_polling_settings
CREATE TABLE IF NOT EXISTS `system_polling_settings` (
  `id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `interval_global` INT UNSIGNED NOT NULL DEFAULT 25,
  `interval_dashboard` INT UNSIGNED NOT NULL DEFAULT 30,
  `interval_jobs` INT UNSIGNED NOT NULL DEFAULT 25,
  `interval_job` INT UNSIGNED NOT NULL DEFAULT 15,
  `interval_hidden` INT UNSIGNED NOT NULL DEFAULT 60,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` INT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `system_polling_settings` (`id`) VALUES (1);

-- 7d. job_comment_reads
CREATE TABLE IF NOT EXISTS `job_comment_reads` (
  `user_id` INT NOT NULL,
  `job_id` INT NOT NULL,
  `last_read_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `job_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. activity_logs table
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `job_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `detail` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX `idx_jobs_status` ON `jobs` (`status`);
CREATE INDEX `idx_jobs_urgency` ON `jobs` (`urgency`);
CREATE INDEX `idx_jobs_created_at` ON `jobs` (`created_at`);
CREATE INDEX `idx_notifications_user_read` ON `notifications` (`user_id`, `is_read`, `created_at` DESC);
CREATE INDEX `idx_notifications_job_id` ON `notifications` (`job_id`);
CREATE INDEX `idx_job_comment_reads_user` ON `job_comment_reads` (`user_id`);
CREATE INDEX `idx_activity_logs_created_at` ON `activity_logs` (`created_at` DESC);
CREATE INDEX `idx_comments_job_id` ON `comments` (`job_id`);
CREATE INDEX `idx_comment_pictures_comment_id` ON `comment_pictures` (`comment_id`);
