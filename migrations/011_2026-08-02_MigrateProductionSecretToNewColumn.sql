-- Migration: 2026-08-02-100001_MigrateProductionSecretToNewColumn
-- Description: Migrate existing production secrets to new column
-- Database: kasbook
-- Note: This migration copies the 'secret' value to 'production_secret' for production certificates

UPDATE `pos_zatca_certificates`
SET `production_secret` = `secret`
WHERE `status` = 'production' AND `production_binary_security_token` IS NOT NULL;
