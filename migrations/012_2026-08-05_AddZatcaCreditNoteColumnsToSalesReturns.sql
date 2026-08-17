-- Migration: 2026-08-05-090000_AddZatcaCreditNoteColumnsToSalesReturns
-- Description: Add ZATCA credit note tracking columns to sales returns
-- Database: kasbook

ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_uuid` VARCHAR(64) NULL AFTER `store_id`;
ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_hash` TEXT NULL AFTER `zatca_credit_note_uuid`;
ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_xml_path` VARCHAR(255) NULL AFTER `zatca_credit_note_hash`;
ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_status` VARCHAR(20) NULL AFTER `zatca_credit_note_xml_path`;
ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_response` TEXT NULL AFTER `zatca_credit_note_status`;
ALTER TABLE `pos_sales_returns` ADD COLUMN IF NOT EXISTS `zatca_credit_note_submitted_at` DATETIME NULL AFTER `zatca_credit_note_response`;

-- Add indexes for credit note fields
ALTER TABLE `pos_sales_returns` ADD INDEX IF NOT EXISTS `idx_zatca_credit_note_uuid` (`zatca_credit_note_uuid`);
ALTER TABLE `pos_sales_returns` ADD INDEX IF NOT EXISTS `idx_zatca_credit_note_status` (`zatca_credit_note_status`);
