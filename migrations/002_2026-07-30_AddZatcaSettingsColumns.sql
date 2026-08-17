-- Migration: 2026-07-30-120000_AddZatcaSettingsColumns
-- Description: Add ZATCA e-invoicing configuration columns to settings table
-- Database: kasbook

ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `einvoicing_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `sales_show_discount_type`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `einvoicing_country` VARCHAR(10) NULL DEFAULT 'SA' AFTER `einvoicing_enabled`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `zatca_environment` VARCHAR(20) NULL DEFAULT 'sandbox' AFTER `einvoicing_country`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `zatca_seller_vat_number` VARCHAR(50) NULL AFTER `zatca_environment`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `zatca_seller_name` VARCHAR(191) NULL AFTER `zatca_seller_vat_number`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `zatca_invoice_type` VARCHAR(20) NULL DEFAULT 'both' AFTER `zatca_seller_name`;
ALTER TABLE `settings` ADD COLUMN IF NOT EXISTS `zatca_enabled_store_ids` TEXT NULL AFTER `zatca_invoice_type`;
