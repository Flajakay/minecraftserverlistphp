ALTER TABLE `server_player_history` ADD COLUMN `date` date GENERATED ALWAYS AS (DATE(created_at)) STORED AFTER `created_at`;
ALTER TABLE `server_player_history` ADD KEY `idx_server_date` (`server_id`, `date`);

ALTER TABLE `payments` ADD COLUMN `expires_at` datetime GENERATED ALWAYS AS (DATE_ADD(created_at, INTERVAL highlighted_days DAY)) STORED AFTER `updated_at`;
ALTER TABLE `payments` ADD KEY `idx_expires_at` (`expires_at`);
