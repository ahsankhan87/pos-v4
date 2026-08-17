# Migration SQL Scripts Guide

## Kasbook POS v4 - Migrations from 2026-07-18 to 2026-08-05

---

## 📋 Overview

This directory contains SQL migration scripts extracted from CodeIgniter 4 PHP migrations for the Kasbook POS v4 system. These scripts can be executed directly on your production MySQL database.

### **Coverage**

- **Date Range**: July 18, 2026 to August 5, 2026
- **Total Migrations**: 12 batches
- **Focus**: ZATCA e-invoicing integration, employee targets, certificate management, and audit logging

---

## 📁 File Structure

```
migrations/
├── migrations_2026-07-18_to_2026-08-05.sql     (Combined - RUN THIS FIRST)
├── 001_2026-07-18_CreateEmployeeCategoryTargetsTable.sql
├── 002_2026-07-30_AddZatcaSettingsColumns.sql
├── 003_2026-07-30_AddZatcaColumnsToSales.sql
├── 004_2026-07-30_CreateZatcaCertificatesTable.sql
├── 005_2026-07-30_CreateZatcaLogsTable.sql
├── 006_2026-07-30_AddVatNumberToCustomers.sql
├── 007_2026-08-01_AddZatcaInvoiceTypeToSales.sql
├── 008_2026-08-01_AddZatcaSellerFieldsToStores.sql
├── 009_2026-08-01_AddZatcaBuyerFieldsToCustomers.sql
├── 010_2026-08-02_AddProductionSecretToZatcaCertificates.sql
├── 011_2026-08-02_MigrateProductionSecretToNewColumn.sql
├── 012_2026-08-05_AddZatcaCreditNoteColumnsToSalesReturns.sql
└── README.md (this file)
```

---

## ⚡ Quick Start - Option 1: Run All Migrations (RECOMMENDED)

### Step 1: Backup Your Database

```bash
mysqldump -u username -p database_name > backup_2026-08-17.sql
```

### Step 2: Execute Combined SQL Script

```bash
mysql -u username -p database_name < migrations_2026-07-18_to_2026-08-05.sql
```

Or via MySQL Workbench/phpMyAdmin:

1. Open MySQL Workbench or phpMyAdmin
2. Select your **kasbook** database
3. Click **File → Open SQL Script** or **Import**
4. Select `migrations_2026-07-18_to_2026-08-05.sql`
5. Click **Execute** / **Run**

---

## 📝 Option 2: Run Individual Migrations

If you prefer to run migrations one at a time (useful for debugging):

```bash
# Run each script in order
mysql -u username -p database_name < migrations/001_2026-07-18_CreateEmployeeCategoryTargetsTable.sql
mysql -u username -p database_name < migrations/002_2026-07-30_AddZatcaSettingsColumns.sql
mysql -u username -p database_name < migrations/003_2026-07-30_AddZatcaColumnsToSales.sql
# ... continue with remaining scripts
```

---

## 🔍 What Each Migration Does

| #   | Date       | Name                                    | Purpose                                                    |
| --- | ---------- | --------------------------------------- | ---------------------------------------------------------- |
| 1   | 2026-07-18 | CreateEmployeeCategoryTargetsTable      | New table for employee category sales targets              |
| 2   | 2026-07-30 | AddZatcaSettingsColumns                 | Global ZATCA settings in settings table                    |
| 3   | 2026-07-30 | AddZatcaColumnsToSales                  | ZATCA tracking fields on invoices                          |
| 4   | 2026-07-30 | CreateZatcaCertificatesTable            | New table for ZATCA certificates (compliance & production) |
| 5   | 2026-07-30 | CreateZatcaLogsTable                    | New table for ZATCA audit logs                             |
| 6   | 2026-07-30 | AddVatNumberToCustomers                 | B2B customer VAT registration numbers                      |
| 7   | 2026-08-01 | AddZatcaInvoiceTypeToSales              | Invoice type tracking for ZATCA                            |
| 8   | 2026-08-01 | AddZatcaSellerFieldsToStores            | Store address details for ZATCA sellers                    |
| 9   | 2026-08-01 | AddZatcaBuyerFieldsToCustomers          | Customer address details for ZATCA compliance              |
| 10  | 2026-08-02 | AddProductionSecretToZatcaCertificates  | Separate production secret column                          |
| 11  | 2026-08-02 | MigrateProductionSecretToNewColumn      | Data migration for production secrets                      |
| 12  | 2026-08-05 | AddZatcaCreditNoteColumnsToSalesReturns | ZATCA credit note tracking on returns                      |

---

## 📊 Tables Affected

### New Tables Created:

- `pos_employee_category_targets` - Employee sales targets by category
- `pos_zatca_certificates` - ZATCA compliance and production certificates
- `pos_zatca_logs` - ZATCA operation audit logs

### Tables Modified:

- `settings` - Added 7 ZATCA configuration columns
- `pos_sales` - Added 9 ZATCA invoice tracking columns + 1 invoice type column
- `pos_customers` - Added VAT number + 8 ZATCA address fields
- `pos_stores` - Added 8 ZATCA seller address fields
- `pos_sales_returns` - Added 6 ZATCA credit note tracking columns

### Total Schema Changes:

- **New Tables**: 3
- **Modified Tables**: 5
- **New Columns**: 44
- **New Indexes**: 11+

---

## ✅ Verification Checklist

After running the migrations, verify the changes:

```sql
-- Check if new tables exist
SHOW TABLES LIKE 'pos_employee_category_targets';
SHOW TABLES LIKE 'pos_zatca_certificates';
SHOW TABLES LIKE 'pos_zatca_logs';

-- Verify columns on pos_sales
SHOW COLUMNS FROM pos_sales WHERE Field LIKE 'zatca%';

-- Verify columns on pos_customers
SHOW COLUMNS FROM pos_customers WHERE Field LIKE 'zatca%';

-- Verify columns on pos_stores
SHOW COLUMNS FROM pos_stores WHERE Field LIKE 'zatca%';

-- Verify settings table
SHOW COLUMNS FROM settings WHERE Field LIKE 'zatca%' OR Field LIKE 'einvoicing%';

-- Verify sales_returns
SHOW COLUMNS FROM pos_sales_returns WHERE Field LIKE 'zatca%';
```

---

## 🔐 Database Credentials Required

Before running any script, ensure you have:

- **MySQL Username** (usually `root` for local XAMPP)
- **MySQL Password**
- **Database Name** (typically `kasbook` for POS v4)
- **Host** (usually `localhost` for local, or your server IP)

---

## ⚠️ Important Notes

1. **Idempotent Scripts**: All scripts use `IF NOT EXISTS` / `IF NOT NULL` to prevent errors if columns/tables already exist
2. **Backup First**: Always backup your database before running migrations
3. **Run in Order**: Individual migrations must be run in the numbered order (001-012)
4. **Production Environment**: Test in staging/development first before applying to production
5. **User Permissions**: Ensure your MySQL user has `ALTER TABLE`, `CREATE TABLE`, and `UPDATE` privileges
6. **InnoDB Engine**: Migrations use InnoDB with UTF8MB4 collation (standard for modern MySQL)

---

## 🐛 Troubleshooting

### Error: "Table already exists"

- This is normal if migrations have been applied before; scripts use `IF NOT EXISTS` to handle this

### Error: "Unknown column"

- Verify the migration ran successfully before dependent migrations
- Check if the database schema is from an older version

### Error: "Access denied"

- Verify your MySQL username and password
- Ensure the user has ALTER/CREATE privileges on the database

### Syntax Errors

- Verify you're using MySQL 5.7+ or MariaDB 10.2+
- Check that the SQL file wasn't corrupted during transfer

---

## 📞 Support

For issues with these migrations:

1. Check the original PHP migration files in `app/Database/Migrations/`
2. Verify your MySQL/MariaDB version compatibility
3. Review the error message carefully for specific column/table names
4. Compare the current schema with the SQL statements to identify conflicts

---

## 📄 Related Files

- **PHP Migrations**: `app/Database/Migrations/2026-07-*` and `2026-08-*`
- **Database Config**: `app/Config/Database.php`
- **CodeIgniter Framework**: Version 4.x

---

## Version History

- **v1.0** - Created 2026-08-17
- **Coverage**: All migrations from July 18 to August 5, 2026
- **Format**: MySQL/MariaDB compatible SQL

---

**Database Name Used**: `kasbook` (update as needed)  
**Table Prefix Used**: `pos_` (as per Kasbook POS v4 convention)  
**Character Set**: UTF8MB4 Unicode (recommended for Arabic support)
