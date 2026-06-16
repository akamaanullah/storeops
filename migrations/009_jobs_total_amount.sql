-- Migration 009: Add total_amount column to jobs table
ALTER TABLE `jobs`
    ADD COLUMN IF NOT EXISTS `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total contract/job amount';
