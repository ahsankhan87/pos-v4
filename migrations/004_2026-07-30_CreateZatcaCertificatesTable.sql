-- Migration: 2026-07-30-140000_CreateZatcaCertificatesTable
-- Description: Creates table for storing ZATCA compliance and production certificates
-- Database: kasbook

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
