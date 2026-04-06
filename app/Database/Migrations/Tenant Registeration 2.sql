START TRANSACTION;

CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `store_id` INT(11) NOT NULL,
    `plan_id` INT(11) NOT NULL,
    `next_plan_id` INT(11) NULL DEFAULT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'active',
    `is_trial` TINYINT(1) NOT NULL DEFAULT 0,
    `trial_ends_at` DATETIME NULL DEFAULT NULL,
    `renews_at` DATETIME NULL DEFAULT NULL,
    `ends_at` DATETIME NULL DEFAULT NULL,
    `provider` VARCHAR(50) NULL DEFAULT 'manual',
    `provider_subscription_id` VARCHAR(191) NULL DEFAULT NULL,
    `created_at` DATETIME NULL DEFAULT NULL,
    `updated_at` DATETIME NULL DEFAULT NULL,
    `deleted_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'user_id'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `user_id` INT(11) NOT NULL AFTER `id`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'store_id'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `store_id` INT(11) NOT NULL DEFAULT 0 AFTER `user_id`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'plan_id'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `plan_id` INT(11) NOT NULL DEFAULT 0 AFTER `store_id`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'next_plan_id'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `next_plan_id` INT(11) NULL DEFAULT NULL AFTER `plan_id`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'status'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `status` VARCHAR(30) NOT NULL DEFAULT ''active'' AFTER `next_plan_id`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'is_trial'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `is_trial` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'trial_ends_at'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `trial_ends_at` DATETIME NULL DEFAULT NULL AFTER `is_trial`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'renews_at'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `renews_at` DATETIME NULL DEFAULT NULL AFTER `trial_ends_at`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'ends_at'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `ends_at` DATETIME NULL DEFAULT NULL AFTER `renews_at`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'provider'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `provider` VARCHAR(50) NULL DEFAULT ''manual'' AFTER `ends_at`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'provider_subscription_id'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `provider_subscription_id` VARCHAR(191) NULL DEFAULT NULL AFTER `provider`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'created_at'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `created_at` DATETIME NULL DEFAULT NULL AFTER `provider_subscription_id`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'updated_at'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL AFTER `created_at`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND COLUMN_NAME = 'deleted_at'
    ),
    'SELECT 1',
    'ALTER TABLE `subscriptions` ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `updated_at`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

COMMIT;