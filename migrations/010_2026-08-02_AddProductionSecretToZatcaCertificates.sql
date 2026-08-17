-- Migration: 2026-08-02-100000_AddProductionSecretToZatcaCertificates
-- Description: Add separate column for production CSID secret
-- Database: kasbook

ALTER TABLE `pos_zatca_certificates` ADD COLUMN IF NOT EXISTS `production_secret` VARCHAR(255) NULL COMMENT 'Production CSID secret from Step 4 onboarding' AFTER `secret`;
