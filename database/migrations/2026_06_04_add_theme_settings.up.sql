ALTER TABLE settings ADD COLUMN `active_theme` varchar(64) NOT NULL DEFAULT 'default' AFTER `minecraft_bedrock_enabled`;
