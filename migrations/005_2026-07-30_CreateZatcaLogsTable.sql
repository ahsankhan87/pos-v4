-- Migration: 2026-07-30-160000_CreateZatcaLogsTable
-- Description: Creates table for audit logging of ZATCA operations
-- Database: kasbook

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
