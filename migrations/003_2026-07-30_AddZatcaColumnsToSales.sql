-- Migration: 2026-07-30-130000_AddZatcaColumnsToSales
-- Description: Add ZATCA invoice tracking columns to sales transactions
-- Database: kasbook

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
