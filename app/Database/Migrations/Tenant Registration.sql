START TRANSACTION;

CREATE TABLE IF NOT EXISTS `pos_company_tenants` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `store_id` INT(11) UNSIGNED NOT NULL,
    `company_name` VARCHAR(191) NOT NULL,
    `slug` VARCHAR(80) NOT NULL,
    `db_host` VARCHAR(191) NOT NULL DEFAULT 'localhost',
    `db_port` INT(5) NOT NULL DEFAULT 3306,
    `db_name` VARCHAR(191) NOT NULL,
    `db_user` VARCHAR(191) NULL DEFAULT NULL,
    `db_pass` TEXT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'active',
    `created_by` INT(11) NULL DEFAULT NULL,
    `created_at` DATETIME NULL DEFAULT NULL,
    `updated_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_pos_company_tenants_store` (`store_id`),
    UNIQUE KEY `uniq_pos_company_tenants_slug` (`slug`),
    UNIQUE KEY `uniq_pos_company_tenants_db_name` (`db_name`),
    KEY `idx_pos_company_tenants_status_slug` (`status`, `slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'pos_company_tenants'
          AND COLUMN_NAME = 'app_path'
    ),
    'SELECT 1',
    'ALTER TABLE `pos_company_tenants` ADD COLUMN `app_path` VARCHAR(255) NULL AFTER `slug`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'pos_company_tenants'
          AND COLUMN_NAME = 'app_url'
    ),
    'SELECT 1',
    'ALTER TABLE `pos_company_tenants` ADD COLUMN `app_url` VARCHAR(255) NULL AFTER `app_path`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `pos_company_tenants`
SET
    `app_path` = CASE
        WHEN `app_path` IS NULL OR `app_path` = '' THEN CONCAT('/home/your-user/public_html/', `slug`)
        ELSE `app_path`
    END,
    `app_url` = CASE
        WHEN `app_url` IS NULL OR `app_url` = '' THEN CONCAT('https://yourdomain.com/', `slug`)
        ELSE `app_url`
    END;

COMMIT;

START TRANSACTION;

SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'pos_stores'
          AND COLUMN_NAME = 'email'
    ),
    'SELECT 1',
    'ALTER TABLE `pos_stores` ADD COLUMN `email` VARCHAR(191) NULL AFTER `phone`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'pos_stores'
          AND COLUMN_NAME = 'receipt_header'
    ),
    'SELECT 1',
    'ALTER TABLE `pos_stores` ADD COLUMN `receipt_header` VARCHAR(255) NULL AFTER `email`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'pos_stores'
          AND COLUMN_NAME = 'receipt_footer'
    ),
    'SELECT 1',
    'ALTER TABLE `pos_stores` ADD COLUMN `receipt_footer` VARCHAR(255) NULL AFTER `receipt_header`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'pos_stores'
          AND COLUMN_NAME = 'is_default'
    ),
    'SELECT 1',
    'ALTER TABLE `pos_stores` ADD COLUMN `is_default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'pos_stores'
          AND COLUMN_NAME = 'currency_code'
    ),
    'SELECT 1',
    'ALTER TABLE `pos_stores` ADD COLUMN `currency_code` VARCHAR(10) NULL AFTER `is_default`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'pos_stores'
          AND COLUMN_NAME = 'currency_symbol'
    ),
    'SELECT 1',
    'ALTER TABLE `pos_stores` ADD COLUMN `currency_symbol` VARCHAR(10) NULL AFTER `currency_code`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'pos_stores'
          AND COLUMN_NAME = 'timezone'
    ),
    'SELECT 1',
    'ALTER TABLE `pos_stores` ADD COLUMN `timezone` VARCHAR(100) NULL AFTER `currency_symbol`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'pos_stores'
          AND COLUMN_NAME = 'website_url'
    ),
    'SELECT 1',
    'ALTER TABLE `pos_stores` ADD COLUMN `website_url` VARCHAR(255) NULL AFTER `timezone`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

COMMIT;