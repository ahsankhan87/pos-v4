-- Migration: 2026-08-01-090000_AddZatcaInvoiceTypeToSales
-- Description: Add ZATCA invoice type tracking to sales invoices
-- Database: kasbook

ALTER TABLE `pos_sales` ADD COLUMN IF NOT EXISTS `zatca_invoice_type` VARCHAR(20) NULL AFTER `due_amount`;
ALTER TABLE `pos_sales` ADD INDEX IF NOT EXISTS `idx_zatca_invoice_type` (`zatca_invoice_type`);
