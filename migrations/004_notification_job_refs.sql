-- Backfill job_id on older notifications (safe to re-run).
-- Display layer also normalizes legacy "Job #N" text at read time.

UPDATE notifications
SET job_id = CAST(SUBSTRING_INDEX(message, '-', -1) AS UNSIGNED)
WHERE job_id IS NULL
  AND message REGEXP 'WO-[0-9]{4}-[0-9]+';

UPDATE notifications
SET job_id = CAST(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(message, '#', -1), ' ', 1)) AS UNSIGNED)
WHERE job_id IS NULL
  AND message REGEXP 'job #[0-9]+|Job #[0-9]+';
