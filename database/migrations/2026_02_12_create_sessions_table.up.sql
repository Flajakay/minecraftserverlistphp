CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `sessions_last_activity_index` (`last_activity`),
    INDEX `sessions_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
