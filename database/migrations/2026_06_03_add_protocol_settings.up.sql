ALTER TABLE `settings`
  ADD COLUMN `minecraft_java_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `favicon_version`,
  ADD COLUMN `steam_a2s_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `minecraft_java_enabled`;

UPDATE `settings` SET `minecraft_java_enabled` = 1, `steam_a2s_enabled` = 1 WHERE `id` = 1;
