-- ================================================================
-- MIGRATION SCRIPTS: 2026-07-18 to 2026-08-05
-- Kasbook POS v4 - MySQL Database Migrations
-- ================================================================
-- Run these scripts in order on your production MySQL database
-- Database: kasbook (or your configured database name)
-- ================================================================

-- ================================================================
-- 1. Migration: 2026-07-18-000001_CreateEmployeeCategoryTargetsTable
-- ================================================================
-- Creates table for employee category-wise sales targets tracking
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

-- ================================================================
-- 2. Migration: 2026-07-30-120000_AddZatcaSettingsColumns
-- ================================================================
-- Add ZATCA e-invoicing configuration columns to settings table
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `einvoicing_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `sales_show_discount_type`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `einvoicing_country` VARCHAR(10) NULL DEFAULT 'SA' AFTER `einvoicing_enabled`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `zatca_environment` VARCHAR(20) NULL DEFAULT 'sandbox' AFTER `einvoicing_country`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `zatca_seller_vat_number` VARCHAR(50) NULL AFTER `zatca_environment`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `zatca_seller_name` VARCHAR(191) NULL AFTER `zatca_seller_vat_number`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `zatca_invoice_type` VARCHAR(20) NULL DEFAULT 'both' AFTER `zatca_seller_name`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `zatca_enabled_store_ids` TEXT NULL AFTER `zatca_invoice_type`;

-- ================================================================
-- 3. Migration: 2026-07-30-130000_AddZatcaColumnsToSales
-- ================================================================
-- Add ZATCA invoice tracking columns to sales transactions
ALTER TABLE `pos_sales` ADD COLUMN IF NOT EXISTS `zatca_uuid` VARCHAR(64) NULL AFTER `due_amount`;
ALTER TABLE `pos_sales` ADD COLUMN IF NOT EXISTS `zatca_invoice_hash` TEXT NULL AFTER `zatca_uuid`;
ALTER TABLE `pos_sales` ADD COLUMN IF NOT EXISTS `zatca_previous_invoice_hash` TEXT NULL AFTER `zatca_invoice_hash`;
ALTER TABLE `pos_sales` ADD COLUMN IF NOT EXISTS `zatca_icv` INT(11) UNSIGNED NULL AFTER `zatca_previous_invoice_hash`;
ALTER TABLE `pos_sales` ADD COLUMN IF NOT EXISTS `zatca_qr_code` TEXT NULL AFTER `zatca_icv`;
ALTER TABLE `pos_sales` ADD COLUMN IF NOT EXISTS `zatca_xml_path` VARCHAR(255) NULL AFTER `zatca_qr_code`;
ALTER TABLE `pos_sales` ADD COLUMN IF NOT EXISTS `zatca_status` VARCHAR(20) NULL AFTER `zatca_xml_path`;
ALTER TABLE `pos_sales` ADD COLUMN IF NOT EXISTS `zatca_response` TEXT NULL AFTER `zatca_status`;
ALTER TABLE `pos_sales` ADD COLUMN IF NOT EXISTS `zatca_submitted_at` DATETIME NULL AFTER `zatca_response`;

-- Add indexes for ZATCA fields
ALTER TABLE `pos_sales` ADD INDEX IF NOT EXISTS `idx_zatca_status` (`zatca_status`);
ALTER TABLE `pos_sales` ADD INDEX IF NOT EXISTS `idx_zatca_uuid` (`zatca_uuid`);

-- ================================================================
-- 4. Migration: 2026-07-30-140000_CreateZatcaCertificatesTable
-- ================================================================
-- Creates table for storing ZATCA compliance and production certificates
CREATE TABLE IF NOT EXISTS `pos_zatca_certificates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) UNSIGNED NOT NULL,
  `environment` VARCHAR(20) NOT NULL DEFAULT 'sandbox',
  `csr` TEXT NULL,
  `private_key` TEXT NULL COMMENT 'Encrypted private key - NEVER expose to frontend',
  `compliance_request_id` VARCHAR(100) NULL,
  `binary_security_token` TEXT NULL COMMENT 'Compliance CSID',
  `production_binary_security_token` TEXT NULL COMMENT 'Production CSID',
  `secret` VARCHAR(255) NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'draft' COMMENT 'draft, compliance, production',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_id` (`store_id`),
  KEY `idx_environment` (`environment`),
  KEY `idx_store_env_status` (`store_id`, `environment`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 5. Migration: 2026-07-30-160000_CreateZatcaLogsTable
-- ================================================================
-- Creates table for audit logging of ZATCA operations
CREATE TABLE IF NOT EXISTS `pos_zatca_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` INT(11) UNSIGNED NULL,
  `action` VARCHAR(50) NOT NULL COMMENT 'generate_xml, sign, submit_report, submit_clearance, retry, etc.',
  `level` VARCHAR(20) NOT NULL DEFAULT 'info' COMMENT 'info, warning, error',
  `message` TEXT NOT NULL,
  `context` JSON NULL COMMENT 'Additional debug data as JSON',
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_id` (`invoice_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_level` (`level`),
  KEY `idx_invoice_action_time` (`invoice_id`, `action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 6. Migration: 2026-07-30-170000_AddVatNumberToCustomers
-- ================================================================
-- Add VAT registration number field to customers for B2B ZATCA compliance
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `vat_number` VARCHAR(50) NULL AFTER `phone` COMMENT 'VAT registration number for B2B customers (ZATCA)';
ALTER TABLE `pos_customers` ADD INDEX IF NOT EXISTS `idx_vat_number` (`vat_number`);

-- ================================================================
-- 7. Migration: 2026-08-01-090000_AddZatcaInvoiceTypeToSales
-- ================================================================
-- Add ZATCA invoice type tracking to sales invoices
ALTER TABLE `pos_sales` ADD COLUMN IF NOT EXISTS `zatca_invoice_type` VARCHAR(20) NULL AFTER `due_amount`;
ALTER TABLE `pos_sales` ADD INDEX IF NOT EXISTS `idx_zatca_invoice_type` (`zatca_invoice_type`);

-- ================================================================
-- 8. Migration: 2026-08-01-190000_AddZatcaSellerFieldsToStores
-- ================================================================
-- Add ZATCA seller address details to stores table
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_seller_vat_number` VARCHAR(50) NULL AFTER `business_type`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_seller_legal_name` VARCHAR(191) NULL AFTER `zatca_seller_vat_number`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_street_name` VARCHAR(255) NULL AFTER `zatca_seller_legal_name`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_building_number` VARCHAR(10) NULL AFTER `zatca_street_name`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_city_subdivision_name` VARCHAR(255) NULL AFTER `zatca_building_number`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_city_name` VARCHAR(100) NULL AFTER `zatca_city_subdivision_name`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_postal_code` VARCHAR(20) NULL AFTER `zatca_city_name`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_country_code` VARCHAR(2) NOT NULL DEFAULT 'SA' AFTER `zatca_postal_code`;

-- ================================================================
-- 9. Migration: 2026-08-01-210000_AddZatcaBuyerFieldsToCustomers
-- ================================================================
-- Add ZATCA buyer address details to customers table
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_street_name` VARCHAR(255) NULL AFTER `address`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_building_number` VARCHAR(20) NULL AFTER `zatca_street_name`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_city_subdivision_name` VARCHAR(255) NULL AFTER `zatca_building_number`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_city_name` VARCHAR(255) NULL AFTER `zatca_city_subdivision_name`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_postal_code` VARCHAR(20) NULL AFTER `zatca_city_name`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_country_code` VARCHAR(2) NULL DEFAULT 'SA' AFTER `zatca_postal_code`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_registration_name` VARCHAR(255) NULL AFTER `zatca_country_code`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_cr_number` VARCHAR(100) NULL AFTER `zatca_registration_name`;

-- ================================================================
-- 10. Migration: 2026-08-02-100000_AddProductionSecretToZatcaCertificates
-- ================================================================
-- Add separate column for production CSID secret
ALTER TABLE `pos_zatca_certificates` ADD COLUMN IF NOT EXISTS `production_secret` VARCHAR(255) NULL COMMENT 'Production CSID secret from Step 4 onboarding' AFTER `secret`;

-- ================================================================
-- 11. Migration: 2026-08-02-100001_MigrateProductionSecretToNewColumn
-- ================================================================
-- Migrate existing production secrets to new column
UPDATE `pos_zatca_certificates`
SET `production_secret` = `secret`
WHERE `status` = 'production' AND `production_binary_security_token` IS NOT NULL;

-- ================================================================
-- 12. Migration: 2026-08-05-090000_AddZatcaCreditNoteColumnsToSalesReturns
-- ================================================================
-- Add ZATCA credit note tracking columns to sales returns
ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_uuid` VARCHAR(64) NULL AFTER `store_id`;
ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_hash` TEXT NULL AFTER `zatca_credit_note_uuid`;
ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_xml_path` VARCHAR(255) NULL AFTER `zatca_credit_note_hash`;
ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_status` VARCHAR(20) NULL AFTER `zatca_credit_note_xml_path`;
ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_response` TEXT NULL AFTER `zatca_credit_note_status`;
ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_submitted_at` DATETIME NULL AFTER `zatca_credit_note_response`;

-- Add indexes for credit note fields
ALTER TABLE `pos_sales_returns` ADD INDEX IF NOT EXISTS `idx_zatca_credit_note_uuid` (`zatca_credit_note_uuid`);
ALTER TABLE `pos_sales_returns` ADD INDEX IF NOT EXISTS `idx_zatca_credit_note_status` (`zatca_credit_note_status`);

-- ================================================================
-- END OF MIGRATION SCRIPTS
-- ================================================================
-- All migrations from 2026-07-18 to 2026-08-05 have been applied
-- Total: 12 migration batches
-- ================================================================
