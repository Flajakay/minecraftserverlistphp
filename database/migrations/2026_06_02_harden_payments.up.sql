ALTER TABLE `payments`
ADD COLUMN `currency` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD' AFTER `revenue`;

UPDATE `payments`
SET `currency` = COALESCE((SELECT `payment_currency` FROM `settings` WHERE `id` = 1), 'USD')
WHERE `currency` IS NULL OR `currency` = '';

UPDATE `payments`
SET `paypal_order_id` = CONCAT('legacy-', `id`)
WHERE `paypal_order_id` IS NULL OR `paypal_order_id` = '';

UPDATE `payments` p
JOIN (
    SELECT `paypal_order_id`, MIN(`id`) AS `first_id`
    FROM `payments`
    GROUP BY `paypal_order_id`
    HAVING COUNT(*) > 1
) duplicates ON duplicates.`paypal_order_id` = p.`paypal_order_id`
SET p.`paypal_order_id` = CONCAT(p.`paypal_order_id`, '-', p.`id`)
WHERE p.`id` <> duplicates.`first_id`;

ALTER TABLE `payments`
DROP INDEX `idx_paypal_order_id`;

ALTER TABLE `payments`
ADD UNIQUE KEY `unique_paypal_order_id` (`paypal_order_id`);
