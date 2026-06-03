ALTER TABLE `servers` ADD KEY `idx_country` (`country`);
ALTER TABLE `servers` ADD KEY `idx_votes` (`votes`);
ALTER TABLE `servers` ADD KEY `idx_players` (`players`);
ALTER TABLE `servers` ADD KEY `idx_created_at` (`created_at`);

ALTER TABLE `reports` ADD KEY `idx_type_reported` (`type`, `reported_id`);
ALTER TABLE `reports` ADD KEY `idx_type` (`type`);
ALTER TABLE `reports` ADD KEY `idx_created_at` (`created_at`);

ALTER TABLE `points` ADD KEY `idx_ip` (`ip`);
ALTER TABLE `points` ADD KEY `idx_server_ip_type` (`server_id`, `ip`, `type`);

ALTER TABLE `login_attempts` ADD KEY `idx_last_attempt` (`last_attempt`);
