-- Kasbook POS v4 IMEI / store feature rollout script
-- Run this against each client database after taking a backup.

SET @db_name := DATABASE();

-- 1) Add pos_stores.business_type when the table exists and the column is missing.
SET @has_pos_stores := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = @db_name
      AND table_name = 'pos_stores'
);

SET @has_business_type := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = @db_name
      AND table_name = 'pos_stores'
      AND column_name = 'business_type'
);

SET @has_website_url := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = @db_name
      AND table_name = 'pos_stores'
      AND column_name = 'website_url'
);

SET @sql := IF(
    @has_pos_stores > 0 AND @has_business_type = 0,
    IF(
        @has_website_url > 0,
        'ALTER TABLE `pos_stores` ADD COLUMN `business_type` VARCHAR(50) NOT NULL DEFAULT ''general'' AFTER `website_url`',
        'ALTER TABLE `pos_stores` ADD COLUMN `business_type` VARCHAR(50) NOT NULL DEFAULT ''general'''
    ),
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `pos_stores`
SET `business_type` = 'general'
WHERE `business_type` IS NULL
   OR `business_type` = '';

-- 2) Create store feature overrides table when missing.
CREATE TABLE IF NOT EXISTS `pos_store_feature_overrides` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `store_id` INT(11) UNSIGNED NOT NULL,
    `feature_key` VARCHAR(80) NOT NULL,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_store_id` (`store_id`),
    KEY `idx_feature_key` (`feature_key`),
    UNIQUE KEY `uq_store_feature` (`store_id`, `feature_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3) Add pos_products.requires_imei when the table exists and the column is missing.
SET @has_pos_products := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = @db_name
      AND table_name = 'pos_products'
);

SET @has_requires_imei := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = @db_name
      AND table_name = 'pos_products'
      AND column_name = 'requires_imei'
);

SET @has_is_stock_tracked := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = @db_name
      AND table_name = 'pos_products'
      AND column_name = 'is_stock_tracked'
);

SET @sql := IF(
    @has_pos_products > 0 AND @has_requires_imei = 0,
    IF(
        @has_is_stock_tracked > 0,
        'ALTER TABLE `pos_products` ADD COLUMN `requires_imei` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_stock_tracked`',
        'ALTER TABLE `pos_products` ADD COLUMN `requires_imei` TINYINT(1) NOT NULL DEFAULT 0'
    ),
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `pos_products`
SET `requires_imei` = 0
WHERE `requires_imei` IS NULL;

-- 4) Create IMEI inventory table when missing.
CREATE TABLE IF NOT EXISTS `pos_product_imeis` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `store_id` INT(11) UNSIGNED NOT NULL,
    `product_id` INT(11) UNSIGNED NOT NULL,
    `imei` VARCHAR(100) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'available' COMMENT 'available|sold|returned|blocked',
    `purchase_id` INT(11) UNSIGNED NULL,
    `purchase_item_id` INT(11) UNSIGNED NULL,
    `sale_id` INT(11) UNSIGNED NULL,
    `sale_item_id` INT(11) UNSIGNED NULL,
    `sold_at` DATETIME NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `idx_store_product` (`store_id`, `product_id`),
    KEY `idx_store_status` (`store_id`, `status`),
    UNIQUE KEY `uq_store_imei` (`store_id`, `imei`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
