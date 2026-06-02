-- Add IP address column to login_attempts for better auditing and tracking
ALTER TABLE `login_attempts` 
ADD COLUMN `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `identifier_type`,
ADD INDEX `idx_ip_address` (`ip_address`);
