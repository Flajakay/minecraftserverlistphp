ALTER TABLE `servers`
  ADD COLUMN `game_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'minecraft' AFTER `custom_data`,
  ADD COLUMN `protocol` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'minecraft_java' AFTER `game_type`,
  ADD COLUMN `query_port` int(11) DEFAULT NULL AFTER `port`,
  ADD COLUMN `game_app_id` int(11) DEFAULT NULL AFTER `protocol`,
  ADD COLUMN `game_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `game_app_id`,
  ADD COLUMN `map_name` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `game_name`,
  ADD COLUMN `password_protected` tinyint(1) DEFAULT NULL AFTER `map_name`,
  ADD COLUMN `protocol_metadata` json DEFAULT NULL AFTER `password_protected`,
  ADD INDEX `idx_game_type` (`game_type`),
  ADD INDEX `idx_protocol` (`protocol`),
  ADD INDEX `idx_game_app_id` (`game_app_id`),
  ADD INDEX `idx_game_name` (`game_name`),
  ADD INDEX `idx_status_protocol` (`status`, `protocol`),
  DROP INDEX `unique_server`,
  ADD UNIQUE KEY `unique_server_protocol` (`protocol`, `address`, `port`, `query_port`);

UPDATE `servers`
SET
  `game_type` = 'minecraft',
  `protocol` = 'minecraft_java',
  `query_port` = `port`,
  `game_name` = 'Minecraft Java'
WHERE `protocol` = 'minecraft_java' OR (`protocol` IS NULL OR `protocol` = '' OR `game_type` IS NULL OR `game_type` = '');
