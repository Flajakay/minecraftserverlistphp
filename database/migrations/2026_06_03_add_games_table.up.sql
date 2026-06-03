CREATE TABLE IF NOT EXISTS `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `steam_app_id` int(11) DEFAULT NULL,
  `protocol` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'steam_a2s',
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_game_name` (`name`),
  KEY `idx_protocol` (`protocol`),
  KEY `idx_enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `servers`
  ADD COLUMN `game_id` int(11) DEFAULT NULL AFTER `game_name`,
  ADD KEY `idx_game_id` (`game_id`);

INSERT IGNORE INTO `games` (`name`, `steam_app_id`, `protocol`, `enabled`) VALUES
  ('Counter-Strike 2', 730, 'steam_a2s', 1),
  ('Team Fortress 2', 440, 'steam_a2s', 1),
  ('Rust', 252490, 'steam_a2s', 1),
  ('ARK: Survival Evolved', 346110, 'steam_a2s', 1),
  ('Garry''s Mod', 4000, 'steam_a2s', 1),
  ('Left 4 Dead 2', 550, 'steam_a2s', 1),
  ('DayZ', 221100, 'steam_a2s', 1),
  ('Squad', 393380, 'steam_a2s', 1),
  ('Insurgency: Sandstorm', 581320, 'steam_a2s', 1),
  ('Project Zomboid', 108600, 'steam_a2s', 1),
  ('7 Days to Die', 251570, 'steam_a2s', 1),
  ('Valheim', 892970, 'steam_a2s', 1),
  ('Satisfactory', 526870, 'steam_a2s', 1),
  ('The Forest', 242760, 'steam_a2s', 1),
  ('Killing Floor 2', 232090, 'steam_a2s', 1),
  ('Counter-Strike: Source', 240, 'steam_a2s', 1),
  ('BattleBit Remastered', 671860, 'steam_a2s', 1),
  ('Terraria', 105600, 'steam_a2s', 1);
