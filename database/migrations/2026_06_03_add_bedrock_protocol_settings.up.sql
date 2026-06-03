ALTER TABLE `settings`
  ADD COLUMN `minecraft_bedrock_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `steam_a2s_enabled`;

UPDATE `settings` SET `minecraft_bedrock_enabled` = 1 WHERE `id` = 1;
