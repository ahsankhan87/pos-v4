-- Migration: 2026-08-01-190000_AddZatcaSellerFieldsToStores
-- Description: Add ZATCA seller address details to stores table
-- Database: kasbook

ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_seller_vat_number` VARCHAR(50) NULL AFTER `business_type`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_seller_legal_name` VARCHAR(191) NULL AFTER `zatca_seller_vat_number`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_street_name` VARCHAR(255) NULL AFTER `zatca_seller_legal_name`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_building_number` VARCHAR(10) NULL AFTER `zatca_street_name`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_city_subdivision_name` VARCHAR(255) NULL AFTER `zatca_building_number`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_city_name` VARCHAR(100) NULL AFTER `zatca_city_subdivision_name`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_postal_code` VARCHAR(20) NULL AFTER `zatca_city_name`;
ALTER TABLE `pos_stores` ADD COLUMN IF NOT EXISTS `zatca_country_code` VARCHAR(2) NOT NULL DEFAULT 'SA' AFTER `zatca_postal_code`;
