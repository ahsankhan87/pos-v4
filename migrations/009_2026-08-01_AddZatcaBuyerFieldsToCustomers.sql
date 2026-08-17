-- Migration: 2026-08-01-210000_AddZatcaBuyerFieldsToCustomers
-- Description: Add ZATCA buyer address details to customers table
-- Database: kasbook

ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_street_name` VARCHAR(255) NULL AFTER `address`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_building_number` VARCHAR(20) NULL AFTER `zatca_street_name`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_city_subdivision_name` VARCHAR(255) NULL AFTER `zatca_building_number`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_city_name` VARCHAR(255) NULL AFTER `zatca_city_subdivision_name`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_postal_code` VARCHAR(20) NULL AFTER `zatca_city_name`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_country_code` VARCHAR(2) NULL DEFAULT 'SA' AFTER `zatca_postal_code`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_registration_name` VARCHAR(255) NULL AFTER `zatca_country_code`;
ALTER TABLE `pos_customers` ADD COLUMN IF NOT EXISTS `zatca_cr_number` VARCHAR(100) NULL AFTER `zatca_registration_name`;
