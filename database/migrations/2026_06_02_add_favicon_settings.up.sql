ALTER TABLE `settings`
ADD COLUMN `favicon_source` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT '' AFTER `smtp_secure`;

ALTER TABLE `settings`
ADD COLUMN `favicon_version` int(11) NOT NULL DEFAULT '0' AFTER `favicon_source`;
