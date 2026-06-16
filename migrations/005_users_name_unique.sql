-- Make users.name unique (login identifier).
-- Run only after confirming no duplicate names:
--   SELECT name, COUNT(*) AS c FROM users GROUP BY name HAVING c > 1;

ALTER TABLE `users`
  ADD UNIQUE KEY `idx_users_name` (`name`);
