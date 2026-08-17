-- Migration: 2026-07-18-000001_CreateEmployeeCategoryTargetsTable
-- Description: Creates table for employee category-wise sales targets tracking
-- Database: kasbook

CREATE TABLE IF NOT EXISTS `pos_employee_category_targets` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) UNSIGNED NOT NULL,
  `employee_id` INT(11) UNSIGNED NOT NULL,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `target_month` VARCHAR(7) NOT NULL COMMENT 'Format: YYYY-MM',
  `target_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `notes` TEXT NULL,
  `created_by` INT(11) UNSIGNED NULL,
  `updated_by` INT(11) UNSIGNED NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_id` (`store_id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_target_month` (`target_month`),
  UNIQUE KEY `uniq_store_emp_cat_month` (`store_id`, `employee_id`, `category_id`, `target_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
