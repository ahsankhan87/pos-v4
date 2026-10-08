# Supplier Ledger Accounting Fix - User Guide

## Overview

A controller method has been added to fix historical supplier ledger entries that were recorded with incorrect accounting logic. This allows you to correct all existing entries in your database with a single click.

## How to Use

### Step 1: Navigate to Fix Accounting Page

Open your browser and go to:

```
http://your-site.com/supplier-ledger/fix-accounting
```

### Step 2: Review the Information

The page displays:

- What will be done (swap DEBIT/CREDIT and recalculate balances)
- Important warnings (cannot be undone, requires backup)
- Expected results after the fix

### Step 3: Create a Database Backup (IMPORTANT)

Before proceeding, **create a backup** of your database:

**Using phpMyAdmin:**

1. Open phpMyAdmin
2. Select your database
3. Click "Export"
4. Click "Go" to download the SQL file
5. Save it in a safe location

**Using Command Line:**

```sql
mysqldump -u username -p database_name > backup.sql
```

### Step 4: Confirm and Execute

1. Check the confirmation checkbox ("I understand this will permanently fix...")
2. Click the "Fix Accounting Now" button
3. The system will:
   - Swap all DEBIT/CREDIT values
   - Recalculate all running balances
   - Update all entries in the database
   - Log the action in audit logs

### Step 5: Verify Results

After the fix completes:

1. Go to **Supplier Ledger** list
2. Click on a supplier to view their ledger
3. Verify that:
   - DEBIT values are now payments/returns (were previously purchases)
   - CREDIT values are now purchases (were previously payments)
   - Balances are calculated correctly (should be positive for unpaid invoices)
   - All balances recalculated properly in sequence

## What Gets Fixed

### Before (Incorrect):

- DEBIT = Purchases (increases what we owe)
- CREDIT = Payments (decreases what we owe)
- Balance Formula = DEBIT - CREDIT
- Negative balance = unpaid purchases (confusing!)

### After (Correct):

- DEBIT = Payments, Returns, Advances (decreases what we owe)
- CREDIT = Purchases (increases what we owe)
- Balance Formula = CREDIT - DEBIT
- Positive balance = amount owed to supplier ✓

## Important Notes

- **One-time operation:** Only run this once per database
- **No undo:** The fix cannot be reversed through the UI
- **Backup first:** Always backup your database before running
- **Admin only:** Only admin users can access this feature
- **All entries fixed:** All supplier ledger entries across all suppliers will be fixed at once
- **Running balances:** All balances are recalculated from the first entry to the last for each supplier

## Permissions Required

Users must have:

- Admin or Administrator role, OR
- Permission: `purchases.update`

## Troubleshooting

### The button is disabled

- Check the confirmation checkbox to enable it

### "Permission denied" error

- Log in as an admin user
- Contact your administrator if you need this permission

### Fix failed with error

- The database transaction was rolled back
- No data was changed
- Try again or contact support with the error message

### Need to restore from backup

If something goes wrong:

1. Restore the database from backup:
   ```sql
   mysql -u username -p database_name < backup.sql
   ```
2. Try the fix again

## Support

If you encounter any issues:

1. Check your backup is safe
2. Review error message details
3. Check audit logs for more information
4. Contact technical support with:
   - Error message (if any)
   - Number of entries in pos_supplier_ledger table
   - Database version

---

**Remember:** Always backup before running data corrections!
