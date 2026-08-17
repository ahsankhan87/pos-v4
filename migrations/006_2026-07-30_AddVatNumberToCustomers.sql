-- Migration: 2026-07-30-170000_AddVatNumberToCustomers
-- Description: Add VAT registration number field to customers for B2B ZATCA compliance
-- Database: kasbook

ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `vat_number` VARCHAR(50) NULL AFTER `phone` COMMENT 'VAT registration number for B2B customers (ZATCA)';
ALTER TABLE `pos_customers` ADD INDEX IF NOT EXISTS `idx_vat_number` (`vat_number`);
