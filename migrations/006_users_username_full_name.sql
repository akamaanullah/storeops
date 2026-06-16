-- Split login name into username + full_name, remove email.
-- Run on existing job_tracker database after backup.

-- Remove old unique index on name (if present)
ALTER TABLE `users` DROP INDEX `idx_users_name`;

-- Add username for login
ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) NULL AFTER `id`;

-- Seed username from current name (spaces -> underscores, lowercase)
UPDATE `users`
SET `username` = LOWER(REPLACE(TRIM(`name`), ' ', '_'))
WHERE `username` IS NULL OR `username` = '';

ALTER TABLE `users`
  MODIFY `username` VARCHAR(50) NOT NULL,
  ADD UNIQUE KEY `idx_users_username` (`username`);

-- Current name becomes display full name
ALTER TABLE `users` CHANGE COLUMN `name` `full_name` VARCHAR(100) NOT NULL;

-- Remove email
ALTER TABLE `users` DROP COLUMN `email`;
