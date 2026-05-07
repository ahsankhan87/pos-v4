-- ============================================================
-- Employee Sales Targets Module
-- Run this on machines that already have the POS database
-- ============================================================

-- 1. Create table (safe: only if it doesn't exist)
CREATE TABLE IF NOT EXISTS `pos_employee_sales_targets` (
    `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `store_id`      INT(11) UNSIGNED NOT NULL,
    `employee_id`   INT(11) UNSIGNED NOT NULL,
    `target_month`  VARCHAR(7) NOT NULL,
    `target_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `notes`         TEXT NULL,
    `created_by`    INT(11) UNSIGNED NULL,
    `updated_by`    INT(11) UNSIGNED NULL,
    `created_at`    DATETIME NULL,
    `updated_at`    DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `store_id` (`store_id`),
    KEY `employee_id` (`employee_id`),
    KEY `target_month` (`target_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Add unique index (safe: only if it doesn't exist)
SET @dbname = DATABASE();
SET @tablename = 'pos_employee_sales_targets';
SET @indexname = 'uniq_store_employee_month';

SET @exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME   = @tablename
      AND INDEX_NAME   = @indexname
);

SET @sql = IF(
    @exists = 0,
    'ALTER TABLE `pos_employee_sales_targets` ADD UNIQUE INDEX `uniq_store_employee_month` (`store_id`, `employee_id`, `target_month`)',
    'SELECT ''index already exists'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Insert permissions (safe: INSERT IGNORE skips duplicates)
INSERT IGNORE INTO `pos_permissions` (`name`) VALUES
    ('employee_targets.view'),
    ('employee_targets.create'),
    ('employee_targets.update'),
    ('employee_targets.delete'),
    ('reports.employee_target_achievement');

-- 4. Backfill role_permissions
--    Roles that already have employees.view  → get employee_targets.view
--    Roles that already have employees.create → get employee_targets.create
--    Roles that already have employees.update → get employee_targets.update
--    Roles that already have employees.delete → get employee_targets.delete
--    Roles that already have reports.employee_commission_report → get reports.employee_target_achievement

INSERT IGNORE INTO `pos_role_permissions` (`role_id`, `permission_id`)
SELECT rp.`role_id`, p_new.`id`
FROM `pos_role_permissions` rp
JOIN `pos_permissions` p_src ON p_src.`id` = rp.`permission_id` AND p_src.`name` = 'employees.view'
JOIN `pos_permissions` p_new ON p_new.`name` = 'employee_targets.view';

INSERT IGNORE INTO `pos_role_permissions` (`role_id`, `permission_id`)
SELECT rp.`role_id`, p_new.`id`
FROM `pos_role_permissions` rp
JOIN `pos_permissions` p_src ON p_src.`id` = rp.`permission_id` AND p_src.`name` = 'employees.create'
JOIN `pos_permissions` p_new ON p_new.`name` = 'employee_targets.create';

INSERT IGNORE INTO `pos_role_permissions` (`role_id`, `permission_id`)
SELECT rp.`role_id`, p_new.`id`
FROM `pos_role_permissions` rp
JOIN `pos_permissions` p_src ON p_src.`id` = rp.`permission_id` AND p_src.`name` = 'employees.update'
JOIN `pos_permissions` p_new ON p_new.`name` = 'employee_targets.update';

INSERT IGNORE INTO `pos_role_permissions` (`role_id`, `permission_id`)
SELECT rp.`role_id`, p_new.`id`
FROM `pos_role_permissions` rp
JOIN `pos_permissions` p_src ON p_src.`id` = rp.`permission_id` AND p_src.`name` = 'employees.delete'
JOIN `pos_permissions` p_new ON p_new.`name` = 'employee_targets.delete';

INSERT IGNORE INTO `pos_role_permissions` (`role_id`, `permission_id`)
SELECT rp.`role_id`, p_new.`id`
FROM `pos_role_permissions` rp
JOIN `pos_permissions` p_src ON p_src.`id` = rp.`permission_id` AND p_src.`name` = 'reports.employee_commission_report'
JOIN `pos_permissions` p_new ON p_new.`name` = 'reports.employee_target_achievement';