-- Migration 007: Add w9_form_path column to jobs table
ALTER TABLE `jobs`
    ADD COLUMN IF NOT EXISTS `w9_form_path` VARCHAR(500) NULL DEFAULT NULL COMMENT 'Path to uploaded W9 form PDF/document';
