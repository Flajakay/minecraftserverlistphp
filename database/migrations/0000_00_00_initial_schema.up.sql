CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  `password` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  `email_activation_code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `lost_password_code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `about` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `website` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `location` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `avatar` varchar(38) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `cover` varchar(38) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `facebook` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `twitter` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `googleplus` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '0',
  `private` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `remember_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_activity` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `title` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `url` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  `image` varchar(38) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `address` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int(11) NOT NULL DEFAULT '25565',
  `private` int(11) NOT NULL DEFAULT '1',
  `active` int(11) NOT NULL DEFAULT '1',
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(38) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `icon` varchar(38) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `website` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `country` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT 'US',
  `youtube_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `highlight` int(11) NOT NULL DEFAULT '0',
  `votes` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `players` int(11) NOT NULL DEFAULT '0',
  `max_players` int(11) NOT NULL DEFAULT '0',
  `version` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `verified_owner_user_id` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `verification_status` int(11) NOT NULL DEFAULT '0',
  `verification_requested_by_user_id` int(11) DEFAULT NULL,
  `verification_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_token_expires_at` datetime DEFAULT NULL,
  `last_verification_attempt_at` datetime DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `custom_data` json DEFAULT NULL,
  `game_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'minecraft',
  `protocol` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'minecraft_java',
  `query_port` int(11) DEFAULT NULL,
  `game_app_id` int(11) DEFAULT NULL,
  `game_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `map_name` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_protected` tinyint(1) DEFAULT NULL,
  `protocol_metadata` json DEFAULT NULL,
  `last_check` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_server_protocol` (`protocol`, `address`, `port`, `query_port`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_verified_owner_user_id` (`verified_owner_user_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_highlight` (`highlight`),
  KEY `idx_game_id` (`game_id`),
  KEY `idx_game_type` (`game_type`),
  KEY `idx_protocol` (`protocol`),
  KEY `idx_game_app_id` (`game_app_id`),
  KEY `idx_game_name` (`game_name`),
  KEY `idx_status_protocol` (`status`, `protocol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_server_type` (`server_id`, `type`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `comment` varchar(512) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_server_id` (`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `reported_id` int(11) NOT NULL,
  `message` varchar(512) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `highlighted_days` int(11) NOT NULL,
  `revenue` decimal(10,2) NOT NULL,
  `currency` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `email` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','completed','cancelled','expired','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `paypal_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  UNIQUE KEY `unique_paypal_order_id` (`paypal_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_server_id` (`server_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier_type` enum('username','ip') COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT '1',
  `first_attempt` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_attempt` datetime DEFAULT CURRENT_TIMESTAMP,
  `lockout_until` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_identifier` (`identifier`,`identifier_type`),
  KEY `idx_lockout` (`lockout_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` int(11) NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_table_record` (`table_name`, `record_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL DEFAULT '1',
  `title` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `premium` int(11) NOT NULL DEFAULT '1',
  `meta_description` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `banned_words` text COLLATE utf8mb4_unicode_ci,
  `analytics_code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `email_confirmation` int(11) NOT NULL DEFAULT '0',
  `servers_pagination` int(11) NOT NULL DEFAULT '15',
  `avatar_max_size` int(11) NOT NULL DEFAULT '1000000',
  `cover_max_size` int(11) NOT NULL DEFAULT '1000000',
  `contact_email` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cache_reset_time` int(11) NOT NULL DEFAULT '600',
  `display_offline_servers` int(11) NOT NULL DEFAULT '1',
  `new_servers_visibility` int(11) NOT NULL DEFAULT '0',
  `top_ads` text COLLATE utf8mb4_unicode_ci,
  `bottom_ads` text COLLATE utf8mb4_unicode_ci,
  `side_ads` text COLLATE utf8mb4_unicode_ci,
  `payment_currency` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `maximum_slots` int(11) NOT NULL DEFAULT '0',
  `per_day_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `minimum_days` int(11) NOT NULL DEFAULT '1',
  `maximum_days` int(11) NOT NULL DEFAULT '30',
  `facebook` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `twitter` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `googleplus` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `smtp_host` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `smtp_port` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `smtp_user` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `smtp_pass` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `smtp_secure` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `favicon_source` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `favicon_version` int(11) NOT NULL DEFAULT '0',
  `minecraft_java_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `steam_a2s_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `minecraft_bedrock_enabled` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`id`, `title`, `url`, `contact_email`) VALUES
(1, 'Game Server List', 'http://localhost/new_server_list/public/', 'admin@admin.com')
ON DUPLICATE KEY UPDATE `id` = `id`;

CREATE TABLE IF NOT EXISTS `server_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_server_category` (`server_id`, `category_id`),
  KEY `idx_server_id` (`server_id`),
  KEY `idx_category_id` (`category_id`),
  FOREIGN KEY (`server_id`) REFERENCES `servers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS `server_player_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `players` int(11) NOT NULL DEFAULT '0',
  `max_players` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_server_id` (`server_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`server_id`) REFERENCES `servers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `url`, `description`) VALUES
(1, 'Survival', 'survival', 'Survival servers'),
(2, 'Creative', 'creative', 'Creative servers'),
(3, 'PvP', 'pvp', 'PvP servers'),
(4, 'Roleplay', 'roleplay', 'Roleplay servers'),
(5, 'Modded', 'modded', 'Modded servers'),
(6, 'Competitive', 'competitive', 'Competitive servers'),
(7, 'Casual', 'casual', 'Casual servers'),
(8, 'Co-op', 'coop', 'Co-op servers')
ON DUPLICATE KEY UPDATE `id` = `id`;
